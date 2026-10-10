<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use Carbon\Carbon;

class FinancialReportService
{
    /**
     * Get General Ledger details for an account within a date range.
     */
    public function getGeneralLedger(int $accountId, string $startDate, string $endDate): array
    {
        $account = ChartOfAccount::findOrFail($accountId);

        // Opening Balance (before start date)
        $openingQuery = JournalEntry::where('account_id', $accountId)
            ->whereHas('journal', function ($q) use ($startDate) {
                $q->where('status', 'posted')
                    ->whereDate('transaction_date', '<', $startDate);
            });

        $openingDebit = (clone $openingQuery)->where('type', 'debit')->sum('amount');
        $openingCredit = (clone $openingQuery)->where('type', 'credit')->sum('amount');

        $openingBalance = $account->normal_balance === 'debit'
            ? ($openingDebit - $openingCredit)
            : ($openingCredit - $openingDebit);

        // Current period entries
        $entries = JournalEntry::with('journal')
            ->where('account_id', $accountId)
            ->whereHas('journal', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted')
                    ->whereDate('transaction_date', '>=', $startDate)
                    ->whereDate('transaction_date', '<=', $endDate);
            })
            ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->orderBy('journals.transaction_date', 'asc')
            ->orderBy('journals.id', 'asc')
            ->select('journal_entries.*')
            ->get();

        $runningBalance = $openingBalance;
        $formattedEntries = [];

        foreach ($entries as $entry) {
            $debit = $entry->type === 'debit' ? (float) $entry->amount : 0;
            $credit = $entry->type === 'credit' ? (float) $entry->amount : 0;

            if ($account->normal_balance === 'debit') {
                $runningBalance += ($debit - $credit);
            } else {
                $runningBalance += ($credit - $debit);
            }

            $formattedEntries[] = [
                'date' => $entry->journal->transaction_date->format('Y-m-d'),
                'journal_number' => $entry->journal->journal_number,
                'reference' => $entry->journal->reference_no ?: $entry->journal->reference_type,
                'description' => $entry->memo ?: $entry->journal->description,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $runningBalance,
            ];
        }

