<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\ChartOfAccount;
use App\Models\Item;
use App\Models\ItemBranchPrice;
use App\Models\ItemCategory;
use App\Models\ItemUnitConversion;
use App\Models\ItemWarehousePrice;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $brandId = $request->query('brand_id');
        $type = $request->query('type') ?: $request->query('item_type');

        $query = Item::with(['category', 'unit', 'brand']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($brandId) {
            $query->where('brand_id', $brandId);
        }

        if ($type) {
            $query->where('item_type', $type);
        }

        $items = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = ItemCategory::with('parent')->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('items.index', compact('items', 'categories', 'brands', 'search', 'categoryId', 'brandId', 'type'));
    }

    public function create(Request $request)
    {
        $isPopup = $request->boolean('popup');
        $categories = ItemCategory::with('parent')->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $units = Unit::all();
        $branches = Branch::where('is_active', true)->orderByDesc('is_main')->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $inventoryAccounts = ChartOfAccount::where('type', 'asset')->where('is_active', true)->orderBy('code')->get();
        $cogsAccounts = ChartOfAccount::where('type', 'cogs')->where('is_active', true)->orderBy('code')->get();
        $salesAccounts = ChartOfAccount::where('type', 'revenue')->where('is_active', true)->orderBy('code')->get();
        $purchaseAccounts = ChartOfAccount::whereIn('type', ['asset', 'liability', 'expense'])->where('is_active', true)->orderBy('code')->get();

        return view('items.create', compact('categories', 'brands', 'units', 'branches', 'warehouses', 'inventoryAccounts', 'cogsAccounts', 'salesAccounts', 'purchaseAccounts', 'isPopup'));
    }

    public function edit(Item $item)
    {
        $item->load(['unitConversions.unit', 'branchPrices.branch', 'warehousePrices.warehouse']);
        $categories = ItemCategory::with('parent')->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $units = Unit::all();
        $branches = Branch::where('is_active', true)->orderByDesc('is_main')->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $inventoryAccounts = ChartOfAccount::where('type', 'asset')->where('is_active', true)->orderBy('code')->get();
        $cogsAccounts = ChartOfAccount::where('type', 'cogs')->where('is_active', true)->orderBy('code')->get();
        $salesAccounts = ChartOfAccount::where('type', 'revenue')->where('is_active', true)->orderBy('code')->get();
        $purchaseAccounts = ChartOfAccount::whereIn('type', ['asset', 'liability', 'expense'])->where('is_active', true)->orderBy('code')->get();

        return view('items.edit', compact('item', 'categories', 'brands', 'units', 'branches', 'warehouses', 'inventoryAccounts', 'cogsAccounts', 'salesAccounts', 'purchaseAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'unique:items,code'],
            'barcode' => ['nullable', 'string', 'unique:items,barcode'],
            'name' => ['required', 'string'],
            'category_id' => ['required', 'exists:item_categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg,gif', 'max:4096'],
            'item_type' => ['nullable', 'string', 'in:finished_good,service,bundle,manufactured_good,raw_material,super_product'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'allow_change_price' => ['nullable', 'boolean'],
            'min_margin_percent' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'inventory_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'cogs_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'sales_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'purchase_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'description' => ['nullable', 'string'],
            'conversions' => ['nullable', 'array'],
            'conversions.*.unit_id' => ['nullable', 'exists:units,id'],
            'conversions.*.conversion_factor' => ['nullable', 'numeric', 'min:0.0001'],
            'conversions.*.selling_price_override' => ['nullable', 'numeric', 'min:0'],
            'conversions.*.purchase_price_override' => ['nullable', 'numeric', 'min:0'],
            'warehouse_prices' => ['nullable', 'array'],
        ]);

        $category = ItemCategory::findOrFail($request->category_id);
        $itemType = $request->item_type ?: $category->type;

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            if (! file_exists(public_path('uploads/items'))) {
                @mkdir(public_path('uploads/items'), 0777, true);
            }
            $file->move(public_path('uploads/items'), $filename);
            $imagePath = 'items/'.$filename;

            // Also copy to storage/app/public/items
            if (! file_exists(storage_path('app/public/items'))) {
                @mkdir(storage_path('app/public/items'), 0777, true);
            }
            @copy(public_path('uploads/items/'.$filename), storage_path('app/public/items/'.$filename));
        }

        $createdItem = DB::transaction(function () use ($request, $itemType, $imagePath) {
            $item = Item::create([
                'code' => $request->code,
                'barcode' => $request->barcode,
                'name' => $request->name,
                'image' => $imagePath,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'unit_id' => $request->unit_id,
                'item_type' => $itemType,
                'purchase_price' => $request->purchase_price,
                'selling_price' => $request->selling_price,
                'allow_change_price' => $request->boolean('allow_change_price'),
                'min_margin_percent' => $request->filled('min_margin_percent') ? $request->min_margin_percent : null,
                'min_stock' => $request->min_stock ?? 0,
                'inventory_account_id' => $request->inventory_account_id,
                'cogs_account_id' => $request->cogs_account_id,
                'sales_account_id' => $request->sales_account_id,
                'purchase_account_id' => $request->purchase_account_id,
                'current_stock' => 0,
                'is_active' => true,
                'description' => $request->description,
            ]);

            // Save multi-unit conversions
            if ($request->filled('conversions')) {
                foreach ($request->conversions as $conv) {
                    if (! empty($conv['unit_id']) && ! empty($conv['conversion_factor'])) {
                        ItemUnitConversion::create([
                            'item_id' => $item->id,
                            'unit_id' => $conv['unit_id'],
                            'conversion_factor' => $conv['conversion_factor'],
                            'selling_price_override' => $conv['selling_price_override'] ?? null,
                            'purchase_price_override' => $conv['purchase_price_override'] ?? null,
                            'barcode' => $conv['barcode'] ?? null,
                        ]);
                    }
                }
            }

            // Save branch pricing & stock limits overrides
            if ($request->filled('branch_prices') || $request->has('branch_prices')) {
                foreach ($request->branch_prices as $branchId => $prices) {
                    $isAvailable = isset($prices['is_available']) && ($prices['is_available'] == '1' || $prices['is_available'] === true || $prices['is_available'] === 1);
                    ItemBranchPrice::create([
                        'item_id' => $item->id,
                        'branch_id' => $branchId,
                        'selling_price' => ! empty($prices['selling_price']) ? $prices['selling_price'] : null,
                        'purchase_price' => ! empty($prices['purchase_price']) ? $prices['purchase_price'] : null,
                        'min_margin_percent' => ! empty($prices['min_margin_percent']) ? $prices['min_margin_percent'] : null,
                        'min_stock' => (isset($prices['min_stock']) && $prices['min_stock'] !== '') ? $prices['min_stock'] : null,
                        'is_available' => $isAvailable,
                    ]);
                }
            }

            // Save warehouse pricing overrides (backward compatibility)
            if ($request->filled('warehouse_prices')) {
                foreach ($request->warehouse_prices as $whId => $prices) {
                    if (! empty($prices['selling_price']) || ! empty($prices['purchase_price'])) {
                        ItemWarehousePrice::create([
                            'item_id' => $item->id,
                            'warehouse_id' => $whId,
                            'selling_price' => $prices['selling_price'] ?? null,
                            'purchase_price' => $prices['purchase_price'] ?? null,
                            'min_stock' => $prices['min_stock'] ?? null,
                            'is_available' => isset($prices['is_available']),
                        ]);
                    }
                }
            }

            return $item;
        });

        if ($request->boolean('popup')) {
            $createdItem->load('unit');

            return view('partials.window_opener_callback', [
                'title' => 'Master Barang "'.$createdItem->name.'" Berhasil Disimpan',
                'payload' => [
                    'type' => 'item_created',
                    'item' => [
                        'id' => $createdItem->id,
                        'code' => $createdItem->code,
                        'name' => $createdItem->name,
                        'unit_id' => $createdItem->unit_id,
                        'unit_name' => $createdItem->unit?->name ?? 'Pcs',
                        'unit_symbol' => $createdItem->unit?->symbol ?? 'pcs',
                        'purchase_price' => (float) $createdItem->purchase_price,
                        'selling_price' => (float) $createdItem->selling_price,
                        'allow_change_price' => (bool) $createdItem->allow_change_price,
                        'item_type' => $createdItem->item_type,
                        'label' => "{$createdItem->code} - {$createdItem->name} (".($createdItem->unit?->symbol ?? 'pcs').')',
                    ],
                ],
                'redirectUrl' => route('items.index'),
            ]);
        }

        return redirect()->route('items.index')->with('success', 'Barang baru berhasil ditambahkan.');
    }

    public function update(Request $request, Item $item)
    {
        $request->validate([
            'code' => ['required', 'string', Rule::unique('items')->ignore($item->id)],
            'barcode' => ['nullable', 'string', Rule::unique('items')->ignore($item->id)],
            'name' => ['required', 'string'],
            'category_id' => ['required', 'exists:item_categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg,gif', 'max:4096'],
            'item_type' => ['nullable', 'string', 'in:finished_good,service,bundle,manufactured_good,raw_material,super_product'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'allow_change_price' => ['nullable', 'boolean'],
            'min_margin_percent' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'inventory_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'cogs_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'sales_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'purchase_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'conversions' => ['nullable', 'array'],
            'warehouse_prices' => ['nullable', 'array'],
        ]);

        $category = ItemCategory::findOrFail($request->category_id);
        $itemType = $request->item_type ?: $category->type;

        $updateData = [
            'code' => $request->code,
            'barcode' => $request->barcode,
            'name' => $request->name,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'unit_id' => $request->unit_id,
            'item_type' => $itemType,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'allow_change_price' => $request->boolean('allow_change_price'),
            'min_margin_percent' => $request->filled('min_margin_percent') ? $request->min_margin_percent : null,
            'min_stock' => $request->min_stock ?? 0,
            'inventory_account_id' => $request->inventory_account_id,
            'cogs_account_id' => $request->cogs_account_id,
            'sales_account_id' => $request->sales_account_id,
            'purchase_account_id' => $request->purchase_account_id,
            'is_active' => $request->boolean('is_active', true),
            'description' => $request->description,
        ];

        if ($request->hasFile('image')) {
            if ($item->image) {
                @unlink(public_path('uploads/'.$item->image));
                @unlink(public_path('storage/'.$item->image));
                @unlink(storage_path('app/public/'.$item->image));
            }
            $file = $request->file('image');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            if (! file_exists(public_path('uploads/items'))) {
                @mkdir(public_path('uploads/items'), 0777, true);
            }
            $file->move(public_path('uploads/items'), $filename);
            $updateData['image'] = 'items/'.$filename;

            // Also copy to storage/app/public/items
            if (! file_exists(storage_path('app/public/items'))) {
                @mkdir(storage_path('app/public/items'), 0777, true);
            }
            @copy(public_path('uploads/items/'.$filename), storage_path('app/public/items/'.$filename));
        } elseif ($request->boolean('remove_image')) {
            if ($item->image) {
                @unlink(public_path('uploads/'.$item->image));
                @unlink(public_path('storage/'.$item->image));
                @unlink(storage_path('app/public/'.$item->image));
            }
            $updateData['image'] = null;
        }

        DB::transaction(function () use ($request, $item, $updateData) {
            $item->update($updateData);

            // Sync unit conversions
            $item->unitConversions()->delete();
            if ($request->filled('conversions')) {
                foreach ($request->conversions as $conv) {
                    if (! empty($conv['unit_id']) && ! empty($conv['conversion_factor'])) {
                        ItemUnitConversion::create([
                            'item_id' => $item->id,
                            'unit_id' => $conv['unit_id'],
                            'conversion_factor' => $conv['conversion_factor'],
                            'selling_price_override' => $conv['selling_price_override'] ?? null,
                            'purchase_price_override' => $conv['purchase_price_override'] ?? null,
                            'barcode' => $conv['barcode'] ?? null,
                        ]);
                    }
                }
            }

            // Sync branch pricing & stock limits overrides
            $item->branchPrices()->delete();
            if ($request->filled('branch_prices') || $request->has('branch_prices')) {
                foreach ($request->branch_prices as $branchId => $prices) {
                    $isAvailable = isset($prices['is_available']) && ($prices['is_available'] == '1' || $prices['is_available'] === true || $prices['is_available'] === 1);
                    ItemBranchPrice::create([
                        'item_id' => $item->id,
                        'branch_id' => $branchId,
                        'selling_price' => ! empty($prices['selling_price']) ? $prices['selling_price'] : null,
                        'purchase_price' => ! empty($prices['purchase_price']) ? $prices['purchase_price'] : null,
                        'min_margin_percent' => ! empty($prices['min_margin_percent']) ? $prices['min_margin_percent'] : null,
                        'min_stock' => (isset($prices['min_stock']) && $prices['min_stock'] !== '') ? $prices['min_stock'] : null,
                        'is_available' => $isAvailable,
                    ]);
                }
            }

            // Sync warehouse prices (backward compatibility)
            $item->warehousePrices()->delete();
            if ($request->filled('warehouse_prices')) {
                foreach ($request->warehouse_prices as $whId => $prices) {
                    if (! empty($prices['selling_price']) || ! empty($prices['purchase_price'])) {
                        ItemWarehousePrice::create([
                            'item_id' => $item->id,
                            'warehouse_id' => $whId,
                            'selling_price' => $prices['selling_price'] ?? null,
                            'purchase_price' => $prices['purchase_price'] ?? null,
                            'min_stock' => $prices['min_stock'] ?? null,
                            'is_available' => isset($prices['is_available']),
                        ]);
                    }
                }
            }
        });

        return redirect()->route('items.index')->with('success', 'Data barang berhasil diperbarui.');
    }

    public function stockCard(Item $item)
    {
        $ledgers = $item->stockLedgers()->with('warehouse')->latest('transaction_date')->latest('id')->paginate(30);

        return view('items.stock_card', compact('item', 'ledgers'));
    }

    public function destroy(Item $item)
    {
        if ($item->stockLedgers()->exists()) {
            return back()->with('error', 'Barang ini telah memiliki riwayat mutasi stok dan tidak dapat dihapus.');
        }

        $item->delete();

        return redirect()->route('items.index')->with('success', 'Barang berhasil dihapus.');
    }

    /**
     * AJAX quick get categories and units for modal
     */
    public function quickFormData()
    {
        $categories = ItemCategory::orderBy('name')->get(['id', 'name']);
        $units = Unit::orderBy('name')->get(['id', 'name', 'symbol']);

        return response()->json([
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    /**
     * AJAX quick store for modal popup in transaction forms
     */
    public function quickStore(Request $request)
    {
        $user = auth()->user();
        if (! $user->hasRole('admin') && ! $user->hasPermission('create_master_items')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk membuat data Master Barang.',
            ], 403);
        }

        $code = $request->input('code');
        if (empty($code)) {
            $code = 'ITM-'.date('ymd').'-'.strtoupper(bin2hex(random_bytes(2)));
        }

        $request->merge(['code' => $code]);

        $validated = $request->validate([
            'code' => ['required', 'string', 'unique:items,code'],
            'barcode' => ['nullable', 'string', 'unique:items,barcode'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:item_categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'item_type' => ['required', 'in:inventory,service,bundle,raw_material,manufactured_good,super_product'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'allow_change_price' => ['nullable', 'boolean'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
        ]);

        $category = ItemCategory::find($validated['category_id']);

        // Resolve default GL accounts from category or first available active account
        $inventoryAccId = $category?->inventory_account_id ?? ChartOfAccount::where('type', 'asset')->where('is_active', true)->value('id');
        $cogsAccId = $category?->cogs_account_id ?? ChartOfAccount::where('type', 'cogs')->where('is_active', true)->value('id');
        $salesAccId = $category?->sales_account_id ?? ChartOfAccount::where('type', 'revenue')->where('is_active', true)->value('id');
        $purchaseAccId = $category?->purchase_account_id ?? $inventoryAccId;

        $item = Item::create([
            'code' => $validated['code'],
            'barcode' => $validated['barcode'] ?? null,
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'unit_id' => $validated['unit_id'],
            'item_type' => $validated['item_type'],
            'purchase_price' => $validated['purchase_price'],
            'selling_price' => $validated['selling_price'],
            'allow_change_price' => $request->boolean('allow_change_price'),
            'cost_price' => $validated['purchase_price'],
            'min_stock' => $validated['min_stock'] ?? 0,
            'inventory_account_id' => $inventoryAccId,
            'cogs_account_id' => $cogsAccId,
            'sales_account_id' => $salesAccId,
            'purchase_account_id' => $purchaseAccId,
            'is_active' => true,
        ]);

        $item->load('unit');

        return response()->json([
            'success' => true,
            'message' => 'Barang baru "'.$item->name.'" berhasil ditambahkan.',
            'item' => [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'unit_id' => $item->unit_id,
                'unit_name' => $item->unit?->name ?? 'Pcs',
                'unit_symbol' => $item->unit?->symbol ?? 'pcs',
                'purchase_price' => (float) $item->purchase_price,
                'selling_price' => (float) $item->selling_price,
                'allow_change_price' => (bool) $item->allow_change_price,
                'item_type' => $item->item_type,
                'label' => "{$item->code} - {$item->name} (".($item->unit?->symbol ?? 'pcs').')',
            ],
        ]);
    }

    public function duplicate(Item $item)
    {
        $newCode = $item->code.'-COPY-'.strtoupper(substr(uniqid(), -3));
        $newItem = $item->replicate([
            'current_stock',
        ]);
        $newItem->code = $newCode;
        $newItem->barcode = null;
        $newItem->name = $item->name.' (Salinan)';
        $newItem->save();

        // Copy unit conversions
        foreach ($item->unitConversions as $conv) {
            $newItem->unitConversions()->create($conv->toArray());
        }

        // Copy warehouse prices
        foreach ($item->warehousePrices as $wp) {
            $newItem->warehousePrices()->create($wp->toArray());
        }

        return redirect()->route('items.edit', $newItem->id)->with('success', 'Barang berhasil disalin. Silakan sesuaikan kode dan data jika diperlukan.');
    }
}
