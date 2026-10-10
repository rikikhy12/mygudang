<?php

use App\Models\ChartOfAccount;
use App\Models\FixedAssetCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $newCoas = [
            ['code' => '1504', 'name' => 'Aset Tak Berwujud - Web, Domain & Aplikasi Digital', 'type' => 'asset', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '1604', 'name' => 'Akum. Amortisasi Aset Web & Aplikasi', 'type' => 'asset', 'normal_balance' => 'credit', 'is_cash_or_bank' => false],
            ['code' => '6007', 'name' => 'Beban Legalitas, Notaris & Pendirian / Perizinan PT', 'type' => 'expense', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '6008', 'name' => 'Beban Utilitas Gas, Bahan Bakar & Energi', 'type' => 'expense', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '6009', 'name' => 'Beban Pengembangan Web, Domain, Hosting & Lisensi IT', 'type' => 'expense', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '6010', 'name' => 'Beban Perlengkapan & Operasional Kantor', 'type' => 'expense', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
        ];

        foreach ($newCoas as $c) {
            ChartOfAccount::firstOrCreate(['code' => $c['code']], $c);
        }

        $assetAcc = ChartOfAccount::where('code', '1504')->first();
        $deprAcc = ChartOfAccount::where('code', '6004')->first();
        $accumAcc = ChartOfAccount::where('code', '1604')->first();

        if ($assetAcc && $deprAcc && $accumAcc) {
            FixedAssetCategory::firstOrCreate(
                ['name' => 'Aset Web, Software & Sistem Digital'],
                [
                    'asset_account_id' => $assetAcc->id,
                    'depreciation_expense_account_id' => $deprAcc->id,
                    'accumulated_depreciation_account_id' => $accumAcc->id,
                    'useful_life_years' => 4,
                ]
            );
        }
    }

    public function down(): void
    {
        ChartOfAccount::whereIn('code', ['1504', '1604', '6007', '6008', '6009', '6010'])->delete();
        FixedAssetCategory::where('name', 'Aset Web, Software & Sistem Digital')->delete();
    }
};
