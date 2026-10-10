<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'type', // customer, supplier
        'amount',
        'transaction_type', // in, out
        'reference_type',
        'reference_id',
        'reference_no',
        'notes',
        'journal_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }
}
