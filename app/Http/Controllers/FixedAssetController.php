<?php

namespace App\Http\Controllers;

use App\Models\AssetDepreciation;
use App\Models\ChartOfAccount;
use App\Models\FixedAsset;
use App\Models\FixedAssetCategory;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FixedAssetController extends Controller
{
    public function index()
    {
        $assets = FixedAsset::with(['category', 'journal'])->latest()->get();
        $totalCost = FixedAsset::where('status', 'active')->sum('acquisition_cost');
        $totalAccumulated = FixedAsset::where('status', 'active')->sum('accumulated_depreciation');
        $totalBookValue = FixedAsset::where('status', 'active')->sum('current_book_value');

        return view('assets.index', compact('assets', 'totalCost', 'totalAccumulated', 'totalBookValue'));
    }

    public function create()
    {
        $categories = FixedAssetCategory::all();
        $paymentAccounts = ChartOfAccount::where('is_cash_or_bank', true)->where('is_active', true)->get();

        return view('assets.create', compact('categories', 'paymentAccounts'));
    }

    public function store(Request $request, JournalService $journalService)
    {
        $request->validate([
            'asset_code' => ['required', 'string', 'unique:fixed_assets,asset_code'],
            'name' => ['required', 'string'],
            'category_id' => ['required', 'exists:fixed_asset_categories,id'],
            'acquisition_date' => ['required', 'date'],
            'acquisition_cost' => ['required', 'numeric', 'min:1'],
            'salvage_value' => ['nullable', 'numeric', 'min:0'],
            'useful_life_months' => ['required', 'integer', 'min:1'],
            'depreciation_method' => ['required', 'in:straight_line,declining_balance,none'],
            'payment_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $category = FixedAssetCategory::findOrFail($request->category_id);
        $cost = (float) $request->acquisition_cost;
        $salvage = (float) ($request->salvage_value ?? 0);
        $method = $request->depreciation_method;

        DB::transaction(function () use ($request, $category, $cost, $salvage, $method, $journalService) {
            $asset = FixedAsset::create([
                'asset_code' => $request->asset_code,
                'name' => $request->name,
                'category_id' => $category->id,
                'acquisition_date' => $request->acquisition_date,
                'acquisition_cost' => $cost,
                'salvage_value' => $salvage,
                'useful_life_months' => $request->useful_life_months,
                'depreciation_method' => $method,
                'accumulated_depreciation' => 0,
                'current_book_value' => $cost,
                'status' => 'active',
                'notes' => $request->notes,
            ]);

            // If payment account specified, create acquisition journal:
            // Debit: Asset Account (category->asset_account_id)
            // Credit: Kas/Bank Account
            if ($request->payment_account_id) {
                $journal = $journalService->createJournal(
                    'ASSET_ACQUISITION',
                    $asset->id,
                    $asset->asset_code,
                    'Perolehan Aset Tetap '.$asset->name.' ('.$asset->asset_code.')',
                    [
                        [
                            'account_id' => $category->asset_account_id,
                            'type' => 'debit',
                            'amount' => $cost,
                            'memo' => 'Perolehan Aset '.$asset->name,
                        ],
                        [
                            'account_id' => $request->payment_account_id,
                            'type' => 'credit',
                            'amount' => $cost,
                            'memo' => 'Pembayaran Perolehan Aset '.$asset->asset_code,
                        ],
                    ],
                    $request->acquisition_date,
                    auth()->id()
                );

                $asset->update(['journal_id' => $journal->id]);
            }
        });

        return redirect()->route('assets.index')->with('success', 'Aset tetap berhasil didaftarkan dan dijurnal.');
    }

    public function show(FixedAsset $asset)
    {
        $asset->load(['category.assetAccount', 'category.depreciationExpenseAccount', 'category.accumulatedDepreciationAccount', 'depreciations.journal', 'journal.entries.account']);

        return view('assets.show', compact('asset'));
    }

    public function depreciationForm()
    {
        $currentPeriod = date('Y-m');
        $activeAssets = FixedAsset::with('category')->where('status', 'active')->get();
        $history = AssetDepreciation::with(['asset', 'journal'])->latest('depreciation_date')->paginate(20);

        return view('assets.depreciation', compact('currentPeriod', 'activeAssets', 'history'));
    }

    public function processDepreciation(Request $request, JournalService $journalService)
    {
        $request->validate([
            'period' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'depreciation_date' => ['required', 'date'],
        ]);

        $period = $request->period;
        $date = $request->depreciation_date;

        // Check if already processed for this period
        $existing = AssetDepreciation::where('period', $period)->count();
        if ($existing > 0) {
            return back()->with('error', "Penyusutan aset untuk periode {$period} sudah pernah diproses.");
        }

        $activeAssets = FixedAsset::with('category')->where('status', 'active')->get();
        $processedCount = 0;

        DB::transaction(function () use ($activeAssets, $period, $date, $journalService, &$processedCount) {
            foreach ($activeAssets as $asset) {
                $monthlyDepr = $asset->calculateMonthlyDepreciation();
                if ($monthlyDepr <= 0) {
                    continue;
                }

                $maxDepreciable = max(0, $asset->current_book_value - $asset->salvage_value);
                $actualDepr = min($monthlyDepr, $maxDepreciable);

                if ($actualDepr <= 0) {
                    continue;
                }

                $cat = $asset->category;

                // Journal:
                // Debit: Beban Penyusutan (cat->depreciation_expense_account_id)
                // Credit: Akumulasi Penyusutan (cat->accumulated_depreciation_account_id)
                $journal = $journalService->createJournal(
                    'ASSET_DEPRECIATION',
                    $asset->id,
                    $asset->asset_code.'-'.$period,
                    "Penyusutan Aset {$asset->name} ({$asset->asset_code}) Periode {$period}",
                    [
                        [
                            'account_id' => $cat->depreciation_expense_account_id,
                            'type' => 'debit',
                            'amount' => $actualDepr,
                            'memo' => "Beban Penyusutan {$asset->name} {$period}",
                        ],
                        [
                            'account_id' => $cat->accumulated_depreciation_account_id,
                            'type' => 'credit',
                            'amount' => $actualDepr,
                            'memo' => "Akum. Penyusutan {$asset->name} {$period}",
                        ],
                    ],
                    $date,
                    auth()->id()
                );

                AssetDepreciation::create([
                    'fixed_asset_id' => $asset->id,
                    'depreciation_date' => $date,
                    'period' => $period,
                    'amount' => $actualDepr,
                    'journal_id' => $journal->id,
                    'created_by_id' => auth()->id(),
                ]);

                $newAccumulated = $asset->accumulated_depreciation + $actualDepr;
                $newBookValue = max($asset->salvage_value, $asset->acquisition_cost - $newAccumulated);

                $asset->update([
                    'accumulated_depreciation' => $newAccumulated,
                    'current_book_value' => $newBookValue,
                ]);

                $processedCount++;
            }
        });

        return redirect()->route('assets.depreciation')->with('success', "Penyusutan berhasil diproses untuk {$processedCount} aset pada periode {$period} dan jurnal otomatis terbentuk.");
    }

    public function edit(FixedAsset $asset)
    {
        $categories = FixedAssetCategory::all();

        return view('assets.edit', compact('asset', 'categories'));
    }

    public function update(Request $request, FixedAsset $asset)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'category_id' => ['required', 'exists:fixed_asset_categories,id'],
            'salvage_value' => ['nullable', 'numeric', 'min:0'],
            'useful_life_months' => ['required', 'integer', 'min:1'],
            'depreciation_method' => ['required', 'in:straight_line,declining_balance,none'],
            'status' => ['required', 'in:active,disposed,written_off'],
            'notes' => ['nullable', 'string'],
        ]);

        $asset->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'salvage_value' => (float) ($request->salvage_value ?? 0),
            'useful_life_months' => $request->useful_life_months,
            'depreciation_method' => $request->depreciation_method,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('assets.index')->with('success', 'Data aset tetap berhasil diperbarui.');
    }

    public function destroy(FixedAsset $asset)
    {
        if ($asset->depreciations()->exists()) {
            return back()->with('error', 'Aset ini sudah memiliki riwayat jurnal penyusutan dan tidak dapat dihapus.');
        }

        $asset->delete();

        return redirect()->route('assets.index')->with('success', 'Aset tetap berhasil dihapus.');
    }
}
