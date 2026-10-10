<?php

use App\Models\ChartOfAccount;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure COA 2004 and 1104 exist
        ChartOfAccount::firstOrCreate(
            ['code' => '2004'],
            [
                'name' => 'Hutang Deposit & Uang Muka Pelanggan',
                'type' => 'liability',
                'normal_balance' => 'credit',
                'is_cash_or_bank' => false,
                'is_active' => true,
            ]
        );

        ChartOfAccount::firstOrCreate(
            ['code' => '1104'],
            [
                'name' => 'Uang Muka & Deposit ke Supplier',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'is_cash_or_bank' => false,
                'is_active' => true,
            ]
        );

        // 2. Define Granular Module Matrix Permissions (View, Create, Edit, Delete, Print)
        $modules = [
            // Master Data
            'master_items' => ['label' => 'Master Barang & Jasa', 'group' => 'Master Data', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'master_coa' => ['label' => 'Chart of Accounts (COA)', 'group' => 'Master Data', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'master_categories' => ['label' => 'Kategori & GL Setting', 'group' => 'Master Data', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'master_contacts' => ['label' => 'Supplier & Customer', 'group' => 'Master Data', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'master_warehouses' => ['label' => 'Master Gudang', 'group' => 'Master Data', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'beginning_balances' => ['label' => 'Saldo Awal Sistem', 'group' => 'Master Data', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],

            // Procurement
            'procurement_pr' => ['label' => 'Purchase Requisition (PR)', 'group' => 'Procurement', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'procurement_po' => ['label' => 'Purchase Order (PO)', 'group' => 'Procurement', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'procurement_grn' => ['label' => 'Penerimaan Barang (GRN)', 'group' => 'Procurement', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'procurement_invoices' => ['label' => 'Tagihan Supplier (AP)', 'group' => 'Procurement', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],

            // Sales
            'sales_pos' => ['label' => 'Terminal Kasir (POS)', 'group' => 'Sales', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'sales_so' => ['label' => 'Sales Order (SO)', 'group' => 'Sales', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'sales_do' => ['label' => 'Surat Jalan (DO)', 'group' => 'Sales', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'sales_invoices' => ['label' => 'Faktur Penjualan (AR)', 'group' => 'Sales', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],

            // Manufacturing
            'manufacturing_bom' => ['label' => 'Bill of Materials (BOM)', 'group' => 'Manufacturing', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'manufacturing_routing' => ['label' => 'Routing Proses Produksi', 'group' => 'Manufacturing', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'manufacturing_wo' => ['label' => 'Work Orders (SPK Produksi)', 'group' => 'Manufacturing', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],

            // Inventory
            'inventory_stock' => ['label' => 'Saldo & Kartu Stok', 'group' => 'Inventory', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'inventory_adjustments' => ['label' => 'Stock Opname & Transfer', 'group' => 'Inventory', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],

            // Finance
            'finance_cash' => ['label' => 'Mutasi Kas & Kas Masuk/Keluar', 'group' => 'Finance', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'finance_deposits' => ['label' => 'Deposit Customer & Supplier', 'group' => 'Finance', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],

            // Assets
            'fixed_assets' => ['label' => 'Aset Tetap & Depresiasi', 'group' => 'Fixed Assets', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],

            // Accounting
            'accounting_journals' => ['label' => 'Jurnal Umum (Manual)', 'group' => 'Accounting', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'accounting_reports' => ['label' => 'Laporan Keuangan & Aging', 'group' => 'Accounting', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],

            // Users & System
            'system_users' => ['label' => 'User Management & Role', 'group' => 'Sistem', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
            'system_maintenance' => ['label' => 'Backup & Maintenance', 'group' => 'Sistem', 'actions' => ['view', 'create', 'edit', 'delete', 'print']],
        ];

        $actionLabels = [
            'view' => 'Lihat',
            'create' => 'Tambah',
            'edit' => 'Ubah',
            'delete' => 'Hapus',
            'print' => 'Cetak',
        ];

        $adminRole = Role::where('name', 'admin')->first();

        foreach ($modules as $modKey => $modData) {
            foreach ($modData['actions'] as $act) {
                $permName = "{$act}_{$modKey}";
                $displayName = "{$actionLabels[$act]} {$modData['label']}";

                $perm = Permission::firstOrCreate(
                    ['name' => $permName],
                    [
                        'display_name' => $displayName,
                        'group_name' => $modData['group'],
                    ]
                );

                if ($adminRole && ! $adminRole->permissions()->where('permission_id', $perm->id)->exists()) {
                    $adminRole->permissions()->attach($perm->id);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
