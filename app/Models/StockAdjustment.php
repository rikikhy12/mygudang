<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'adjustment_number',
        'warehouse_id',
        'adjustment_date',
        'status', // draft, posted
        'reason',
        'adjusted_by_id',
        'journal_id',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    public function adjustedBy()
    {
        return $this->belongsTo(User::class, 'adjusted_by_id');
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }
}