        return [
            'account' => $account,
            'opening_balance' => $openingBalance,
            'entries' => $formattedEntries,
            'closing_balance' => $runningBalance,
            'total_debit' => collect($formattedEntries)->sum('debit'),
            'total_credit' => collect($formattedEntries)->sum('credit'),
        ];
    }

    /**
     * Get Trial Balance (Neraca Saldo)
     */
    public function getTrialBalance(string $asOfDate): array
    {
        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();
        $report = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $query = JournalEntry::where('account_id', $account->id)
                ->whereHas('journal', function ($q) use ($asOfDate) {
                    $q->where('status', 'posted')
                        ->whereDate('transaction_date', '<=', $asOfDate);
                });

            $debit = (clone $query)->where('type', 'debit')->sum('amount');
            $credit = (clone $query)->where('type', 'credit')->sum('amount');

            $balance = $debit - $credit;

            $debitBal = 0;
            $creditBal = 0;

            if ($balance > 0) {
                $debitBal = $balance;
            } elseif ($balance < 0) {
                $creditBal = abs($balance);
            }

            if ($debitBal > 0 || $creditBal > 0) {
                $report[] = [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type,
                    'debit' => $debitBal,
                    'credit' => $creditBal,
                ];

                $totalDebit += $debitBal;
                $totalCredit += $creditBal;
            }
        }

        return [
            'as_of_date' => $asOfDate,
            'accounts' => $report,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }

    /**
     * Get Profit & Loss (Laba Rugi)
     */
    public function getProfitAndLoss(string $startDate, string $endDate): array
    {
        $revenueAccounts = ChartOfAccount::where('type', 'revenue')->where('is_active', true)->orderBy('code')->get();
        $cogsAccounts = ChartOfAccount::where('type', 'cogs')->where('is_active', true)->orderBy('code')->get();
        $expenseAccounts = ChartOfAccount::where('type', 'expense')->where('is_active', true)->orderBy('code')->get();

        $revenues = [];
        $totalRevenue = 0;
        foreach ($revenueAccounts as $acc) {
            $bal = $acc->calculateBalance($startDate, $endDate);
            if ($bal != 0) {
                $revenues[] = ['code' => $acc->code, 'name' => $acc->name, 'amount' => $bal];
                $totalRevenue += $bal;
            }
        }

        $cogs = [];
        $totalCogs = 0;
        foreach ($cogsAccounts as $acc) {
            $bal = $acc->calculateBalance($startDate, $endDate);
            if ($bal != 0) {
                $cogs[] = ['code' => $acc->code, 'name' => $acc->name, 'amount' => $bal];
                $totalCogs += $bal;
            }
        }

        $grossProfit = $totalRevenue - $totalCogs;

        $expenses = [];
        $totalExpense = 0;
        foreach ($expenseAccounts as $acc) {
            $bal = $acc->calculateBalance($startDate, $endDate);
            if ($bal != 0) {
                $expenses[] = ['code' => $acc->code, 'name' => $acc->name, 'amount' => $bal];
                $totalExpense += $bal;
            }
        }

        $netProfit = $grossProfit - $totalExpense;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'revenues' => $revenues,
            'total_revenue' => $totalRevenue,
            'cogs' => $cogs,
            'total_cogs' => $totalCogs,
            'gross_profit' => $grossProfit,
            'expenses' => $expenses,
            'total_expense' => $totalExpense,
            'net_profit' => $netProfit,
        ];
    }

    /**
     * Get Balance Sheet (Neraca)
     */
    public function getBalanceSheet(string $asOfDate): array
    {
        $assetAccounts = ChartOfAccount::where('type', 'asset')->where('is_active', true)->orderBy('code')->get();
        $liabilityAccounts = ChartOfAccount::where('type', 'liability')->where('is_active', true)->orderBy('code')->get();
        $equityAccounts = ChartOfAccount::where('type', 'equity')->where('is_active', true)->orderBy('code')->get();

        $assets = [];
        $totalAssets = 0;
        foreach ($assetAccounts as $acc) {
            $bal = $acc->calculateBalance(null, $asOfDate);
            if ($bal != 0) {
                if ($acc->normal_balance === 'credit') {
                    // Contra asset (e.g. Akumulasi Penyusutan) reduces total assets
                    $assets[] = ['code' => $acc->code, 'name' => $acc->name, 'amount' => -$bal, 'is_contra' => true];
                    $totalAssets -= $bal;
                } else {
                    $assets[] = ['code' => $acc->code, 'name' => $acc->name, 'amount' => $bal, 'is_contra' => false];
                    $totalAssets += $bal;
                }
            }
        }

        $liabilities = [];
        $totalLiabilities = 0;
        foreach ($liabilityAccounts as $acc) {
            $bal = $acc->calculateBalance(null, $asOfDate);
            if ($bal != 0) {
                $liabilities[] = ['code' => $acc->code, 'name' => $acc->name, 'amount' => $bal];
                $totalLiabilities += $bal;
            }
        }

        $equities = [];
        $totalEquityBase = 0;
        foreach ($equityAccounts as $acc) {
            $bal = $acc->calculateBalance(null, $asOfDate);
            if ($bal != 0) {
                $equities[] = ['code' => $acc->code, 'name' => $acc->name, 'amount' => $bal];
                $totalEquityBase += $bal;
            }
        }

        // Calculate Net Profit up to asOfDate to include in Equity (Laba Tahun Berjalan)
        $pnl = $this->getProfitAndLoss('1970-01-01', $asOfDate);
        $currentEarnings = $pnl['net_profit'];

        $equities[] = [
            'code' => '3999',
            'name' => 'Laba Periode Berjalan (Current Earnings)',
            'amount' => $currentEarnings,
        ];
        $totalEquity = $totalEquityBase + $currentEarnings;
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        return [
            'as_of_date' => $asOfDate,
            'assets' => $assets,
            'total_assets' => $totalAssets,
            'liabilities' => $liabilities,
            'total_liabilities' => $totalLiabilities,
            'equities' => $equities,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
            'is_balanced' => abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01,
        ];
    }

    /**
     * Get Accounts Receivable (AR) Aging Report (Laporan Umur Piutang Usaha)
     */
    public function getArAgingReport(string $asOfDate): array
    {
        $asOf = Carbon::parse($asOfDate)->endOfDay();

        $invoices = SalesInvoice::with('customer')
            ->where('status', '!=', 'paid')
            ->whereDate('invoice_date', '<=', $asOfDate)
            ->get()
            ->filter(fn ($inv) => $inv->remaining_balance > 0.01);

        $customerGroups = [];
        $grandTotals = [
            'current' => 0,
            'days_1_30' => 0,
            'days_31_60' => 0,
            'days_61_90' => 0,
            'days_over_90' => 0,
            'total' => 0,
        ];

        foreach ($invoices as $inv) {
            $custId = $inv->customer_id;
            if (! isset($customerGroups[$custId])) {
                $customerGroups[$custId] = [
                    'customer' => $inv->customer,
                    'invoices' => [],
                    'current' => 0,
                    'days_1_30' => 0,
                    'days_31_60' => 0,
                    'days_61_90' => 0,
                    'days_over_90' => 0,
                    'total' => 0,
                ];
            }

            $remaining = (float) $inv->remaining_balance;
            $dueDate = $inv->due_date ? Carbon::parse($inv->due_date)->endOfDay() : $asOf;

            $bracket = 'current';
            $daysOverdue = 0;

            if ($asOf->gt($dueDate)) {
                $daysOverdue = $dueDate->diffInDays($asOf);
                if ($daysOverdue <= 30) {
                    $bracket = 'days_1_30';
                } elseif ($daysOverdue <= 60) {
                    $bracket = 'days_31_60';
                } elseif ($daysOverdue <= 90) {
                    $bracket = 'days_61_90';
                } else {
                    $bracket = 'days_over_90';
                }
            }

            $customerGroups[$custId][$bracket] += $remaining;
            $customerGroups[$custId]['total'] += $remaining;

            $grandTotals[$bracket] += $remaining;
            $grandTotals['total'] += $remaining;

            $customerGroups[$custId]['invoices'][] = [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'invoice_date' => $inv->invoice_date->format('d/m/Y'),
                'due_date' => $inv->due_date ? $inv->due_date->format('d/m/Y') : '-',
                'grand_total' => (float) $inv->grand_total,
                'paid_amount' => (float) $inv->paid_amount,
                'remaining' => $remaining,
                'days_overdue' => $daysOverdue,
                'bracket' => $bracket,
            ];
        }

        return [
            'as_of_date' => $asOfDate,
            'customers' => array_values($customerGroups),
            'grand_totals' => $grandTotals,
            'invoice_count' => $invoices->count(),
        ];
    }

    /**
     * Get Accounts Payable (AP) Aging Report (Laporan Umur Hutang Usaha)
     */
    public function getApAgingReport(string $asOfDate): array
    {
        $asOf = Carbon::parse($asOfDate)->endOfDay();

        $invoices = PurchaseInvoice::with('supplier')
            ->where('status', '!=', 'paid')
            ->whereDate('invoice_date', '<=', $asOfDate)
            ->get()
            ->filter(fn ($inv) => $inv->remaining_balance > 0.01);

        $supplierGroups = [];
        $grandTotals = [
            'current' => 0,
            'days_1_30' => 0,
            'days_31_60' => 0,
            'days_61_90' => 0,
            'days_over_90' => 0,
            'total' => 0,
        ];

        foreach ($invoices as $inv) {
            $suppId = $inv->supplier_id;
            if (! isset($supplierGroups[$suppId])) {
                $supplierGroups[$suppId] = [
                    'supplier' => $inv->supplier,
                    'invoices' => [],
                    'current' => 0,
                    'days_1_30' => 0,
                    'days_31_60' => 0,
                    'days_61_90' => 0,
                    'days_over_90' => 0,
                    'total' => 0,
                ];
            }

            $remaining = (float) $inv->remaining_balance;
            $dueDate = $inv->due_date ? Carbon::parse($inv->due_date)->endOfDay() : $asOf;

            $bracket = 'current';
            $daysOverdue = 0;

            if ($asOf->gt($dueDate)) {
                $daysOverdue = $dueDate->diffInDays($asOf);
                if ($daysOverdue <= 30) {
                    $bracket = 'days_1_30';
                } elseif ($daysOverdue <= 60) {
                    $bracket = 'days_31_60';
                } elseif ($daysOverdue <= 90) {
                    $bracket = 'days_61_90';
                } else {
                    $bracket = 'days_over_90';
                }
            }

            $supplierGroups[$suppId][$bracket] += $remaining;
            $supplierGroups[$suppId]['total'] += $remaining;

            $grandTotals[$bracket] += $remaining;
            $grandTotals['total'] += $remaining;

            $supplierGroups[$suppId]['invoices'][] = [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'supplier_invoice_number' => $inv->supplier_invoice_number,
                'invoice_date' => $inv->invoice_date->format('d/m/Y'),
                'due_date' => $inv->due_date ? $inv->due_date->format('d/m/Y') : '-',
                'grand_total' => (float) $inv->grand_total,
                'paid_amount' => (float) $inv->paid_amount,
                'remaining' => $remaining,
                'days_overdue' => $daysOverdue,
                'bracket' => $bracket,
            ];
        }

        return [
            'as_of_date' => $asOfDate,
            'suppliers' => array_values($supplierGroups),
            'grand_totals' => $grandTotals,
            'invoice_count' => $invoices->count(),
        ];
    }
}
