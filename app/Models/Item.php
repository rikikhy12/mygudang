<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'barcode',
        'name',
        'image',
        'category_id',
        'brand_id',
        'unit_id',
        'item_type', // service, finished_good, manufactured_good
        'inventory_account_id',
        'cogs_account_id',
        'sales_account_id',
        'purchase_account_id',
        'purchase_price',
        'selling_price',
        'allow_change_price',
        'min_margin_percent',
        'min_stock',
        'current_stock',
        'current_koli',
        'is_active',
        'description',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'allow_change_price' => 'boolean',
        'min_margin_percent' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'current_stock' => 'decimal:2',
        'current_koli' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function canChangePrice(): bool
    {
        return (bool) $this->allow_change_price;
    }

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function stockLedgers()
    {
        return $this->hasMany(StockLedger::class);
    }

    public function inventoryAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'inventory_account_id');
    }

    public function cogsAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'cogs_account_id');
    }

    public function salesAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'sales_account_id');
    }

    public function purchaseAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'purchase_account_id');
    }

    /**
     * Get effective GL accounts with fallback to category default
     */
    public function getEffectiveInventoryAccountId(): ?int
    {
        return $this->inventory_account_id ?: $this->category?->inventory_account_id;
    }

    public function getEffectiveCogsAccountId(): ?int
    {
        return $this->cogs_account_id ?: $this->category?->cogs_account_id;
    }

    public function getEffectiveSalesAccountId(): ?int
    {
        return $this->sales_account_id ?: $this->category?->sales_account_id;
    }

    public function getEffectivePurchaseAccountId(): ?int
    {
        return $this->purchase_account_id ?: $this->category?->purchase_account_id;
    }

    /**
     * Get effective minimum margin percentage (item override or category default)
     */
    public function getEffectiveMinMarginPercent(): float
    {
        if ($this->min_margin_percent !== null) {
            return (float) $this->min_margin_percent;
        }

        return (float) ($this->category?->min_margin_percent ?? 0);
    }

    /**
     * Get minimum allowed selling price based on HPP (purchase_price) and margin
     */
    public function getMinimumSellingPrice(?float $customMarginPercent = null): float
    {
        // Service items do not have physical HPP or margin limit restriction
        if (! $this->isPhysical() || $this->item_type === 'service' || $this->category?->type === 'service') {
            return 0.0;
        }

        $cost = (float) $this->purchase_price;
        $marginPercent = $customMarginPercent !== null ? (float) $customMarginPercent : $this->getEffectiveMinMarginPercent();

        if ($marginPercent <= 0 || $cost <= 0) {
            return $cost > 0 ? $cost : 0.0;
        }

        return round($cost * (1 + ($marginPercent / 100)), 2);
    }

    public function unitConversions()
    {
        return $this->hasMany(ItemUnitConversion::class);
    }

    public function branchPrices()
    {
        return $this->hasMany(ItemBranchPrice::class);
    }

    public function warehousePrices()
    {
        return $this->hasMany(ItemWarehousePrice::class);
    }

    public function boms()
    {
        return $this->hasMany(Bom::class);
    }

    public function activeBom()
    {
        return $this->hasOne(Bom::class)->where('is_active', true);
    }

    public function isRawMaterial(): bool
    {
        return $this->item_type === 'raw_material';
    }

    public function isSuperProduct(): bool
    {
        return $this->item_type === 'super_product';
    }

    public function isBundle(): bool
    {
        return $this->item_type === 'bundle';
    }

    public function isManufactured(): bool
    {
        return $this->item_type === 'manufactured_good';
    }

    public function canBeSold(): bool
    {
        if ($this->isRawMaterial()) {
            return false;
        }

        return true;
    }

    /**
     * Get selling price with optional warehouse override and unit conversion
     */
    public function getPriceForWarehouse(?int $warehouseId = null, ?int $unitId = null): float
    {
        $price = (float) $this->selling_price;

        if ($warehouseId) {
            $whPrice = $this->warehousePrices->where('warehouse_id', $warehouseId)->first();
            if ($whPrice && $whPrice->selling_price !== null && $whPrice->selling_price > 0) {
                $price = (float) $whPrice->selling_price;
            }
        }

        if ($unitId && $unitId !== $this->unit_id) {
            $conv = $this->unitConversions->where('unit_id', $unitId)->first();
            if ($conv) {
                if ($conv->selling_price_override !== null && $conv->selling_price_override > 0) {
                    return (float) $conv->selling_price_override;
                }
                if ($conv->conversion_factor > 0) {
                    return round($price * (float) $conv->conversion_factor, 2);
                }
            }
        }

        return $price;
    }

    /**
     * Get HPP / purchase price with optional warehouse override and unit conversion
     */
    public function getPurchasePriceForWarehouse(?int $warehouseId = null, ?int $unitId = null): float
    {
        $cost = (float) $this->purchase_price;

        if ($warehouseId) {
            $whPrice = $this->warehousePrices->where('warehouse_id', $warehouseId)->first();
            if ($whPrice && $whPrice->purchase_price !== null && $whPrice->purchase_price > 0) {
                $cost = (float) $whPrice->purchase_price;
            }
        }

        if ($unitId && $unitId !== $this->unit_id) {
            $conv = $this->unitConversions->where('unit_id', $unitId)->first();
            if ($conv) {
                if ($conv->purchase_price_override !== null && $conv->purchase_price_override > 0) {
                    return (float) $conv->purchase_price_override;
                }
                if ($conv->conversion_factor > 0) {
                    return round($cost * (float) $conv->conversion_factor, 2);
                }
            }
        }

        return $cost;
    }

    public function isService(): bool
    {
        if ($this->item_type === 'service') {
            return true;
        }
        if ($this->item_type !== null) {
            return false;
        }

        return $this->category && $this->category->type === 'service';
    }

    public function isPhysical(): bool
    {
        return ! $this->isService();
    }

    public function calculateStock(?int $warehouseId = null): float
    {
        if (! $this->isPhysical()) {
            return 0;
        }

        $query = $this->stockLedgers();
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $in = (clone $query)->where('type', 'in')->sum('quantity');
        $out = (clone $query)->where('type', 'out')->sum('quantity');

        return (float) ($in - $out);
    }

    public function calculateKoliStock(?int $warehouseId = null): float
    {
        if (! $this->isPhysical()) {
            return 0;
        }

        $query = $this->stockLedgers();
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $in = (clone $query)->where('type', 'in')->sum('koli');
        $out = (clone $query)->where('type', 'out')->sum('koli');

        return (float) ($in - $out);
    }

    /**
     * Format catch weight / meat stock breakdown:
     * e.g. "2 Bal (60.00 Kg)" or "1 Koli (25.00 Kg)"
     */
    public function getFormattedMeatStock(?float $kg = null, ?float $koli = null): string
    {
        $kg = $kg !== null ? (float) $kg : (float) $this->current_stock;
        $koli = $koli !== null ? (float) $koli : (float) ($this->current_koli ?? 0);
        $unitName = $this->unit ? ($this->unit->symbol ?: $this->unit->name) : 'Kg';

        if (! $this->isPhysical() || $this->isService()) {
            return '-';
        }

        if ($koli > 0) {
            $koliStr = (fmod($koli, 1) === 0.0) ? number_format($koli, 0) : number_format($koli, 2);

            return "{$koliStr} Bal/Koli (".number_format($kg, 2, ',', '.')." {$unitName})";
        }

        return number_format($kg, 2, ',', '.')." {$unitName}";
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }
        if (file_exists(public_path('uploads/'.$this->image))) {
            return asset('uploads/'.$this->image);
        }
        if (file_exists(public_path('storage/'.$this->image))) {
            return asset('storage/'.$this->image);
        }
        if (file_exists(storage_path('app/public/'.$this->image))) {
            return url('/storage/'.$this->image);
        }

        return url('/storage/'.$this->image);
    }

    /**
     * Get stock breakdown text in multi-UOM representation (e.g. 26 PCS with 1 DUS=24 PCS => '1 DUS 2 PCS')
     */
    public function getFormattedStockBreakdown(?float $qty = null): string
    {
        $qty = $qty !== null ? (float) $qty : (float) $this->current_stock;
        $baseUnitName = $this->unit ? ($this->unit->symbol ?: $this->unit->name) : 'Pcs';

        if (! $this->isPhysical() || $this->isService()) {
            return '-';
        }

        // Find primary packing unit conversion with factor > 1
        $conv = $this->unitConversions
            ->where('conversion_factor', '>', 1)
            ->sortByDesc('conversion_factor')
            ->first();

        if (! $conv || ! $conv->unit) {
            return number_format($qty, 0, ',', '.').' '.$baseUnitName;
        }

        $factor = (float) $conv->conversion_factor;
        $highUnitName = $conv->unit->symbol ?: $conv->unit->name;

        if ($qty <= 0) {
            return number_format($qty, 0, ',', '.').' '.$baseUnitName;
        }

        $highQty = (int) floor($qty / $factor);
        $remainder = fmod($qty, $factor);

        if ($highQty > 0 && $remainder > 0) {
            return "{$highQty} {$highUnitName} ".number_format($remainder, 0, ',', '.')." {$baseUnitName} (".number_format($qty, 0, ',', '.')." {$baseUnitName})";
        } elseif ($highQty > 0 && $remainder == 0) {
            return "{$highQty} {$highUnitName} (".number_format($qty, 0, ',', '.')." {$baseUnitName})";
        } else {
            return number_format($qty, 0, ',', '.')." {$baseUnitName}";
        }
    }

    public function getStockBreakdownTextAttribute(): string
    {
        return $this->getFormattedStockBreakdown();
    }
}
