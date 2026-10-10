<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', // customer, supplier, both
        'min_margin_percent',
        'code',
        'name',
        'email',
        'phone',
        'address',
        'tax_number',
        'is_active',
    ];

    protected $casts = [
        'min_margin_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeCustomers($query)
    {
        return $query->whereIn('type', ['customer', 'both']);
    }

    public function scopeSuppliers($query)
    {
        return $query->whereIn('type', ['supplier', 'both']);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'customer_id');
    }

    public function deposits()
    {
        return $this->hasMany(ContactDeposit::class);
    }

    public function getDepositBalance(): float
    {
        $in = (float) $this->deposits()->where('transaction_type', 'in')->sum('amount');
        $out = (float) $this->deposits()->where('transaction_type', 'out')->sum('amount');

        return round($in - $out, 2);
    }
}
