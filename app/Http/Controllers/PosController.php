<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\Contact;
use App\Models\ContactDeposit;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\PosShift;
use App\Models\PosShiftDrop;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\JournalService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /**
     * Display Point of Sale Cashier interface.
     */
    public function index(Request $request)
    {
        $branches = Branch::with(['cashAccount', 'depositAccount'])->where('is_active', true)->orderByDesc('is_main')->orderBy('name')->get();
        if ($branches->isEmpty()) {
            $branches = collect([
                Branch::firstOrCreate(
                    ['code' => 'HO'],
                    ['name' => 'Kantor Pusat', 'is_main' => true, 'is_active' => true]
                ),
            ]);
        }

        // Active Shift for current cashier user
        $activeShift = PosShift::with(['branch.cashAccount', 'branch.depositAccount', 'cashAccount', 'drops.toAccount', 'invoices'])
            ->where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        if ($activeShift) {
            $activeShift->recalculateTotals();
            $currentBranchId = $activeShift->branch_id;
        } else {
            $currentBranchId = $request->get('branch_id') ?: session('pos_branch_id') ?: $branches->first()->id;
        }

        session(['pos_branch_id' => $currentBranchId]);
        $currentBranch = $branches->firstWhere('id', $currentBranchId) ?: $branches->first();

        $categories = ItemCategory::orderBy('name')->get();

        // 1. Filter warehouses strictly by current branch first
        $branchWarehouses = Warehouse::where('is_active', true)
            ->where('branch_id', $currentBranchId)
            ->orderBy('id')
            ->get();

        if ($branchWarehouses->isNotEmpty()) {
            $warehouses = $branchWarehouses;
        } else {
            $warehouses = Warehouse::where('is_active', true)
                ->where(function ($q) use ($currentBranchId) {
                    $q->where('branch_id', $currentBranchId)
                        ->orWhereNull('branch_id');
                })
                ->orderBy('id')
                ->get();

            if ($warehouses->isEmpty()) {
                $warehouses = Warehouse::where('is_active', true)->orderBy('id')->get();
            }
        }

        $requestedWarehouseId = $request->get('warehouse_id') ?: session('pos_warehouse_id');
        $defaultWarehouse = $warehouses->firstWhere('id', $requestedWarehouseId) ?: $warehouses->first();
        if ($defaultWarehouse) {
            session(['pos_warehouse_id' => $defaultWarehouse->id]);
        }
        $warehouseIds = $warehouses->pluck('id')->toArray();

        // 2. Items that can be sold: not raw_material unless super_product, and available for this branch
        $itemsQuery = Item::with([
            'category',
            'unit',
            'unitConversions.unit',
            'branchPrices' => function ($q) use ($currentBranchId) {
                $q->where('branch_id', $currentBranchId);
            },
            'warehousePrices' => function ($q) use ($warehouseIds) {
                $q->whereIn('warehouse_id', $warehouseIds);
            },
        ])
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('item_type', '!=', 'raw_material')
                    ->orWhereNull('item_type')
                    ->orWhere('item_type', 'super_product');
            });

        // Filter out items explicitly marked unavailable for this branch
        $itemsQuery->whereDoesntHave('branchPrices', function ($q) use ($currentBranchId) {
            $q->where('branch_id', $currentBranchId)->where('is_available', false);
        });

        if ($request->has('category_id') && $request->category_id) {
            $itemsQuery->where('category_id', $request->category_id);
        }
        if ($request->has('search') && $request->search) {
            $s = $request->search;
            $itemsQuery->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('barcode', 'like', "%{$s}%");
            });
        }

        $items = $itemsQuery->orderBy('name')->get()->map(function ($item) use ($currentBranchId, $defaultWarehouse) {
            $bp = $item->branchPrices->firstWhere('branch_id', $currentBranchId);
            $item->branch_price = ($bp && $bp->selling_price !== null && $bp->selling_price > 0)
                ? (float) $bp->selling_price
                : (float) $item->selling_price;

            $item->branch_cost = ($bp && $bp->purchase_price !== null && $bp->purchase_price > 0)
                ? (float) $bp->purchase_price
                : (float) $item->purchase_price;

            $item->branch_margin = ($bp && $bp->min_margin_percent !== null)
                ? (float) $bp->min_margin_percent
                : $item->getEffectiveMinMarginPercent();

            $item->branch_min_price = ($item->branch_margin > 0 && $item->branch_cost > 0)
                ? round($item->branch_cost * (1 + ($item->branch_margin / 100)), 2)
                : 0;

            // Stock in current branch's default warehouse
            $item->branch_stock = ($defaultWarehouse && $item->isPhysical())
                ? $item->calculateStock($defaultWarehouse->id)
                : $item->current_stock;

            return $item;
        });

        $customers = Contact::customers()->where('is_active', true)->with('deposits')->orderBy('name')->get();

        // Ensure default walk-in customer exists
        $walkinCustomer = Contact::firstOrCreate(
            ['code' => 'CUST-WALKIN'],
            [
                'name' => 'Pelanggan Umum (Walk-in)',
                'type' => 'customer',
                'phone' => '-',
                'is_active' => true,
            ]
        );

        $cashAccounts = ChartOfAccount::where('is_cash_or_bank', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        if ($cashAccounts->isEmpty()) {
            $cashAccounts = ChartOfAccount::whereIn('code', ['1001', '1002', '1003'])
                ->where('is_active', true)
                ->orderBy('code')
                ->get();
        }

        return view('pos.index', compact(
            'branches',
            'currentBranch',
            'currentBranchId',
            'activeShift',
            'categories',
            'warehouses',
            'defaultWarehouse',
            'items',
            'customers',
            'walkinCustomer',
            'cashAccounts'
        ));
    }

    /**
     * Switch current working branch in POS session.
     */
    public function switchBranch(Request $request)
    {
        $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $activeShift = PosShift::where('user_id', auth()->id())->where('status', 'open')->first();
        if ($activeShift && $activeShift->branch_id != $request->branch_id) {
            return back()->with('error', 'Anda masih memiliki Shift Kasir aktif di cabang '.($activeShift->branch->name ?? '').'. Mohon lakukan Tutup Kasir terlebih dahulu sebelum berganti cabang.');
        }

        session(['pos_branch_id' => $request->branch_id]);

        return redirect()->route('pos.index')->with('success', 'Cabang POS berhasil dialihkan.');
    }

    /**
     * Open Cash Register / Shift.
     */
    public function openShift(Request $request)
    {
        $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'cash_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'starting_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $existingShift = PosShift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return back()->with('error', 'Anda sudah memiliki Shift Kasir aktif ('.$existingShift->shift_number.').');
        }

        $branch = Branch::with('cashAccount')->findOrFail($request->branch_id);

        // Take cash account from branch setting first, fallback to request or default COA
        $cashAccountId = $branch->cash_account_id ?: $request->cash_account_id;
        if (! $cashAccountId) {
            $defaultCoa = ChartOfAccount::where('is_cash_or_bank', true)->where('is_active', true)->first();
            $cashAccountId = $defaultCoa ? $defaultCoa->id : null;
        }

        $shiftNumber = 'SHIFT-'.date('Ymd').'-'.str_pad(PosShift::count() + 1, 4, '0', STR_PAD_LEFT);
        $startingCash = (float) $request->starting_cash;

        $shift = PosShift::create([
            'branch_id' => $branch->id,
            'user_id' => auth()->id(),
            'cash_account_id' => $cashAccountId,
            'shift_number' => $shiftNumber,
            'start_time' => now(),
            'starting_cash' => $startingCash,
            'closing_cash_expected' => $startingCash,
            'status' => 'open',
            'notes' => $request->notes,
        ]);

        session(['pos_branch_id' => $request->branch_id]);

        return redirect()->route('pos.index')->with('success', 'Kasir berhasil dibuka! Shift No: '.$shift->shift_number);
    }

    /**
     * Cash Drop / Setoran Kas Kasir ke Bank / Kas Utama.
     */
    public function dropCash(Request $request, JournalService $journalService)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'to_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $activeShift = PosShift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        if (! $activeShift) {
            return back()->with('error', 'Tidak ada Shift Kasir aktif untuk melakukan setoran kas.');
        }

        $amount = (float) $request->amount;
        $fromAccId = $activeShift->cash_account_id;
        $toAccId = $request->to_account_id;

        if ($fromAccId == $toAccId) {
            return back()->with('error', 'Rekening Kas/Bank tujuan setoran tidak boleh sama dengan Kas Laci Kasir.');
        }

        try {
            DB::transaction(function () use ($request, $activeShift, $amount, $fromAccId, $toAccId, $journalService) {
                $refNo = 'DROP-'.date('Ymd').'-'.str_pad(PosShiftDrop::count() + 1, 4, '0', STR_PAD_LEFT);
                $fromAcc = ChartOfAccount::find($fromAccId);
                $toAcc = ChartOfAccount::find($toAccId);

                $journalEntries = [
                    [
                        'account_id' => $toAccId,
                        'type' => 'debit',
                        'amount' => $amount,
                        'memo' => "Penerimaan Setoran Kasir {$activeShift->shift_number} ke {$toAcc->name}",
                    ],
                    [
                        'account_id' => $fromAccId,
                        'type' => 'credit',
                        'amount' => $amount,
                        'memo' => "Setoran Kas Kasir {$activeShift->shift_number} dari {$fromAcc->name}",
                    ],
                ];

                $journal = $journalService->createJournal(
                    'CASH_TRANSFER',
                    $activeShift->id,
                    $refNo,
                    "Setoran Kas POS {$activeShift->shift_number} ({$fromAcc->name} -> {$toAcc->name})",
                    $journalEntries,
                    date('Y-m-d'),
                    auth()->id()
                );

                PosShiftDrop::create([
                    'pos_shift_id' => $activeShift->id,
                    'user_id' => auth()->id(),
                    'from_account_id' => $fromAccId,
                    'to_account_id' => $toAccId,
                    'amount' => $amount,
                    'drop_time' => now(),
                    'notes' => $request->notes,
                    'journal_id' => $journal->id,
                ]);

                $activeShift->recalculateTotals();
            });

            return redirect()->route('pos.index')->with('success', 'Setoran Kas sebesar Rp '.number_format($amount, 0, ',', '.').' berhasil dibukukan.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses setoran kas: '.$e->getMessage());
        }
    }

    /**
     * Close Cash Register / Shift & Calculate Balance.
     */
    public function closeShift(Request $request, JournalService $journalService)
    {
        $request->validate([
            'closing_cash_actual' => ['required', 'numeric', 'min:0'],
            'deposit_to_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $activeShift = PosShift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        if (! $activeShift) {
            return back()->with('error', 'Tidak ada Shift Kasir aktif yang dapat ditutup.');
        }

        try {
            DB::transaction(function () use ($request, $activeShift, $journalService) {
                $activeShift->recalculateTotals();

                $actualCash = (float) $request->closing_cash_actual;
                $expectedCash = (float) $activeShift->closing_cash_expected;
                $diff = $actualCash - $expectedCash;

                $activeShift->update([
                    'closing_cash_actual' => $actualCash,
                    'cash_difference' => $diff,
                    'end_time' => now(),
                    'status' => 'closed',
                    'notes' => $request->notes,
                ]);

                // If cashier also deposits the final remaining cash into a bank/main vault
                if ($request->filled('deposit_to_account_id') && $actualCash > 0) {
                    $toAccId = $request->deposit_to_account_id;
                    $fromAccId = $activeShift->cash_account_id;

                    if ($fromAccId != $toAccId) {
                        $refNo = 'DROP-'.date('Ymd').'-'.str_pad(PosShiftDrop::count() + 1, 4, '0', STR_PAD_LEFT);
                        $fromAcc = ChartOfAccount::find($fromAccId);
                        $toAcc = ChartOfAccount::find($toAccId);

                        $journalEntries = [
                            [
                                'account_id' => $toAccId,
                                'type' => 'debit',
                                'amount' => $actualCash,
                                'memo' => "Setoran Tutup Kasir {$activeShift->shift_number} ke {$toAcc->name}",
                            ],
                            [
                                'account_id' => $fromAccId,
                                'type' => 'credit',
                                'amount' => $actualCash,
                                'memo' => "Setoran Tutup Kasir {$activeShift->shift_number} dari {$fromAcc->name}",
                            ],
                        ];

                        $journal = $journalService->createJournal(
                            'CASH_TRANSFER',
                            $activeShift->id,
                            $refNo,
                            "Setoran Akhir Tutup Kasir POS {$activeShift->shift_number}",
                            $journalEntries,
                            date('Y-m-d'),
                            auth()->id()
                        );

                        PosShiftDrop::create([
                            'pos_shift_id' => $activeShift->id,
                            'user_id' => auth()->id(),
                            'from_account_id' => $fromAccId,
                            'to_account_id' => $toAccId,
                            'amount' => $actualCash,
                            'drop_time' => now(),
                            'notes' => 'Setoran Akhir Tutup Shift Kasir',
                            'journal_id' => $journal->id,
                        ]);
                    }
                }
            });

            return redirect()->route('pos.shift.receipt', $activeShift->id)->with('success', 'Tutup Kasir berhasil diproses.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menutup kasir: '.$e->getMessage());
        }
    }

    /**
     * Display printable shift closing summary / Z-Report receipt.
     */
    public function shiftReceipt(PosShift $shift)
    {
        $shift->load(['branch', 'user', 'cashAccount', 'drops.toAccount', 'invoices.customer']);
        $shift->recalculateTotals();

        return view('pos.shift_receipt', compact('shift'));
    }

    /**
     * Process POS Checkout transaction.
     */
    public function store(Request $request, InventoryService $inventoryService, JournalService $journalService)
    {
        $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'customer_id' => ['nullable', 'exists:contacts,id'],
            'payment_method' => ['nullable', 'in:cash,transfer,qris,deposit,split'],
            'account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.koli' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'payments' => ['nullable', 'array'],
            'payments.*.payment_method' => ['nullable', 'in:cash,transfer,qris,deposit'],
            'payments.*.account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'payments.*.amount' => ['nullable', 'numeric', 'min:0'],
            'payments.*.reference_no' => ['nullable', 'string', 'max:100'],
        ]);

        $customer = $request->customer_id ? Contact::find($request->customer_id) : null;
        $custMargin = $customer?->min_margin_percent;

        // Validate Minimum Margin Limit for Physical Items (Services are exempt)
        foreach ($request->items as $itemData) {
            $item = Item::with('category')->find($itemData['item_id']);
            if ($item && $item->isPhysical() && ! $item->isBundle()) {
                $minPrice = $item->getMinimumSellingPrice($custMargin);
                $unitPrice = (float) $itemData['unit_price'];
                if ($minPrice > 0 && $unitPrice < $minPrice) {
                    $margin = $custMargin !== null ? (float) $custMargin : $item->getEffectiveMinMarginPercent();
                    $cost = (float) $item->purchase_price;
                    $msg = "Harga jual '{$item->name}' (Rp ".number_format($unitPrice, 0, ',', '.').") di bawah batas minimum margin {$margin}% (Harga minimal: Rp ".number_format($minPrice, 0, ',', '.').', HPP: Rp '.number_format($cost, 0, ',', '.').').';
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $msg,
                        ], 422);
                    }

                    return back()->with('error', $msg)->withInput();
                }
            }
        }

        try {
            $invoice = DB::transaction(function () use ($request, $inventoryService, $journalService) {
                $warehouseId = (int) $request->warehouse_id;
                $customerId = $request->customer_id;

                if (! $customerId) {
                    $walkin = Contact::firstOrCreate(
                        ['code' => 'CUST-WALKIN'],
                        [
                            'name' => 'Pelanggan Umum (Walk-in)',
                            'type' => 'customer',
                            'phone' => '-',
                            'is_active' => true,
                        ]
                    );
                    $customerId = $walkin->id;
                }

                $customer = Contact::findOrFail($customerId);

                // Active Shift
                $activeShift = PosShift::where('user_id', auth()->id())
                    ->where('status', 'open')
                    ->latest()
                    ->first();

                if (! $activeShift) {
                    throw new Exception('Sesi Kasir belum dibuka. Silakan Buka Kasir (Start Shift) terlebih dahulu sebelum memproses transaksi.');
                }

                $branchId = $activeShift->branch_id;

                $subtotal = 0;
                foreach ($request->items as $itemData) {
                    $subtotal += ($itemData['quantity'] * $itemData['unit_price']);
                }

                $discountAmount = (float) ($request->discount_amount ?? 0);
                $taxAmount = (float) ($request->tax_amount ?? 0);
                $grandTotal = max(0, round($subtotal - $discountAmount + $taxAmount, 2));

                // Process Payment Breakdown (Multi-Payment / Split Bill or Single Payment)
                $payments = [];
                if ($request->has('payments') && is_array($request->payments) && count($request->payments) > 0) {
                    foreach ($request->payments as $p) {
                        $pAmt = (float) ($p['amount'] ?? 0);
                        if ($pAmt > 0) {
                            $payments[] = [
                                'payment_method' => $p['payment_method'] ?? 'cash',
                                'account_id' => ! empty($p['account_id']) ? (int) $p['account_id'] : null,
                                'amount' => $pAmt,
                                'reference_no' => $p['reference_no'] ?? null,
                            ];
                        }
                    }
                }

                // Fallback to single payment method if payments array is empty
                if (empty($payments)) {
                    $method = $request->payment_method ?: 'cash';
                    $paidAmt = $method === 'deposit' ? $grandTotal : (float) ($request->paid_amount ?? $grandTotal);
                    $payments[] = [
                        'payment_method' => $method,
                        'account_id' => $request->account_id ? (int) $request->account_id : null,
                        'amount' => $paidAmt,
                        'reference_no' => $request->reference_no ?? null,
                    ];
                }

                $totalPaid = array_sum(array_column($payments, 'amount'));
                if ($totalPaid < $grandTotal) {
                    throw new Exception('Total pembayaran (Rp '.number_format($totalPaid, 0, ',', '.').') kurang dari total tagihan (Rp '.number_format($grandTotal, 0, ',', '.').').');
                }

                // Check deposit balance if customer deposit is used
                $totalDepositUsed = 0;
                foreach ($payments as $p) {
                    if ($p['payment_method'] === 'deposit') {
                        $totalDepositUsed += $p['amount'];
                    }
                }

                if ($totalDepositUsed > 0) {
                    $depositBalance = $customer->getDepositBalance();
                    if ($depositBalance < $totalDepositUsed) {
                        throw new Exception("Saldo deposit pelanggan ({$customer->name}) tidak mencukupi (Saldo: Rp ".number_format($depositBalance, 0, ',', '.').', Digunakan: Rp '.number_format($totalDepositUsed, 0, ',', '.').').');
                    }
                }

                $changeAmount = max(0, round($totalPaid - $grandTotal, 2));
                $invoiceNumber = 'POS-'.date('Ymd').'-'.str_pad(SalesInvoice::count() + 1, 4, '0', STR_PAD_LEFT);

                // Build payment notes & enriched payment details
                $paymentDescriptions = [];
                $enrichedPaymentDetails = [];
                foreach ($payments as $p) {
                    $methodLabel = match ($p['payment_method']) {
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer Bank',
                        'qris' => 'QRIS',
                        'deposit' => 'Saldo Deposit',
                        default => ucfirst($p['payment_method']),
                    };

                    $accName = '';
                    if (! empty($p['account_id'])) {
                        $acc = ChartOfAccount::find($p['account_id']);
                        if ($acc) {
                            $accName = ' ('.$acc->name.')';
                        }
                    }

                    $desc = $methodLabel.$accName.': Rp '.number_format($p['amount'], 0, ',', '.');
                    if (! empty($p['reference_no'])) {
                        $desc .= ' [Ref: '.$p['reference_no'].']';
                    }
                    $paymentDescriptions[] = $desc;

                    $enrichedPaymentDetails[] = [
                        'payment_method' => $p['payment_method'],
                        'payment_method_label' => $methodLabel,
                        'account_id' => $p['account_id'],
                        'account_name' => $accName ? trim($accName, ' ()') : null,
                        'amount' => $p['amount'],
                        'reference_no' => $p['reference_no'] ?? null,
                    ];
                }

                $notesText = 'POS: '.implode(' + ', $paymentDescriptions);
                if ($changeAmount > 0) {
                    $notesText .= ' | Kembali: Rp '.number_format($changeAmount, 0, ',', '.');
                }

                // Create Sales Invoice record
                $invoice = SalesInvoice::create([
                    'branch_id' => $branchId,
                    'pos_shift_id' => $activeShift?->id,
                    'invoice_number' => $invoiceNumber,
                    'customer_id' => $customerId,
                    'invoice_date' => date('Y-m-d'),
                    'due_date' => date('Y-m-d'),
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'grand_total' => $grandTotal,
                    'paid_amount' => $grandTotal,
                    'status' => 'paid',
                    'created_by_id' => auth()->id(),
                    'payment_details' => $enrichedPaymentDetails,
                    'notes' => $notesText,
                ]);

                // Default accounts
                $defaultSalesAcc = ChartOfAccount::where('code', '4001')->first() ?: ChartOfAccount::where('type', 'revenue')->first();
                $defaultCogsAcc = ChartOfAccount::where('code', '5001')->first() ?: ChartOfAccount::where('type', 'expense')->first();
                $defaultInvAcc = ChartOfAccount::where('code', '1201')->first() ?: ChartOfAccount::where('type', 'asset')->first();
                $depositLiabilityAcc = ChartOfAccount::where('code', '2004')->first() ?: ChartOfAccount::where('type', 'liability')->first();
                $defaultCashAcc = ChartOfAccount::where('is_cash_or_bank', true)->first();

                $revenueDebits = [];
                $cogsBreakdown = [];

                // Process Line Items & Inventory
                foreach ($request->items as $itemData) {
                    $item = Item::with(['category', 'boms.items.item'])->findOrFail($itemData['item_id']);
                    $qty = (float) $itemData['quantity'];
                    $price = (float) $itemData['unit_price'];
                    $lineTotal = round($qty * $price, 2);

                    SalesInvoiceItem::create([
                        'sales_invoice_id' => $invoice->id,
                        'item_id' => $item->id,
                        'quantity' => $qty,
                        'koli' => ! empty($itemData['koli']) ? (int) $itemData['koli'] : null,
                        'unit_price' => $price,
                        'subtotal' => $lineTotal,
                    ]);

                    // If item is BUNDLE: Auto deduct constituent materials via BOM
                    if ($item->isBundle()) {
                        $bundleResult = $inventoryService->deductBundleComponents(
                            $item,
                            $warehouseId,
                            $qty,
                            'POS_BUNDLE_SALE',
                            $invoice->id,
                            $invoice->invoice_number,
                            date('Y-m-d')
                        );

                        foreach ($bundleResult['components'] as $comp) {
                            $cogsAccId = $comp['cogs_account_id'] ?: ($defaultCogsAcc ? $defaultCogsAcc->id : null);
                            $invAccId = $comp['inventory_account_id'] ?: ($defaultInvAcc ? $defaultInvAcc->id : null);
                            $compCost = (float) $comp['total_cost'];

                            if ($cogsAccId && $invAccId && $compCost > 0) {
                                $key = "{$cogsAccId}_{$invAccId}";
                                if (! isset($cogsBreakdown[$key])) {
                                    $cogsBreakdown[$key] = [
                                        'cogs_account_id' => $cogsAccId,
                                        'inventory_account_id' => $invAccId,
                                        'amount' => 0,
                                    ];
                                }
                                $cogsBreakdown[$key]['amount'] += $compCost;
                            }
                        }
                    } elseif ($item->isPhysical()) {
                        $cost = (float) $item->purchase_price;
                        $lineCost = round($qty * $cost, 2);

                        $inventoryService->recordStockMovement(
                            $item->id,
                            $warehouseId,
                            'POS_SALE',
                            $invoice->id,
                            $invoice->invoice_number,
                            'out',
                            $qty,
                            $cost,
                            'Penjualan Kasir POS '.$invoice->invoice_number,
                            date('Y-m-d')
                        );

                        $cogsAccId = $item->getEffectiveCogsAccountId() ?: ($defaultCogsAcc ? $defaultCogsAcc->id : null);
                        $invAccId = $item->getEffectiveInventoryAccountId() ?: ($defaultInvAcc ? $defaultInvAcc->id : null);

                        if ($cogsAccId && $invAccId && $lineCost > 0) {
                            $key = "{$cogsAccId}_{$invAccId}";
                            if (! isset($cogsBreakdown[$key])) {
                                $cogsBreakdown[$key] = [
                                    'cogs_account_id' => $cogsAccId,
                                    'inventory_account_id' => $invAccId,
                                    'amount' => 0,
                                ];
                            }
                            $cogsBreakdown[$key]['amount'] += $lineCost;
                        }
                    }

                    // Revenue mapping
                    $salesAccId = $item->getEffectiveSalesAccountId() ?: ($defaultSalesAcc ? $defaultSalesAcc->id : null);
                    if ($salesAccId) {
                        $revenueDebits[$salesAccId] = ($revenueDebits[$salesAccId] ?? 0) + $lineTotal;
                    }
                }

                // Process Customer Deposits deductions & Journal Debits for all payment rows
                $journalEntries = [];
                $remainingChangeToAbsorb = $changeAmount;

                foreach ($payments as $p) {
                    $pMethod = $p['payment_method'];
                    $pAmount = (float) $p['amount'];

                    if ($pMethod === 'deposit') {
                        // Deduct customer deposit
                        ContactDeposit::create([
                            'contact_id' => $customer->id,
                            'type' => 'customer',
                            'amount' => $pAmount,
                            'transaction_type' => 'out',
                            'reference_type' => 'POS_INVOICE',
                            'reference_id' => $invoice->id,
                            'reference_no' => $invoice->invoice_number,
                            'notes' => 'Pembayaran Kasir POS '.$invoice->invoice_number,
                        ]);

                        $accId = $depositLiabilityAcc ? $depositLiabilityAcc->id : null;
                        $memo = 'Penerimaan Deposit POS '.$invoice->invoice_number;
                    } else {
                        // Cash, Transfer, QRIS
                        if (! empty($p['account_id'])) {
                            $accId = $p['account_id'];
                        } elseif ($pMethod === 'cash' && $activeShift && $activeShift->cash_account_id) {
                            $accId = $activeShift->cash_account_id;
                        } else {
                            $accId = $defaultCashAcc ? $defaultCashAcc->id : null;
                        }

                        $methodLabel = match ($pMethod) {
                            'cash' => 'Tunai',
                            'transfer' => 'Transfer Bank',
                            'qris' => 'QRIS',
                            default => ucfirst($pMethod),
                        };
                        $memo = "Penerimaan {$methodLabel} POS {$invoice->invoice_number}";
                    }

                    // Net amount debited to journal (absorb change from cash if applicable)
                    $netDebit = $pAmount;
                    if ($pMethod === 'cash' && $remainingChangeToAbsorb > 0) {
                        $absorb = min($netDebit, $remainingChangeToAbsorb);
                        $netDebit -= $absorb;
                        $remainingChangeToAbsorb -= $absorb;
                    }

                    if ($accId && $netDebit > 0) {
                        $journalEntries[] = [
                            'account_id' => $accId,
                            'type' => 'debit',
                            'amount' => $netDebit,
                            'memo' => $memo,
                        ];
                    }
                }

                if ($discountAmount > 0) {
                    $discountAcc = ChartOfAccount::where('code', '4002')->first() ?: $defaultSalesAcc;
                    if ($discountAcc) {
                        $journalEntries[] = [
                            'account_id' => $discountAcc->id,
                            'type' => 'debit',
                            'amount' => $discountAmount,
                            'memo' => 'Potongan Diskon POS '.$invoice->invoice_number,
                        ];
                    }
                }

                if ($taxAmount > 0) {
                    $taxAcc = ChartOfAccount::where('code', '2002')->first() ?: ChartOfAccount::where('type', 'liability')->first();
                    if ($taxAcc) {
                        $journalEntries[] = [
                            'account_id' => $taxAcc->id,
                            'type' => 'credit',
                            'amount' => $taxAmount,
                            'memo' => 'PPN Keluaran POS '.$invoice->invoice_number,
                        ];
                    }
                }

                foreach ($revenueDebits as $accId => $amt) {
                    if ($amt > 0) {
                        $journalEntries[] = [
                            'account_id' => $accId,
                            'type' => 'credit',
                            'amount' => $amt,
                            'memo' => 'Pendapatan Penjualan POS '.$invoice->invoice_number,
                        ];
                    }
                }

                // COA & HPP Journal Entries
                foreach ($cogsBreakdown as $cogsData) {
                    $journalEntries[] = [
                        'account_id' => $cogsData['cogs_account_id'],
                        'type' => 'debit',
                        'amount' => $cogsData['amount'],
                        'memo' => 'Beban Pokok Penjualan (HPP) POS '.$invoice->invoice_number,
                    ];
                    $journalEntries[] = [
                        'account_id' => $cogsData['inventory_account_id'],
                        'type' => 'credit',
                        'amount' => $cogsData['amount'],
                        'memo' => 'Pengeluaran Persediaan POS '.$invoice->invoice_number,
                    ];
                }

                if (! empty($journalEntries)) {
                    $journal = $journalService->createJournal(
                        'POS_SALE',
                        $invoice->id,
                        $invoice->invoice_number,
                        'Penjualan Kasir POS '.$invoice->invoice_number.' - '.$invoice->customer->name,
                        $journalEntries,
                        date('Y-m-d'),
                        auth()->id()
                    );
                    $invoice->update(['journal_id' => $journal->id]);
                }

                if ($activeShift) {
                    $activeShift->recalculateTotals();
                }

                return $invoice;
            });

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi POS berhasil diproses!',
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'receipt_url' => route('pos.receipt', $invoice->id),
                ]);
            }

            return redirect()->route('pos.receipt', $invoice->id)->with('success', 'Transaksi Kasir POS Berhasil!');
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Gagal memproses transaksi: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Display printable thermal receipt for POS sale.
     */
    public function receipt(SalesInvoice $invoice)
    {
        $invoice->load(['branch', 'customer', 'items.item.unit', 'createdBy']);

        return view('pos.receipt', compact('invoice'));
    }
}
