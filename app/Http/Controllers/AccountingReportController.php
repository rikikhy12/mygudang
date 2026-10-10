<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Services\FinancialReportService;
use App\Services\JournalService;
use Exception;
use Illuminate\Http\Request;

class AccountingReportController extends Controller
{
    // ==========================================
    // 1. JURNAL UMUM (JOURNAL ENTRIES)
    // ==========================================
    public function journalsIndex(Request $request)
    {
        $startDate = $request->query('start_date', date('Y-m-01'));
        $endDate = $request->query('end_date', date('Y-m-d'));
        $refType = $request->query('reference_type');

        $query = Journal::with(['entries.account', 'creator'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->latest('transaction_date')
            ->latest('id');

        if ($refType) {
            $query->where('reference_type', $refType);
        }

        $journals = $query->get();
        $refTypes = Journal::select('reference_type')->distinct()->pluck('reference_type');

        return view('accounting.journals.index', compact('journals', 'startDate', 'endDate', 'refType', 'refTypes'));
    }

    public function journalCreate()
    {
        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();

        return view('accounting.journals.create', compact('accounts'));
    }

    public function journalStore(Request $request, JournalService $journalService)
    {
        $request->validate([
            'transaction_date' => ['required', 'date'],
            'description' => ['required', 'string'],
            'reference_no' => ['nullable', 'string'],
            'entries' => ['required', 'array', 'min:2'],
            'entries.*.account_id' => ['required', 'exists:chart_of_accounts,id'],
            'entries.*.type' => ['required', 'in:debit,credit'],
            'entries.*.amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        try {
            $journalService->createJournal(
                'MANUAL',
                null,
                $request->reference_no,
                $request->description,
                $request->entries,
                $request->transaction_date,
                auth()->id()
            );

            return redirect()->route('accounting.journals.index')->with('success', 'Jurnal umum manual berhasil disimpan.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function journalShow(Journal $journal)
    {
        $journal->load(['entries.account', 'creator']);

        return view('accounting.journals.show', compact('journal'));
    }

    public function journalPrint(Journal $journal)
    {
        $journal->load(['entries.account', 'creator']);

        return view('accounting.journals.print', compact('journal'));
    }

    // ==========================================
    // 2. BUKU BESAR (GENERAL LEDGER)
    // ==========================================
    public function generalLedger(Request $request, FinancialReportService $reportService)
    {
        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();
        $accountId = $request->query('account_id', $accounts->first()?->id);
        $startDate = $request->query('start_date', date('Y-01-01'));
        $endDate = $request->query('end_date', date('Y-m-d'));

        $report = null;
        if ($accountId) {
            $report = $reportService->getGeneralLedger($accountId, $startDate, $endDate);
        }

        return view('accounting.reports.general_ledger', compact('accounts', 'accountId', 'startDate', 'endDate', 'report'));
    }

    // ==========================================
    // 3. NERACA SALDO (TRIAL BALANCE)
    // ==========================================
    public function trialBalance(Request $request, FinancialReportService $reportService)
    {
        $asOfDate = $request->query('as_of_date', date('Y-m-d'));
        $report = $reportService->getTrialBalance($asOfDate);

        return view('accounting.reports.trial_balance', compact('report', 'asOfDate'));
    }

    // ==========================================
    // 4. LABA RUGI (PROFIT & LOSS)
    // ==========================================
    public function profitAndLoss(Request $request, FinancialReportService $reportService)
    {
        $startDate = $request->query('start_date', date('Y-m-01'));
        $endDate = $request->query('end_date', date('Y-m-d'));

        $report = $reportService->getProfitAndLoss($startDate, $endDate);

        return view('accounting.reports.profit_loss', compact('report', 'startDate', 'endDate'));
    }

    // ==========================================
    // 5. NERACA (BALANCE SHEET)
    // ==========================================
    public function balanceSheet(Request $request, FinancialReportService $reportService)
    {
        $asOfDate = $request->query('as_of_date', date('Y-m-d'));
        $report = $reportService->getBalanceSheet($asOfDate);

        return view('accounting.reports.balance_sheet', compact('report', 'asOfDate'));
    }

    // ==========================================
    // 6. LAPORAN AGING PIUTANG (AR AGING)
    // ==========================================
    public function arAging(Request $request, FinancialReportService $reportService)
    {
        $asOfDate = $request->query('as_of_date', date('Y-m-d'));
        $report = $reportService->getArAgingReport($asOfDate);

        return view('accounting.reports.ar_aging', compact('report', 'asOfDate'));
    }

    // ==========================================
    // 7. LAPORAN AGING HUTANG (AP AGING)
    // ==========================================
    public function apAging(Request $request, FinancialReportService $reportService)
    {
        $asOfDate = $request->query('as_of_date', date('Y-m-d'));
        $report = $reportService->getApAgingReport($asOfDate);

        return view('accounting.reports.ap_aging', compact('report', 'asOfDate'));
    }
}
