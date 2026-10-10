<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemBranchPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'branch_id',
        'selling_price',
        'purchase_price',
        'min_margin_percent',
        'min_stock',
        'is_available',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'min_margin_percent' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
