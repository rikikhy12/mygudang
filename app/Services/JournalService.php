<?php

namespace App\Services;

use App\Models\Journal;
use App\Models\JournalEntry;
use Exception;
use Illuminate\Support\Facades\DB;

class JournalService
{
    /**
     * Create a double-entry journal with automatic number generation and balance check.
     *
     * @param  string  $referenceType  e.g., 'MANUAL', 'GRN', 'PURCHASE_INVOICE', 'DO', 'SALES_INVOICE', 'CASH_IN', 'CASH_OUT', 'ASSET_DEPRECIATION', 'ADJUSTMENT'
     * @param  array  $entries  Array of ['account_id' => int, 'type' => 'debit'|'credit', 'amount' => float, 'memo' => string|null]
     * @param  string|null  $transactionDate  YYYY-MM-DD
     *
     * @throws Exception
     */
    public function createJournal(
        string $referenceType,
        ?int $referenceId,
        ?string $referenceNo,
        string $description,
        array $entries,
        ?string $transactionDate = null,
        ?int $userId = null
    ): Journal {
        $transactionDate = $transactionDate ?: date('Y-m-d');
        $userId = $userId ?: (auth()->check() ? auth()->id() : 1);

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($entries as $entry) {
            $amount = round((float) ($entry['amount'] ?? 0), 2);
            if ($amount <= 0) {
                continue;
            }

            if ($entry['type'] === 'debit') {
                $totalDebit += $amount;
            } elseif ($entry['type'] === 'credit') {
                $totalCredit += $amount;
            }
        }

        $totalDebit = round($totalDebit, 2);
        $totalCredit = round($totalCredit, 2);

        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw new Exception('Jurnal tidak seimbang! Total Debit: '.number_format($totalDebit, 2).', Total Credit: '.number_format($totalCredit, 2));
        }

        return DB::transaction(function () use (
            $referenceType, $referenceId, $referenceNo, $description, $entries, $transactionDate, $userId, $totalDebit, $totalCredit
        ) {
            $datePrefix = date('Ymd', strtotime($transactionDate));
            $countToday = Journal::whereDate('transaction_date', $transactionDate)->count() + 1;
            $journalNumber = 'JRN-'.$datePrefix.'-'.str_pad($countToday, 4, '0', STR_PAD_LEFT);

            $journal = Journal::create([
                'journal_number' => $journalNumber,
                'transaction_date' => $transactionDate,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reference_no' => $referenceNo,
                'description' => $description,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'is_balanced' => true,
                'status' => 'posted',
                'created_by_id' => $userId,
            ]);

            foreach ($entries as $entry) {
                $amount = round((float) ($entry['amount'] ?? 0), 2);
                if ($amount <= 0) {
                    continue;
                }

                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $entry['account_id'],
                    'type' => $entry['type'],
                    'amount' => $amount,
                    'memo' => $entry['memo'] ?? null,
                ]);
            }

            return $journal;
        });
    }
}
