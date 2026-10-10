<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'type', // cash_in, cash_out, transfer
        'account_id',
        'target_account_id',
        'contact_id',
        'transaction_date',
        'reference_no',
        'total_amount',
        'description',
        'status', // posted, void
        'created_by_id',
        'journal_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function targetAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'target_account_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function items()
    {
        return $this->hasMany(CashTransactionItem::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
