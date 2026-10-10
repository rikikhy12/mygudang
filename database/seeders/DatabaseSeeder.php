<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Contact;
use App\Models\FixedAsset;
use App\Models\FixedAssetCategory;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\JournalService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles and Permissions
        $rolesData = [
            ['name' => 'admin', 'display_name' => 'Super Administrator', 'description' => 'Akses penuh ke semua modul ERP'],
            ['name' => 'finance', 'display_name' => 'Finance & Accounting', 'description' => 'Akses modul keuangan, jurnal, dan laporan keuangan'],
            ['name' => 'procurement', 'display_name' => 'Purchasing & Warehouse', 'description' => 'Akses pengadaan, penerimaan barang, dan inventori'],
            ['name' => 'sales', 'display_name' => 'Sales Executive', 'description' => 'Akses pemesanan penjualan, surat jalan, dan faktur'],
        ];

        $roles = [];
        foreach ($rolesData as $r) {
            $roles[$r['name']] = Role::create($r);
        }

        $permissionsData = [
            // Master Data
            ['name' => 'view_master', 'display_name' => 'Lihat Master Data', 'group_name' => 'master'],
            ['name' => 'manage_master', 'display_name' => 'Kelola Master Data', 'group_name' => 'master'],
            // Procurement
            ['name' => 'view_procurement', 'display_name' => 'Lihat Pembelian', 'group_name' => 'procurement'],
            ['name' => 'manage_procurement', 'display_name' => 'Kelola Pembelian', 'group_name' => 'procurement'],
            ['name' => 'approve_procurement', 'display_name' => 'Approve PR/PO', 'group_name' => 'procurement'],
            // Sales
            ['name' => 'view_sales', 'display_name' => 'Lihat Penjualan', 'group_name' => 'sales'],
            ['name' => 'manage_sales', 'display_name' => 'Kelola Penjualan', 'group_name' => 'sales'],
            // Inventory
            ['name' => 'view_inventory', 'display_name' => 'Lihat Stok & Mutasi', 'group_name' => 'inventory'],
            ['name' => 'manage_inventory', 'display_name' => 'Kelola Penyesuaian/Transfer Stok', 'group_name' => 'inventory'],
            // Fixed Assets
            ['name' => 'view_assets', 'display_name' => 'Lihat Aset Tetap', 'group_name' => 'assets'],
            ['name' => 'manage_assets', 'display_name' => 'Kelola Aset & Penyusutan', 'group_name' => 'assets'],
            // Finance & Kas
            ['name' => 'view_finance', 'display_name' => 'Lihat Kas & Bank', 'group_name' => 'finance'],
            ['name' => 'manage_finance', 'display_name' => 'Kelola Kas Masuk/Keluar', 'group_name' => 'finance'],
            // Accounting & Laporan
            ['name' => 'view_accounting', 'display_name' => 'Lihat Jurnal & Buku Besar', 'group_name' => 'accounting'],
            ['name' => 'manage_accounting', 'display_name' => 'Input Jurnal Manual', 'group_name' => 'accounting'],
            ['name' => 'view_reports', 'display_name' => 'Lihat Laporan Keuangan (Neraca, Laba Rugi)', 'group_name' => 'accounting'],
            // User Management
            ['name' => 'manage_users', 'display_name' => 'Kelola User & Role', 'group_name' => 'users'],
        ];

        foreach ($permissionsData as $p) {
            $perm = Permission::create($p);
            $roles['admin']->permissions()->attach($perm);

            if (in_array($p['group_name'], ['finance', 'accounting', 'assets', 'master'])) {
                $roles['finance']->permissions()->attach($perm);
            }
            if (in_array($p['group_name'], ['procurement', 'inventory', 'master'])) {
                $roles['procurement']->permissions()->attach($perm);
            }
            if (in_array($p['group_name'], ['sales', 'inventory', 'master'])) {
                $roles['sales']->permissions()->attach($perm);
            }
        }

        // Create Users
        $adminUser = User::create([
            'name' => 'Administrator ERP',
            'email' => 'admin@erp.local',
            'password' => Hash::make('password'),
        ]);
        $adminUser->roles()->attach($roles['admin']);

        $financeUser = User::create([
            'name' => 'Budi Finansia',
            'email' => 'finance@erp.local',
            'password' => Hash::make('password'),
        ]);
        $financeUser->roles()->attach($roles['finance']);

        $purchasingUser = User::create([
            'name' => 'Rian Purchasing',
            'email' => 'purchasing@erp.local',
            'password' => Hash::make('password'),
        ]);
        $purchasingUser->roles()->attach($roles['procurement']);

        $salesUser = User::create([
            'name' => 'Siti Sales',
            'email' => 'sales@erp.local',
            'password' => Hash::make('password'),
        ]);
        $salesUser->roles()->attach($roles['sales']);

        // 2. Chart of Accounts (COA)
        $coaList = [
            // Aset Lancar
            ['code' => '1001', 'name' => 'Kas Utama (Petty Cash)', 'type' => 'asset', 'normal_balance' => 'debit', 'is_cash_or_bank' => true],
            ['code' => '1002', 'name' => 'Bank BCA Operasional', 'type' => 'asset', 'normal_balance' => 'debit', 'is_cash_or_bank' => true],
            ['code' => '1003', 'name' => 'Bank Mandiri Giro', 'type' => 'asset', 'normal_balance' => 'debit', 'is_cash_or_bank' => true],
            ['code' => '1101', 'name' => 'Piutang Usaha (Accounts Receivable)', 'type' => 'asset', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '1201', 'name' => 'Persediaan Barang Siap Jadi', 'type' => 'asset', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '1202', 'name' => 'Persediaan Barang Hasil Produksi', 'type' => 'asset', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '1301', 'name' => 'Penerimaan Barang Belum Ditagih (Unbilled GRN)', 'type' => 'asset', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '1302', 'name' => 'Uang Muka Pembelian', 'type' => 'asset', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],

            // Aset Tetap & Akumulasi
            ['code' => '1501', 'name' => 'Aset Tetap - Peralatan Kantor & Komputer', 'type' => 'asset', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '1502', 'name' => 'Aset Tetap - Kendaraan Operasional', 'type' => 'asset', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '1503', 'name' => 'Aset Tetap - Mesin & Peralatan Produksi', 'type' => 'asset', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '1504', 'name' => 'Aset Tak Berwujud - Web, Domain & Aplikasi Digital', 'type' => 'asset', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '1601', 'name' => 'Akum. Penyusutan Peralatan Kantor', 'type' => 'asset', 'normal_balance' => 'credit', 'is_cash_or_bank' => false],
            ['code' => '1602', 'name' => 'Akum. Penyusutan Kendaraan Operasional', 'type' => 'asset', 'normal_balance' => 'credit', 'is_cash_or_bank' => false],
            ['code' => '1603', 'name' => 'Akum. Penyusutan Mesin Produksi', 'type' => 'asset', 'normal_balance' => 'credit', 'is_cash_or_bank' => false],
            ['code' => '1604', 'name' => 'Akum. Amortisasi Aset Web & Aplikasi', 'type' => 'asset', 'normal_balance' => 'credit', 'is_cash_or_bank' => false],

            // Kewajiban
            ['code' => '2001', 'name' => 'Hutang Usaha (Accounts Payable)', 'type' => 'liability', 'normal_balance' => 'credit', 'is_cash_or_bank' => false],
            ['code' => '2002', 'name' => 'Hutang Gaji & Upah', 'type' => 'liability', 'normal_balance' => 'credit', 'is_cash_or_bank' => false],
            ['code' => '2003', 'name' => 'Hutang Pajak PPN', 'type' => 'liability', 'normal_balance' => 'credit', 'is_cash_or_bank' => false],

            // Ekuitas
            ['code' => '3001', 'name' => 'Modal Disetor (Paid-in Capital)', 'type' => 'equity', 'normal_balance' => 'credit', 'is_cash_or_bank' => false],
            ['code' => '3002', 'name' => 'Laba Ditahan (Retained Earnings)', 'type' => 'equity', 'normal_balance' => 'credit', 'is_cash_or_bank' => false],

            // Pendapatan
            ['code' => '4001', 'name' => 'Pendapatan Penjualan Barang Siap Jadi', 'type' => 'revenue', 'normal_balance' => 'credit', 'is_cash_or_bank' => false],
            ['code' => '4002', 'name' => 'Pendapatan Penjualan Hasil Produksi', 'type' => 'revenue', 'normal_balance' => 'credit', 'is_cash_or_bank' => false],
            ['code' => '4003', 'name' => 'Pendapatan Jasa & Servis', 'type' => 'revenue', 'normal_balance' => 'credit', 'is_cash_or_bank' => false],
            ['code' => '4004', 'name' => 'Pendapatan Lain-lain', 'type' => 'revenue', 'normal_balance' => 'credit', 'is_cash_or_bank' => false],

            // HPP
            ['code' => '5001', 'name' => 'HPP Barang Siap Jadi', 'type' => 'cogs', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '5002', 'name' => 'HPP Barang Hasil Produksi', 'type' => 'cogs', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '5003', 'name' => 'Biaya Produksi Langsung', 'type' => 'cogs', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],

            // Beban Operasional
            ['code' => '6001', 'name' => 'Beban Gaji Karyawan', 'type' => 'expense', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '6002', 'name' => 'Beban Listrik, Air & Internet', 'type' => 'expense', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '6003', 'name' => 'Beban Sewa Gedung', 'type' => 'expense', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '6004', 'name' => 'Beban Penyusutan Aset Tetap', 'type' => 'expense', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '6005', 'name' => 'Beban Transportasi & Logistik', 'type' => 'expense', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '6006', 'name' => 'Beban Selisih / Penyesuaian Persediaan', 'type' => 'expense', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '6007', 'name' => 'Beban Legalitas, Notaris & Pendirian / Perizinan PT', 'type' => 'expense', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '6008', 'name' => 'Beban Utilitas Gas, Bahan Bakar & Energi', 'type' => 'expense', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '6009', 'name' => 'Beban Pengembangan Web, Domain, Hosting & Lisensi IT', 'type' => 'expense', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
            ['code' => '6010', 'name' => 'Beban Perlengkapan & Operasional Kantor', 'type' => 'expense', 'normal_balance' => 'debit', 'is_cash_or_bank' => false],
        ];

        $coas = [];
        foreach ($coaList as $c) {
            $coas[$c['code']] = ChartOfAccount::firstOrCreate(['code' => $c['code']], $c);
        }

        // 3. Units
        $unitsData = [
            ['name' => 'Pieces / Buah', 'symbol' => 'PCS'],
            ['name' => 'Unit', 'symbol' => 'UNIT'],
            ['name' => 'Box / Dus', 'symbol' => 'BOX'],
            ['name' => 'Kilogram', 'symbol' => 'KG'],
            ['name' => 'Jam (Service)', 'symbol' => 'JAM'],
        ];
        $units = [];
        foreach ($unitsData as $u) {
            $units[$u['symbol']] = Unit::create($u);
        }

        // 4. Item Categories with GL Mapping
        $catJasa = ItemCategory::create([
            'name' => 'Jasa Konsultasi & Layanan IT',
            'code' => 'CAT-JASA',
            'type' => 'service',
            'inventory_account_id' => null,
            'cogs_account_id' => null,
            'purchase_account_id' => null,
            'sales_account_id' => $coas['4003']->id,
            'description' => 'Kategori layanan jasa tanpa kartu stok fisik',
        ]);

        $catTrading = ItemCategory::create([
            'name' => 'Barang Siap Jadi (Trading/Hardware)',
            'code' => 'CAT-TRADING',
            'type' => 'finished_good',
            'inventory_account_id' => $coas['1201']->id,
            'cogs_account_id' => $coas['5001']->id,
            'purchase_account_id' => $coas['1301']->id,
            'sales_account_id' => $coas['4001']->id,
            'description' => 'Barang siap jual langsung dibeli dari supplier',
        ]);

        $catProduksi = ItemCategory::create([
            'name' => 'Barang Hasil Produksi / Rakitan',
            'code' => 'CAT-MANUF',
            'type' => 'manufactured_good',
            'inventory_account_id' => $coas['1202']->id,
            'cogs_account_id' => $coas['5002']->id,
            'purchase_account_id' => $coas['1301']->id,
            'sales_account_id' => $coas['4002']->id,
            'description' => 'Barang hasil fabrikasi / assembly internal',
        ]);

        // 5. Warehouses
        $warehousePusat = Warehouse::create([
            'code' => 'WH-PST',
            'name' => 'Gudang Utama (Pusat)',
            'location' => 'Jakarta Barat',
            'is_active' => true,
        ]);
        $warehouseCabang = Warehouse::create([
            'code' => 'WH-BDG',
            'name' => 'Gudang Distribusi Bandung',
            'location' => 'Bandung Timur',
            'is_active' => true,
        ]);

        // 6. Contacts (Suppliers & Customers)
        $sup1 = Contact::create([
            'type' => 'supplier',
            'code' => 'SUP-001',
            'name' => 'PT Mega Pratama Distributor',
            'email' => 'sales@megapratama.com',
            'phone' => '021-5551234',
            'address' => 'Jl. Industri Raya No. 45, Jakarta Pusat',
            'tax_number' => '01.234.567.8-012.000',
        ]);
        $sup2 = Contact::create([
            'type' => 'supplier',
            'code' => 'SUP-002',
            'name' => 'CV Sumber Elektronika Komponen',
            'email' => 'order@sumberkomponen.co.id',
            'phone' => '022-7778899',
            'address' => 'Jl. Soekarno Hatta No. 100, Bandung',
        ]);

        $cust1 = Contact::create([
            'type' => 'customer',
            'code' => 'CUST-001',
            'name' => 'PT Jaya Abadi Sentosa',
            'email' => 'purchasing@jayaabadi.co.id',
            'phone' => '021-8889900',
            'address' => 'Gedung Wisma Niaga Lt. 8, Sudirman, Jakarta',
            'tax_number' => '02.345.678.9-034.000',
        ]);
        $cust2 = Contact::create([
            'type' => 'customer',
            'code' => 'CUST-002',
            'name' => 'Toko Mitra Mandiri Sejahtera',
            'email' => 'mitramandiri@gmail.com',
            'phone' => '081234567890',
            'address' => 'Ruko Mangga Dua Square Blok C No. 12, Jakarta Utara',
        ]);

        // 7. Master Items
        $item1 = Item::create([
            'code' => 'SRV-001',
            'barcode' => null,
            'name' => 'Jasa Setup Server & Network Infrastructure',
            'category_id' => $catJasa->id,
            'unit_id' => $units['JAM']->id,
            'item_type' => 'service',
            'purchase_price' => 0,
            'selling_price' => 750000,
            'min_stock' => 0,
            'current_stock' => 0,
            'is_active' => true,
            'description' => 'Jasa instalasi, konfigurasi router, switch, firewall dan server',
        ]);

        $item2 = Item::create([
            'code' => 'LAP-001',
            'barcode' => '8991234567891',
            'name' => 'Laptop ASUS Vivobook 15 OLED Core i7',
            'category_id' => $catTrading->id,
            'unit_id' => $units['UNIT']->id,
            'item_type' => 'finished_good',
            'purchase_price' => 12500000,
            'selling_price' => 15200000,
            'min_stock' => 5,
            'current_stock' => 20,
            'is_active' => true,
            'description' => 'Laptop komersial 15.6 inch OLED, 16GB RAM, 1TB SSD',
        ]);

        $item3 = Item::create([
            'code' => 'MON-001',
            'barcode' => '8991234567892',
            'name' => 'Monitor Dell UltraSharp 27" 4K IPS',
            'category_id' => $catTrading->id,
            'unit_id' => $units['UNIT']->id,
            'item_type' => 'finished_good',
            'purchase_price' => 6200000,
            'selling_price' => 7800000,
            'min_stock' => 5,
            'current_stock' => 15,
            'is_active' => true,
            'description' => 'Monitor profesional color-accurate USB-C hub',
        ]);

        $item4 = Item::create([
            'code' => 'SRV-PRD-01',
            'barcode' => '8991234567893',
            'name' => 'Server Rack Enterprise Nexus 2U Dual Xeon',
            'category_id' => $catProduksi->id,
            'unit_id' => $units['UNIT']->id,
            'item_type' => 'manufactured_good',
            'purchase_price' => 28000000,
            'selling_price' => 38500000,
            'min_stock' => 2,
            'current_stock' => 5,
            'is_active' => true,
            'description' => 'Server enterprise rakitan internal dengan redundant PSU dan RAID controller',
        ]);

        // 8. Fixed Asset Categories & Assets
        $assetCat1 = FixedAssetCategory::create([
            'name' => 'Peralatan Kantor & Komputer',
            'asset_account_id' => $coas['1501']->id,
            'depreciation_expense_account_id' => $coas['6004']->id,
            'accumulated_depreciation_account_id' => $coas['1601']->id,
            'useful_life_years' => 4,
        ]);

        $assetCat2 = FixedAssetCategory::create([
            'name' => 'Kendaraan Operasional',
            'asset_account_id' => $coas['1502']->id,
            'depreciation_expense_account_id' => $coas['6004']->id,
            'accumulated_depreciation_account_id' => $coas['1602']->id,
            'useful_life_years' => 8,
        ]);

        $asset1 = FixedAsset::create([
            'asset_code' => 'AST-PC-001',
            'name' => 'Apple MacBook Pro M3 Max 64GB',
            'category_id' => $assetCat1->id,
            'acquisition_date' => '2026-01-10',
            'acquisition_cost' => 48000000,
            'salvage_value' => 0,
            'useful_life_months' => 48,
            'depreciation_method' => 'straight_line',
            'accumulated_depreciation' => 0,
            'current_book_value' => 48000000,
            'status' => 'active',
            'notes' => 'Laptop development tim lead engineer',
        ]);

        $asset2 = FixedAsset::create([
            'asset_code' => 'AST-VEH-001',
            'name' => 'Mobil Daihatsu Gran Max Blindvan Logistik',
            'category_id' => $assetCat2->id,
            'acquisition_date' => '2026-01-15',
            'acquisition_cost' => 180000000,
            'salvage_value' => 20000000,
            'useful_life_months' => 96,
            'depreciation_method' => 'straight_line',
            'accumulated_depreciation' => 0,
            'current_book_value' => 180000000,
            'status' => 'active',
            'notes' => 'Kendaraan armada pengiriman barang',
        ]);

        // 9. Initial Opening Journals & Stock
        $journalService = new JournalService;
        $inventoryService = new InventoryService;

        // Saldo Awal Kas, Bank, Aset Tetap, Persediaan & Modal
        $totalStockValue = (20 * 12500000) + (15 * 6200000) + (5 * 28000000); // 250jt + 93jt + 140jt = 483.000.000
        $totalAssetValue = 48000000 + 180000000; // 228.000.000
        $totalCashBank = 300000000 + 200000000 + 50000000; // BCA 300jt, Mandiri 200jt, Kas 50jt = 550.000.000
        $totalOpeningEquity = $totalStockValue + $totalAssetValue + $totalCashBank; // 1.261.000.000

        $openingJournal = $journalService->createJournal(
            'MANUAL',
            null,
            'OPENING-BALANCE-2026',
            'Saldo Awal Ekuitas, Kas/Bank, Aset Tetap, dan Persediaan Barang',
            [
                ['account_id' => $coas['1001']->id, 'type' => 'debit', 'amount' => 50000000, 'memo' => 'Saldo Awal Kas Utama'],
                ['account_id' => $coas['1002']->id, 'type' => 'debit', 'amount' => 300000000, 'memo' => 'Saldo Awal Bank BCA'],
                ['account_id' => $coas['1003']->id, 'type' => 'debit', 'amount' => 200000000, 'memo' => 'Saldo Awal Bank Mandiri'],
                ['account_id' => $coas['1201']->id, 'type' => 'debit', 'amount' => 343000000, 'memo' => 'Persediaan Awal Barang Siap Jadi'],
                ['account_id' => $coas['1202']->id, 'type' => 'debit', 'amount' => 140000000, 'memo' => 'Persediaan Awal Barang Produksi'],
                ['account_id' => $coas['1501']->id, 'type' => 'debit', 'amount' => 48000000, 'memo' => 'Saldo Awal Aset Peralatan'],
                ['account_id' => $coas['1502']->id, 'type' => 'debit', 'amount' => 180000000, 'memo' => 'Saldo Awal Aset Kendaraan'],
                ['account_id' => $coas['3001']->id, 'type' => 'credit', 'amount' => $totalOpeningEquity, 'memo' => 'Modal Disetor Pemilik'],
            ],
            '2026-01-01',
            $adminUser->id
        );

        // Record initial stock movement
        $inventoryService->recordStockMovement($item2->id, $warehousePusat->id, 'OPENING_BALANCE', $openingJournal->id, 'OPENING-2026', 'in', 20, 12500000, 'Stok Awal Laptop ASUS', '2026-01-01');
        $inventoryService->recordStockMovement($item3->id, $warehousePusat->id, 'OPENING_BALANCE', $openingJournal->id, 'OPENING-2026', 'in', 15, 6200000, 'Stok Awal Monitor Dell', '2026-01-01');
        $inventoryService->recordStockMovement($item4->id, $warehousePusat->id, 'OPENING_BALANCE', $openingJournal->id, 'OPENING-2026', 'in', 5, 28000000, 'Stok Awal Server Rack', '2026-01-01');
    }
}
