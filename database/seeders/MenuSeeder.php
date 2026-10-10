<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('menus')->truncate();

        $menus = [
            // Section: Menu Utama
            [
                'section' => 'Menu Utama',
                'name' => 'Executive Dashboard',
                'route' => 'dashboard',
                'icon' => 'bi-speedometer2',
                'order' => 1,
            ],

            // Section: Master Data
            [
                'section' => 'Master Data',
                'name' => 'Chart of Accounts (COA)',
                'route' => 'coa.index',
                'icon' => 'bi-diagram-3-fill',
                'order' => 10,
            ],
            [
                'section' => 'Master Data',
                'name' => 'Kategori & GL Setting',
                'route' => 'categories.index',
                'icon' => 'bi-tags-fill',
                'order' => 11,
            ],
            [
                'section' => 'Master Data',
                'name' => 'Master Barang & Jasa',
                'route' => 'items.index',
                'icon' => 'bi-box-seam-fill',
                'order' => 12,
            ],
            [
                'section' => 'Master Data',
                'name' => 'Master Merk (Brand)',
                'route' => 'brands.index',
                'icon' => 'bi-patch-check-fill text-info',
                'order' => 13,
            ],
            [
                'section' => 'Master Data',
                'name' => 'Supplier & Customer',
                'route' => 'contacts.index',
                'icon' => 'bi-people-fill',
                'order' => 14,
            ],
            [
                'section' => 'Master Data',
                'name' => 'Master Cabang',
                'route' => 'branches.index',
                'icon' => 'bi-geo-alt-fill text-primary',
                'order' => 15,
            ],
            [
                'section' => 'Master Data',
                'name' => 'Master Gudang',
                'route' => 'warehouses.index',
                'icon' => 'bi-building',
                'order' => 16,
            ],
            [
                'section' => 'Master Data',
                'name' => 'Saldo Awal Sistem',
                'route' => 'accounting.beginning-balances.index',
                'icon' => 'bi-box-arrow-in-right text-success',
                'order' => 17,
            ],

            // Section: Procurement (Pembelian)
            [
                'section' => 'Procurement (Pembelian)',
                'name' => 'Purchase Requisition',
                'route' => 'procurement.pr.index',
                'icon' => 'bi-file-earmark-text',
                'order' => 20,
            ],
            [
                'section' => 'Procurement (Pembelian)',
                'name' => 'Purchase Order (PO)',
                'route' => 'procurement.po.index',
                'icon' => 'bi-cart-check-fill',
                'order' => 21,
            ],
            [
                'section' => 'Procurement (Pembelian)',
                'name' => 'Penerimaan Barang (GRN)',
                'route' => 'procurement.grn.index',
                'icon' => 'bi-box-arrow-in-down',
                'order' => 22,
            ],
            [
                'section' => 'Procurement (Pembelian)',
                'name' => 'Tagihan Supplier (AP)',
                'route' => 'procurement.invoices.index',
                'icon' => 'bi-receipt',
                'order' => 23,
            ],
            [
                'section' => 'Procurement (Pembelian)',
                'name' => 'Retur Pembelian',
                'route' => 'procurement.returns.index',
                'icon' => 'bi-box-arrow-left text-danger',
                'order' => 24,
            ],

            // Section: Sales (Penjualan)
            [
                'section' => 'Sales (Penjualan)',
                'name' => 'Terminal Kasir (POS)',
                'route' => 'pos.index',
                'icon' => 'bi-calculator text-warning',
                'badge' => 'POS',
                'badge_color' => 'badge-light-warning',
                'order' => 30,
            ],
            [
                'section' => 'Sales (Penjualan)',
                'name' => 'Sales Order (SO)',
                'route' => 'sales.so.index',
                'icon' => 'bi-bag-check-fill',
                'order' => 31,
            ],
            [
                'section' => 'Sales (Penjualan)',
                'name' => 'Surat Jalan (DO)',
                'route' => 'sales.do.index',
                'icon' => 'bi-truck',
                'order' => 32,
            ],
            [
                'section' => 'Sales (Penjualan)',
                'name' => 'Faktur Penjualan (AR)',
                'route' => 'sales.invoices.index',
                'icon' => 'bi-receipt-cutoff',
                'order' => 33,
            ],
            [
                'section' => 'Sales (Penjualan)',
                'name' => 'Retur Penjualan',
                'route' => 'sales.returns.index',
                'icon' => 'bi-arrow-return-left text-warning',
                'order' => 34,
            ],
            [
                'section' => 'Sales (Penjualan)',
                'name' => 'Laporan Penjualan Cabang',
                'route' => 'sales.reports.sales-summary',
                'icon' => 'bi-bar-chart-line-fill text-success',
                'order' => 35,
            ],
            [
                'section' => 'Sales (Penjualan)',
                'name' => 'Laporan Komisi Sales',
                'route' => 'sales.reports.commissions',
                'icon' => 'bi-award-fill text-warning',
                'order' => 36,
            ],

            // Section: Manufacturing & Produksi
            [
                'section' => 'Manufacturing & Produksi',
                'name' => 'Bill of Materials (BOM)',
                'route' => 'manufacturing.boms.index',
                'icon' => 'bi-diagram-2-fill text-info',
                'order' => 40,
            ],
            [
                'section' => 'Manufacturing & Produksi',
                'name' => 'Routing Proses Produksi',
                'route' => 'manufacturing.routings.index',
                'icon' => 'bi-bezier2 text-primary',
                'order' => 41,
            ],
            [
                'section' => 'Manufacturing & Produksi',
                'name' => 'Work Orders (SPK)',
                'route' => 'manufacturing.work_orders.index',
                'icon' => 'bi-gear-wide-connected text-warning',
                'order' => 42,
            ],

            // Section: Inventory & Stok
            [
                'section' => 'Inventory & Stok',
                'name' => 'Saldo Stok Gudang',
                'route' => 'inventory.overview',
                'icon' => 'bi-boxes',
                'order' => 50,
            ],
            [
                'section' => 'Inventory & Stok',
                'name' => 'Kartu Stok (Ledger)',
                'route' => 'inventory.movements',
                'icon' => 'bi-card-list',
                'order' => 51,
            ],
            [
                'section' => 'Inventory & Stok',
                'name' => 'Stock Opname',
                'route' => 'inventory.adjustments.index',
                'icon' => 'bi-sliders',
                'order' => 52,
            ],
            [
                'section' => 'Inventory & Stok',
                'name' => 'Transfer Gudang',
                'route' => 'inventory.transfers.index',
                'icon' => 'bi-arrow-left-right',
                'order' => 53,
            ],

            // Section: Aset Tetap (Fixed Assets)
            [
                'section' => 'Aset Tetap (Fixed Assets)',
                'name' => 'Daftar Aset Tetap',
                'route' => 'assets.index',
                'icon' => 'bi-laptop',
                'order' => 60,
            ],
            [
                'section' => 'Aset Tetap (Fixed Assets)',
                'name' => 'Penyusutan Otomatis',
                'route' => 'assets.depreciation',
                'icon' => 'bi-calculator-fill',
                'order' => 61,
            ],

            // Section: Finance & Kas Bank
            [
                'section' => 'Finance & Kas Bank',
                'name' => 'Mutasi Kas & Bank',
                'route' => 'finance.index',
                'icon' => 'bi-wallet2',
                'order' => 70,
            ],
            [
                'section' => 'Finance & Kas Bank',
                'name' => 'Deposit Customer',
                'route' => 'finance.deposits.customer',
                'icon' => 'bi-person-check-fill text-success',
                'order' => 71,
            ],
            [
                'section' => 'Finance & Kas Bank',
                'name' => 'Deposit Supplier',
                'route' => 'finance.deposits.supplier',
                'icon' => 'bi-building-check text-warning',
                'order' => 72,
            ],
            [
                'section' => 'Finance & Kas Bank',
                'name' => 'Kas Masuk (Cash In)',
                'route' => 'finance.cash-in.create',
                'icon' => 'bi-arrow-down-left-circle-fill text-success',
                'order' => 73,
            ],
            [
                'section' => 'Finance & Kas Bank',
                'name' => 'Kas Keluar (Cash Out)',
                'route' => 'finance.cash-out.create',
                'icon' => 'bi-arrow-up-right-circle-fill text-danger',
                'order' => 74,
            ],
            [
                'section' => 'Finance & Kas Bank',
                'name' => 'Transfer Kas / Bank',
                'route' => 'finance.transfer.create',
                'icon' => 'bi-arrow-left-right text-info',
                'order' => 75,
            ],

            // Section: Accounting & Laporan
            [
                'section' => 'Accounting & Laporan',
                'name' => 'Jurnal Umum (Manual)',
                'route' => 'accounting.journals.index',
                'icon' => 'bi-journal-bookmark-fill',
                'order' => 80,
            ],
            [
                'section' => 'Accounting & Laporan',
                'name' => 'Buku Besar (GL)',
                'route' => 'accounting.reports.general-ledger',
                'icon' => 'bi-book-half',
                'order' => 81,
            ],
            [
                'section' => 'Accounting & Laporan',
                'name' => 'Neraca Saldo (Trial)',
                'route' => 'accounting.reports.trial-balance',
                'icon' => 'bi-scale',
                'order' => 82,
            ],
            [
                'section' => 'Accounting & Laporan',
                'name' => 'Laba Rugi (P&L)',
                'route' => 'accounting.reports.profit-loss',
                'icon' => 'bi-graph-up-arrow',
                'order' => 83,
            ],
            [
                'section' => 'Accounting & Laporan',
                'name' => 'Neraca (Balance Sheet)',
                'route' => 'accounting.reports.balance-sheet',
                'icon' => 'bi-file-earmark-spreadsheet-fill',
                'order' => 84,
            ],
            [
                'section' => 'Accounting & Laporan',
                'name' => 'Aging Piutang (AR)',
                'route' => 'accounting.reports.ar-aging',
                'icon' => 'bi-clock-history text-primary',
                'order' => 85,
            ],
            [
                'section' => 'Accounting & Laporan',
                'name' => 'Aging Hutang (AP)',
                'route' => 'accounting.reports.ap-aging',
                'icon' => 'bi-clock-history text-danger',
                'order' => 86,
            ],

            // Section: Sistem & Keamanan
            [
                'section' => 'Sistem & Keamanan',
                'name' => 'Kelola User',
                'route' => 'users.index',
                'icon' => 'bi-person-gear',
                'permission_name' => 'role:admin',
                'order' => 90,
            ],
            [
                'section' => 'Sistem & Keamanan',
                'name' => 'Role & Hak Akses',
                'route' => 'roles.index',
                'icon' => 'bi-shield-lock-fill',
                'permission_name' => 'role:admin',
                'order' => 91,
            ],
            [
                'section' => 'Sistem & Keamanan',
                'name' => 'Backup & Reset Transaksi',
                'route' => 'maintenance.index',
                'icon' => 'bi-database-gear text-warning',
                'permission_name' => 'role:admin',
                'order' => 92,
            ],
        ];

        foreach ($menus as $m) {
            Menu::create($m);
        }
    }
}
