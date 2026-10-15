<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\Contact;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Item;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcurementController extends Controller
{
    // ==========================================
    // 1. PURCHASE REQUISITIONS (PR)
    // ==========================================
    public function prIndex()
    {
        $requisitions = PurchaseRequisition::with(['requester', 'approver'])->latest()->get();

        return view('procurement.pr.index', compact('requisitions'));
    }

    public function prCreate()
    {
        $items = Item::with(['category', 'unit'])->where('is_active', true)->get();

        return view('procurement.pr.create', compact('items'));
    }

    public function prStore(Request $request)
    {
        $request->validate([
            'request_date' => ['required', 'date'],
            'required_date' => ['nullable', 'date'],
            'department' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.koli' => ['nullable', 'string', 'max:255'],
            'items.*.estimated_unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request) {
            $prNumber = 'PR-'.date('Ymd').'-'.str_pad(PurchaseRequisition::count() + 1, 4, '0', STR_PAD_LEFT);

            $pr = PurchaseRequisition::create([
                'pr_number' => $prNumber,
                'request_date' => $request->request_date,
                'required_date' => $request->required_date,
                'department' => $request->department,
                'notes' => $request->notes,
                'status' => 'pending_approval',
                'requester_id' => auth()->id(),
            ]);

            foreach ($request->items as $itemData) {
                $pr->items()->create([
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'koli' => $itemData['koli'] ?? null,
                    'estimated_unit_price' => $itemData['estimated_unit_price'],
                    'estimated_total_price' => $itemData['quantity'] * $itemData['estimated_unit_price'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('procurement.pr.index')->with('success', 'Purchase Requisition berhasil diajukan.');
    }

    public function prShow(PurchaseRequisition $pr)
    {
        $pr->load(['requester', 'approver', 'items.item.unit']);

        return view('procurement.pr.show', compact('pr'));
    }

    public function prApprove(PurchaseRequisition $pr)
    {
        $pr->update([
            'status' => 'approved',
            'approved_by_id' => auth()->id(),
        ]);

        return back()->with('success', 'Purchase Requisition berhasil disetujui (Approved).');
    }

    public function prReject(PurchaseRequisition $pr)
    {
        $pr->update([
            'status' => 'rejected',
            'approved_by_id' => auth()->id(),
        ]);

        return back()->with('success', 'Purchase Requisition ditolak.');
    }

    // ==========================================
    // 2. PURCHASE ORDERS (PO)
    // ==========================================
    public function poIndex()
    {
        $orders = PurchaseOrder::with(['supplier', 'creator'])->latest()->get();

        return view('procurement.po.index', compact('orders'));
    }

    public function poCreate(Request $request)
    {
        $suppliers = Contact::suppliers()->where('is_active', true)->get();
        $items = Item::with(['category', 'unit'])->where('is_active', true)->get();
        $approvedPrs = PurchaseRequisition::where('status', 'approved')->get();

        $selectedPr = null;
        if ($request->has('pr_id')) {
            $selectedPr = PurchaseRequisition::with('items.item')->find($request->pr_id);
        }

        return view('procurement.po.create', compact('suppliers', 'items', 'approvedPrs', 'selectedPr'));
    }

    public function poStore(Request $request)
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:contacts,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date'],
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.koli' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request) {
            $poNumber = 'PO-'.date('Ymd').'-'.str_pad(PurchaseOrder::count() + 1, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $subtotal += ($itemData['quantity'] * $itemData['unit_price']);
            }

            $discount = (float) ($request->discount_amount ?? 0);
            $taxRate = (float) ($request->tax_rate ?? 0);
            $taxAmount = round(($subtotal - $discount) * ($taxRate / 100), 2);
            $grandTotal = round($subtotal - $discount + $taxAmount, 2);

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'purchase_requisition_id' => $request->purchase_requisition_id,
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'expected_date' => $request->expected_date,
                'status' => 'approved', // Auto approved or ready
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'grand_total' => $grandTotal,
                'notes' => $request->notes,
                'created_by_id' => auth()->id(),
            ]);

            foreach ($request->items as $itemData) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'koli' => $itemData['koli'] ?? null,
                    'received_quantity' => 0,
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemData['quantity'] * $itemData['unit_price'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            if ($request->purchase_requisition_id) {
                PurchaseRequisition::where('id', $request->purchase_requisition_id)->update(['status' => 'completed']);
            }
        });

        return redirect()->route('procurement.po.index')->with('success', 'Purchase Order berhasil dibuat.');
    }

    public function poShow(PurchaseOrder $po)
    {
        $po->load(['supplier', 'creator', 'items.item.unit', 'goodsReceipts', 'invoices']);

        return view('procurement.po.show', compact('po'));
    }

    public function poDuplicate(PurchaseOrder $po)
    {
        $newPoNumber = 'PO-'.date('Ymd').'-'.str_pad(PurchaseOrder::count() + 1, 4, '0', STR_PAD_LEFT);

        $newPo = $po->replicate([
            'status',
            'created_at',
            'updated_at',
        ]);
        $newPo->po_number = $newPoNumber;
        $newPo->status = 'draft';
        $newPo->order_date = date('Y-m-d');
        $newPo->notes = ($po->notes ? $po->notes.' ' : '').'(Salinan dari '.$po->po_number.')';
        $newPo->save();

        foreach ($po->items as $item) {
            $newPo->items()->create([
                'item_id' => $item->item_id,
                'quantity' => $item->quantity,
                'received_quantity' => 0,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
                'notes' => $item->notes,
            ]);
        }

        return redirect()->route('procurement.po.show', $newPo->id)->with('success', "Purchase Order {$po->po_number} berhasil disalin menjadi {$newPo->po_number}.");
    }

    public function poDestroy(PurchaseOrder $po)
    {
        if ($po->goodsReceipts()->exists() || $po->invoices()->exists()) {
            return back()->with('error', 'Purchase Order tidak dapat dihapus karena sudah memiliki Penerimaan GRN atau Faktur Tagihan AP terkait.');
        }

        $po->items()->delete();
        $po->delete();

        return redirect()->route('procurement.po.index')->with('success', 'Purchase Order berhasil dihapus.');
    }

    // ==========================================
    // 3. GOODS RECEIPT NOTE (GRN / Penerimaan Barang)
    // ==========================================
    public function grnIndex()
    {
        $receipts = GoodsReceipt::with(['purchaseOrder', 'supplier', 'warehouse', 'receivedBy', 'journal'])->latest()->get();

        return view('procurement.grn.index', compact('receipts'));
    }

    public function grnCreate(Request $request)
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $suppliers = Contact::suppliers()->where('is_active', true)->get();
        $items = Item::with(['category', 'unit'])->where('is_active', true)->get();
        $openOrders = PurchaseOrder::with(['supplier', 'items.item'])
            ->whereIn('status', ['approved', 'partial_received'])
            ->get();

        $selectedPo = null;
        if ($request->filled('po_id')) {
            $selectedPo = PurchaseOrder::with(['supplier', 'items.item.unit'])->find($request->po_id);
        }

        return view('procurement.grn.create', compact('warehouses', 'suppliers', 'items', 'openOrders', 'selectedPo'));
    }

    public function grnStore(Request $request, InventoryService $inventoryService, JournalService $journalService)
    {
        $request->validate([
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'supplier_id' => ['required_without:purchase_order_id', 'nullable', 'exists:contacts,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'receipt_date' => ['required', 'date'],
            'delivery_note_number' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.koli' => ['nullable', 'string', 'max:255'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $po = $request->filled('purchase_order_id')
            ? PurchaseOrder::with(['supplier', 'items'])->find($request->purchase_order_id)
            : null;

        $supplierId = $po ? $po->supplier_id : $request->supplier_id;
        $supplier = Contact::find($supplierId);

        DB::transaction(function () use ($request, $po, $supplierId, $supplier, $inventoryService, $journalService) {
            $grnNumber = 'GRN-'.date('Ymd').'-'.str_pad(GoodsReceipt::count() + 1, 4, '0', STR_PAD_LEFT);

            $grn = GoodsReceipt::create([
                'grn_number' => $grnNumber,
                'purchase_order_id' => $po ? $po->id : null,
                'supplier_id' => $supplierId,
                'warehouse_id' => $request->warehouse_id,
                'receipt_date' => $request->receipt_date,
                'delivery_note_number' => $request->delivery_note_number,
                'status' => 'posted',
                'received_by_id' => auth()->id(),
                'notes' => $request->notes,
            ]);

            $totalReceivedCost = 0;
            $categoryDebits = []; // [inventory_account_id => total_amount]

            foreach ($request->items as $itemData) {
                $item = Item::with('category')->findOrFail($itemData['item_id']);
                $poItem = null;
                if ($po) {
                    $poItem = PurchaseOrderItem::where('purchase_order_id', $po->id)
                        ->where('item_id', $item->id)
                        ->first();
                }

                $unitCost = $poItem
                    ? (float) $poItem->unit_price
                    : (isset($itemData['unit_cost']) && (float) $itemData['unit_cost'] > 0
                        ? (float) $itemData['unit_cost']
                        : (float) $item->purchase_price);

                $receivedQty = (float) $itemData['quantity'];
                $totalCost = round($receivedQty * $unitCost, 2);
                $koli = $itemData['koli'] ?? ($poItem ? $poItem->koli : null);

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $grn->id,
                    'purchase_order_item_id' => $poItem ? $poItem->id : null,
                    'item_id' => $item->id,
                    'quantity' => $receivedQty,
                    'koli' => $koli,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                ]);

                // Update PO received quantity if from PO
                if ($poItem) {
                    $poItem->increment('received_quantity', $receivedQty);
                }

                // If physical item, update stock ledger & recalculate Moving Average HPP
                if ($item->isPhysical()) {
                    $oldStock = (float) $item->current_stock;
                    $oldCost = (float) $item->purchase_price;
                    $newQty = $receivedQty;
                    $newCost = (float) $unitCost;

                    // Moving Average formula
                    if ($oldStock <= 0) {
                        $avgCost = $newCost;
                    } else {
                        $avgCost = (($oldStock * $oldCost) + ($newQty * $newCost)) / ($oldStock + $newQty);
                    }

                    $movementNotes = $po
                        ? 'Penerimaan Barang PO '.$po->po_number
                        : 'Penerimaan Barang Langsung (Direct GRN)';

                    $inventoryService->recordStockMovement(
                        $item->id,
                        $request->warehouse_id,
                        'GRN',
                        $grn->id,
                        $grn->grn_number,
                        'in',
                        $receivedQty,
                        $unitCost,
                        $movementNotes,
                        $request->receipt_date,
                        $koli
                    );

                    // Update Item HPP with moving average
                    if ($avgCost > 0) {
                        $item->update(['purchase_price' => round($avgCost, 2)]);
                    }

                    // Accounting Category GL Mapping
                    $invAccount = $item->getEffectiveInventoryAccountId() ?: ChartOfAccount::where('code', '1201')->first()?->id;
                    if ($invAccount) {
                        $categoryDebits[$invAccount] = ($categoryDebits[$invAccount] ?? 0) + $totalCost;
                    }
                    $totalReceivedCost += $totalCost;
                }
            }

            // Check if PO is completely received (only if from PO)
            if ($po) {
                $allReceived = true;
                foreach ($po->items as $pItem) {
                    if ($pItem->received_quantity < $pItem->quantity) {
                        $allReceived = false;
                        break;
                    }
                }
                $po->update(['status' => $allReceived ? 'received' : 'partial_received']);
            }

            // Auto-Journal: Debit Inventory Accounts vs Credit Unbilled GRN (1301)
            if ($totalReceivedCost > 0) {
                $unbilledAccount = ChartOfAccount::where('code', '1301')->first() ?: ChartOfAccount::where('code', '2001')->first();
                $journalEntries = [];

                foreach ($categoryDebits as $accId => $amount) {
                    $journalEntries[] = [
                        'account_id' => $accId,
                        'type' => 'debit',
                        'amount' => $amount,
                        'memo' => 'Penerimaan Persediaan '.$grn->grn_number,
                    ];
                }

                $journalEntries[] = [
                    'account_id' => $unbilledAccount->id,
                    'type' => 'credit',
                    'amount' => $totalReceivedCost,
                    'memo' => 'Penerimaan Barang Belum Ditagih (Unbilled GRN) '.$grn->grn_number,
                ];

                $journalMemo = 'Jurnal Penerimaan Barang '.$grn->grn_number.' dari '.($supplier ? $supplier->name : ($po ? $po->supplier->name : 'Supplier'));

                $journal = $journalService->createJournal(
                    'GRN',
                    $grn->id,
                    $grn->grn_number,
                    $journalMemo,
                    $journalEntries,
                    $request->receipt_date,
                    auth()->id()
                );

                $grn->update(['journal_id' => $journal->id]);
            }
        });

        return redirect()->route('procurement.grn.index')->with('success', 'Penerimaan barang berhasil diposting, stok bertambah, dan jurnal terbentuk otomatis.');
    }

    public function grnShow(GoodsReceipt $grn)
    {
        $grn->load(['purchaseOrder', 'supplier', 'warehouse', 'receivedBy', 'items.item.unit', 'journal.entries.account']);

        return view('procurement.grn.show', compact('grn'));
    }

    // ==========================================
    // 4. PURCHASE INVOICE (Tagihan Pembelian AP)
    // ==========================================
    public function invoiceIndex()
    {
        $invoices = PurchaseInvoice::with(['supplier', 'purchaseOrder', 'goodsReceipt', 'journal'])->latest()->get();

        return view('procurement.invoices.index', compact('invoices'));
    }

    public function invoiceCreate(Request $request)
    {
        $suppliers = Contact::suppliers()->where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $items = Item::where('is_active', true)->with(['unit', 'category'])->get();
        $unbilledGrns = GoodsReceipt::with(['purchaseOrder.items', 'supplier', 'items.item'])
            ->whereDoesntHave('invoices')
            ->where('status', 'posted')
            ->get();

        $selectedGrn = null;
        if ($request->has('grn_id') && $request->grn_id) {
            $selectedGrn = GoodsReceipt::with(['purchaseOrder.items', 'supplier', 'items.item.unit'])->find($request->grn_id);
        }

        return view('procurement.invoices.create', compact('suppliers', 'warehouses', 'items', 'unbilledGrns', 'selectedGrn'));
    }

    public function invoiceStore(Request $request, InventoryService $inventoryService, JournalService $journalService)
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:contacts,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'supplier_invoice_number' => ['nullable', 'string'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $inventoryService, $journalService) {
            $invoiceNumber = 'PINV-'.date('Ymd').'-'.str_pad(PurchaseInvoice::count() + 1, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $subtotal += ($itemData['quantity'] * $itemData['unit_price']);
            }

            $taxRate = (float) ($request->tax_rate ?? 0);
            $taxAmount = $taxRate > 0 ? round($subtotal * ($taxRate / 100), 2) : (float) ($request->tax_amount ?? 0);
            $grandTotal = round($subtotal + $taxAmount, 2);

            $isDirect = empty($request->goods_receipt_id);
            $warehouseId = $request->warehouse_id ?: Warehouse::where('is_active', true)->first()?->id;

            $invoice = PurchaseInvoice::create([
                'invoice_number' => $invoiceNumber,
                'supplier_invoice_number' => $request->supplier_invoice_number,
                'purchase_order_id' => $request->purchase_order_id,
                'goods_receipt_id' => $request->goods_receipt_id,
                'supplier_id' => $request->supplier_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'paid_amount' => 0,
                'status' => 'unpaid',
                'created_by_id' => auth()->id(),
                'notes' => $request->notes,
            ]);

            $categoryDebits = []; // [account_id => amount]

            foreach ($request->items as $itemData) {
                $item = Item::with('category')->findOrFail($itemData['item_id']);
                $qty = (float) $itemData['quantity'];
                $price = (float) $itemData['unit_price'];
                $line = round($qty * $price, 2);

                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'item_id' => $item->id,
                    'quantity' => $qty,
                    'koli' => $itemData['koli'] ?? null,
                    'unit_price' => $price,
                    'subtotal' => $line,
                ]);

                // If Direct AP Invoice (Without GRN), automatically record stock in and update purchase price
                if ($isDirect && $warehouseId && $item->isPhysical()) {
                    $oldStock = (float) $item->current_stock;
                    $oldCost = (float) $item->purchase_price;
                    $newQty = $qty;
                    $newCost = $price;

                    if ($oldStock + $newQty <= 0) {
                        $avgCost = $newCost;
                    } else {
                        $avgCost = (($oldStock * $oldCost) + ($newQty * $newCost)) / ($oldStock + $newQty);
                    }

                    $inventoryService->recordStockMovement(
                        $item->id,
                        $warehouseId,
                        'DIRECT_PURCHASE',
                        $invoice->id,
                        $invoice->invoice_number,
                        'in',
                        $qty,
                        $price,
                        'Pembelian Langsung Tagihan AP '.$invoice->invoice_number,
                        $request->invoice_date,
                        $itemData['koli'] ?? null
                    );

                    $item->update(['purchase_price' => round($avgCost, 2)]);
                }

                // Determine Debit Account: Direct vs GRN
                if ($isDirect) {
                    $debitAccId = $item->getEffectiveInventoryAccountId()
                        ?: ($item->getEffectivePurchaseAccountId() ?: ChartOfAccount::where('code', '1201')->first()?->id);
                } else {
                    $debitAccId = ChartOfAccount::where('code', '1301')->first()?->id
                        ?: ChartOfAccount::where('code', '2001')->first()?->id;
                }

                if ($debitAccId) {
                    $categoryDebits[$debitAccId] = ($categoryDebits[$debitAccId] ?? 0) + $line;
                }
            }

            // Double-entry Accounting Journal
            $apAccount = ChartOfAccount::where('code', '2001')->first() ?: ChartOfAccount::where('type', 'liability')->first();
            $taxAccount = ChartOfAccount::where('code', '1103')->first() ?: ChartOfAccount::where('code', '2003')->first();

            $journalEntries = [];

            foreach ($categoryDebits as $accId => $amount) {
                $journalEntries[] = [
                    'account_id' => $accId,
                    'type' => 'debit',
                    'amount' => $amount,
                    'memo' => ($isDirect ? 'Pembelian Persediaan Langsung ' : 'Tagihan GRN ').$invoice->invoice_number,
                ];
            }

            if ($taxAmount > 0 && $taxAccount) {
                $journalEntries[] = [
                    'account_id' => $taxAccount->id,
                    'type' => 'debit',
                    'amount' => $taxAmount,
                    'memo' => 'PPN Masukan Faktur Pembelian '.$invoice->invoice_number,
                ];
            }

            if ($apAccount) {
                $journalEntries[] = [
                    'account_id' => $apAccount->id,
                    'type' => 'credit',
                    'amount' => $grandTotal,
                    'memo' => 'Hutang Usaha AP '.$invoice->invoice_number.' - '.$invoice->supplier->name,
                ];
            }

            $journal = $journalService->createJournal(
                'PURCHASE_INVOICE',
                $invoice->id,
                $invoice->invoice_number,
                'Faktur Tagihan Hutang Supplier '.$invoice->invoice_number.' dari '.$invoice->supplier->name,
                $journalEntries,
                $request->invoice_date,
                auth()->id()
            );

            $invoice->update(['journal_id' => $journal->id]);
        });

        return redirect()->route('procurement.invoices.index')->with('success', 'Tagihan Supplier (AP) berhasil dicatat dan diposting.');
    }

    public function invoiceShow(PurchaseInvoice $invoice)
    {
        $invoice->load(['supplier', 'purchaseOrder', 'goodsReceipt', 'items.item.unit', 'journal.entries.account']);

        return view('procurement.invoices.show', compact('invoice'));
    }

    public function invoiceEdit(PurchaseInvoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Tagihan yang sudah lunas tidak dapat diedit. Silakan sesuaikan via menu pembayaran kas/bank.');
        }

        $suppliers = Contact::suppliers()->where('is_active', true)->get();
        $invoice->load(['supplier', 'purchaseOrder', 'goodsReceipt', 'items.item.unit']);

        return view('procurement.invoices.edit', compact('invoice', 'suppliers'));
    }

    public function invoiceUpdate(Request $request, PurchaseInvoice $invoice, JournalService $journalService)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Tagihan yang sudah lunas tidak dapat diedit.');
        }

        $request->validate([
            'supplier_id' => ['required', 'exists:contacts,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'supplier_invoice_number' => ['nullable', 'string'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $invoice, $journalService) {
            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $subtotal += ($itemData['quantity'] * $itemData['unit_price']);
            }

            $taxRate = (float) ($request->tax_rate ?? 0);
            $taxAmount = $taxRate > 0 ? round($subtotal * ($taxRate / 100), 2) : (float) ($request->tax_amount ?? 0);
            $grandTotal = round($subtotal + $taxAmount, 2);

            $invoice->update([
                'supplier_id' => $request->supplier_id,
                'supplier_invoice_number' => $request->supplier_invoice_number,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'notes' => $request->notes,
            ]);

            // Re-create items
            $invoice->items()->delete();
            foreach ($request->items as $itemData) {
                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'koli' => $itemData['koli'] ?? null,
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => round($itemData['quantity'] * $itemData['unit_price'], 2),
                ]);
            }

            // Sync Journal
            $unbilledAccount = ChartOfAccount::where('code', '1301')->first() ?: ChartOfAccount::where('code', '1201')->first();
            $apAccount = ChartOfAccount::where('code', '2001')->first();
            $taxAccount = ChartOfAccount::where('code', '2003')->first() ?: $apAccount;

            $journalEntries = [
                [
                    'account_id' => $unbilledAccount->id,
                    'type' => 'debit',
                    'amount' => $subtotal,
                    'memo' => 'Penyelesaian Unbilled GRN untuk Faktur '.$invoice->invoice_number,
                ],
            ];

            if ($taxAmount > 0) {
                $journalEntries[] = [
                    'account_id' => $taxAccount->id,
                    'type' => 'debit',
                    'amount' => $taxAmount,
                    'memo' => 'PPN Masukan Faktur '.$invoice->invoice_number,
                ];
            }

            $journalEntries[] = [
                'account_id' => $apAccount->id,
                'type' => 'credit',
                'amount' => $grandTotal,
                'memo' => 'Hutang Usaha atas Faktur '.$invoice->invoice_number,
            ];

            if ($invoice->journal_id && $invoice->journal) {
                $invoice->journal->entries()->delete();
                $invoice->journal->update([
                    'transaction_date' => $request->invoice_date,
                    'description' => 'Faktur Pembelian '.$invoice->invoice_number.' - '.$invoice->supplier->name,
                ]);
                foreach ($journalEntries as $entry) {
                    $invoice->journal->entries()->create($entry);
                }
            } else {
                $journal = $journalService->createJournal(
                    'PURCHASE_INVOICE',
                    $invoice->id,
                    $invoice->invoice_number,
                    'Faktur Pembelian '.$invoice->invoice_number.' - '.$invoice->supplier->name,
                    $journalEntries,
                    $request->invoice_date,
                    auth()->id()
                );
                $invoice->update(['journal_id' => $journal->id]);
            }
        });

        return redirect()->route('procurement.invoices.show', $invoice->id)->with('success', 'Faktur pembelian berhasil diperbarui dan jurnal Hutang Usaha disinkronkan.');
    }

    public function invoiceDestroy(PurchaseInvoice $invoice)
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

        return redirect()->route('procurement.invoices.index')->with('success', 'Faktur pembelian berhasil dihapus.');
    }

    public function poPrint(PurchaseOrder $po)
    {
        $po->load(['supplier', 'purchaseRequisition', 'createdByUser', 'items.item.unit']);

        return view('procurement.po.print', compact('po'));
    }

    public function grnPrint(GoodsReceipt $grn)
    {
        $grn->load(['supplier', 'purchaseOrder', 'warehouse', 'receivedBy', 'items.item.unit']);

        return view('procurement.grn.print', compact('grn'));
    }

    public function invoicePrint(PurchaseInvoice $invoice)
    {
        $invoice->load(['supplier', 'purchaseOrder', 'goodsReceipt', 'createdBy', 'items.item.unit']);

        return view('procurement.invoices.print', compact('invoice'));
    }

    // ==========================================
    // 5. PURCHASE RETURNS (RETUR PEMBELIAN)
    // ==========================================
    public function returnIndex(Request $request)
    {
        $query = PurchaseReturn::with(['supplier', 'warehouse', 'branch', 'purchaseInvoice', 'createdBy'])->latest();

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('return_number', 'like', "%{$s}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$s}%"));
            });
        }

        $returns = $query->paginate(15);
        $suppliers = Contact::suppliers()->orderBy('name')->get();

        return view('procurement.returns.index', compact('returns', 'suppliers'));
    }

    public function returnCreate(Request $request)
    {
        $suppliers = Contact::suppliers()->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $items = Item::where('is_active', true)->with('unit')->orderBy('name')->get();
        $invoices = PurchaseInvoice::with(['supplier', 'items.item.unit'])->latest()->get();
        $cashAccounts = ChartOfAccount::where('is_cash_or_bank', true)->where('is_active', true)->get();

        $selectedInvoiceId = $request->query('purchase_invoice_id');
        $selectedInvoice = $selectedInvoiceId ? PurchaseInvoice::with(['supplier', 'items.item.unit'])->find($selectedInvoiceId) : null;

        return view('procurement.returns.create', compact('suppliers', 'warehouses', 'branches', 'items', 'invoices', 'cashAccounts', 'selectedInvoice'));
    }

    public function returnStore(Request $request, InventoryService $inventoryService, JournalService $journalService)
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:contacts,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'return_date' => ['required', 'date'],
            'refund_method' => ['required', 'in:potong_hutang,tunai_kas'],
            'cash_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'purchase_invoice_id' => ['nullable', 'exists:purchase_invoices,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $return = DB::transaction(function () use ($request, $inventoryService, $journalService) {
            $returnNo = 'PRET-'.date('Ymd').'-'.str_pad(PurchaseReturn::count() + 1, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $subtotal += ($itemData['quantity'] * $itemData['unit_price']);
            }

            $taxRate = (float) ($request->tax_rate ?? 0);
            $taxAmount = $taxRate > 0 ? round($subtotal * ($taxRate / 100), 2) : (float) ($request->tax_amount ?? 0);
            $grandTotal = round($subtotal + $taxAmount, 2);

            $purchaseReturn = PurchaseReturn::create([
                'return_number' => $returnNo,
                'purchase_invoice_id' => $request->purchase_invoice_id,
                'supplier_id' => $request->supplier_id,
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

            $categoryCredits = [];

            foreach ($request->items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                $qty = (float) $itemData['quantity'];
                $price = (float) $itemData['unit_price'];
                $lineTotal = round($qty * $price, 2);

                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'item_id' => $item->id,
                    'unit_id' => $item->unit_id,
                    'quantity' => $qty,
                    'koli' => $itemData['koli'] ?? null,
                    'unit_price' => $price,
                    'total_price' => $lineTotal,
                    'reason' => $itemData['reason'] ?? null,
                ]);

                // Stock Out (Keluarkan stok dari gudang karena retur ke supplier)
                if ($item->isPhysical()) {
                    $inventoryService->recordStockMovement(
                        $item->id,
                        $request->warehouse_id,
                        'PURCHASE_RETURN',
                        $purchaseReturn->id,
                        $purchaseReturn->return_number,
                        'out',
                        $qty,
                        $price,
                        'Retur Pembelian '.$purchaseReturn->return_number.' ke Supplier',
                        $request->return_date,
                        $itemData['koli'] ?? null
                    );
                }

                $invAccId = $item->getEffectiveInventoryAccountId()
                    ?: ChartOfAccount::where('code', '1201')->first()?->id;

                if ($invAccId) {
                    $categoryCredits[$invAccId] = ($categoryCredits[$invAccId] ?? 0) + $lineTotal;
                }
            }

            // Journal entries:
            // Debit: Hutang Usaha (2001) / Kas Bank (if refund cash)
            // Credit: Persediaan Barang (1201) & PPN Masukan (if any)
            $apAccount = ChartOfAccount::where('code', '2001')->first() ?: ChartOfAccount::where('type', 'liability')->first();
            $refundAccId = ($request->refund_method === 'tunai_kas' && $request->cash_account_id)
                ? $request->cash_account_id
                : ($apAccount ? $apAccount->id : null);

            $taxAccount = ChartOfAccount::where('code', '1103')->first() ?: ChartOfAccount::where('code', '2003')->first();

            $journalEntries = [];

            if ($refundAccId) {
                $journalEntries[] = [
                    'account_id' => $refundAccId,
                    'type' => 'debit',
                    'amount' => $grandTotal,
                    'memo' => 'Retur Pembelian '.$purchaseReturn->return_number.' ('.$request->refund_method.')',
                ];
            }

            foreach ($categoryCredits as $accId => $amt) {
                $journalEntries[] = [
                    'account_id' => $accId,
                    'type' => 'credit',
                    'amount' => $amt,
                    'memo' => 'Pengurangan Persediaan Retur Pembelian '.$purchaseReturn->return_number,
                ];
            }

            if ($taxAmount > 0 && $taxAccount) {
                $journalEntries[] = [
                    'account_id' => $taxAccount->id,
                    'type' => 'credit',
                    'amount' => $taxAmount,
                    'memo' => 'Kredit PPN Masukan Retur Pembelian '.$purchaseReturn->return_number,
                ];
            }

            $journal = $journalService->createJournal(
                'PURCHASE_RETURN',
                $purchaseReturn->id,
                $purchaseReturn->return_number,
                'Retur Pembelian '.$purchaseReturn->return_number.' ke '.$purchaseReturn->supplier->name,
                $journalEntries,
                $request->return_date,
                auth()->id()
            );

            $purchaseReturn->update(['journal_id' => $journal->id]);

            // Adjust invoice remaining balance if linked
            if ($request->purchase_invoice_id && $request->refund_method === 'potong_hutang') {
                $inv = PurchaseInvoice::find($request->purchase_invoice_id);
                if ($inv) {
                    $newPaid = $inv->paid_amount + $grandTotal;
                    $inv->update([
                        'paid_amount' => $newPaid,
                        'status' => $newPaid >= $inv->grand_total ? 'paid' : 'partial',
                    ]);
                }
            }

            return $purchaseReturn;
        });

        return redirect()->route('procurement.returns.show', $return->id)->with('success', 'Retur Pembelian berhasil dicatat, stok gudang dipotong, dan jurnal diposting.');
    }

    public function returnShow(PurchaseReturn $return)
    {
        $return->load(['supplier', 'warehouse', 'branch', 'purchaseInvoice', 'cashAccount', 'items.item.unit', 'journal.entries.account', 'createdBy']);

        return view('procurement.returns.show', compact('return'));
    }

    public function returnPrint(PurchaseReturn $return)
    {
        $return->load(['supplier', 'warehouse', 'branch', 'purchaseInvoice', 'items.item.unit', 'createdBy']);

        return view('procurement.returns.print', compact('return'));
    }
}
