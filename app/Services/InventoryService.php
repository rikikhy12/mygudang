<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Item;
use App\Models\StockLedger;
use App\Models\WorkOrder;
use Exception;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Record physical stock movement in Stock Ledger and update Item balance.
     */
    public function recordStockMovement(
        int $itemId,
        int $warehouseId,
        string $referenceType,
        ?int $referenceId,
        ?string $referenceNo,
        string $type, // 'in' or 'out'
        float $quantity,
        float $unitCost,
        ?string $notes = null,
        ?string $transactionDate = null
    ): StockLedger {
        $transactionDate = $transactionDate ?: date('Y-m-d');
        $item = Item::findOrFail($itemId);

        // If service, no physical ledger needed
        if (! $item->isPhysical()) {
            throw new Exception('Barang bertipe Jasa tidak memiliki pergerakan stok fisik.');
        }

        return DB::transaction(function () use (
            $item, $warehouseId, $referenceType, $referenceId, $referenceNo, $type, $quantity, $unitCost, $notes, $transactionDate
        ) {
            $currentStock = $item->current_stock;
            $quantity = abs($quantity);
            $totalCost = round($quantity * $unitCost, 2);

            if ($type === 'in') {
                $newStock = $currentStock + $quantity;
            } else {
                $newStock = $currentStock - $quantity;
            }

            $ledger = StockLedger::create([
                'item_id' => $item->id,
                'warehouse_id' => $warehouseId,
                'transaction_date' => $transactionDate,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reference_no' => $referenceNo,
                'type' => $type,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'current_stock_after' => $newStock,
                'notes' => $notes,
            ]);

            $item->update(['current_stock' => $newStock]);

            return $ledger;
        });
    }

    /**
     * Deduct constituent ingredients/components for Bundle items on-the-fly.
     * Returns array of movements and total COGS for accounting.
     *
     * @return array{total_cogs: float, components: array}
     */
    public function deductBundleComponents(
        Item $bundleItem,
        int $warehouseId,
        float $bundleQuantity,
        string $referenceType,
        ?int $referenceId,
        ?string $referenceNo,
        ?string $transactionDate = null
    ): array {
        $transactionDate = $transactionDate ?: date('Y-m-d');
        $bom = $bundleItem->activeBom ?: $bundleItem->boms()->first();

        if (! $bom) {
            // If bundle has no BOM configured, treat as direct stock movement of the bundle itself if physical
            $cost = (float) $bundleItem->purchase_price;
            $this->recordStockMovement(
                $bundleItem->id,
                $warehouseId,
                $referenceType,
                $referenceId,
                $referenceNo,
                'out',
                $bundleQuantity,
                $cost,
                "Penjualan Paket: {$bundleItem->name}",
                $transactionDate
            );

            return [
                'total_cogs' => round($bundleQuantity * $cost, 2),
                'components' => [],
            ];
        }

        $bom->load('items.item');
        $yield = (float) $bom->yield_quantity ?: 1.0;
        $totalCogs = 0.0;
        $processedComponents = [];

        foreach ($bom->items as $bomItem) {
            $material = $bomItem->item;
            if (! $material) {
                continue;
            }

            $requiredQty = round(($bundleQuantity * (float) $bomItem->quantity) / $yield, 4);
            $unitCost = (float) ($bomItem->unit_cost ?: $material->purchase_price);
            $lineCogs = round($requiredQty * $unitCost, 2);
            $totalCogs += $lineCogs;

            // If material is itself a sub-bundle, recurse
            if ($material->isBundle()) {
                $subResult = $this->deductBundleComponents(
                    $material,
                    $warehouseId,
                    $requiredQty,
                    $referenceType,
                    $referenceId,
                    $referenceNo,
                    $transactionDate
                );
                $totalCogs += $subResult['total_cogs'];
            } elseif ($material->isPhysical()) {
                $this->recordStockMovement(
                    $material->id,
                    $warehouseId,
                    $referenceType,
                    $referenceId,
                    $referenceNo,
                    'out',
                    $requiredQty,
                    $unitCost,
                    "Pemakaian Komponen BOM '{$bom->name}' untuk Paket '{$bundleItem->name}'",
                    $transactionDate
                );
            }

            $processedComponents[] = [
                'item_id' => $material->id,
                'item_name' => $material->name,
                'quantity' => $requiredQty,
                'unit_cost' => $unitCost,
                'total_cost' => $lineCogs,
                'inventory_account_id' => $material->getEffectiveInventoryAccountId(),
                'cogs_account_id' => $material->getEffectiveCogsAccountId(),
            ];
        }

        return [
            'total_cogs' => round($totalCogs, 2),
            'components' => $processedComponents,
        ];
    }

    /**
     * Process completion of a Work Order (Manufacturing Finished Good).
     * Deducts materials from raw material warehouse, adds finished goods to finished good warehouse,
     * and posts journal entry (Debit Persediaan Barang Jadi, Credit Persediaan Bahan Baku).
     */
    public function processWorkOrderCompletion(
        WorkOrder $workOrder,
        ?float $producedQuantity = null,
        ?int $userId = null
    ): WorkOrder {
        return DB::transaction(function () use ($workOrder, $producedQuantity, $userId) {
            $workOrder->load(['item', 'bom.items.item']);
            $bom = $workOrder->bom;

            $qtyToProduce = $producedQuantity ?: (float) $workOrder->target_quantity;
            $yield = (float) ($bom->yield_quantity ?: 1.0);
            $multiplier = $qtyToProduce / $yield;

            $totalMaterialCost = 0.0;
            $rawWarehouseId = $workOrder->raw_material_warehouse_id;
            $finishWarehouseId = $workOrder->finished_good_warehouse_id;
            $today = date('Y-m-d');

            // 1. Deduct raw materials / ingredients
            foreach ($bom->items as $bomItem) {
                $mat = $bomItem->item;
                if (! $mat || ! $mat->isPhysical()) {
                    continue;
                }

                $consumedQty = round((float) $bomItem->quantity * $multiplier, 4);
                $costPerUnit = (float) ($bomItem->unit_cost ?: $mat->purchase_price);
                $lineCost = round($consumedQty * $costPerUnit, 2);
                $totalMaterialCost += $lineCost;

                $this->recordStockMovement(
                    $mat->id,
                    $rawWarehouseId,
                    'WORK_ORDER_RAW',
                    $workOrder->id,
                    $workOrder->wo_number,
                    'out',
                    $consumedQty,
                    $costPerUnit,
                    "Pemakaian Bahan Produksi WO: {$workOrder->wo_number}",
                    $today
                );
            }

            // Additional labor & overhead cost
            $overhead = (float) $bom->overhead_cost * $multiplier;
            $labor = (float) $bom->labor_cost * $multiplier;
            $totalProductionCost = round($totalMaterialCost + $overhead + $labor, 2);
            $unitCostFinishedGood = $qtyToProduce > 0 ? round($totalProductionCost / $qtyToProduce, 2) : 0;

            // 2. Increase finished goods stock
            $this->recordStockMovement(
                $workOrder->item_id,
                $finishWarehouseId,
                'WORK_ORDER_FINISHED',
                $workOrder->id,
                $workOrder->wo_number,
                'in',
                $qtyToProduce,
                $unitCostFinishedGood,
                "Hasil Produksi Selesai WO: {$workOrder->wo_number}",
                $today
            );

            // 3. Post Double-entry Accounting Journal
            $journalService = app(JournalService::class);
            $finishedGoodItem = $workOrder->item;

            $finishedGoodInvAccId = $finishedGoodItem->getEffectiveInventoryAccountId()
                ?: ChartOfAccount::where('code', '1201')->first()?->id;

            $rawMaterialInvAccId = ChartOfAccount::where('code', '1202')->first()?->id
                ?: ($finishedGoodInvAccId);

            $journalEntries = [];
            if ($finishedGoodInvAccId && $rawMaterialInvAccId && $totalProductionCost > 0) {
                // Debit: Persediaan Barang Jadi
                $journalEntries[] = [
                    'account_id' => $finishedGoodInvAccId,
                    'type' => 'debit',
                    'amount' => $totalProductionCost,
                    'memo' => "Hasil Produksi Barang Jadi WO {$workOrder->wo_number}",
                ];

                // Credit: Persediaan Bahan Baku (Material Cost)
                if ($totalMaterialCost > 0) {
                    $journalEntries[] = [
                        'account_id' => $rawMaterialInvAccId,
                        'type' => 'credit',
                        'amount' => $totalMaterialCost,
                        'memo' => "Pemakaian Bahan Baku WO {$workOrder->wo_number}",
                    ];
                }

                // Credit: Overhead / WIP (if any)
                $otherCosts = round($overhead + $labor, 2);
                if ($otherCosts > 0) {
                    $wipAccount = ChartOfAccount::where('code', '5001')->first() ?: ChartOfAccount::where('type', 'expense')->first();
                    if ($wipAccount) {
                        $journalEntries[] = [
                            'account_id' => $wipAccount->id,
                            'type' => 'credit',
                            'amount' => $otherCosts,
                            'memo' => "Alokasi Biaya Tenaga Kerja & Overhead WO {$workOrder->wo_number}",
                        ];
                    }
                }

                $journal = $journalService->createJournal(
                    'WORK_ORDER',
                    $workOrder->id,
                    $workOrder->wo_number,
                    "Penyelesaian Produksi Work Order {$workOrder->wo_number} - {$finishedGoodItem->name}",
                    $journalEntries,
                    $today,
                    $userId ?: auth()->id()
                );

                $workOrder->journal_id = $journal->id;
            }

            // Update Work Order
            $workOrder->status = 'completed';
            $workOrder->produced_quantity = $qtyToProduce;
            $workOrder->save();

            return $workOrder;
        });
    }
}
