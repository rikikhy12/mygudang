<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Item;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockLedger;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    // ==========================================
    // 1. STOCK OVERVIEW
    // ==========================================
    public function stockOverview(Request $request)
    {
        $warehouseId = $request->query('warehouse_id');
        $warehouses = Warehouse::where('is_active', true)->get();

        $items = Item::with(['category', 'unit'])
            ->where('item_type', '!=', 'service')
            ->where('is_active', true)
            ->get();

        $stockData = $items->map(function ($item) use ($warehouseId) {
            $stock = $item->calculateStock($warehouseId);
            $valuation = round($stock * $item->purchase_price, 2);

            return [
                'item' => $item,
                'stock' => $stock,
                'valuation' => $valuation,
            ];
        });

        return view('inventory.overview', compact('stockData', 'warehouses', 'warehouseId'));
    }

    // ==========================================
    // 2. STOCK MOVEMENTS (KARTU STOK)
    // ==========================================
    public function movements(Request $request)
    {
        $itemId = $request->query('item_id');
        $warehouseId = $request->query('warehouse_id');

        $query = StockLedger::with(['item.unit', 'warehouse'])->latest('transaction_date')->latest('id');

        if ($itemId) {
            $query->where('item_id', $itemId);
        }

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $ledgers = $query->get();
        $items = Item::where('item_type', '!=', 'service')->get();
        $warehouses = Warehouse::all();

        return view('inventory.movements', compact('ledgers', 'items', 'warehouses', 'itemId', 'warehouseId'));
    }

    // ==========================================
    // 3. STOCK ADJUSTMENT (PENYESUAIAN STOK)
    // ==========================================
    public function adjustmentIndex()
    {
        $adjustments = StockAdjustment::with(['warehouse', 'adjustedBy', 'journal'])->latest()->get();

        return view('inventory.adjustments.index', compact('adjustments'));
    }

    public function adjustmentCreate()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $items = Item::where('item_type', '!=', 'service')->where('is_active', true)->get();

        return view('inventory.adjustments.create', compact('warehouses', 'items'));
    }

    public function adjustmentStore(Request $request, InventoryService $inventoryService, JournalService $journalService)
    {
        $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'adjustment_date' => ['required', 'date'],
            'reason' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.physical_stock' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $inventoryService, $journalService) {
            $adjNumber = 'ADJ-'.date('Ymd').'-'.str_pad(StockAdjustment::count() + 1, 4, '0', STR_PAD_LEFT);

            $adjustment = StockAdjustment::create([
                'adjustment_number' => $adjNumber,
                'warehouse_id' => $request->warehouse_id,
                'adjustment_date' => $request->adjustment_date,
                'status' => 'posted',
                'reason' => $request->reason,
                'adjusted_by_id' => auth()->id(),
            ]);

            $totalVarianceCost = 0; // Positive = gain, Negative = loss
            $inventoryCategoryGains = [];
            $inventoryCategoryLosses = [];

            foreach ($request->items as $itemData) {
                $item = Item::with('category')->findOrFail($itemData['item_id']);
                $currentStock = $item->calculateStock($request->warehouse_id);
                $physicalStock = (float) $itemData['physical_stock'];
                $diffQty = $physicalStock - $currentStock;
                $unitCost = (float) $item->purchase_price;
                $diffAmount = round($diffQty * $unitCost, 2);

                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'item_id' => $item->id,
                    'system_stock' => $currentStock,
                    'physical_stock' => $physicalStock,
                    'difference_qty' => $diffQty,
                    'unit_cost' => $unitCost,
                    'total_amount' => $diffAmount,
                ]);

                if ($diffQty != 0) {
                    $type = $diffQty > 0 ? 'in' : 'out';
                    $inventoryService->recordStockMovement(
                        $item->id,
                        $request->warehouse_id,
                        'ADJUSTMENT',
                        $adjustment->id,
                        $adjustment->adjustment_number,
                        $type,
                        abs($diffQty),
                        $unitCost,
                        'Penyesuaian Stok: '.$request->reason,
                        $request->adjustment_date
                    );

                    $invAccount = $item->getEffectiveInventoryAccountId();
                    if ($invAccount) {
                        if ($diffQty > 0) {
                            $inventoryCategoryGains[$invAccount] = ($inventoryCategoryGains[$invAccount] ?? 0) + abs($diffAmount);
                        } else {
                            $inventoryCategoryLosses[$invAccount] = ($inventoryCategoryLosses[$invAccount] ?? 0) + abs($diffAmount);
                        }
                    }
                }
            }

            // Auto-Journal Stock Variance (6006 Beban Selisih Persediaan)
            $expenseAccount = ChartOfAccount::where('code', '6006')->first();
            $journalEntries = [];

            // For inventory loss (Physical < System):
            // Debit: 6006 Beban Selisih Persediaan
            // Credit: Akun Persediaan Kategori
            foreach ($inventoryCategoryLosses as $accId => $amt) {
                $journalEntries[] = [
                    'account_id' => $expenseAccount->id,
                    'type' => 'debit',
                    'amount' => $amt,
                    'memo' => 'Beban Selisih Stok Kurang '.$adjustment->adjustment_number,
                ];
                $journalEntries[] = [
                    'account_id' => $accId,
                    'type' => 'credit',
                    'amount' => $amt,
                    'memo' => 'Penyesuaian Pengurangan Persediaan '.$adjustment->adjustment_number,
                ];
            }

            // For inventory gain (Physical > System):
            // Debit: Akun Persediaan Kategori
            // Credit: 6006 Beban Selisih Persediaan (or Revenue)
            foreach ($inventoryCategoryGains as $accId => $amt) {
                $journalEntries[] = [
                    'account_id' => $accId,
                    'type' => 'debit',
                    'amount' => $amt,
                    'memo' => 'Penyesuaian Penambahan Persediaan '.$adjustment->adjustment_number,
                ];
                $journalEntries[] = [
                    'account_id' => $expenseAccount->id,
                    'type' => 'credit',
                    'amount' => $amt,
                    'memo' => 'Pendapatan/Koreksi Selisih Stok Lebih '.$adjustment->adjustment_number,
                ];
            }

            if (count($journalEntries) > 0) {
                $journal = $journalService->createJournal(
                    'ADJUSTMENT',
                    $adjustment->id,
                    $adjustment->adjustment_number,
                    'Jurnal Penyesuaian Stok Opname '.$adjustment->adjustment_number.' ('.$adjustment->reason.')',
                    $journalEntries,
                    $request->adjustment_date,
                    auth()->id()
                );

                $adjustment->update(['journal_id' => $journal->id]);
            }
        });

        return redirect()->route('inventory.adjustments.index')->with('success', 'Penyesuaian stok berhasil diposting dan jurnal tercatat otomatis.');
    }

    public function adjustmentShow(StockAdjustment $adjustment)
    {
        $adjustment->load(['warehouse', 'adjustedBy', 'items.item.unit', 'journal.entries.account']);

        return view('inventory.adjustments.show', compact('adjustment'));
    }

    // ==========================================
    // 4. STOCK TRANSFER (TRANSFER ANTAR GUDANG)
    // ==========================================
    public function transferIndex()
    {
        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'creator'])->latest()->get();

        return view('inventory.transfers.index', compact('transfers'));
    }

    public function transferCreate()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $items = Item::where('item_type', '!=', 'service')->where('is_active', true)->get();

        return view('inventory.transfers.create', compact('warehouses', 'items'));
    }

    public function transferStore(Request $request, InventoryService $inventoryService)
    {
        $request->validate([
            'from_warehouse_id' => ['required', 'exists:warehouses,id', 'different:to_warehouse_id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id'],
            'transfer_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ]);

        DB::transaction(function () use ($request, $inventoryService) {
            $transferNumber = 'TRF-'.date('Ymd').'-'.str_pad(StockTransfer::count() + 1, 4, '0', STR_PAD_LEFT);

            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'transfer_date' => $request->transfer_date,
                'status' => 'completed',
                'notes' => $request->notes,
                'created_by_id' => auth()->id(),
            ]);

            foreach ($request->items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                $qty = (float) $itemData['quantity'];
                $unitCost = (float) $item->purchase_price;

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'item_id' => $item->id,
                    'quantity' => $qty,
                ]);

                // Record movement out from source warehouse
                $inventoryService->recordStockMovement(
                    $item->id,
                    $request->from_warehouse_id,
                    'TRANSFER',
                    $transfer->id,
                    $transfer->transfer_number,
                    'out',
                    $qty,
                    $unitCost,
                    'Transfer Keluar ke '.$transfer->toWarehouse->name,
                    $request->transfer_date
                );

                // Record movement in to destination warehouse
                $inventoryService->recordStockMovement(
                    $item->id,
                    $request->to_warehouse_id,
                    'TRANSFER',
                    $transfer->id,
                    $transfer->transfer_number,
                    'in',
                    $qty,
                    $unitCost,
                    'Transfer Masuk dari '.$transfer->fromWarehouse->name,
                    $request->transfer_date
                );
            }
        });

        return redirect()->route('inventory.transfers.index')->with('success', 'Transfer barang antar gudang berhasil diselesaikan.');
    }

    public function transferShow(StockTransfer $transfer)
    {
        $transfer->load(['fromWarehouse', 'toWarehouse', 'creator', 'items.item.unit']);

        return view('inventory.transfers.show', compact('transfer'));
    }
}
