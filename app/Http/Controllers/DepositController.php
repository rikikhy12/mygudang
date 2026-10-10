<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Contact;
use App\Models\ContactDeposit;
use App\Services\JournalService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    public function customerIndex(Request $request)
    {
        return $this->renderIndex($request, 'customer');
    }

    public function supplierIndex(Request $request)
    {
        return $this->renderIndex($request, 'supplier');
    }

    public function index(Request $request)
    {
        $type = $request->get('type', 'customer');
        return $this->renderIndex($request, $type);
    }

    private function renderIndex(Request $request, string $type)
    {
        $contactsQuery = Contact::where('is_active', true)
            ->where(function ($q) use ($type) {
                $q->where('type', $type)->orWhere('type', 'both');
            });

        if ($request->filled('search')) {
            $s = $request->search;
            $contactsQuery->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $contacts = $contactsQuery->with('deposits')->orderBy('name')->paginate(15);

        $recentDeposits = ContactDeposit::with(['contact', 'journal'])
            ->where('type', $type)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        // Summary Statistics
        $totalDepositsIn = ContactDeposit::where('type', $type)->where('transaction_type', 'in')->sum('amount');
        $totalDepositsOut = ContactDeposit::where('type', $type)->where('transaction_type', 'out')->sum('amount');
        $currentNetDeposit = max(0, $totalDepositsIn - $totalDepositsOut);

        $deposits = $recentDeposits;
        $view = $type === 'supplier' ? 'finance.deposits.supplier' : 'finance.deposits.customer';

        return view($view, compact(
            'contacts',
            'deposits',
            'recentDeposits',
            'type',
            'totalDepositsIn',
            'totalDepositsOut',
            'currentNetDeposit'
        ));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'customer');
        $contactId = $request->get('contact_id');

        $contacts = Contact::where('is_active', true)
            ->where(function ($q) use ($type) {
                $q->where('type', $type)->orWhere('type', 'both');
            })
            ->orderBy('name')
            ->get();

        $cashAccounts = ChartOfAccount::where('is_cash_or_bank', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        if ($cashAccounts->isEmpty()) {
            $cashAccounts = ChartOfAccount::whereIn('code', ['1001', '1002', '1003'])
                ->where('is_active', true)
                ->orderBy('code')
                ->get();
        }

        return view('finance.deposits.create', compact('contacts', 'cashAccounts', 'type', 'contactId'));
    }

    public function store(Request $request, JournalService $journalService)
    {
        $request->validate([
            'contact_id' => ['required', 'exists:contacts,id'],
            'type' => ['required', 'in:customer,supplier'],
            'transaction_type' => ['required', 'in:in,out'],
            'amount' => ['required', 'numeric', 'min:1'],
            'account_id' => ['required', 'exists:chart_of_accounts,id'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () use ($request, $journalService) {
                $contact = Contact::findOrFail($request->contact_id);
                $amount = (float) $request->amount;
                $cashAccId = $request->account_id;
                $refNo = 'DEP-'.date('Ymd').'-'.str_pad(ContactDeposit::count() + 1, 4, '0', STR_PAD_LEFT);

                // Deposit liability / asset account
                $depositAcc = null;
                if ($request->type === 'customer') {
                    $depositAcc = ChartOfAccount::where('code', '2004')->first()
                        ?: ChartOfAccount::where('type', 'liability')->first();
                } else {
                    $depositAcc = ChartOfAccount::where('code', '1104')->first()
                        ?: ChartOfAccount::where('type', 'asset')->first();
                }

                $journalEntries = [];
                if ($request->transaction_type === 'in') {
                    $journalEntries[] = [
                        'account_id' => $cashAccId,
                        'type' => 'debit',
                        'amount' => $amount,
                        'memo' => "Penerimaan Deposit {$contact->name}",
                    ];
                    $journalEntries[] = [
                        'account_id' => $depositAcc->id,
                        'type' => 'credit',
                        'amount' => $amount,
                        'memo' => "Deposit Rekanan: {$contact->name}",
                    ];
                } else {
                    $journalEntries[] = [
                        'account_id' => $depositAcc->id,
                        'type' => 'debit',
                        'amount' => $amount,
                        'memo' => "Pengembalian/Pemakaian Deposit {$contact->name}",
                    ];
                    $journalEntries[] = [
                        'account_id' => $cashAccId,
                        'type' => 'credit',
                        'amount' => $amount,
                        'memo' => "Pengeluaran Kas Deposit {$contact->name}",
                    ];
                }

                $journal = $journalService->createJournal(
                    'DEPOSIT',
                    $contact->id,
                    $refNo,
                    "Transaksi Deposit {$contact->name} (".strtoupper($request->transaction_type).')',
                    $journalEntries,
                    date('Y-m-d'),
                    auth()->id()
                );

                ContactDeposit::create([
                    'contact_id' => $contact->id,
                    'type' => $request->type,
                    'amount' => $amount,
                    'transaction_type' => $request->transaction_type,
                    'reference_type' => 'MANUAL_DEPOSIT',
                    'reference_id' => $journal->id,
                    'reference_no' => $refNo,
                    'notes' => $request->notes,
                    'journal_id' => $journal->id,
                ]);
            });

            $route = $request->type === 'supplier' ? 'finance.deposits.supplier' : 'finance.deposits.customer';
            return redirect()->route($route)->with('success', 'Transaksi Deposit berhasil dicatat dan dijurnal otomatis.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mencatat deposit: '.$e->getMessage());
        }
    }

    public function destroy(ContactDeposit $deposit)
    {
        try {
            $type = $deposit->type;
            DB::transaction(function () use ($deposit) {
                if ($deposit->journal_id) {
                    $journal = \App\Models\Journal::find($deposit->journal_id);
                    if ($journal) {
                        $journal->items()->delete();
                        $journal->delete();
                    }
                }
                $deposit->delete();
            });

            $route = $type === 'supplier' ? 'finance.deposits.supplier' : 'finance.deposits.customer';
            return redirect()->route($route)->with('success', 'Transaksi deposit berhasil dibatalkan dan dihapus.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menghapus deposit: '.$e->getMessage());
        }
    }
}
