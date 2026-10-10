<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Contact;
use App\Models\Item;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\JournalService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BeginningBalanceController extends Controller
{
    /**
     * Display Beginning Balances Center with 4 tabs:
     * 1. Neraca Saldo Awal Akun (COA)
     * 2. Saldo Awal Stok Barang
     * 3. Saldo Awal Piutang Usaha (AR)
     * 4. Saldo Awal Hutang Usaha (AP)
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'inventory');

        // 1. COA
        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();

        // 2. Inventory Items & Warehouses
        $items = Item::with(['category', 'unit'])->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        // 3. Customers
        $customers = Contact::customers()->where('is_active', true)->orderBy('name')->get();

        // 4. Suppliers
        $suppliers = Contact::suppliers()->where('is_active', true)->orderBy('name')->get();

        return view('accounting.beginning_balances.index', compact(
            'tab',
            'accounts',
            'items',
            'warehouses',
            'customers',
            'suppliers'
        ));
    }

    /**
     * Store Opening Inventory Stock with Journal:
     * Debit: Persediaan Barang (1201)
     * Credit: Modal Saldo Awal (3001)
     */
    public function storeInventory(Request $request, InventoryService $inventoryService, JournalService $journalService)
    {
        $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'transaction_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $recordedCount = 0;
        $totalCostAll = 0;
        $inventoryDebits = []; // [inv_acc_id => amount]

        DB::transaction(function () use ($request, $inventoryService, $journalService, &$recordedCount, &$totalCostAll, &$inventoryDebits) {
            $defaultInvAcc = ChartOfAccount::where('code', '1201')->first() ?: ChartOfAccount::where('type', 'asset')->first();
            $equityAcc = ChartOfAccount::where('code', '3001')->first() ?: ChartOfAccount::where('type', 'equity')->first();

            foreach ($request->items as $row) {
                $qty = (float) $row['quantity'];
                $cost = (float) $row['unit_cost'];

                if ($qty <= 0) {
                    continue;
                }

                $item = Item::with('category')->findOrFail($row['item_id']);
                if (! $item->isPhysical()) {
                    continue;
                }

                $lineTotal = round($qty * $cost, 2);
                $totalCostAll += $lineTotal;

                // Update purchase price HPP
                $item->update(['purchase_price' => $cost]);

                // Record stock ledger
                $inventoryService->recordStockMovement(
                    $item->id,
                    (int) $request->warehouse_id,
                    'OPENING_STOCK',
                    null,
                    'SALDO-AWAL-'.date('Ymd'),
                    'in',
                    $qty,
                    $cost,
                    'Saldo Awal Persediaan Barang - '.$item->name,
                    $request->transaction_date
                );

                $invAccId = $item->getEffectiveInventoryAccountId() ?: ($defaultInvAcc ? $defaultInvAcc->id : null);
                if ($invAccId) {
                    $inventoryDebits[$invAccId] = ($inventoryDebits[$invAccId] ?? 0) + $lineTotal;
                }

                $recordedCount++;
            }

            // Create Balancing Journal
            if ($totalCostAll > 0 && $equityAcc) {
                $journalEntries = [];
                foreach ($inventoryDebits as $accId => $amt) {
                    $journalEntries[] = [
                        'account_id' => $accId,
                        'type' => 'debit',
                        'amount' => $amt,
                        'memo' => 'Saldo Awal Persediaan Barang',
                    ];
                }

                $journalEntries[] = [
                    'account_id' => $equityAcc->id,
                    'type' => 'credit',
                    'amount' => $totalCostAll,
                    'memo' => 'Modal Saldo Awal Persediaan Barang',
                ];

                $journalService->createJournal(
                    'OPENING_BALANCE',
                    null,
                    'OB-STOK-'.date('Ymd'),
                    'Jurnal Saldo Awal Persediaan Barang - '.Warehouse::find($request->warehouse_id)->name,
                    $journalEntries,
                    $request->transaction_date,
                    auth()->id()
                );
            }
        });

        if ($recordedCount === 0) {
            return back()->with('error', 'Tidak ada item dengan kuantitas > 0 yang disimpan.');
        }

        return redirect()->route('accounting.beginning-balances.index', ['tab' => 'inventory'])
            ->with('success', "Berhasil mencatat {$recordedCount} item Saldo Awal Stok senilai Rp ".number_format($totalCostAll, 0, ',', '.').' dengan jurnal otomatis lawan Modal Ekuitas (3001).');
    }

    /**
     * Store Opening Accounts Receivable (Piutang Usaha)
     * Debit: Piutang Usaha (1101)
     * Credit: Modal Saldo Awal (3001)
     */
    public function storeAr(Request $request, JournalService $journalService)
    {
        $request->validate([
            'customer_id' => ['required', 'exists:contacts,id'],
            'invoice_number' => ['required', 'string'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $journalService) {
            $amount = (float) $request->amount;
            $customer = Contact::findOrFail($request->customer_id);

            // Create Sales Invoice for AR tracking
            $invoice = SalesInvoice::create([
                'invoice_number' => $request->invoice_number,
                'customer_id' => $customer->id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'subtotal' => $amount,
                'tax_amount' => 0,
                'grand_total' => $amount,
                'paid_amount' => 0,
                'status' => 'unpaid',
                'created_by_id' => auth()->id(),
                'notes' => 'Saldo Awal Piutang: '.($request->notes ?: 'Faktur sebelum sistem live'),
            ]);

            // Create a general service line item
            $serviceItem = Item::where('item_type', 'service')->first() ?: Item::first();
            if ($serviceItem) {
                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'item_id' => $serviceItem->id,
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'subtotal' => $amount,
                ]);
            }

            // Journal
            // Debit: Piutang Usaha (1101)
            // Credit: Modal Saldo Awal (3001)
            $arAcc = ChartOfAccount::where('code', '1101')->first();
            $equityAcc = ChartOfAccount::where('code', '3001')->first() ?: ChartOfAccount::where('type', 'equity')->first();

            if ($arAcc && $equityAcc) {
                $journal = $journalService->createJournal(
                    'OPENING_AR',
                    $invoice->id,
                    $invoice->invoice_number,
                    'Saldo Awal Piutang '.$invoice->invoice_number.' - '.$customer->name,
                    [
                        [
                            'account_id' => $arAcc->id,
                            'type' => 'debit',
                            'amount' => $amount,
                            'memo' => 'Saldo Awal Piutang '.$customer->name,
                        ],
                        [
                            'account_id' => $equityAcc->id,
                            'type' => 'credit',
                            'amount' => $amount,
                            'memo' => 'Modal Saldo Awal Piutang Usaha',
                        ],
                    ],
                    $request->invoice_date,
                    auth()->id()
                );
                $invoice->update(['journal_id' => $journal->id]);
            }
        });

        return redirect()->route('accounting.beginning-balances.index', ['tab' => 'ar'])
            ->with('success', 'Faktur Saldo Awal Piutang berhasil disimpan dan dijurnal ke Piutang Usaha (1101) lawan Modal (3001).');
    }

    /**
     * Store Opening Accounts Payable (Hutang Usaha)
     * Debit: Modal Saldo Awal (3001)
     * Credit: Hutang Usaha (2001)
     */
    public function storeAp(Request $request, JournalService $journalService)
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:contacts,id'],
            'invoice_number' => ['required', 'string'],
            'supplier_invoice_number' => ['nullable', 'string'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $journalService) {
            $amount = (float) $request->amount;
            $supplier = Contact::findOrFail($request->supplier_id);

            $invoice = PurchaseInvoice::create([
                'invoice_number' => $request->invoice_number,
                'supplier_invoice_number' => $request->supplier_invoice_number,
                'supplier_id' => $supplier->id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'subtotal' => $amount,
                'tax_amount' => 0,
                'grand_total' => $amount,
                'paid_amount' => 0,
                'status' => 'unpaid',
                'created_by_id' => auth()->id(),
                'notes' => 'Saldo Awal Hutang: '.($request->notes ?: 'Tagihan sebelum sistem live'),
            ]);

            $serviceItem = Item::where('item_type', 'service')->first() ?: Item::first();
            if ($serviceItem) {
                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'item_id' => $serviceItem->id,
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'subtotal' => $amount,
                ]);
            }

            // Journal
            // Debit: Modal Saldo Awal (3001)
            // Credit: Hutang Usaha (2001)
            $apAcc = ChartOfAccount::where('code', '2001')->first();
            $equityAcc = ChartOfAccount::where('code', '3001')->first() ?: ChartOfAccount::where('type', 'equity')->first();

            if ($apAcc && $equityAcc) {
                $journal = $journalService->createJournal(
                    'OPENING_AP',
                    $invoice->id,
                    $invoice->invoice_number,
                    'Saldo Awal Hutang '.$invoice->invoice_number.' - '.$supplier->name,
                    [
                        [
                            'account_id' => $equityAcc->id,
                            'type' => 'debit',
                            'amount' => $amount,
                            'memo' => 'Modal Saldo Awal Hutang Usaha',
                        ],
                        [
                            'account_id' => $apAcc->id,
                            'type' => 'credit',
                            'amount' => $amount,
                            'memo' => 'Saldo Awal Hutang Usaha '.$supplier->name,
                        ],
                    ],
                    $request->invoice_date,
                    auth()->id()
                );
                $invoice->update(['journal_id' => $journal->id]);
            }
        });

        return redirect()->route('accounting.beginning-balances.index', ['tab' => 'ap'])
            ->with('success', 'Tagihan Saldo Awal Hutang berhasil disimpan dan dijurnal ke Modal (3001) lawan Hutang Usaha (2001).');
    }

    /**
     * Store Opening COA Journal (Neraca Saldo Awal)
     */
    public function storeCoa(Request $request, JournalService $journalService)
    {
        $request->validate([
            'transaction_date' => ['required', 'date'],
            'balances' => ['required', 'array'],
        ]);

        $entries = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($request->balances as $accId => $vals) {
            $debit = (float) ($vals['debit'] ?? 0);
            $credit = (float) ($vals['credit'] ?? 0);

            if ($debit > 0) {
                $entries[] = [
                    'account_id' => (int) $accId,
                    'type' => 'debit',
                    'amount' => $debit,
                    'memo' => 'Saldo Awal Akun COA',
                ];
                $totalDebit += $debit;
            }

            if ($credit > 0) {
                $entries[] = [
                    'account_id' => (int) $accId,
                    'type' => 'credit',
                    'amount' => $credit,
                    'memo' => 'Saldo Awal Akun COA',
                ];
                $totalCredit += $credit;
            }
        }

        if (count($entries) < 2) {
            return back()->with('error', 'Minimal harus mengisi 2 akun dengan saldo debit dan kredit.');
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->with('error', 'Jurnal Neraca Saldo Awal tidak balance! Total Debit: Rp '.number_format($totalDebit, 0, ',', '.').' vs Total Kredit: Rp '.number_format($totalCredit, 0, ',', '.').'. Selisih: Rp '.number_format(abs($totalDebit - $totalCredit), 0, ',', '.').'.');
        }

        try {
            $journalService->createJournal(
                'OPENING_BALANCE',
                null,
                'OB-COA-'.date('Ymd'),
                'Neraca Saldo Awal Pembukuan Akun COA',
                $entries,
                $request->transaction_date,
                auth()->id()
            );

            return redirect()->route('accounting.beginning-balances.index', ['tab' => 'coa'])
                ->with('success', 'Jurnal Neraca Saldo Awal Akun COA berhasil diposting seimbang (Balance: Rp '.number_format($totalDebit, 0, ',', '.').').');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
