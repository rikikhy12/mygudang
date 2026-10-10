<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemUnitConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'unit_id',
        'conversion_factor',
        'selling_price_override',
        'purchase_price_override',
        'barcode',
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:4',
        'selling_price_override' => 'decimal:2',
        'purchase_price_override' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
