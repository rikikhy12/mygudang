<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'transaction_date',
        'reference_type', // GRN, DELIVERY_ORDER, ADJUSTMENT, TRANSFER, OPENING_BALANCE
        'reference_id',
        'reference_no',
        'type', // in, out
        'quantity',
        'koli',
        'unit_cost',
        'total_cost',
        'current_stock_after',
        'current_koli_after',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'quantity' => 'decimal:2',
        'koli' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'current_stock_after' => 'decimal:2',
        'current_koli_after' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
