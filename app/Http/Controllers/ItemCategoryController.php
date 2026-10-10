<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemCategoryController extends Controller
{
    public function index()
    {
        $categories = ItemCategory::with(['parent', 'inventoryAccount', 'cogsAccount', 'purchaseAccount', 'salesAccount'])
            ->withCount('items')
            ->orderBy('name')
            ->paginate(15);

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = ItemCategory::orderBy('name')->get();
        $inventoryAccounts = ChartOfAccount::where('type', 'asset')->where('is_active', true)->orderBy('code')->get();
        $cogsAccounts = ChartOfAccount::where('type', 'cogs')->where('is_active', true)->orderBy('code')->get();
        $purchaseAccounts = ChartOfAccount::whereIn('type', ['asset', 'liability', 'expense'])->where('is_active', true)->orderBy('code')->get();
        $salesAccounts = ChartOfAccount::where('type', 'revenue')->where('is_active', true)->orderBy('code')->get();

        return view('categories.create', compact('parentCategories', 'inventoryAccounts', 'cogsAccounts', 'purchaseAccounts', 'salesAccounts'));
    }

    public function edit(ItemCategory $category)
    {
        $parentCategories = ItemCategory::where('id', '!=', $category->id)->orderBy('name')->get();
        $inventoryAccounts = ChartOfAccount::where('type', 'asset')->where('is_active', true)->orderBy('code')->get();
        $cogsAccounts = ChartOfAccount::where('type', 'cogs')->where('is_active', true)->orderBy('code')->get();
        $purchaseAccounts = ChartOfAccount::whereIn('type', ['asset', 'liability', 'expense'])->where('is_active', true)->orderBy('code')->get();
        $salesAccounts = ChartOfAccount::where('type', 'revenue')->where('is_active', true)->orderBy('code')->get();

        return view('categories.edit', compact('category', 'parentCategories', 'inventoryAccounts', 'cogsAccounts', 'purchaseAccounts', 'salesAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_id' => ['nullable', 'exists:item_categories,id'],
            'name' => ['required', 'string'],
            'code' => ['required', 'string', 'unique:item_categories,code'],
            'type' => ['required', 'in:service,finished_good,manufactured_good'],
            'inventory_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'cogs_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'purchase_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'sales_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'min_margin_percent' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'description' => ['nullable', 'string'],
        ]);

        ItemCategory::create($request->all());

        return redirect()->route('categories.index')->with('success', 'Kategori barang & GL mapping berhasil disimpan.');
    }

    public function update(Request $request, ItemCategory $category)
    {
        $request->validate([
            'parent_id' => ['nullable', 'exists:item_categories,id', Rule::notIn([$category->id])],
            'name' => ['required', 'string'],
            'code' => ['required', 'string', Rule::unique('item_categories')->ignore($category->id)],
            'type' => ['required', 'in:service,finished_good,manufactured_good'],
            'inventory_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'cogs_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'purchase_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'sales_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'min_margin_percent' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'description' => ['nullable', 'string'],
        ]);

        $category->update($request->all());

        return redirect()->route('categories.index')->with('success', 'Kategori barang & GL mapping berhasil diperbarui.');
    }

    public function destroy(ItemCategory $category)
    {
        if ($category->items()->exists()) {
            return back()->with('error', 'Kategori ini tidak dapat dihapus karena masih memiliki relasi data barang.');
        }

        if ($category->children()->exists()) {
            return back()->with('error', 'Kategori ini tidak dapat dihapus karena memiliki sub-kategori turunan.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori barang berhasil dihapus.');
    }
}
