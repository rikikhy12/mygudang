<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'item_id',
        'yield_quantity',
        'overhead_cost',
        'labor_cost',
        'description',
        'is_active',
    ];

    protected $casts = [
        'yield_quantity' => 'decimal:4',
        'overhead_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function items()
    {
        return $this->hasMany(BomItem::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    /**
     * Calculate estimated total component material cost
     */
    public function calculateMaterialCost(): float
    {
        return (float) $this->items->sum(function ($bomItem) {
            $cost = $bomItem->unit_cost ?: ($bomItem->item ? $bomItem->item->purchase_price : 0);

            return $bomItem->quantity * $cost;
        });
    }

    /**
     * Calculate total BOM unit production cost
     */
    public function calculateTotalCost(): float
    {
        $materialCost = $this->calculateMaterialCost();
        $totalCost = $materialCost + (float) $this->overhead_cost + (float) $this->labor_cost;
        $yield = (float) $this->yield_quantity ?: 1.0;

        return round($totalCost / $yield, 2);
    }
}
