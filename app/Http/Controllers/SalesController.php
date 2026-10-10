<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\Contact;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\Item;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesController extends Controller
{
    // ==========================================
    // 1. SALES ORDERS (SO)
    // ==========================================
    public function soIndex()
    {
        $orders = SalesOrder::with(['customer', 'creator'])->latest()->get();

        return view('sales.so.index', compact('orders'));
    }

    public function soCreate()
    {
        $customers = Contact::customers()->where('is_active', true)->get();
        $items = Item::with(['category', 'unit', 'unitConversions.unit'])->where('is_active', true)->get();
        $salespersons = User::orderBy('name')->get();

        return view('sales.so.create', compact('customers', 'items', 'salespersons'));
    }

    public function soStore(Request $request)
    {
        $request->validate([
            'customer_id' => ['required', 'exists:contacts,id'],
            'salesperson_id' => ['nullable', 'exists:users,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $customer = Contact::find($request->customer_id);
        $custMargin = $customer?->min_margin_percent;

        // Validate Minimum Margin Limit for Physical Items (Services are exempt)
        foreach ($request->items as $itemData) {
            $item = Item::with('category')->find($itemData['item_id']);
            if ($item && $item->isPhysical()) {
                $minPrice = $item->getMinimumSellingPrice($custMargin);
                $unitPrice = (float) $itemData['unit_price'];
                if ($minPrice > 0 && $unitPrice < $minPrice) {
                    $margin = $custMargin !== null ? (float) $custMargin : $item->getEffectiveMinMarginPercent();
                    $cost = (float) $item->purchase_price;
                    throw ValidationException::withMessages([
                        'items' => "Harga jual '{$item->name}' (Rp ".number_format($unitPrice, 0, ',', '.').") berada di bawah batas minimum margin {$margin}% (Harga minimal: Rp ".number_format($minPrice, 0, ',', '.').', HPP: Rp '.number_format($cost, 0, ',', '.').').',
                    ]);
                }
            }
        }

        DB::transaction(function () use ($request) {
            $soNumber = 'SO-'.date('Ymd').'-'.str_pad(SalesOrder::count() + 1, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $subtotal += ($itemData['quantity'] * $itemData['unit_price']);
            }

            $discount = (float) ($request->discount_amount ?? 0);
            $taxRate = (float) ($request->tax_rate ?? 0);
            $taxAmount = round(($subtotal - $discount) * ($taxRate / 100), 2);
            $grandTotal = round($subtotal - $discount + $taxAmount, 2);

            $salesperson = $request->salesperson_id ? User::find($request->salesperson_id) : null;
            $commissionRate = (float) ($salesperson?->commission_rate ?? 0);
            $commissionAmount = round(($subtotal - $discount) * ($commissionRate / 100), 2);

            $so = SalesOrder::create([
                'so_number' => $soNumber,
                'customer_id' => $request->customer_id,
                'salesperson_id' => $request->salesperson_id,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => 'approved',
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'grand_total' => $grandTotal,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'notes' => $request->notes,
                'created_by_id' => auth()->id(),
            ]);

            foreach ($request->items as $itemData) {
                SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'koli' => $itemData['koli'] ?? null,
                    'delivered_quantity' => 0,
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemData['quantity'] * $itemData['unit_price'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('sales.so.index')->with('success', 'Sales Order berhasil diterbitkan.');
    }

    public function soShow(SalesOrder $so)
    {
        $so->load(['customer', 'creator', 'items.item.unit', 'deliveryOrders', 'invoices']);

        return view('sales.so.show', compact('so'));
    }

    public function soDuplicate(SalesOrder $so)
    {
        $newSoNumber = 'SO-'.date('Ymd').'-'.str_pad(SalesOrder::count() + 1, 4, '0', STR_PAD_LEFT);

        $newSo = $so->replicate([
            'status',
            'created_at',
            'updated_at',
        ]);
        $newSo->so_number = $newSoNumber;
        $newSo->status = 'draft';
        $newSo->order_date = date('Y-m-d');
        $newSo->notes = ($so->notes ? $so->notes.' ' : '').'(Salinan dari '.$so->so_number.')';
        $newSo->save();

        foreach ($so->items as $item) {
            $newSo->items()->create([
                'item_id' => $item->item_id,
                'quantity' => $item->quantity,
                'delivered_quantity' => 0,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
                'notes' => $item->notes,
            ]);
        }

        return redirect()->route('sales.so.show', $newSo->id)->with('success', "Sales Order {$so->so_number} berhasil disalin menjadi {$newSo->so_number}.");
    }

    public function soDestroy(SalesOrder $so)
    {
        if ($so->deliveryOrders()->exists() || $so->invoices()->exists()) {
            return back()->with('error', 'Sales Order tidak dapat dihapus karena sudah memiliki Surat Jalan atau Faktur AR terkait.');
        }

        $so->items()->delete();
        $so->delete();

        return redirect()->route('sales.so.index')->with('success', 'Sales Order berhasil dihapus.');
    }

    // ==========================================
    // 2. DELIVERY ORDERS (DO / Surat Jalan)
    // ==========================================
    public function doIndex()
    {
        $deliveries = DeliveryOrder::with(['salesOrder', 'customer', 'warehouse', 'dispatchedBy', 'journal'])->latest()->get();

        return view('sales.do.index', compact('deliveries'));
    }

    public function doCreate(Request $request)
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $openOrders = SalesOrder::with(['customer', 'items.item'])
            ->whereIn('status', ['approved', 'processing'])
            ->get();

        $selectedSo = null;
        if ($request->has('so_id')) {
            $selectedSo = SalesOrder::with(['customer', 'items.item.unit'])->find($request->so_id);
        }

        return view('sales.do.create', compact('warehouses', 'openOrders', 'selectedSo'));
    }

    public function doStore(Request $request, InventoryService $inventoryService, JournalService $journalService)
    {
        $request->validate([
            'sales_order_id' => ['required', 'exists:sales_orders,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'delivery_date' => ['required', 'date'],
            'driver_name' => ['nullable', 'string'],
            'vehicle_number' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ]);

        $so = SalesOrder::with('customer')->findOrFail($request->sales_order_id);

        DB::transaction(function () use ($request, $so, $inventoryService, $journalService) {
            $doNumber = 'DO-'.date('Ymd').'-'.str_pad(DeliveryOrder::count() + 1, 4, '0', STR_PAD_LEFT);

            $do = DeliveryOrder::create([
                'do_number' => $doNumber,
                'sales_order_id' => $so->id,
                'customer_id' => $so->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'delivery_date' => $request->delivery_date,
                'driver_name' => $request->driver_name,
                'vehicle_number' => $request->vehicle_number,
                'status' => 'posted',
                'dispatched_by_id' => auth()->id(),
                'notes' => $request->notes,
            ]);

            $totalCogs = 0;
            $cogsCategoryJournals = []; // [cogs_account_id => amount, inv_account_id => amount]

            foreach ($request->items as $itemData) {
                $item = Item::with('category')->findOrFail($itemData['item_id']);
                $soItem = SalesOrderItem::where('sales_order_id', $so->id)
                    ->where('item_id', $item->id)
                    ->first();

                $deliveredQty = (float) $itemData['quantity'];
                $unitCost = (float) $item->purchase_price; // HPP basis
                $totalCost = round($deliveredQty * $unitCost, 2);

                DeliveryOrderItem::create([
                    'delivery_order_id' => $do->id,
                    'sales_order_item_id' => $soItem ? $soItem->id : null,
                    'item_id' => $item->id,
                    'quantity' => $deliveredQty,
                    'koli' => $itemData['koli'] ?? ($soItem ? $soItem->koli : null),
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                ]);

                if ($soItem) {
                    $soItem->increment('delivered_quantity', $deliveredQty);
                }

                // If physical item, deduce stock & record HPP
                if ($item->isPhysical()) {
                    $inventoryService->recordStockMovement(
                        $item->id,
                        $request->warehouse_id,
                        'DELIVERY_ORDER',
                        $do->id,
                        $do->do_number,
                        'out',
                        $deliveredQty,
                        $unitCost,
                        'Pengiriman Barang SO '.$so->so_number.' ke '.$so->customer->name,
                        $request->delivery_date
                    );

                    $cogsAcc = $item->getEffectiveCogsAccountId();
                    $invAcc = $item->getEffectiveInventoryAccountId();

                    if ($cogsAcc && $invAcc && $totalCost > 0) {
                        $cogsCategoryJournals[] = [
                            'cogs_acc' => $cogsAcc,
                            'inv_acc' => $invAcc,
                            'amount' => $totalCost,
                            'item_name' => $item->name,
                        ];
                        $totalCogs += $totalCost;
                    }
                }
            }

            // Update SO status
            $allDelivered = true;
            foreach ($so->items as $sItem) {
                if ($sItem->delivered_quantity < $sItem->quantity) {
                    $allDelivered = false;
                    break;
                }
            }
            $so->update(['status' => $allDelivered ? 'completed' : 'processing']);

            // Auto Journal HPP:
            // Debit: Akun HPP (sesuai setting GL kategori)
            // Credit: Akun Persediaan (sesuai setting GL kategori)
            if ($totalCogs > 0 && count($cogsCategoryJournals) > 0) {
                $journalEntries = [];
                foreach ($cogsCategoryJournals as $cj) {
                    $journalEntries[] = [
                        'account_id' => $cj['cogs_acc'],
                        'type' => 'debit',
                        'amount' => $cj['amount'],
                        'memo' => 'Beban Pokok Penjualan (HPP) '.$cj['item_name'].' - '.$do->do_number,
                    ];
                    $journalEntries[] = [
                        'account_id' => $cj['inv_acc'],
                        'type' => 'credit',
                        'amount' => $cj['amount'],
                        'memo' => 'Pengeluaran Persediaan '.$cj['item_name'].' - '.$do->do_number,
                    ];
                }

                $journal = $journalService->createJournal(
                    'DO',
                    $do->id,
                    $do->do_number,
                    'Jurnal HPP Pengiriman Barang '.$do->do_number.' ke '.$so->customer->name,
                    $journalEntries,
                    $request->delivery_date,
                    auth()->id()
                );

                $do->update(['journal_id' => $journal->id]);
            }
        });

        return redirect()->route('sales.do.index')->with('success', 'Surat jalan (Delivery Order) diposting, stok berkurang, dan jurnal HPP otomatis tercatat.');
    }

    public function doShow(DeliveryOrder $do)
    {
        $do->load(['salesOrder', 'customer', 'warehouse', 'dispatchedBy', 'items.item.unit', 'journal.entries.account']);

        return view('sales.do.show', compact('do'));
    }

    // ==========================================
    // 3. SALES INVOICES (Faktur Penjualan AR)
    // ==========================================
    public function invoiceIndex()
    {
        $invoices = SalesInvoice::with(['customer', 'salesOrder', 'deliveryOrder', 'journal'])->latest()->get();

        return view('sales.invoices.index', compact('invoices'));
    }

    public function invoiceCreate(Request $request)
    {
        $customers = Contact::customers()->where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $items = Item::where('is_active', true)
            ->where(function ($q) {
                $q->where('item_type', '!=', 'raw_material')
                    ->orWhereNull('item_type')
                    ->orWhere('item_type', 'super_product');
            })
            ->with(['unit', 'category'])
            ->get();
        $openDeliveries = DeliveryOrder::with(['salesOrder.items', 'customer', 'items.item'])
            ->whereDoesntHave('invoices')
            ->where('status', 'posted')
            ->get();

        $selectedDo = null;
        if ($request->has('do_id') && $request->do_id) {
            $selectedDo = DeliveryOrder::with(['salesOrder.items', 'customer', 'items.item.unit'])->find($request->do_id);
        }

        $salespersons = User::orderBy('name')->get();

        return view('sales.invoices.create', compact('customers', 'openDeliveries', 'selectedDo', 'items', 'warehouses', 'salespersons'));
    }

    public function invoiceStore(Request $request, InventoryService $inventoryService, JournalService $journalService)
    {
        $request->validate([
            'customer_id' => ['required', 'exists:contacts,id'],
            'salesperson_id' => ['nullable', 'exists:users,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $customer = Contact::find($request->customer_id);
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
                    throw ValidationException::withMessages([
                        'items' => "Harga jual '{$item->name}' (Rp ".number_format($unitPrice, 0, ',', '.').") berada di bawah batas minimum margin {$margin}% (Harga minimal: Rp ".number_format($minPrice, 0, ',', '.').', HPP: Rp '.number_format($cost, 0, ',', '.').').',
                    ]);
                }
            }
        }

        DB::transaction(function () use ($request, $inventoryService, $journalService) {
            $invoiceNumber = 'SINV-'.date('Ymd').'-'.str_pad(SalesInvoice::count() + 1, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $revenueDebits = []; // [sales_account_id => amount]
            $cogsBreakdown = []; // [cogs_key => ['cogs_account_id' => ..., 'inventory_account_id' => ..., 'amount' => ...]]

            foreach ($request->items as $itemData) {
                $subtotal += ($itemData['quantity'] * $itemData['unit_price']);
            }

            $taxRate = (float) ($request->tax_rate ?? 0);
            $taxAmount = $taxRate > 0 ? round($subtotal * ($taxRate / 100), 2) : (float) ($request->tax_amount ?? 0);
            $grandTotal = round($subtotal + $taxAmount, 2);

            $isDirect = empty($request->delivery_order_id);
            $warehouseId = $request->warehouse_id ?: Warehouse::where('is_active', true)->first()?->id;

            $salesperson = $request->salesperson_id ? User::find($request->salesperson_id) : null;
            $commissionRate = (float) ($salesperson?->commission_rate ?? 0);
            $commissionAmount = round($subtotal * ($commissionRate / 100), 2);

            $invoice = SalesInvoice::create([
                'invoice_number' => $invoiceNumber,
                'sales_order_id' => $request->sales_order_id,
                'delivery_order_id' => $request->delivery_order_id,
                'customer_id' => $request->customer_id,
                'salesperson_id' => $request->salesperson_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'paid_amount' => 0,
                'status' => 'unpaid',
                'created_by_id' => auth()->id(),
                'notes' => $request->notes,
            ]);

            $defaultSalesAcc = ChartOfAccount::where('code', '4001')->first() ?: ChartOfAccount::where('type', 'revenue')->first();
            $defaultCogsAcc = ChartOfAccount::where('code', '5001')->first() ?: ChartOfAccount::where('type', 'expense')->first();
            $defaultInvAcc = ChartOfAccount::where('code', '1201')->first() ?: ChartOfAccount::where('type', 'asset')->first();

            foreach ($request->items as $itemData) {
                $item = Item::with(['category', 'boms.items.item'])->findOrFail($itemData['item_id']);
                $qty = (float) $itemData['quantity'];
                $price = (float) $itemData['unit_price'];
                $lineTotal = round($qty * $price, 2);

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'item_id' => $item->id,
                    'quantity' => $qty,
                    'koli' => $itemData['koli'] ?? null,
                    'unit_price' => $price,
                    'subtotal' => $lineTotal,
                ]);

                // Direct AR invoice without DO: Deduct stock directly & track COGS
                if ($isDirect && $warehouseId) {
                    if ($item->isBundle()) {
                        $bundleResult = $inventoryService->deductBundleComponents(
                            $item,
                            $warehouseId,
                            $qty,
                            'DIRECT_SALES_INVOICE_BUNDLE',
                            $invoice->id,
                            $invoice->invoice_number,
                            $request->invoice_date
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
                            'DIRECT_SALES_INVOICE',
                            $invoice->id,
                            $invoice->invoice_number,
                            'out',
                            $qty,
                            $cost,
                            'Penjualan Langsung Faktur AR '.$invoice->invoice_number,
                            $request->invoice_date
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
                }

                // Sales Revenue Account from Item / Category GL setting
                $salesAcc = $item->getEffectiveSalesAccountId() ?: ($defaultSalesAcc ? $defaultSalesAcc->id : null);
                if ($salesAcc) {
                    $revenueDebits[$salesAcc] = ($revenueDebits[$salesAcc] ?? 0) + $lineTotal;
                }
            }

            // Auto Journal Piutang Usaha & Pendapatan (+ HPP if direct sale)
            $arAccount = ChartOfAccount::where('code', '1101')->first();
            $taxAccount = ChartOfAccount::where('code', '2003')->first();

            $journalEntries = [
                [
                    'account_id' => $arAccount->id,
                    'type' => 'debit',
                    'amount' => $grandTotal,
                    'memo' => 'Piutang Usaha Faktur '.$invoice->invoice_number,
                ],
            ];

            foreach ($revenueDebits as $accId => $amount) {
                $journalEntries[] = [
                    'account_id' => $accId,
                    'type' => 'credit',
                    'amount' => $amount,
                    'memo' => 'Pendapatan Penjualan Faktur '.$invoice->invoice_number,
                ];
            }

            if ($taxAmount > 0 && $taxAccount) {
                $journalEntries[] = [
                    'account_id' => $taxAccount->id,
                    'type' => 'credit',
                    'amount' => $taxAmount,
                    'memo' => 'PPN Keluaran Faktur '.$invoice->invoice_number,
                ];
            }

            // If direct sale without DO, add COGS vs Persediaan entries to the same journal
            foreach ($cogsBreakdown as $cogsData) {
                if ($cogsData['amount'] > 0) {
                    $journalEntries[] = [
                        'account_id' => $cogsData['cogs_account_id'],
                        'type' => 'debit',
                        'amount' => round($cogsData['amount'], 2),
                        'memo' => 'HPP Penjualan Langsung Faktur '.$invoice->invoice_number,
                    ];
                    $journalEntries[] = [
                        'account_id' => $cogsData['inventory_account_id'],
                        'type' => 'credit',
                        'amount' => round($cogsData['amount'], 2),
                        'memo' => 'Persediaan Keluar Penjualan Langsung '.$invoice->invoice_number,
                    ];
                }
            }

            $journal = $journalService->createJournal(
                'SALES_INVOICE',
                $invoice->id,
                $invoice->invoice_number,
                'Faktur Penjualan '.$invoice->invoice_number.' - '.$invoice->customer->name.($isDirect ? ' (Langsung)' : ''),
                $journalEntries,
                $request->invoice_date,
                auth()->id()
            );

            $invoice->update(['journal_id' => $journal->id]);
        });

        return redirect()->route('sales.invoices.index')->with('success', 'Faktur Penjualan berhasil dibuat dan dijurnal ke Piutang Usaha & Pendapatan.');
    }

    public function invoiceEdit(SalesInvoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Faktur yang sudah lunas tidak dapat diedit. Silakan sesuaikan via menu pembayaran/jurnal.');
        }

        $customers = Contact::customers()->where('is_active', true)->get();
        $invoice->load(['customer', 'salesOrder', 'deliveryOrder', 'items.item.unit']);

        return view('sales.invoices.edit', compact('invoice', 'customers'));
    }

    public function invoiceUpdate(Request $request, SalesInvoice $invoice, JournalService $journalService)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Faktur yang sudah lunas tidak dapat diedit.');
        }

        $request->validate([
            'customer_id' => ['required', 'exists:contacts,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        // Validate Minimum Margin Limit for Physical Items
        foreach ($request->items as $itemData) {
            $item = Item::with('category')->find($itemData['item_id']);
            if ($item && $item->isPhysical()) {
                $minPrice = $item->getMinimumSellingPrice();
                $unitPrice = (float) $itemData['unit_price'];
                if ($minPrice > 0 && $unitPrice < $minPrice) {
                    $margin = $item->getEffectiveMinMarginPercent();
                    $cost = (float) $item->purchase_price;
                    throw ValidationException::withMessages([
                        'items' => "Harga jual '{$item->name}' (Rp ".number_format($unitPrice, 0, ',', '.').") berada di bawah batas minimum margin {$margin}% (Harga minimal: Rp ".number_format($minPrice, 0, ',', '.').', HPP: Rp '.number_format($cost, 0, ',', '.').').',
                    ]);
                }
            }
        }

        DB::transaction(function () use ($request, $invoice, $journalService) {
            $subtotal = 0;
            $revenueDebits = [];

            // Re-create items
            $invoice->items()->delete();

            foreach ($request->items as $itemData) {
                $subtotal += ($itemData['quantity'] * $itemData['unit_price']);
            }

            $taxRate = (float) ($request->tax_rate ?? 0);
            $taxAmount = $taxRate > 0 ? round($subtotal * ($taxRate / 100), 2) : (float) ($request->tax_amount ?? 0);
            $grandTotal = round($subtotal + $taxAmount, 2);

            $invoice->update([
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $itemData) {
                $item = Item::with('category')->findOrFail($itemData['item_id']);
                $lineTotal = round($itemData['quantity'] * $itemData['unit_price'], 2);

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'item_id' => $item->id,
                    'quantity' => $itemData['quantity'],
                    'koli' => $itemData['koli'] ?? null,
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $lineTotal,
                ]);

                $salesAcc = $item->getEffectiveSalesAccountId();
                if (! $salesAcc) {
                    $defaultSalesAcc = ChartOfAccount::where('code', '4001')->first();
                    $salesAcc = $defaultSalesAcc->id;
                }

                $revenueDebits[$salesAcc] = ($revenueDebits[$salesAcc] ?? 0) + $lineTotal;
            }

            // Update Journal
            $arAccount = ChartOfAccount::where('code', '1101')->first();
            $taxAccount = ChartOfAccount::where('code', '2003')->first();

            $journalEntries = [
                [
                    'account_id' => $arAccount->id,
                    'type' => 'debit',
                    'amount' => $grandTotal,
                    'memo' => 'Piutang Usaha Faktur '.$invoice->invoice_number,
                ],
            ];

            foreach ($revenueDebits as $accId => $amount) {
                $journalEntries[] = [
                    'account_id' => $accId,
                    'type' => 'credit',
                    'amount' => $amount,
                    'memo' => 'Pendapatan Penjualan Faktur '.$invoice->invoice_number,
                ];
            }

            if ($taxAmount > 0 && $taxAccount) {
                $journalEntries[] = [
                    'account_id' => $taxAccount->id,
                    'type' => 'credit',
                    'amount' => $taxAmount,
                    'memo' => 'PPN Keluaran Faktur '.$invoice->invoice_number,
                ];
            }

            if ($invoice->journal_id && $invoice->journal) {
                $invoice->journal->entries()->delete();
                $invoice->journal->update([
                    'transaction_date' => $request->invoice_date,
                    'description' => 'Faktur Penjualan '.$invoice->invoice_number.' - '.$invoice->customer->name,
                ]);
                foreach ($journalEntries as $entry) {
                    $invoice->journal->entries()->create($entry);
                }
            } else {
                $journal = $journalService->createJournal(
                    'SALES_INVOICE',
                    $invoice->id,
                    $invoice->invoice_number,
                    'Faktur Penjualan '.$invoice->invoice_number.' - '.$invoice->customer->name,
                    $journalEntries,
                    $request->invoice_date,
                    auth()->id()
                );
                $invoice->update(['journal_id' => $journal->id]);
            }
        });

        return redirect()->route('sales.invoices.show', $invoice->id)->with('success', 'Faktur Penjualan berhasil diperbarui dan jurnal disesuaikan.');
    }

    public function invoiceDestroy(SalesInvoice $invoice)
    {
        if ($invoice->status === 'paid' || $invoice->paid_amount > 0) {
            return back()->with('error', 'Faktur yang sudah memiliki pembayaran tidak dapat dihapus.');
        }

        DB::transaction(function () use ($invoice) {
            if ($invoice->journal) {
                $invoice->journal->entries()->delete();
                $invoice->journal->delete();
            }
            $invoice->items()->delete();
            $invoice->delete();
        });

        return redirect()->route('sales.invoices.index')->with('success', 'Faktur Penjualan berhasil dihapus.');
    }

    public function invoiceShow(SalesInvoice $invoice)
    {
        $invoice->load(['customer', 'salesOrder', 'deliveryOrder', 'items.item.unit', 'journal.entries.account']);

        return view('sales.invoices.show', compact('invoice'));
    }

    public function soPrint(SalesOrder $so)
    {
        $so->load(['customer', 'salesPerson', 'items.item.unit']);

        return view('sales.so.print', compact('so'));
    }

    public function doPrint(DeliveryOrder $do)
    {
        $do->load(['customer', 'salesOrder', 'warehouse', 'createdByUser', 'items.item.unit']);

        return view('sales.do.print', compact('do'));
    }

    public function invoicePrint(SalesInvoice $invoice)
    {
        $invoice->load(['customer', 'salesOrder', 'deliveryOrder', 'createdBy', 'items.item.unit']);

        return view('sales.invoices.print', compact('invoice'));
    }

    // ==========================================
    // 5. SALES RETURNS (RETUR PENJUALAN)
    // ==========================================
    public function returnIndex(Request $request)
    {
        $query = SalesReturn::with(['customer', 'warehouse', 'branch', 'salesInvoice', 'createdBy'])->latest();

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('return_number', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($sq) => $sq->where('name', 'like', "%{$s}%"));
            });
        }

        $returns = $query->paginate(15);
        $customers = Contact::customers()->orderBy('name')->get();

        return view('sales.returns.index', compact('returns', 'customers'));
    }

    public function returnCreate(Request $request)
    {
        $customers = Contact::customers()->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $items = Item::where('is_active', true)->with('unit')->orderBy('name')->get();
        $invoices = SalesInvoice::with(['customer', 'items.item.unit'])->latest()->get();
        $cashAccounts = ChartOfAccount::where('is_cash_or_bank', true)->where('is_active', true)->get();

        $selectedInvoiceId = $request->query('sales_invoice_id');
        $selectedInvoice = $selectedInvoiceId ? SalesInvoice::with(['customer', 'items.item.unit'])->find($selectedInvoiceId) : null;

        return view('sales.returns.create', compact('customers', 'warehouses', 'branches', 'items', 'invoices', 'cashAccounts', 'selectedInvoice'));
    }

    public function returnStore(Request $request, InventoryService $inventoryService, JournalService $journalService)
    {
        $request->validate([
            'customer_id' => ['required', 'exists:contacts,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'return_date' => ['required', 'date'],
            'refund_method' => ['required', 'in:potong_piutang,tunai_kas'],
            'cash_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'sales_invoice_id' => ['nullable', 'exists:sales_invoices,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $return = DB::transaction(function () use ($request, $inventoryService, $journalService) {
            $returnNo = 'SRET-'.date('Ymd').'-'.str_pad(SalesReturn::count() + 1, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $subtotal += ($itemData['quantity'] * $itemData['unit_price']);
            }

            $taxRate = (float) ($request->tax_rate ?? 0);
            $taxAmount = $taxRate > 0 ? round($subtotal * ($taxRate / 100), 2) : (float) ($request->tax_amount ?? 0);
            $grandTotal = round($subtotal + $taxAmount, 2);

            $salesReturn = SalesReturn::create([
                'return_number' => $returnNo,
                'sales_invoice_id' => $request->sales_invoice_id,
                'customer_id' => $request->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'branch_id' => $request->branch_id,
                'return_date' => $request->return_date,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'refund_method' => $request->refund_method,
                'cash_account_id' => $request->cash_account_id,
                'status' => 'completed',
                'notes' => $request->notes,
                'created_by_id' => auth()->id(),
            ]);

            $totalCogsRestock = 0;

            foreach ($request->items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                $qty = (float) $itemData['quantity'];
                $price = (float) $itemData['unit_price'];
                $lineTotal = round($qty * $price, 2);
                $cost = (float) $item->purchase_price;

                SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'item_id' => $item->id,
                    'unit_id' => $item->unit_id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price' => $lineTotal,
                    'reason' => $itemData['reason'] ?? null,
                ]);

                // Stock In (Kembalikan stok ke gudang karena barang diretur oleh pelanggan)
                if ($item->isPhysical()) {
                    $inventoryService->recordStockMovement(
                        $item->id,
                        $request->warehouse_id,
                        'SALES_RETURN',
                        $salesReturn->id,
                        $salesReturn->return_number,
                        'in',
                        $qty,
                        $cost,
                        'Retur Penjualan '.$salesReturn->return_number.' dari Pelanggan',
                        $request->return_date
                    );
                    $totalCogsRestock += ($qty * $cost);
                }
            }

            // Journal entries:
            // Debit: Retur Penjualan (4102) / Pendapatan Penjualan (4101) -> amount: subtotal
            // Debit: PPN Keluaran (2003 / 2102) -> amount: tax_amount
            // Credit: Piutang Usaha (1101/1102) / Kas Bank (if refund cash) -> amount: grand_total
            $salesReturnAccount = ChartOfAccount::where('code', '4102')->first() ?: (ChartOfAccount::where('code', '4101')->first() ?: ChartOfAccount::where('type', 'revenue')->first());
            $arAccount = ChartOfAccount::where('code', '1101')->first() ?: ChartOfAccount::where('code', '1102')->first();
            $refundAccId = ($request->refund_method === 'tunai_kas' && $request->cash_account_id)
                ? $request->cash_account_id
                : ($arAccount ? $arAccount->id : null);

            $taxAccount = ChartOfAccount::where('code', '2003')->first();

            $journalEntries = [];

            if ($salesReturnAccount) {
                $journalEntries[] = [
                    'account_id' => $salesReturnAccount->id,
                    'type' => 'debit',
                    'amount' => $subtotal,
                    'memo' => 'Retur Penjualan '.$salesReturn->return_number,
                ];
            }

            if ($taxAmount > 0 && $taxAccount) {
                $journalEntries[] = [
                    'account_id' => $taxAccount->id,
                    'type' => 'debit',
                    'amount' => $taxAmount,
                    'memo' => 'Pengurangan PPN Keluaran Retur Penjualan '.$salesReturn->return_number,
                ];
            }

            if ($refundAccId) {
                $journalEntries[] = [
                    'account_id' => $refundAccId,
                    'type' => 'credit',
                    'amount' => $grandTotal,
                    'memo' => 'Kredit Piutang / Kas Retur Penjualan '.$salesReturn->return_number.' ('.$request->refund_method.')',
                ];
            }

            // Inventory & COGS recovery journal entries:
            // Debit: Persediaan (1201) -> amount: totalCogsRestock
            // Credit: HPP (5101) -> amount: totalCogsRestock
            if ($totalCogsRestock > 0) {
                $invAcc = ChartOfAccount::where('code', '1201')->first();
                $cogsAcc = ChartOfAccount::where('code', '5101')->first();
                if ($invAcc && $cogsAcc) {
                    $journalEntries[] = [
                        'account_id' => $invAcc->id,
                        'type' => 'debit',
                        'amount' => round($totalCogsRestock, 2),
                        'memo' => 'Pemulihan Persediaan Masuk Retur Penjualan '.$salesReturn->return_number,
                    ];
                    $journalEntries[] = [
                        'account_id' => $cogsAcc->id,
                        'type' => 'credit',
                        'amount' => round($totalCogsRestock, 2),
                        'memo' => 'Koreksi HPP Retur Penjualan '.$salesReturn->return_number,
                    ];
                }
            }

            $journal = $journalService->createJournal(
                'SALES_RETURN',
                $salesReturn->id,
                $salesReturn->return_number,
                'Retur Penjualan '.$salesReturn->return_number.' dari '.$salesReturn->customer->name,
                $journalEntries,
                $request->return_date,
                auth()->id()
            );

            $salesReturn->update(['journal_id' => $journal->id]);

            // Adjust invoice remaining balance if linked
            if ($request->sales_invoice_id && $request->refund_method === 'potong_piutang') {
                $inv = SalesInvoice::find($request->sales_invoice_id);
                if ($inv) {
                    $newPaid = $inv->paid_amount + $grandTotal;
                    $inv->update([
                        'paid_amount' => $newPaid,
                        'status' => $newPaid >= $inv->grand_total ? 'paid' : 'partial',
                    ]);
                }
            }

            return $salesReturn;
        });

        return redirect()->route('sales.returns.show', $return->id)->with('success', 'Retur Penjualan berhasil dicatat, stok gudang dipulihkan, dan jurnal diposting.');
    }

    public function returnShow(SalesReturn $return)
    {
        $return->load(['customer', 'warehouse', 'branch', 'salesInvoice', 'cashAccount', 'items.item.unit', 'journal.entries.account', 'createdBy']);

        return view('sales.returns.show', compact('return'));
    }

    public function returnPrint(SalesReturn $return)
    {
        $return->load(['customer', 'warehouse', 'branch', 'salesInvoice', 'items.item.unit', 'createdBy']);

        return view('sales.returns.print', compact('return'));
    }
}
