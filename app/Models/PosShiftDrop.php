<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosShiftDrop extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_shift_id',
        'user_id',
        'from_account_id',
        'to_account_id',
        'amount',
        'drop_time',
        'notes',
        'journal_id',
    ];

    protected $casts = [
        'drop_time' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function shift()
    {
        return $this->belongsTo(PosShift::class, 'pos_shift_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'to_account_id');
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }
}
