<?php

use App\Http\Controllers\AccountingReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BeginningBalanceController;
use App\Http\Controllers\BeginningBalanceImportController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\FixedAssetController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemImportController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ManufacturingController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

// Storage file fallback route to prevent 403 Forbidden on Windows / Laragon / artisan serve
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    if (! file_exists($filePath)) {
        $filePath = public_path('storage/' . $path);
    }
    if (! file_exists($filePath)) {
        $filePath = public_path('uploads/' . $path);
    }
    if (! file_exists($filePath)) {
        abort(404);
    }

    $mime = mime_content_type($filePath) ?: 'application/octet-stream';
    return response()->file($filePath, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('path', '.*')->name('storage.file');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated ERP Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 1. User & Role Management
    Route::middleware(['permission:manage_users'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
    });

    // 2. Master Data & Quick Modals
    Route::post('contacts/quick-store', [ContactController::class, 'quickStore'])->name('contacts.quick-store');
    Route::get('items/quick-form-data', [ItemController::class, 'quickFormData'])->name('items.quick-form-data');
    Route::post('items/quick-store', [ItemController::class, 'quickStore'])->name('items.quick-store');
    Route::post('coa/quick-store', [ChartOfAccountController::class, 'quickStore'])->name('coa.quick-store');

    Route::middleware(['permission:view_master'])->group(function () {
        Route::resource('branches', BranchController::class);
        Route::resource('brands', BrandController::class);
        Route::resource('coa', ChartOfAccountController::class);
        Route::resource('categories', ItemCategoryController::class);
        Route::resource('contacts', ContactController::class);
        Route::resource('warehouses', WarehouseController::class);

        Route::get('items/import', [ItemImportController::class, 'index'])->name('items.import');
        Route::get('items/import/template', [ItemImportController::class, 'downloadTemplate'])->name('items.import.template');
        Route::post('items/import/preview', [ItemImportController::class, 'preview'])->name('items.import.preview');
        Route::post('items/import/store', [ItemImportController::class, 'store'])->name('items.import.store');
        Route::post('items/{item}/duplicate', [ItemController::class, 'duplicate'])->name('items.duplicate');
        Route::resource('items', ItemController::class);
        Route::get('items/{item}/stock-card', [ItemController::class, 'stockCard'])->name('items.stock-card');
    });

    // 3. Procurement (Pembelian)
    Route::prefix('procurement')->name('procurement.')->group(function () {
        // Purchase Requisition
        Route::get('pr', [ProcurementController::class, 'prIndex'])->name('pr.index');
        Route::get('pr/create', [ProcurementController::class, 'prCreate'])->name('pr.create');
        Route::post('pr', [ProcurementController::class, 'prStore'])->name('pr.store');
        Route::get('pr/{pr}', [ProcurementController::class, 'prShow'])->name('pr.show');
        Route::post('pr/{pr}/approve', [ProcurementController::class, 'prApprove'])->name('pr.approve');
        Route::post('pr/{pr}/reject', [ProcurementController::class, 'prReject'])->name('pr.reject');

        // Purchase Order
        Route::get('po', [ProcurementController::class, 'poIndex'])->name('po.index');
        Route::get('po/create', [ProcurementController::class, 'poCreate'])->name('po.create');
        Route::post('po', [ProcurementController::class, 'poStore'])->name('po.store');
        Route::get('po/{po}', [ProcurementController::class, 'poShow'])->name('po.show');
        Route::post('po/{po}/duplicate', [ProcurementController::class, 'poDuplicate'])->name('po.duplicate');
        Route::delete('po/{po}', [ProcurementController::class, 'poDestroy'])->name('po.destroy');
        Route::get('po/{po}/print', [ProcurementController::class, 'poPrint'])->name('po.print');

        // Goods Receipt (GRN)
        Route::get('grn', [ProcurementController::class, 'grnIndex'])->name('grn.index');
        Route::get('grn/create', [ProcurementController::class, 'grnCreate'])->name('grn.create');
        Route::post('grn', [ProcurementController::class, 'grnStore'])->name('grn.store');
        Route::get('grn/{grn}', [ProcurementController::class, 'grnShow'])->name('grn.show');
        Route::get('grn/{grn}/print', [ProcurementController::class, 'grnPrint'])->name('grn.print');

        // Purchase Invoices (Tagihan AP)
        Route::get('invoices', [ProcurementController::class, 'invoiceIndex'])->name('invoices.index');
        Route::get('invoices/create', [ProcurementController::class, 'invoiceCreate'])->name('invoices.create');
        Route::post('invoices', [ProcurementController::class, 'invoiceStore'])->name('invoices.store');
        Route::get('invoices/{invoice}', [ProcurementController::class, 'invoiceShow'])->name('invoiceShow');
        Route::get('invoices/{invoice}/show', [ProcurementController::class, 'invoiceShow'])->name('invoices.show');
        Route::get('invoices/{invoice}/print', [ProcurementController::class, 'invoicePrint'])->name('invoices.print');
        Route::get('invoices/{invoice}/edit', [ProcurementController::class, 'invoiceEdit'])->name('invoices.edit');
        Route::put('invoices/{invoice}', [ProcurementController::class, 'invoiceUpdate'])->name('invoices.update');
        Route::delete('invoices/{invoice}', [ProcurementController::class, 'invoiceDestroy'])->name('invoices.destroy');

        // Purchase Returns (Retur Pembelian)
        Route::get('returns', [ProcurementController::class, 'returnIndex'])->name('returns.index');
        Route::get('returns/create', [ProcurementController::class, 'returnCreate'])->name('returns.create');
        Route::post('returns', [ProcurementController::class, 'returnStore'])->name('returns.store');
        Route::get('returns/{return}', [ProcurementController::class, 'returnShow'])->name('returns.show');
        Route::get('returns/{return}/print', [ProcurementController::class, 'returnPrint'])->name('returns.print');
    });

    // 4. Sales (Penjualan)
    Route::prefix('sales')->name('sales.')->group(function () {
        // Sales Reports
        Route::get('reports/sales-summary', [SalesReportController::class, 'index'])->name('reports.sales-summary');
        Route::get('reports/commissions', [SalesReportController::class, 'commissions'])->name('reports.commissions');

        // Sales Orders
        Route::get('so', [SalesController::class, 'soIndex'])->name('so.index');
        Route::get('so/create', [SalesController::class, 'soCreate'])->name('so.create');
        Route::post('so', [SalesController::class, 'soStore'])->name('so.store');
        Route::get('so/{so}', [SalesController::class, 'soShow'])->name('so.show');
        Route::post('so/{so}/duplicate', [SalesController::class, 'soDuplicate'])->name('so.duplicate');
        Route::delete('so/{so}', [SalesController::class, 'soDestroy'])->name('so.destroy');
        Route::get('so/{so}/print', [SalesController::class, 'soPrint'])->name('so.print');

        // Delivery Orders (DO / Surat Jalan)
        Route::get('do', [SalesController::class, 'doIndex'])->name('do.index');
        Route::get('do/create', [SalesController::class, 'doCreate'])->name('do.create');
        Route::post('do', [SalesController::class, 'doStore'])->name('do.store');
        Route::get('do/{do}', [SalesController::class, 'doShow'])->name('do.show');
        Route::get('do/{do}/print', [SalesController::class, 'doPrint'])->name('do.print');

        // Sales Invoices (Faktur AR)
        Route::get('invoices', [SalesController::class, 'invoiceIndex'])->name('invoices.index');
        Route::get('invoices/create', [SalesController::class, 'invoiceCreate'])->name('invoices.create');
        Route::post('invoices', [SalesController::class, 'invoiceStore'])->name('invoices.store');
        Route::get('invoices/{invoice}', [SalesController::class, 'invoiceShow'])->name('invoices.show');
        Route::get('invoices/{invoice}/print', [SalesController::class, 'invoicePrint'])->name('invoices.print');
        Route::get('invoices/{invoice}/edit', [SalesController::class, 'invoiceEdit'])->name('invoices.edit');
        Route::put('invoices/{invoice}', [SalesController::class, 'invoiceUpdate'])->name('invoices.update');
        Route::delete('invoices/{invoice}', [SalesController::class, 'invoiceDestroy'])->name('invoices.destroy');

        // Sales Returns (Retur Penjualan)
        Route::get('returns', [SalesController::class, 'returnIndex'])->name('returns.index');
        Route::get('returns/create', [SalesController::class, 'returnCreate'])->name('returns.create');
        Route::post('returns', [SalesController::class, 'returnStore'])->name('returns.store');
        Route::get('returns/{return}', [SalesController::class, 'returnShow'])->name('returns.show');
        Route::get('returns/{return}/print', [SalesController::class, 'returnPrint'])->name('returns.print');
    });

    // 4.1 Point of Sale (POS Kasir)
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('checkout', [PosController::class, 'store'])->name('store');
        Route::get('receipt/{invoice}', [PosController::class, 'receipt'])->name('receipt');

        // Shift Kasir (Buka, Tutup, Setoran Kas & Struk Z-Report)
        Route::post('shift/open', [PosController::class, 'openShift'])->name('shift.open');
        Route::post('shift/close', [PosController::class, 'closeShift'])->name('shift.close');
        Route::post('shift/drop', [PosController::class, 'dropCash'])->name('shift.drop');
        Route::get('shift/{shift}/receipt', [PosController::class, 'shiftReceipt'])->name('shift.receipt');
        Route::post('branch/switch', [PosController::class, 'switchBranch'])->name('branch.switch');
    });

    // 5. Manufacturing & Produksi (BOM, Routing, Work Orders)
    Route::prefix('manufacturing')->name('manufacturing.')->group(function () {
        // BOM (Bill of Materials)
        Route::get('boms', [ManufacturingController::class, 'bomIndex'])->name('boms.index');
        Route::get('boms/create', [ManufacturingController::class, 'bomCreate'])->name('boms.create');
        Route::post('boms', [ManufacturingController::class, 'bomStore'])->name('boms.store');
        Route::get('boms/{bom}/edit', [ManufacturingController::class, 'bomEdit'])->name('boms.edit');
        Route::put('boms/{bom}', [ManufacturingController::class, 'bomUpdate'])->name('boms.update');
        Route::delete('boms/{bom}', [ManufacturingController::class, 'bomDestroy'])->name('boms.destroy');

        // Routings (Tahapan Proses A -> B -> C -> D -> E)
        Route::get('routings', [ManufacturingController::class, 'routingIndex'])->name('routings.index');
        Route::get('routings/create', [ManufacturingController::class, 'routingCreate'])->name('routings.create');
        Route::post('routings', [ManufacturingController::class, 'routingStore'])->name('routings.store');
        Route::get('routings/{routing}/edit', [ManufacturingController::class, 'routingEdit'])->name('routings.edit');
        Route::put('routings/{routing}', [ManufacturingController::class, 'routingUpdate'])->name('routings.update');
        Route::delete('routings/{routing}', [ManufacturingController::class, 'routingDestroy'])->name('routings.destroy');

        // Work Orders (SPK Produksi)
        Route::get('work-orders', [ManufacturingController::class, 'workOrderIndex'])->name('work_orders.index');
        Route::get('work-orders/create', [ManufacturingController::class, 'workOrderCreate'])->name('work_orders.create');
        Route::post('work-orders', [ManufacturingController::class, 'workOrderStore'])->name('work_orders.store');
        Route::get('work-orders/{workOrder}', [ManufacturingController::class, 'workOrderShow'])->name('work_orders.show');
        Route::post('work-orders/{workOrder}/advance-step', [ManufacturingController::class, 'advanceStep'])->name('work_orders.advance_step');
        Route::post('work-orders/{workOrder}/complete', [ManufacturingController::class, 'completeWorkOrder'])->name('work_orders.complete');
    });

    // 6. Inventory & Stok
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('overview', [InventoryController::class, 'stockOverview'])->name('overview');
        Route::get('movements', [InventoryController::class, 'movements'])->name('movements');

        // Adjustments
        Route::get('adjustments', [InventoryController::class, 'adjustmentIndex'])->name('adjustments.index');
        Route::get('adjustments/create', [InventoryController::class, 'adjustmentCreate'])->name('adjustments.create');
        Route::post('adjustments', [InventoryController::class, 'adjustmentStore'])->name('adjustments.store');
        Route::get('adjustments/{adjustment}', [InventoryController::class, 'adjustmentShow'])->name('adjustments.show');

        // Transfers
        Route::get('transfers', [InventoryController::class, 'transferIndex'])->name('transfers.index');
        Route::get('transfers/create', [InventoryController::class, 'transferCreate'])->name('transfers.create');
        Route::post('transfers', [InventoryController::class, 'transferStore'])->name('transfers.store');
        Route::get('transfers/{transfer}', [InventoryController::class, 'transferShow'])->name('transfers.show');
    });

    // 7. Fixed Assets (Aset Tetap)
    Route::prefix('fixed-assets')->name('assets.')->group(function () {
        Route::get('/', [FixedAssetController::class, 'index'])->name('index');
        Route::get('create', [FixedAssetController::class, 'create'])->name('create');
        Route::post('/', [FixedAssetController::class, 'store'])->name('store');
        Route::get('depreciation', [FixedAssetController::class, 'depreciationForm'])->name('depreciation');
        Route::post('depreciation', [FixedAssetController::class, 'processDepreciation'])->name('depreciation.process');
        Route::get('{asset}', [FixedAssetController::class, 'show'])->name('show');
        Route::get('{asset}/edit', [FixedAssetController::class, 'edit'])->name('edit');
        Route::put('{asset}', [FixedAssetController::class, 'update'])->name('update');
        Route::delete('{asset}', [FixedAssetController::class, 'destroy'])->name('destroy');
    });

    // 8. Finance (Kas & Bank + Deposit)
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('index');
        Route::get('cash-in', [FinanceController::class, 'createCashIn'])->name('cash-in.create');
        Route::post('cash-in', [FinanceController::class, 'storeCashIn'])->name('cash-in.store');
        Route::get('cash-out', [FinanceController::class, 'createCashOut'])->name('cash-out.create');
        Route::post('cash-out', [FinanceController::class, 'storeCashOut'])->name('cash-out.store');
        Route::get('transfer', [FinanceController::class, 'createTransfer'])->name('transfer.create');
        Route::post('transfer', [FinanceController::class, 'storeTransfer'])->name('transfer.store');

        // Deposits Customer & Supplier (Separated)
        Route::get('deposits/customer', [DepositController::class, 'customerIndex'])->name('deposits.customer');
        Route::get('deposits/supplier', [DepositController::class, 'supplierIndex'])->name('deposits.supplier');
        Route::get('deposits/create', [DepositController::class, 'create'])->name('deposits.create');
        Route::get('deposits', [DepositController::class, 'index'])->name('deposits.index');
        Route::post('deposits', [DepositController::class, 'store'])->name('deposits.store');
        Route::delete('deposits/{deposit}', [DepositController::class, 'destroy'])->name('deposits.destroy');

        Route::get('{trx}', [FinanceController::class, 'show'])->name('show');
        Route::get('{trx}/print', [FinanceController::class, 'print'])->name('print');
    });

    // 9. Accounting & Financial Reports
    Route::prefix('accounting')->name('accounting.')->group(function () {
        // Jurnal Umum
        Route::get('journals', [AccountingReportController::class, 'journalsIndex'])->name('journals.index');
        Route::get('journals/create', [AccountingReportController::class, 'journalCreate'])->name('journals.create');
        Route::post('journals', [AccountingReportController::class, 'journalStore'])->name('journals.store');
        Route::get('journals/{journal}', [AccountingReportController::class, 'journalShow'])->name('journals.show');
        Route::get('journals/{journal}/print', [AccountingReportController::class, 'journalPrint'])->name('journals.print');

        // Reports
        Route::get('reports/general-ledger', [AccountingReportController::class, 'generalLedger'])->name('reports.general-ledger');
        Route::get('reports/trial-balance', [AccountingReportController::class, 'trialBalance'])->name('reports.trial-balance');
        Route::get('reports/profit-loss', [AccountingReportController::class, 'profitAndLoss'])->name('reports.profit-loss');
        Route::get('reports/balance-sheet', [AccountingReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
        Route::get('reports/ar-aging', [AccountingReportController::class, 'arAging'])->name('reports.ar-aging');
        Route::get('reports/ap-aging', [AccountingReportController::class, 'apAging'])->name('reports.ap-aging');

        // Saldo Awal Sistem (Beginning Balances)
        Route::prefix('beginning-balances')->name('beginning-balances.')->group(function () {
            Route::get('/', [BeginningBalanceController::class, 'index'])->name('index');
            Route::get('import', [BeginningBalanceImportController::class, 'index'])->name('import');
            Route::get('import/template/stock', [BeginningBalanceImportController::class, 'downloadStockTemplate'])->name('import.template.stock');
            Route::get('import/template/account', [BeginningBalanceImportController::class, 'downloadAccountTemplate'])->name('import.template.account');
            Route::post('import/preview', [BeginningBalanceImportController::class, 'preview'])->name('import.preview');
            Route::post('import/store', [BeginningBalanceImportController::class, 'store'])->name('import.store');
            Route::post('inventory', [BeginningBalanceController::class, 'storeInventory'])->name('store-inventory');
            Route::post('ar', [BeginningBalanceController::class, 'storeAr'])->name('store-ar');
            Route::post('ap', [BeginningBalanceController::class, 'storeAp'])->name('store-ap');
            Route::post('coa', [BeginningBalanceController::class, 'storeCoa'])->name('store-coa');
        });
    });

    // 10. System Maintenance (Backup, Restore & Reset Transaksi)
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', [MaintenanceController::class, 'index'])->name('index');
        Route::get('backup', [MaintenanceController::class, 'backup'])->name('backup');
        Route::post('import-sql', [MaintenanceController::class, 'importSql'])->name('import-sql');
        Route::post('reset-transactions', [MaintenanceController::class, 'resetTransactions'])->name('reset-transactions');
    });
});
