<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'do_number',
        'sales_order_id',
        'customer_id',
        'warehouse_id',
        'delivery_date',
        'driver_name',
        'vehicle_number',
        'status', // draft, posted
        'dispatched_by_id',
        'journal_id',
        'notes',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Contact::class, 'customer_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryOrderItem::class);
    }

    public function dispatchedBy()
    {
        return $this->belongsTo(User::class, 'dispatched_by_id');
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function invoices()
    {
        return $this->hasMany(SalesInvoice::class);
    }
}
