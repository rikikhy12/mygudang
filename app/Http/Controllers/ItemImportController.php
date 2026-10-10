<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\ChartOfAccount;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ItemImportController extends Controller
{
    /**
     * Show import form & upload UI.
     */
    public function index()
    {
        return view('items.import');
    }

    /**
     * Download CSV Template for Item Import.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_barang.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header columns
            fputcsv($handle, [
                'kode_barang',
                'barcode',
                'nama_barang',
                'kategori',
                'merk',
                'satuan_dasar',
                'tipe_barang',
                'harga_beli',
                'harga_jual',
                'bisa_ubah_harga',
                'limit_min_margin_persen',
                'min_stok',
                'deskripsi',
            ]);

            // Sample rows
            fputcsv($handle, [
                'BRG-001',
                '8991234567890',
                'Kopi Susu Aren Gula Asli 250ml',
                'Minuman',
                'Kopi Kenangan',
                'PCS',
                'finished_good',
                '12000',
                '18000',
                '0',
                '10',
                '20',
                'Minuman kopi siap saji botol',
            ]);

            fputcsv($handle, [
                'BRG-002',
                '8999876543210',
                'Roti Croissant Butter Premium',
                'Makanan',
                'Holland Bakery',
                'PCS',
                'finished_good',
                '8500',
                '14000',
                '1',
                '15',
                '15',
                'Pastry mentega renyah',
            ]);

            fputcsv($handle, [
                'JSA-001',
                '',
                'Jasa Konsultasi & Instalasi Mesin',
                'Jasa',
                '',
                'JAM',
                'service',
                '0',
                '150000',
                '0',
                '0',
                'Layanan jasa servis operasional',
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Parse uploaded file and show interactive preview table.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'], // Max 5MB
        ]);

        $file = $request->file('file');
        $filePath = $file->getRealPath();

        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Read first line (header)
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $headers = fgetcsv($handle, 2000, ',');
            if (! $headers || count($headers) < 3) {
                // Try semicolon delimiter
                rewind($handle);
                $headers = fgetcsv($handle, 2000, ';');
                $delimiter = ';';
            } else {
                $delimiter = ',';
            }

            // Clean headers
            $headers = array_map(function ($h) {
                return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h)));
            }, $headers);

            $line = 1;
            while (($data = fgetcsv($handle, 2000, $delimiter)) !== false) {
                $line++;
                if (empty(array_filter($data))) {
                    continue;
                }

                // Map columns
                $row = [
                    'code' => trim($data[0] ?? ''),
                    'barcode' => trim($data[1] ?? ''),
                    'name' => trim($data[2] ?? ''),
                    'category' => trim($data[3] ?? ''),
                    'brand' => trim($data[4] ?? ''),
                    'unit' => strtoupper(trim($data[5] ?? 'PCS')),
                    'item_type' => trim($data[6] ?? 'finished_good'),
                    'purchase_price' => (float) str_replace(['Rp', '.', ','], ['', '', '.'], trim($data[7] ?? '0')),
                    'selling_price' => (float) str_replace(['Rp', '.', ','], ['', '', '.'], trim($data[8] ?? '0')),
                    'allow_change_price' => ! empty($data[9]) && in_array(strtolower(trim($data[9])), ['1', 'ya', 'yes', 'true', 'bisa', 'y']),
                    'min_margin_percent' => ! empty($data[10]) ? (float) trim($data[10]) : null,
                    'min_stock' => (float) trim($data[11] ?? '0'),
                    'description' => trim($data[12] ?? ''),
                    'status' => 'valid',
                    'messages' => [],
                ];

                if (empty($row['code'])) {
                    $row['status'] = 'error';
                    $row['messages'][] = 'Kode barang wajib diisi.';
                }

                if (empty($row['name'])) {
                    $row['status'] = 'error';
                    $row['messages'][] = 'Nama barang wajib diisi.';
                }

                // Check SKU duplicate in DB
                $existingItem = Item::where('code', $row['code'])->first();
                if ($existingItem) {
                    $row['status'] = 'warning';
                    $row['messages'][] = 'Kode SKU sudah ada di database (Akan diperbarui).';
                }

                // Check Category
                if (! empty($row['category'])) {
                    $catExists = ItemCategory::where('name', $row['category'])->orWhere('code', $row['category'])->exists();
                    if (! $catExists) {
                        $row['messages'][] = 'Kategori baru (akan otomatis dibuat).';
                    }
                }

                // Check Brand
                if (! empty($row['brand'])) {
                    $brandExists = Brand::where('name', $row['brand'])->exists();
                    if (! $brandExists) {
                        $row['messages'][] = 'Merk baru (akan otomatis dibuat).';
                    }
                }

                $rows[] = $row;
            }

            fclose($handle);
        }

        if (empty($rows)) {
            return back()->with('error', 'File tidak memiliki baris data barang yang valid.');
        }

        session(['import_items_data' => $rows]);

        return view('items.import_preview', compact('rows'));
    }

    /**
     * Commit and store parsed items into database.
     */
    public function store(Request $request)
    {
        $rows = session('import_items_data');
        if (empty($rows)) {
            return redirect()->route('items.import')->with('error', 'Sesi data import kadaluarsa. Silakan upload ulang.');
        }

        $defaultInventoryAcc = ChartOfAccount::where('type', 'asset')->where('is_active', true)->value('id');
        $defaultCogsAcc = ChartOfAccount::where('type', 'cogs')->where('is_active', true)->value('id');
        $defaultSalesAcc = ChartOfAccount::where('type', 'revenue')->where('is_active', true)->value('id');
        $defaultPurchaseAcc = ChartOfAccount::whereIn('type', ['asset', 'liability'])->where('is_active', true)->value('id') ?? $defaultInventoryAcc;

        $createdCount = 0;
        $updatedCount = 0;

        DB::transaction(function () use ($rows, &$createdCount, &$updatedCount, $defaultInventoryAcc, $defaultCogsAcc, $defaultSalesAcc, $defaultPurchaseAcc) {
            foreach ($rows as $row) {
                if (empty($row['code']) || empty($row['name'])) {
                    continue;
                }

                // 1. Resolve or Create Category
                $categoryName = $row['category'] ?: 'Umum';
                $category = ItemCategory::firstOrCreate(
                    ['name' => $categoryName],
                    [
                        'code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $categoryName), 0, 4)).rand(10, 99),
                        'type' => in_array($row['item_type'], ['service', 'finished_good', 'manufactured_good']) ? $row['item_type'] : 'finished_good',
                        'inventory_account_id' => $defaultInventoryAcc,
                        'cogs_account_id' => $defaultCogsAcc,
                        'sales_account_id' => $defaultSalesAcc,
                        'purchase_account_id' => $defaultPurchaseAcc,
                    ]
                );

                // 2. Resolve or Create Brand
                $brandId = null;
                if (! empty($row['brand'])) {
                    $brand = Brand::firstOrCreate(
                        ['name' => $row['brand']],
                        ['is_active' => true]
                    );
                    $brandId = $brand->id;
                }

                // 3. Resolve or Create Unit
                $unitName = $row['unit'] ?: 'PCS';
                $unit = Unit::firstOrCreate(
                    ['name' => $unitName],
                    ['symbol' => strtolower($unitName)]
                );

                // 4. Create or Update Item
                $item = Item::updateOrCreate(
                    ['code' => $row['code']],
                    [
                        'barcode' => ! empty($row['barcode']) ? $row['barcode'] : null,
                        'name' => $row['name'],
                        'category_id' => $category->id,
                        'brand_id' => $brandId,
                        'unit_id' => $unit->id,
                        'item_type' => in_array($row['item_type'], ['finished_good', 'service', 'bundle', 'manufactured_good', 'raw_material', 'super_product']) ? $row['item_type'] : 'finished_good',
                        'purchase_price' => $row['purchase_price'],
                        'selling_price' => $row['selling_price'],
                        'allow_change_price' => (bool) ($row['allow_change_price'] ?? false),
                        'cost_price' => $row['purchase_price'],
                        'min_margin_percent' => $row['min_margin_percent'],
                        'min_stock' => $row['min_stock'] ?? 0,
                        'inventory_account_id' => $category->inventory_account_id ?? $defaultInventoryAcc,
                        'cogs_account_id' => $category->cogs_account_id ?? $defaultCogsAcc,
                        'sales_account_id' => $category->sales_account_id ?? $defaultSalesAcc,
                        'purchase_account_id' => $category->purchase_account_id ?? $defaultPurchaseAcc,
                        'description' => $row['description'] ?? null,
                        'is_active' => true,
                    ]
                );

                if ($item->wasRecentlyCreated) {
                    $createdCount++;
                } else {
                    $updatedCount++;
                }
            }
        });

        session()->forget('import_items_data');

        return redirect()->route('items.index')->with(
            'success',
            "Proses import selesai! {$createdCount} barang baru berhasil ditambahkan dan {$updatedCount} barang berhasil diperbarui."
        );
    }
}
