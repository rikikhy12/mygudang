<?php

namespace App\Http\Controllers;

use App\Models\AssetDepreciation;
use App\Models\CashTransaction;
use App\Models\ChartOfAccount;
use App\Models\Contact;
use App\Models\DeliveryOrder;
use App\Models\FixedAsset;
use App\Models\GoodsReceipt;
use App\Models\Item;
use App\Models\Journal;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\StockAdjustment;
use App\Models\StockLedger;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaintenanceController extends Controller
{
    /**
     * Display the System Maintenance & Backup / Reset Dashboard.
     */
    public function index()
    {
        // Master Data Counts (Preserved)
        $masterCounts = [
            'users' => User::count(),
            'accounts' => ChartOfAccount::count(),
            'items' => Item::count(),
            'contacts' => Contact::count(),
            'warehouses' => Warehouse::count(),
            'fixed_assets' => FixedAsset::count(),
        ];

        // Transaction Counts (Will be reset)
        $transactionCounts = [
            'purchase_requisitions' => PurchaseRequisition::count(),
            'purchase_orders' => PurchaseOrder::count(),
            'goods_receipts' => GoodsReceipt::count(),
            'purchase_invoices' => PurchaseInvoice::count(),
            'sales_orders' => SalesOrder::count(),
            'delivery_orders' => DeliveryOrder::count(),
            'sales_invoices' => SalesInvoice::count(),
            'stock_ledgers' => StockLedger::count(),
            'stock_adjustments' => StockAdjustment::count(),
            'stock_transfers' => StockTransfer::count(),
            'cash_transactions' => CashTransaction::count(),
            'journals' => Journal::count(),
            'asset_depreciations' => AssetDepreciation::count(),
        ];

        $totalTransactions = array_sum($transactionCounts);

        $dbConnection = config('database.default');
        $dbName = config("database.connections.{$dbConnection}.database");

        return view('maintenance.index', compact(
            'masterCounts',
            'transactionCounts',
            'totalTransactions',
            'dbConnection',
            'dbName'
        ));
    }

    /**
     * Export & Download Full Database Backup.
     */
    public function backup(Request $request)
    {
        $dbConnection = config('database.default');
        $timestamp = date('Y-m-d_His');
        $format = $request->query('format', 'sql');

        if ($dbConnection === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');

            if ($format === 'db' && file_exists($dbPath)) {
                $filename = "mycrud_erp_backup_{$timestamp}.sqlite";

                return response()->download($dbPath, $filename, [
                    'Content-Type' => 'application/x-sqlite3',
                ]);
            }
        }

        // Generate standard SQL dump
        $filename = "mycrud_erp_database_backup_{$timestamp}.sql";

        return new StreamedResponse(function () use ($dbConnection) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "-- ========================================================\n");
            fwrite($handle, "-- MYCRUD ERP DATABASE BACKUP\n");
            fwrite($handle, '-- Generated: '.date('Y-m-d H:i:s')."\n");
            fwrite($handle, '-- Driver: '.$dbConnection."\n");
            fwrite($handle, "-- ========================================================\n\n");

            if ($dbConnection === 'sqlite') {
                fwrite($handle, "PRAGMA foreign_keys = OFF;\n\n");
            } else {
                fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");
            }

            // Get list of tables
            $tables = $this->getAllTableNames($dbConnection);

            foreach ($tables as $table) {
                if (in_array($table, ['migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'failed_jobs'])) {
                    continue;
                }

                fwrite($handle, "-- --------------------------------------------------------\n");
                fwrite($handle, "-- Table data for `{$table}`\n");
                fwrite($handle, "-- --------------------------------------------------------\n");

                $rows = DB::table($table)->get();

                if ($rows->count() > 0) {
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        $columns = array_keys($rowArray);
                        $escapedValues = array_map(function ($value) {
                            if (is_null($value)) {
                                return 'NULL';
                            }

                            return "'".addslashes((string) $value)."'";
                        }, array_values($rowArray));

                        $colList = implode(', ', array_map(fn ($c) => "`{$c}`", $columns));
                        $valList = implode(', ', $escapedValues);

                        fwrite($handle, "INSERT INTO `{$table}` ({$colList}) VALUES ({$valList});\n");
                    }
                    fwrite($handle, "\n");
                }
            }

            if ($dbConnection === 'sqlite') {
                fwrite($handle, "PRAGMA foreign_keys = ON;\n");
            } else {
                fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Reset only Transaction Data while strictly preserving all Master Data.
     */
    public function resetTransactions(Request $request)
    {
        $request->validate([
            'confirmation' => ['required', 'string'],
        ]);

        if (strtoupper(trim($request->confirmation)) !== 'RESET TRANSAKSI') {
            return back()->with('error', 'Konfirmasi gagal. Silakan ketik kata kunci "RESET TRANSAKSI" dengan benar.');
        }

        $dbDriver = config('database.default');

        try {
            DB::beginTransaction();

            // Disable foreign key constraints during wipe
            if ($dbDriver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            // 1. Reset Procurement Transactions
            DB::table('purchase_invoice_items')->delete();
            DB::table('purchase_invoices')->delete();
            DB::table('goods_receipt_items')->delete();
            DB::table('goods_receipts')->delete();
            DB::table('purchase_order_items')->delete();
            DB::table('purchase_orders')->delete();
            DB::table('purchase_requisition_items')->delete();
            DB::table('purchase_requisitions')->delete();

            // 2. Reset Sales & POS Transactions
            DB::table('sales_invoice_items')->delete();
            DB::table('sales_invoices')->delete();
            DB::table('delivery_order_items')->delete();
            DB::table('delivery_orders')->delete();
            DB::table('sales_order_items')->delete();
            DB::table('sales_orders')->delete();

            // 3. Reset Inventory & Stock Transactions
            DB::table('stock_transfer_items')->delete();
            DB::table('stock_transfers')->delete();
            DB::table('stock_adjustment_items')->delete();
            DB::table('stock_adjustments')->delete();
            DB::table('stock_ledgers')->delete();

            // 4. Reset Finance & Cash/Bank Transactions & Deposits
            if (DB::getSchemaBuilder()->hasTable('customer_deposits')) {
                DB::table('customer_deposits')->delete();
            }
            DB::table('cash_transaction_items')->delete();
            DB::table('cash_transactions')->delete();

            // 5. Reset Manufacturing & BOM Data
            if (DB::getSchemaBuilder()->hasTable('work_order_stages')) {
                DB::table('work_order_stages')->delete();
            }
            if (DB::getSchemaBuilder()->hasTable('work_order_items')) {
                DB::table('work_order_items')->delete();
            }
            if (DB::getSchemaBuilder()->hasTable('work_orders')) {
                DB::table('work_orders')->delete();
            }
            if (DB::getSchemaBuilder()->hasTable('routing_steps')) {
                DB::table('routing_steps')->delete();
            }
            if (DB::getSchemaBuilder()->hasTable('routings')) {
                DB::table('routings')->delete();
            }
            if (DB::getSchemaBuilder()->hasTable('bill_of_material_items')) {
                DB::table('bill_of_material_items')->delete();
            }
            if (DB::getSchemaBuilder()->hasTable('bill_of_materials')) {
                DB::table('bill_of_materials')->delete();
            }

            // 6. Reset Fixed Asset Depreciations
            DB::table('asset_depreciations')->delete();

            // 7. Reset Accounting Journals & Ledger Entries
            DB::table('journal_entries')->delete();
            DB::table('journals')->delete();

            // 8. Reset Item stock balance back to 0
            DB::table('items')->update(['current_stock' => 0]);

            // 9. Reset Fixed Asset accumulation balance
            if (DB::getSchemaBuilder()->hasTable('fixed_assets')) {
                DB::table('fixed_assets')->update([
                    'accumulated_depreciation' => 0,
                    'current_book_value' => DB::raw('acquisition_cost'),
                    'journal_id' => null,
                ]);
            }

            // Re-enable foreign key constraints
            if ($dbDriver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            DB::commit();

            return redirect()->route('maintenance.index')->with(
                'success',
                'Semua data transaksi (Penjualan, Pembelian, POS, Stok, Kas/Bank, Jurnal, dan BOM/Manufaktur) berhasil di-reset. Seluruh Master Data (COA, Barang, Merk, Kontak, Gudang, Cabang, User) tetap aman dan utuh!'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($dbDriver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            return back()->with('error', 'Gagal mereset data transaksi: '.$e->getMessage());
        }
    }

    /**
     * Import & Execute SQL Backup file.
     */
    public function importSql(Request $request)
    {
        $request->validate([
            'sql_file' => ['required', 'file', 'max:51200'], // max 50MB
        ]);

        $file = $request->file('sql_file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['sql', 'txt'])) {
            return back()->with('error', 'Format file tidak didukung. Harap unggah file dengan ekstensi .sql');
        }

        $sqlContent = file_get_contents($file->getRealPath());
        if (empty(trim($sqlContent))) {
            return back()->with('error', 'File SQL yang diunggah kosong.');
        }

        $dbDriver = config('database.default');

        try {
            DB::beginTransaction();

            if ($dbDriver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            // Remove SQL comments and split statements
            $lines = explode("\n", $sqlContent);
            $cleanSql = '';
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (empty($trimmed) || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#') || str_starts_with($trimmed, '/*')) {
                    continue;
                }
                $cleanSql .= $line . "\n";
            }

            // Execute raw statement or split by semicolon
            $statements = array_filter(array_map('trim', explode(';', $cleanSql)));
            $executedCount = 0;

            foreach ($statements as $stmt) {
                if (! empty($stmt)) {
                    DB::unprepared($stmt . ';');
                    $executedCount++;
                }
            }

            if ($dbDriver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            DB::commit();

            return redirect()->route('maintenance.index')->with(
                'success',
                "Database SQL berhasil diimport! Sebanyak {$executedCount} query SQL telah sukses dieksekusi ke dalam database."
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($dbDriver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            return back()->with('error', 'Gagal mengimport file SQL: ' . $e->getMessage());
        }
    }

    /**
     * Helper to get all tables.
     */
    protected function getAllTableNames(string $driver): array
    {
        if ($driver === 'sqlite') {
            $results = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';");

            return array_map(fn ($r) => $r->name, $results);
        }

        $tables = DB::select('SHOW TABLES');
        $key = 'Tables_in_'.config('database.connections.mysql.database');

        return array_map(fn ($t) => $t->$key ?? array_values((array) $t)[0], $tables);
    }
}
