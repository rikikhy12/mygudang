<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->withCount('users')->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $matrix = $this->buildPermissionMatrix();

        return view('roles.create', compact('matrix'));
    }

    public function edit(Role $role)
    {
        $matrix = $this->buildPermissionMatrix();

        return view('roles.edit', compact('role', 'matrix'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'unique:roles,name'],
            'display_name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
        ]);

        $role = Role::create([
            'name' => strtolower(str_replace(' ', '_', $request->name)),
            'display_name' => $request->display_name,
            'description' => $request->description,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Role baru berhasil dibuat.');
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'display_name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
        ]);

        $role->update([
            'display_name' => $request->display_name,
            'description' => $request->description,
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', 'Hak akses role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return back()->with('error', 'Role Administrator Utama tidak boleh dihapus.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus.');
    }

    /**
     * Build tabular matrix of permissions for Add, View, Edit, Delete, Print
     * strictly mirroring all sidebar navigation menus.
     */
    protected function buildPermissionMatrix(): array
    {
        $modules = [
            'Master Data' => [
                'master_coa' => ['label' => 'Chart of Accounts (COA)', 'icon' => 'bi-diagram-3-fill'],
                'master_categories' => ['label' => 'Kategori & GL Setting', 'icon' => 'bi-tags-fill'],
                'master_items' => ['label' => 'Master Barang & Jasa', 'icon' => 'bi-box-seam-fill'],
                'master_contacts' => ['label' => 'Supplier & Customer', 'icon' => 'bi-people-fill'],
                'master_warehouses' => ['label' => 'Master Gudang', 'icon' => 'bi-building'],
                'beginning_balances' => ['label' => 'Saldo Awal Sistem', 'icon' => 'bi-box-arrow-in-right'],
            ],
            'Procurement (Pembelian)' => [
                'procurement_pr' => ['label' => 'Purchase Requisition (PR)', 'icon' => 'bi-file-earmark-text'],
                'procurement_po' => ['label' => 'Purchase Order (PO)', 'icon' => 'bi-cart-check-fill'],
                'procurement_grn' => ['label' => 'Penerimaan Barang (GRN)', 'icon' => 'bi-box-arrow-in-down'],
                'procurement_invoices' => ['label' => 'Tagihan Supplier (AP)', 'icon' => 'bi-receipt'],
            ],
            'Sales (Penjualan)' => [
                'sales_pos' => ['label' => 'Terminal Kasir (POS)', 'icon' => 'bi-calculator-fill'],
                'sales_so' => ['label' => 'Sales Order (SO)', 'icon' => 'bi-bag-check-fill'],
                'sales_do' => ['label' => 'Surat Jalan (DO)', 'icon' => 'bi-truck'],
                'sales_invoices' => ['label' => 'Faktur Penjualan (AR)', 'icon' => 'bi-receipt-cutoff'],
            ],
            'Manufacturing & Produksi' => [
                'manufacturing_bom' => ['label' => 'Bill of Materials (BOM)', 'icon' => 'bi-diagram-2-fill'],
                'manufacturing_routing' => ['label' => 'Routing Proses Produksi', 'icon' => 'bi-bezier2'],
                'manufacturing_wo' => ['label' => 'Work Orders (SPK Produksi)', 'icon' => 'bi-gear-wide-connected'],
            ],
            'Inventory & Stok' => [
                'inventory_stock' => ['label' => 'Saldo Stok Gudang', 'icon' => 'bi-boxes'],
                'inventory_ledger' => ['label' => 'Kartu Stok (Ledger)', 'icon' => 'bi-card-list'],
                'inventory_adjustments' => ['label' => 'Stock Opname', 'icon' => 'bi-sliders'],
                'inventory_transfers' => ['label' => 'Transfer Gudang', 'icon' => 'bi-arrow-left-right'],
            ],
            'Aset Tetap (Fixed Assets)' => [
                'fixed_assets' => ['label' => 'Daftar Aset Tetap', 'icon' => 'bi-laptop'],
                'assets_depreciation' => ['label' => 'Penyusutan Otomatis', 'icon' => 'bi-calculator-fill'],
            ],
            'Finance & Kas Bank' => [
                'finance_cash' => ['label' => 'Mutasi Kas & Bank', 'icon' => 'bi-wallet2'],
                'finance_deposits' => ['label' => 'Deposit Customer & Supplier', 'icon' => 'bi-piggy-bank-fill'],
                'finance_cash_in' => ['label' => 'Kas Masuk (Cash In)', 'icon' => 'bi-arrow-down-left-circle-fill'],
                'finance_cash_out' => ['label' => 'Kas Keluar (Cash Out)', 'icon' => 'bi-arrow-up-right-circle-fill'],
                'finance_transfers' => ['label' => 'Transfer Kas / Bank', 'icon' => 'bi-arrow-left-right'],
            ],
            'Accounting & Laporan' => [
                'accounting_journals' => ['label' => 'Jurnal Umum (Manual)', 'icon' => 'bi-journal-bookmark-fill'],
                'accounting_gl' => ['label' => 'Buku Besar (GL)', 'icon' => 'bi-book-half'],
                'accounting_trial_balance' => ['label' => 'Neraca Saldo (Trial)', 'icon' => 'bi-scale'],
                'accounting_profit_loss' => ['label' => 'Laba Rugi (P&L)', 'icon' => 'bi-graph-up-arrow'],
                'accounting_balance_sheet' => ['label' => 'Neraca (Balance Sheet)', 'icon' => 'bi-file-earmark-spreadsheet-fill'],
                'accounting_ar_aging' => ['label' => 'Aging Piutang (AR)', 'icon' => 'bi-clock-history'],
                'accounting_ap_aging' => ['label' => 'Aging Hutang (AP)', 'icon' => 'bi-clock-history'],
            ],
            'Sistem & Keamanan' => [
                'system_users' => ['label' => 'Kelola User', 'icon' => 'bi-person-gear'],
                'system_roles' => ['label' => 'Role & Hak Akses', 'icon' => 'bi-shield-lock-fill'],
                'system_maintenance' => ['label' => 'Backup & Reset Transaksi', 'icon' => 'bi-database-gear'],
            ],
        ];

        $actions = ['view', 'create', 'edit', 'delete', 'print'];
        $actionLabels = [
            'view' => 'Lihat',
            'create' => 'Tambah',
            'edit' => 'Ubah',
            'delete' => 'Hapus',
            'print' => 'Cetak',
        ];

        $permissions = Permission::all()->keyBy('name');
        $adminRole = Role::where('name', 'admin')->first();

        $result = [];
        foreach ($modules as $groupName => $modList) {
            $result[$groupName] = [];
            foreach ($modList as $modKey => $info) {
                $row = [
                    'key' => $modKey,
                    'label' => $info['label'],
                    'icon' => $info['icon'],
                    'actions' => [],
                ];
                foreach ($actions as $act) {
                    $pName = "{$act}_{$modKey}";
                    $perm = $permissions->get($pName);

                    if (! $perm) {
                        $displayName = "{$actionLabels[$act]} {$info['label']}";
                        $perm = Permission::firstOrCreate(
                            ['name' => $pName],
                            [
                                'display_name' => $displayName,
                                'group_name' => $groupName,
                            ]
                        );
                        $permissions->put($pName, $perm);

                        if ($adminRole && ! $adminRole->permissions()->where('permission_id', $perm->id)->exists()) {
                            $adminRole->permissions()->attach($perm->id);
                        }
                    }

                    $row['actions'][$act] = $perm;
                }
                $result[$groupName][] = $row;
            }
        }

        return $result;
    }
}
