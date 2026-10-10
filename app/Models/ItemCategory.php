<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'type', // service, finished_good, manufactured_good
        'inventory_account_id',
        'cogs_account_id',
        'purchase_account_id',
        'sales_account_id',
        'description',
        'min_margin_percent',
    ];

    protected $casts = [
        'min_margin_percent' => 'decimal:2',
    ];

    public function parent()
    {
        return $this->belongsTo(ItemCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ItemCategory::class, 'parent_id');
    }

    public function inventoryAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'inventory_account_id');
    }

    public function cogsAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'cogs_account_id');
    }

    public function purchaseAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'purchase_account_id');
    }

    public function salesAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'sales_account_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'category_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'service' => 'Jasa (Service)',
            'finished_good' => 'Barang Siap Jadi',
            'manufactured_good' => 'Barang Hasil Produksi',
            default => $this->type,
        };
    }

    public function getHierarchyNameAttribute(): string
    {
        return $this->parent ? $this->parent->name.' > '.$this->name : $this->name;
    }
}
