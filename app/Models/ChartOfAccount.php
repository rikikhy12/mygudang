<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type', // asset, liability, equity, revenue, cogs, expense
        'normal_balance', // debit, credit
        'parent_id',
        'is_cash_or_bank',
        'cash_flow_category', // operating, investing, financing, none
        'is_active',
        'description',
    ];

    public function getCashFlowCategoryLabelAttribute(): string
    {
        return match ($this->cash_flow_category) {
            'operating' => 'Aktivitas Operasi (Operating)',
            'investing' => 'Aktivitas Investasi (Investing)',
            'financing' => 'Aktivitas Pendanaan (Financing)',
            'none' => 'Non-Arus Kas / Tidak Berpengaruh',
            default => 'Aktivitas Operasi (Default)',
        };
    }

    protected $casts = [
        'is_cash_or_bank' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'account_id');
    }

    public function getFormattedNameAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }

    /**
     * Calculate current account balance based on journal entries up to a certain date.
     */
    public function calculateBalance(?string $startDate = null, ?string $endDate = null): float
    {
        $query = $this->journalEntries()->whereHas('journal', function ($q) use ($startDate, $endDate) {
            $q->where('status', 'posted');
            if ($startDate) {
                $q->whereDate('transaction_date', '>=', $startDate);
            }
            if ($endDate) {
                $q->whereDate('transaction_date', '<=', $endDate);
            }
        });

        $debitTotal = (clone $query)->where('type', 'debit')->sum('amount');
        $creditTotal = (clone $query)->where('type', 'credit')->sum('amount');

        if ($this->normal_balance === 'debit') {
            return (float) ($debitTotal - $creditTotal);
        } else {
            return (float) ($creditTotal - $debitTotal);
        }
    }
}
