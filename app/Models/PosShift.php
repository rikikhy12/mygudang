<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'cash_account_id',
        'shift_number',
        'start_time',
        'end_time',
        'starting_cash',
        'total_cash_sales',
        'total_non_cash_sales',
        'total_drops',
        'closing_cash_expected',
        'closing_cash_actual',
        'cash_difference',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'starting_cash' => 'decimal:2',
        'total_cash_sales' => 'decimal:2',
        'total_non_cash_sales' => 'decimal:2',
        'total_drops' => 'decimal:2',
        'closing_cash_expected' => 'decimal:2',
        'closing_cash_actual' => 'decimal:2',
        'cash_difference' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'cash_account_id');
    }

    public function drops()
    {
        return $this->hasMany(PosShiftDrop::class);
    }

    public function invoices()
    {
        return $this->hasMany(SalesInvoice::class, 'pos_shift_id');
    }

    /**
     * Recalculate sales and expected cash dynamically.
     */
    public function recalculateTotals(): void
    {
        $cashSales = 0;
        $nonCashSales = 0;

        foreach ($this->invoices as $inv) {
            if ($inv->status !== 'cancelled') {
                if (!empty($inv->payment_details) && is_array($inv->payment_details)) {
                    foreach ($inv->payment_details as $p) {
                        $pMethod = strtolower($p['payment_method'] ?? 'cash');
                        $pAmount = (float) ($p['amount'] ?? 0);
                        if ($pMethod === 'cash') {
                            $cashSales += $pAmount;
                        } else {
                            $nonCashSales += $pAmount;
                        }
                    }
                } elseif (str_contains(strtolower($inv->notes ?? ''), 'tunai') || str_contains(strtolower($inv->notes ?? ''), 'cash')) {
                    $cashSales += (float) $inv->grand_total;
                } else {
                    $nonCashSales += (float) $inv->grand_total;
                }
            }
        }

        $totalDrops = (float) $this->drops()->sum('amount');
        $expectedCash = max(0, (float) $this->starting_cash + $cashSales - $totalDrops);

        $this->total_cash_sales = $cashSales;
        $this->total_non_cash_sales = $nonCashSales;
        $this->total_drops = $totalDrops;
        $this->closing_cash_expected = $expectedCash;

        if ($this->closing_cash_actual !== null) {
            $this->cash_difference = (float) $this->closing_cash_actual - $expectedCash;
        }

        $this->save();
    }
}
