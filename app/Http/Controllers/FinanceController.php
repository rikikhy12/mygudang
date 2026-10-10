<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\CashTransactionItem;
use App\Models\ChartOfAccount;
use App\Models\Contact;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $query = CashTransaction::with(['account', 'targetAccount', 'contact', 'journal'])->latest('transaction_date')->latest('id');

        if ($type) {
            $query->where('type', $type);
        }

        $transactions = $query->get();

        // Summary metrics
        $cashAccounts = ChartOfAccount::where('is_cash_or_bank', true)->where('is_active', true)->get();
        $totalCash = 0;
        $accountsSummary = [];

        foreach ($cashAccounts as $acc) {
            $bal = $acc->calculateBalance();
            $totalCash += $bal;
            $accountsSummary[] = [
                'account' => $acc,
                'balance' => $bal,
            ];
        }

        return view('finance.index', compact('transactions', 'totalCash', 'accountsSummary', 'type'));
    }

    public function createCashIn(Request $request)
    {
        $cashAccounts = ChartOfAccount::where('is_cash_or_bank', true)->where('is_active', true)->get();
        $contraAccounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();
        $contacts = Contact::all();
        $unpaidSalesInvoices = SalesInvoice::with('customer')->whereIn('status', ['unpaid', 'partial'])->get();
        $selectedInvoiceId = $request->query('sales_invoice_id');

        return view('finance.cash_in', compact('cashAccounts', 'contraAccounts', 'contacts', 'unpaidSalesInvoices', 'selectedInvoiceId'));
    }

    public function storeCashIn(Request $request, JournalService $journalService)
    {
        $isSplit = $request->get('payment_mode') === 'split';

        $rules = [
            'payment_mode' => ['nullable', 'string', 'in:single,split'],
            'account_id' => [$isSplit ? 'nullable' : 'required', 'exists:chart_of_accounts,id'],
            'transaction_date' => ['required', 'date'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'reference_no' => ['nullable', 'string'],
            'description' => ['required', 'string'],
            'sales_invoice_id' => ['nullable', 'exists:sales_invoices,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.account_id' => ['required', 'exists:chart_of_accounts,id'],
            'items.*.amount' => ['required', 'numeric', 'min:0.01'],
        ];

        if ($isSplit) {
            $rules['target_accounts'] = ['required', 'array', 'min:1'];
            $rules['target_accounts.*.account_id'] = ['required', 'exists:chart_of_accounts,id'];
            $rules['target_accounts.*.amount'] = ['required', 'numeric', 'min:0.01'];
        }

        $request->validate($rules);

        DB::transaction(function () use ($request, $journalService) {
            $trxNumber = 'CR-'.date('Ymd').'-'.str_pad(CashTransaction::count() + 1, 4, '0', STR_PAD_LEFT);

            $totalAmount = 0;
            $creditEntries = [];

            foreach ($request->items as $itemData) {
                $amt = (float) $itemData['amount'];
                $totalAmount += $amt;

                $creditEntries[] = [
                    'account_id' => $itemData['account_id'],
                    'type' => 'credit',
                    'amount' => $amt,
                    'memo' => $itemData['notes'] ?? $request->description,
                ];
            }

            // Determine Debit entries (single target vs split targets)
            $debitEntries = [];
            $primaryAccountId = $request->account_id;

            $validTargets = collect($request->target_accounts)->filter(fn ($t) => ! empty($t['account_id']) && ! empty($t['amount']) && (float) $t['amount'] > 0);

            if ($request->get('payment_mode') === 'split' && $validTargets->isNotEmpty()) {
                $primaryAccountId = $validTargets->first()['account_id'];
                foreach ($validTargets as $t) {
                    $tAmt = (float) $t['amount'];
                    $debitEntries[] = [
                        'account_id' => $t['account_id'],
                        'type' => 'debit',
                        'amount' => $tAmt,
                        'memo' => $t['notes'] ?? ('Penerimaan Kas/Bank '.$trxNumber.': '.$request->description),
                    ];
                }
            } else {
                if (! $primaryAccountId) {
                    $defaultCoa = ChartOfAccount::where('is_cash_or_bank', true)->where('is_active', true)->first();
                    $primaryAccountId = $defaultCoa ? $defaultCoa->id : $request->items[0]['account_id'];
                }
                $debitEntries[] = [
                    'account_id' => $primaryAccountId,
                    'type' => 'debit',
                    'amount' => $totalAmount,
                    'memo' => 'Penerimaan Kas/Bank '.$trxNumber.': '.$request->description,
                ];
            }

            $trx = CashTransaction::create([
                'transaction_number' => $trxNumber,
                'type' => 'cash_in',
                'account_id' => $primaryAccountId,
                'contact_id' => $request->contact_id,
                'transaction_date' => $request->transaction_date,
                'reference_no' => $request->reference_no,
                'total_amount' => $totalAmount,
                'description' => $request->description,
                'status' => 'posted',
                'created_by_id' => auth()->id(),
            ]);

            foreach ($request->items as $itemData) {
                CashTransactionItem::create([
                    'cash_transaction_id' => $trx->id,
                    'account_id' => $itemData['account_id'],
                    'amount' => $itemData['amount'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Combine Debit and Credit entries for journal
            $journalEntries = array_merge($debitEntries, $creditEntries);

            // If paying a sales invoice
            if ($request->sales_invoice_id) {
                $invoice = SalesInvoice::findOrFail($request->sales_invoice_id);
                $newPaid = $invoice->paid_amount + $totalAmount;
                $invoice->update([
                    'paid_amount' => $newPaid,
                    'status' => $newPaid >= $invoice->grand_total ? 'paid' : 'partial',
                ]);
            }

            $journal = $journalService->createJournal(
                'CASH_IN',
                $trx->id,
                $trx->transaction_number,
                'Kas Masuk: '.$request->description,
                $journalEntries,
                $request->transaction_date,
                auth()->id()
            );

            $trx->update(['journal_id' => $journal->id]);
        });

        return redirect()->route('finance.index')->with('success', 'Transaksi Kas Masuk berhasil diposting dan dijurnal.');
    }

    public function createCashOut(Request $request)
    {
        $cashAccounts = ChartOfAccount::where('is_cash_or_bank', true)->where('is_active', true)->get();
        $contraAccounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();
        $contacts = Contact::all();
        $unpaidPurchaseInvoices = PurchaseInvoice::with('supplier')->whereIn('status', ['unpaid', 'partial'])->get();
        $selectedInvoiceId = $request->query('purchase_invoice_id');

        return view('finance.cash_out', compact('cashAccounts', 'contraAccounts', 'contacts', 'unpaidPurchaseInvoices', 'selectedInvoiceId'));
    }

    public function storeCashOut(Request $request, JournalService $journalService)
    {
        $isSplit = $request->get('payment_mode') === 'split';

        $rules = [
            'payment_mode' => ['nullable', 'string', 'in:single,split'],
            'account_id' => [$isSplit ? 'nullable' : 'required', 'exists:chart_of_accounts,id'],
            'transaction_date' => ['required', 'date'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'reference_no' => ['nullable', 'string'],
            'description' => ['required', 'string'],
            'purchase_invoice_id' => ['nullable', 'exists:purchase_invoices,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.account_id' => ['required', 'exists:chart_of_accounts,id'],
            'items.*.amount' => ['required', 'numeric', 'min:0.01'],
        ];

        if ($isSplit) {
            $rules['source_accounts'] = ['required', 'array', 'min:1'];
            $rules['source_accounts.*.account_id'] = ['required', 'exists:chart_of_accounts,id'];
            $rules['source_accounts.*.amount'] = ['required', 'numeric', 'min:0.01'];
        }

        $request->validate($rules);

        DB::transaction(function () use ($request, $journalService) {
            $trxNumber = 'CD-'.date('Ymd').'-'.str_pad(CashTransaction::count() + 1, 4, '0', STR_PAD_LEFT);

            $totalAmount = 0;
            $debitEntries = [];

            foreach ($request->items as $itemData) {
                $amt = (float) $itemData['amount'];
                $totalAmount += $amt;

                $debitEntries[] = [
                    'account_id' => $itemData['account_id'],
                    'type' => 'debit',
                    'amount' => $amt,
                    'memo' => $itemData['notes'] ?? $request->description,
                ];
            }

            // Determine Credit entries (single source vs split sources)
            $creditEntries = [];
            $primaryAccountId = $request->account_id;

            $validSources = collect($request->source_accounts)->filter(fn ($s) => ! empty($s['account_id']) && ! empty($s['amount']) && (float) $s['amount'] > 0);

            if ($request->get('payment_mode') === 'split' && $validSources->isNotEmpty()) {
                $primaryAccountId = $validSources->first()['account_id'];
                foreach ($validSources as $s) {
                    $sAmt = (float) $s['amount'];
                    $creditEntries[] = [
                        'account_id' => $s['account_id'],
                        'type' => 'credit',
                        'amount' => $sAmt,
                        'memo' => $s['notes'] ?? ('Pengeluaran Kas/Bank '.$trxNumber.': '.$request->description),
                    ];
                }
            } else {
                if (! $primaryAccountId) {
                    $defaultCoa = ChartOfAccount::where('is_cash_or_bank', true)->where('is_active', true)->first();
                    $primaryAccountId = $defaultCoa ? $defaultCoa->id : $request->items[0]['account_id'];
                }
                $creditEntries[] = [
                    'account_id' => $primaryAccountId,
                    'type' => 'credit',
                    'amount' => $totalAmount,
                    'memo' => 'Pengeluaran Kas/Bank '.$trxNumber.': '.$request->description,
                ];
            }

            $trx = CashTransaction::create([
                'transaction_number' => $trxNumber,
                'type' => 'cash_out',
                'account_id' => $primaryAccountId,
                'contact_id' => $request->contact_id,
                'transaction_date' => $request->transaction_date,
                'reference_no' => $request->reference_no,
                'total_amount' => $totalAmount,
                'description' => $request->description,
                'status' => 'posted',
                'created_by_id' => auth()->id(),
            ]);

            foreach ($request->items as $itemData) {
                CashTransactionItem::create([
                    'cash_transaction_id' => $trx->id,
                    'account_id' => $itemData['account_id'],
                    'amount' => $itemData['amount'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Combine Debit and Credit entries for journal
            $journalEntries = array_merge($debitEntries, $creditEntries);

            // If paying a purchase invoice
            if ($request->purchase_invoice_id) {
                $invoice = PurchaseInvoice::findOrFail($request->purchase_invoice_id);
                $newPaid = $invoice->paid_amount + $totalAmount;
                $invoice->update([
                    'paid_amount' => $newPaid,
                    'status' => $newPaid >= $invoice->grand_total ? 'paid' : 'partial',
                ]);
            }

            $journal = $journalService->createJournal(
                'CASH_OUT',
                $trx->id,
                $trx->transaction_number,
                'Kas Keluar: '.$request->description,
                $journalEntries,
                $request->transaction_date,
                auth()->id()
            );

            $trx->update(['journal_id' => $journal->id]);
        });

        return redirect()->route('finance.index')->with('success', 'Transaksi Kas Keluar berhasil diposting dan dijurnal.');
    }

    public function createTransfer()
    {
        $cashAccounts = ChartOfAccount::where('is_cash_or_bank', true)->where('is_active', true)->get();

        return view('finance.transfer', compact('cashAccounts'));
    }

    public function storeTransfer(Request $request, JournalService $journalService)
    {
        $request->validate([
            'account_id' => ['required', 'exists:chart_of_accounts,id', 'different:target_account_id'],
            'target_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'transaction_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($request, $journalService) {
            $trxNumber = 'TRF-'.date('Ymd').'-'.str_pad(CashTransaction::count() + 1, 4, '0', STR_PAD_LEFT);
            $amount = (float) $request->amount;

            $sourceAcc = ChartOfAccount::findOrFail($request->account_id);
            $targetAcc = ChartOfAccount::findOrFail($request->target_account_id);

            $trx = CashTransaction::create([
                'transaction_number' => $trxNumber,
                'type' => 'transfer',
                'account_id' => $sourceAcc->id,
                'target_account_id' => $targetAcc->id,
                'transaction_date' => $request->transaction_date,
                'total_amount' => $amount,
                'description' => $request->description,
                'status' => 'posted',
                'created_by_id' => auth()->id(),
            ]);

            // Journal:
            // Debit: Target Account (target_account_id)
            // Credit: Source Account (account_id)
            $journal = $journalService->createJournal(
                'CASH_TRANSFER',
                $trx->id,
                $trx->transaction_number,
                "Transfer Dana dari {$sourceAcc->name} ke {$targetAcc->name} ({$request->description})",
                [
                    [
                        'account_id' => $targetAcc->id,
                        'type' => 'debit',
                        'amount' => $amount,
                        'memo' => "Penerimaan Transfer dari {$sourceAcc->name}",
                    ],
                    [
                        'account_id' => $sourceAcc->id,
                        'type' => 'credit',
                        'amount' => $amount,
                        'memo' => "Pengiriman Transfer ke {$targetAcc->name}",
                    ],
                ],
                $request->transaction_date,
                auth()->id()
            );

            $trx->update(['journal_id' => $journal->id]);
        });

        return redirect()->route('finance.index')->with('success', 'Transfer kas/bank berhasil diposting dan dijurnal.');
    }

    public function show(CashTransaction $trx)
    {
        $trx->load(['account', 'targetAccount', 'contact', 'items.account', 'journal.entries.account', 'creator']);

        return view('finance.show', compact('trx'));
    }

    public function print(CashTransaction $trx)
    {
        $trx->load(['account', 'targetAccount', 'contact', 'items.account', 'creator']);

        return view('finance.print', ['transaction' => $trx]);
    }
}
