<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_code',
        'name',
        'category_id',
        'acquisition_date',
        'acquisition_cost',
        'salvage_value',
        'useful_life_months',
        'depreciation_method', // straight_line
        'accumulated_depreciation',
        'current_book_value',
        'status', // active, disposed
        'journal_id',
        'notes',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'acquisition_cost' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'current_book_value' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(FixedAssetCategory::class, 'category_id');
    }

    public function depreciations()
    {
        return $this->hasMany(AssetDepreciation::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function getDepreciationMethodLabelAttribute(): string
    {
        return match ($this->depreciation_method) {
            'straight_line' => 'Garis Lurus (Straight-line)',
            'declining_balance' => 'Saldo Menurun Ganda (Declining Balance)',
            'none' => 'Tidak Disusutkan (Tanah / Non-Depreciable)',
            default => 'Garis Lurus (Straight-line)',
        };
    }

    public function calculateMonthlyDepreciation(): float
    {
        if ($this->depreciation_method === 'none' || $this->useful_life_months <= 0) {
            return 0;
        }

        if ($this->depreciation_method === 'declining_balance') {
            // Double Declining Balance: Rate = 2 / useful_life_months
            // Monthly depr = current_book_value * (2 / useful_life_months)
            $rate = 2 / max(1, $this->useful_life_months);
            $monthlyDepr = $this->current_book_value * $rate;
            $maxDepreciable = max(0, $this->current_book_value - $this->salvage_value);
            return round(min($monthlyDepr, $maxDepreciable), 2);
        }

        // Default: straight_line
        $depreciableAmount = max(0, (float) ($this->acquisition_cost - $this->salvage_value));

        return round($depreciableAmount / $this->useful_life_months, 2);
    }
}
