<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::with(['cashAccount', 'depositAccount'])->withCount('warehouses');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('address', 'like', "%{$s}%");
            });
        }

        $branches = $query->orderByDesc('is_main')->orderBy('name')->paginate(15);

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        $cashAccounts = ChartOfAccount::where('is_cash_or_bank', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('branches.create', compact('cashAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:branches,code'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'cash_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'deposit_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'is_main' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_main')) {
            Branch::where('is_main', true)->update(['is_main' => false]);
        }

        Branch::create([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'cash_account_id' => $request->cash_account_id,
            'deposit_account_id' => $request->deposit_account_id,
            'is_main' => $request->boolean('is_main'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function edit(Branch $branch)
    {
        $cashAccounts = ChartOfAccount::where('is_cash_or_bank', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('branches.edit', compact('branch', 'cashAccounts'));
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('branches')->ignore($branch->id)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'cash_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'deposit_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'is_main' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_main')) {
            Branch::where('id', '!=', $branch->id)->where('is_main', true)->update(['is_main' => false]);
        }

        $branch->update([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'cash_account_id' => $request->cash_account_id,
            'deposit_account_id' => $request->deposit_account_id,
            'is_main' => $request->boolean('is_main'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('branches.index')->with('success', 'Data Cabang berhasil diperbarui.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->is_main) {
            return back()->with('error', 'Cabang Utama tidak dapat dihapus.');
        }

        if ($branch->warehouses()->count() > 0) {
            return back()->with('error', 'Cabang tidak dapat dihapus karena masih memiliki gudang terafiliasi.');
        }

        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil dihapus.');
    }
}
