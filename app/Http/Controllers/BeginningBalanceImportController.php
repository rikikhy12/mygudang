<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Item;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BeginningBalanceImportController extends Controller
{
    /**
     * Show import form for beginning balances.
     */
    public function index()
    {
        return view('accounting.beginning_balances.import');
    }

    /**
     * Download Stock Opening Balance Template.
     */
    public function downloadStockTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_saldo_awal_stok.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'kode_barang',
                'kode_gudang',
                'jumlah_stok',
                'harga_pokok_satuan',
                'tanggal_saldo'
            ]);

            // Sample rows
            $sampleItem = Item::first();
            $sampleWh = Warehouse::first();

            fputcsv($handle, [
                $sampleItem ? $sampleItem->code : 'BRG-001',
                $sampleWh ? $sampleWh->code : 'GDG-01',
                '100',
                $sampleItem ? $sampleItem->purchase_price : '15000',
                date('Y-01-01')
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Download Account (COA) Opening Balance Template.
     */
    public function downloadAccountTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_saldo_awal_akun.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'kode_akun',
                'debit',
                'kredit',
                'tanggal_saldo'
            ]);

            // Populate all active COA accounts as pre-filled template
            $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();
            foreach ($accounts as $acc) {
                fputcsv($handle, [
                    $acc->code,
                    '0',
                    '0',
                    date('Y-01-01')
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Preview uploaded file.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:stock,account'],
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $type = $request->input('type');
        $file = $request->file('file');
        $filePath = $file->getRealPath();

        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;
        $totalValuation = 0;

        if (($handle = fopen($filePath, 'r')) !== false) {
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $headers = fgetcsv($handle, 2000, ',');
            if (!$headers || count($headers) < 2) {
                rewind($handle);
                $headers = fgetcsv($handle, 2000, ';');
                $delimiter = ';';
            } else {
                $delimiter = ',';
            }

            while (($data = fgetcsv($handle, 2000, $delimiter)) !== false) {
                if (empty(array_filter($data))) {
                    continue;
                }

                if ($type === 'stock') {
                    $itemCode = trim($data[0] ?? '');
                    $whCode = trim($data[1] ?? '');
                    $qty = (float) str_replace(['Rp', '.', ','], ['', '', '.'], trim($data[2] ?? '0'));
                    $cost = (float) str_replace(['Rp', '.', ','], ['', '', '.'], trim($data[3] ?? '0'));
                    $date = trim($data[4] ?? date('Y-m-d'));

                    $item = Item::where('code', $itemCode)->first();
                    $wh = Warehouse::where('code', $whCode)->orWhere('name', $whCode)->first();

                    $subtotal = $qty * $cost;
                    $totalValuation += $subtotal;

                    $status = 'valid';
                    $messages = [];

                    if (!$item) {
                        $status = 'error';
                        $messages[] = 'Kode barang tidak ditemukan di master.';
                    }
                    if (!$wh) {
                        $status = 'error';
                        $messages[] = 'Kode gudang tidak ditemukan di master.';
                    }

                    $rows[] = [
                        'item_code' => $itemCode,
                        'item_name' => $item ? $item->name : 'N/A',
                        'warehouse_code' => $whCode,
                        'warehouse_name' => $wh ? $wh->name : 'N/A',
                        'quantity' => $qty,
                        'cost' => $cost,
                        'subtotal' => $subtotal,
                        'date' => $date,
                        'status' => $status,
                        'messages' => $messages,
                        'item_id' => $item ? $item->id : null,
                        'warehouse_id' => $wh ? $wh->id : null,
                    ];
                } else {
                    $accCode = trim($data[0] ?? '');
                    $debit = (float) str_replace(['Rp', '.', ','], ['', '', '.'], trim($data[1] ?? '0'));
                    $credit = (float) str_replace(['Rp', '.', ','], ['', '', '.'], trim($data[2] ?? '0'));
                    $date = trim($data[3] ?? date('Y-m-d'));

                    $acc = ChartOfAccount::where('code', $accCode)->first();

                    $totalDebit += $debit;
                    $totalCredit += $credit;

                    $status = 'valid';
                    $messages = [];

                    if (!$acc) {
                        $status = 'error';
                        $messages[] = 'Kode akun tidak terdaftar di COA.';
                    }

                    $rows[] = [
                        'account_code' => $accCode,
                        'account_name' => $acc ? $acc->name : 'N/A',
                        'account_type' => $acc ? $acc->type : 'N/A',
                        'debit' => $debit,
                        'credit' => $credit,
                        'date' => $date,
                        'status' => $status,
                        'messages' => $messages,
                        'account_id' => $acc ? $acc->id : null,
                    ];
                }
            }

            fclose($handle);
        }

        if (empty($rows)) {
            return back()->with('error', 'File tidak berisi data saldo awal yang valid.');
        }

        session([
            'import_balance_type' => $type,
            'import_balance_rows' => $rows,
        ]);

        return view('accounting.beginning_balances.import_preview', compact(
            'type',
            'rows',
            'totalDebit',
            'totalCredit',
            'totalValuation'
        ));
    }

    /**
     * Store and commit beginning balances into database.
     */
    public function store(Request $request, InventoryService $inventoryService, JournalService $journalService)
    {
        $type = session('import_balance_type');
        $rows = session('import_balance_rows');

        if (empty($rows) || empty($type)) {
            return redirect()->route('accounting.beginning-balances.import')->with('error', 'Sesi import telah kadaluarsa. Silakan upload ulang.');
        }

        DB::transaction(function () use ($type, $rows, $inventoryService, $journalService) {
            if ($type === 'stock') {
                $defaultInvAcc = ChartOfAccount::where('code', '1201')->first() ?: ChartOfAccount::where('type', 'asset')->first();
                $equityAcc = ChartOfAccount::where('code', '3001')->first() ?: ChartOfAccount::where('type', 'equity')->first();

                $totalCostAll = 0;
                $inventoryDebits = [];

                foreach ($rows as $r) {
                    if ($r['status'] === 'error' || $r['quantity'] <= 0) {
                        continue;
                    }

                    $item = Item::find($r['item_id']);
                    $wh = Warehouse::find($r['warehouse_id']);

                    if (!$item || !$wh) {
                        continue;
                    }

                    $lineTotal = round($r['quantity'] * $r['cost'], 2);
                    $totalCostAll += $lineTotal;

                    // Update item price
                    $item->update(['purchase_price' => $r['cost']]);

                    // Record stock movement
                    $inventoryService->recordStockMovement(
                        $item->id,
                        $wh->id,
                        $r['quantity'],
                        'in',
                        'beginning_balance',
                        null,
                        $r['cost'],
                        'Saldo Awal Stok Gudang ' . $wh->name . ' (Import Massal)'
                    );

                    $accId = $item->inventory_account_id ?: ($defaultInvAcc ? $defaultInvAcc->id : null);
                    if ($accId) {
                        $inventoryDebits[$accId] = ($inventoryDebits[$accId] ?? 0) + $lineTotal;
                    }
                }

                // Create balancing journal entry
                if ($totalCostAll > 0 && $equityAcc) {
                    $journalLines = [];
                    foreach ($inventoryDebits as $accId => $amount) {
                        $journalLines[] = [
                            'account_id' => $accId,
                            'debit' => $amount,
                            'credit' => 0,
                            'memo' => 'Saldo Awal Persediaan Barang (Import)',
                        ];
                    }
                    $journalLines[] = [
                        'account_id' => $equityAcc->id,
                        'debit' => 0,
                        'credit' => $totalCostAll,
                        'memo' => 'Modal Saldo Awal Persediaan (Import)',
                    ];

                    $journalService->createJournalEntry(
                        date('Y-m-d'),
                        'Saldo Awal Persediaan Barang (Import Massal)',
                        'beginning_balance',
                        null,
                        $journalLines
                    );
                }
            } else {
                // Account (COA) Balances
                $journalLines = [];
                $totalDebit = 0;
                $totalCredit = 0;

                foreach ($rows as $r) {
                    if ($r['status'] === 'error') {
                        continue;
                    }

                    if ($r['debit'] > 0 || $r['credit'] > 0) {
                        $journalLines[] = [
                            'account_id' => $r['account_id'],
                            'debit' => $r['debit'],
                            'credit' => $r['credit'],
                            'memo' => 'Saldo Awal Akun ' . $r['account_code'] . ' (Import)',
                        ];
                        $totalDebit += $r['debit'];
                        $totalCredit += $r['credit'];
                    }
                }

                if (count($journalLines) > 0) {
                    // Balancing difference to Modal Saldo Awal (3001) if unbalanced
                    $diff = round($totalDebit - $totalCredit, 2);
                    if ($diff != 0) {
                        $equityAcc = ChartOfAccount::where('code', '3001')->first() ?: ChartOfAccount::where('type', 'equity')->first();
                        if ($equityAcc) {
                            if ($diff > 0) {
                                $journalLines[] = [
                                    'account_id' => $equityAcc->id,
                                    'debit' => 0,
                                    'credit' => $diff,
                                    'memo' => 'Penyeimbang Saldo Awal (Modal 3001)',
                                ];
                            } else {
                                $journalLines[] = [
                                    'account_id' => $equityAcc->id,
                                    'debit' => abs($diff),
                                    'credit' => 0,
                                    'memo' => 'Penyeimbang Saldo Awal (Modal 3001)',
                                ];
                            }
                        }
                    }

                    $journalService->createJournalEntry(
                        date('Y-m-d'),
                        'Posting Saldo Awal Neraca Akun COA (Import Massal)',
                        'beginning_balance',
                        null,
                        $journalLines
                    );
                }
            }
        });

        session()->forget(['import_balance_type', 'import_balance_rows']);

        return redirect()->route('accounting.beginning-balances.index')->with(
            'success',
            'Saldo awal berhasil di-import dan diposting ke kartu stok & buku besar akuntansi!'
        );
    }
}
