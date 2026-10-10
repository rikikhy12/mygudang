<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $query = ChartOfAccount::orderBy('code');

        if ($type) {
            $query->where('type', $type);
        }

        $accounts = $query->paginate(25)->withQueryString();

        return view('coa.index', compact('accounts', 'type'));
    }

    public function create(Request $request)
    {
        $isPopup = $request->boolean('popup');
        $allAccounts = ChartOfAccount::orderBy('code')->get();

        return view('coa.create', compact('allAccounts', 'isPopup'));
    }

    public function edit(ChartOfAccount $coa)
    {
        $allAccounts = ChartOfAccount::where('id', '!=', $coa->id)->orderBy('code')->get();

        return view('coa.edit', compact('coa', 'allAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'unique:chart_of_accounts,code'],
            'name' => ['required', 'string'],
            'type' => ['required', 'in:asset,liability,equity,revenue,cogs,expense'],
            'normal_balance' => ['required', 'in:debit,credit'],
            'parent_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'is_cash_or_bank' => ['nullable', 'boolean'],
            'cash_flow_category' => ['nullable', 'in:operating,investing,financing,none'],
            'description' => ['nullable', 'string'],
        ]);

        $coa = ChartOfAccount::create([
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type,
            'normal_balance' => $request->normal_balance,
            'parent_id' => $request->parent_id,
            'is_cash_or_bank' => $request->boolean('is_cash_or_bank'),
            'cash_flow_category' => $request->cash_flow_category ?? 'operating',
            'is_active' => true,
            'description' => $request->description,
        ]);

        if ($request->boolean('popup')) {
            return view('partials.window_opener_callback', [
                'title' => 'Akun COA "'.$coa->code.' - '.$coa->name.'" Berhasil Disimpan',
                'payload' => [
                    'type' => 'coa_created',
                    'account' => [
                        'id' => $coa->id,
                        'code' => $coa->code,
                        'name' => $coa->name,
                        'type' => $coa->type,
                        'is_cash_or_bank' => (bool) $coa->is_cash_or_bank,
                        'cash_flow_category' => $coa->cash_flow_category,
                        'label' => "{$coa->code} - {$coa->name}",
                    ],
                ],
                'redirectUrl' => route('coa.index'),
            ]);
        }

        return redirect()->route('coa.index')->with('success', 'Akun COA berhasil dibuat.');
    }

    public function update(Request $request, ChartOfAccount $coa)
    {
        $request->validate([
            'code' => ['required', 'string', Rule::unique('chart_of_accounts')->ignore($coa->id)],
            'name' => ['required', 'string'],
            'type' => ['required', 'in:asset,liability,equity,revenue,cogs,expense'],
            'normal_balance' => ['required', 'in:debit,credit'],
            'parent_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'is_cash_or_bank' => ['nullable', 'boolean'],
            'cash_flow_category' => ['nullable', 'in:operating,investing,financing,none'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $coa->update([
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type,
            'normal_balance' => $request->normal_balance,
            'parent_id' => $request->parent_id,
            'is_cash_or_bank' => $request->boolean('is_cash_or_bank'),
            'cash_flow_category' => $request->cash_flow_category ?? 'operating',
            'is_active' => $request->boolean('is_active', true),
            'description' => $request->description,
        ]);

        return redirect()->route('coa.index')->with('success', 'Akun COA berhasil diperbarui.');
    }

    public function destroy(ChartOfAccount $coa)
    {
        if ($coa->journalEntries()->exists()) {
            return back()->with('error', 'Akun ini sudah memiliki transaksi jurnal dan tidak dapat dihapus.');
        }

        $coa->delete();

        return redirect()->route('coa.index')->with('success', 'Akun COA berhasil dihapus.');
    }

    /**
     * AJAX quick store for modal popup in transaction forms
     */
    public function quickStore(Request $request)
    {
        $user = auth()->user();
        if (! $user->hasRole('admin') && ! $user->hasPermission('create_master_coa')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk membuat data Akun COA.',
            ], 403);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'unique:chart_of_accounts,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:asset,liability,equity,revenue,cogs,expense'],
            'normal_balance' => ['required', 'in:debit,credit'],
            'is_cash_or_bank' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $coa = ChartOfAccount::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'normal_balance' => $validated['normal_balance'],
            'is_cash_or_bank' => $request->boolean('is_cash_or_bank'),
            'is_active' => true,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun COA "'.$coa->code.' - '.$coa->name.'" berhasil dibuat.',
            'account' => [
                'id' => $coa->id,
                'code' => $coa->code,
                'name' => $coa->name,
                'type' => $coa->type,
                'is_cash_or_bank' => (bool) $coa->is_cash_or_bank,
                'label' => "{$coa->code} - {$coa->name}",
            ],
        ]);
    }
}
