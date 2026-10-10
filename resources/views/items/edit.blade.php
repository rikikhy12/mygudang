@extends('layouts.app')

@section('title', 'Edit Barang: ' . $item->name)
@section('page_title', 'Perbarui Master Barang & Jasa: ' . $item->name)

@section('content')
<div class="d-flex flex-column gap-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Edit Master Barang / Jasa</h2>
            <p class="text-muted fs-7 mb-0">Perbarui spesifikasi, tipe barang, multi-satuan, harga cabang, dan pemetaan akun GL</p>
        </div>
        <a href="{{ route('items.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog Barang
        </a>
    </div>

    <div class="card card-flush shadow-sm">
        <form method="POST" action="{{ route('items.update', $item->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="remove_image" id="removeImageFlag" value="0" />
            
            <div class="card-header pt-4">
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-6 fw-bold">
                    <li class="nav-item">
                        <a class="nav-link text-active-primary active py-3" data-bs-toggle="tab" href="#tab_item_info">
                            <i class="bi bi-box-seam me-2"></i> Informasi Produk & Persediaan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary py-3" data-bs-toggle="tab" href="#tab_item_units">
                            <i class="bi bi-layers me-2"></i> Konversi Multi-Satuan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary py-3" data-bs-toggle="tab" href="#tab_item_branch">
                            <i class="bi bi-shop me-2"></i> Limit & Harga Khusus per Cabang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary py-3" data-bs-toggle="tab" href="#tab_item_gl">
                            <i class="bi bi-diagram-3 me-2"></i> Pengaturan Akun GL
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body pt-4">
                <div class="tab-content" id="itemTabContent">
                    <!-- Tab 1: Informasi Produk -->
                    <div class="tab-pane fade show active" id="tab_item_info" role="tabpanel">
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fs-7 fw-semibold text-gray-700 required">Kode SKU / Item</label>
                                <input type="text" name="code" class="form-control form-control-solid form-control-sm" value="{{ old('code', $item->code) }}" required placeholder="Kode SKU..." />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-7 fw-semibold text-gray-700">Barcode Scanner</label>
                                <input type="text" name="barcode" class="form-control form-control-solid form-control-sm" value="{{ old('barcode', $item->barcode) }}" placeholder="Barcode scanner..." />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-7 fw-semibold text-gray-700 required">Tipe Spesifikasi Barang</label>
                                <select name="item_type" id="itemTypeSelect" class="form-select form-select-solid form-select-sm" data-control="select2" data-hide-search="true" required onchange="handleItemTypeChange()">
                                    <option value="finished_good" {{ old('item_type', $item->item_type) == 'finished_good' ? 'selected' : '' }}>Barang Jadi Standar (Siap Jual)</option>
                                    <option value="bundle" {{ old('item_type', $item->item_type) == 'bundle' ? 'selected' : '' }}>Paket / Bundle (Auto-Potong Bahan BOM saat Dijual)</option>
                                    <option value="manufactured_good" {{ old('item_type', $item->item_type) == 'manufactured_good' ? 'selected' : '' }}>Hasil Produksi (Dirakit via Work Order)</option>
                                    <option value="raw_material" {{ old('item_type', $item->item_type) == 'raw_material' ? 'selected' : '' }}>Bahan Baku Murni (Hanya untuk BOM, Tidak Dijual)</option>
                                    <option value="super_product" {{ old('item_type', $item->item_type) == 'super_product' ? 'selected' : '' }}>Super Product (Bahan Baku & Bisa Dijual Langsung)</option>
                                    <option value="service" {{ old('item_type', $item->item_type) == 'service' ? 'selected' : '' }}>Jasa / Layanan Non-Fisik</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fs-7 fw-semibold text-gray-700">Foto / Gambar Produk</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="symbol symbol-70px bg-light border border-dashed border-gray-300 rounded-3 overflow-hidden d-flex align-items-center justify-content-center position-relative shadow-xs" style="width: 70px; height: 70px;" id="imagePreviewContainer">
                                        @if($item->image_url)
                                            <i class="bi bi-image text-muted fs-2 d-none" id="placeholderImage"></i>
                                            <img id="previewImg" src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-100 h-100 object-fit-cover" />
                                        @else
                                            <i class="bi bi-image text-muted fs-2" id="placeholderImage"></i>
                                            <img id="previewImg" src="#" alt="Preview" class="w-100 h-100 object-fit-cover d-none" />
                                        @endif
                                    </div>
                                    <div>
                                        <input type="file" name="image" id="imageInput" class="d-none" accept="image/*" onchange="previewProductImage(this)" />
                                        <button type="button" class="btn btn-sm btn-light-primary fw-bold py-1 px-3" onclick="document.getElementById('imageInput').click()">
                                            <i class="bi bi-cloud-arrow-up me-1"></i> Ganti Foto
                                        </button>
                                        <div class="text-muted fs-9 mt-1">Maks. 4MB (JPG, PNG, WEBP)</div>
                                        <button type="button" class="btn btn-xs btn-link text-danger p-0 {{ $item->image ? '' : 'd-none' }} mt-1" id="btnRemoveImage" onclick="removeSelectedImage()">
                                            <i class="bi bi-trash me-1"></i> Hapus Foto
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fs-7 fw-semibold text-gray-700 required">Nama Barang / Layanan Jasa</label>
                                <input type="text" name="name" class="form-control form-control-solid form-control-sm" value="{{ old('name', $item->name) }}" required placeholder="Nama item..." />
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                 <label class="form-label fs-7 fw-semibold text-gray-700 required">Kategori Barang</label>
                                 <select name="category_id" id="itemCategorySelect" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="Pilih Kategori..." required onchange="handleItemCategoryChange()">
                                     <option value=""></option>
                                     @foreach($categories as $c)
                                         <option value="{{ $c->id }}" 
                                             data-type="{{ $c->type }}" 
                                             data-margin="{{ $c->min_margin_percent ?? 0 }}"
                                             {{ old('category_id', $item->category_id) == $c->id ? 'selected' : '' }}>
                                             {{ $c->hierarchy_name }} ({{ $c->type_label }})
                                         </option>
                                     @endforeach
                                 </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-7 fw-semibold text-gray-700">Merk / Brand</label>
                                <select name="brand_id" id="itemBrandSelect" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="Tanpa Merk / Polos" data-allow-clear="true">
                                    <option value=""></option>
                                    @foreach($brands as $b)
                                        <option value="{{ $b->id }}" {{ old('brand_id', $item->brand_id) == $b->id ? 'selected' : '' }}>
                                            {{ $b->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-7 fw-semibold text-gray-700 required">Satuan Dasar (Base Unit)</label>
                                <select name="unit_id" id="baseUnitSelect" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="Pilih Satuan..." required>
                                    <option value=""></option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}" {{ old('unit_id', $item->unit_id) == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }} ({{ $u->symbol }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-4 mb-4" id="priceStockSection">
                            <div class="col-md-6" id="purchasePriceCol">
                                <label class="form-label fs-7 fw-semibold text-gray-700 required">Harga Beli Standar / HPP Pokok (Rp)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" step="0.01" name="purchase_price" id="purchasePriceInput" class="form-control form-control-solid form-control-sm" value="{{ old('purchase_price', $item->purchase_price) }}" required oninput="calculateMinSellingPrice()" />
                                </div>
                                <div class="form-text fs-8 text-muted">HPP dasar persediaan barang.</div>
                            </div>
                            <div class="col-md-6" id="sellingPriceCol">
                                <label class="form-label fs-7 fw-semibold text-gray-700 required">Harga Jual Standar (Rp)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" step="0.01" name="selling_price" id="sellingPriceInput" class="form-control form-control-solid form-control-sm" value="{{ old('selling_price', $item->selling_price) }}" required />
                                </div>
                                <div id="minPricePreview" class="form-text fs-8 text-danger fw-bold d-none"></div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4" id="marginAndStockSection">
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-semibold text-gray-700">Limit Margin Penjualan Minimum (%)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" min="0" max="1000" name="min_margin_percent" id="minMarginInput" class="form-control form-control-solid form-control-sm" value="{{ old('min_margin_percent', $item->min_margin_percent) }}" placeholder="Ikuti Default Kategori" oninput="calculateMinSellingPrice()" />
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text fs-8 text-muted" id="marginHelpText">Kosongkan untuk otomatis mengikuti batas margin kategori default.</div>
                            </div>
                            <div class="col-md-6" id="minStockCol">
                                <label class="form-label fs-7 fw-semibold text-gray-700">Batas Minimum Stok</label>
                                <input type="number" step="0.01" name="min_stock" class="form-control form-control-solid form-control-sm" value="{{ old('min_stock', $item->min_stock) }}" />
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-semibold text-gray-700">Otorisasi Ubah Harga Jual</label>
                                <div class="form-check form-check-solid p-3 rounded bg-light border border-gray-200">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="allow_change_price" value="1" id="allowChangePrice" {{ old('allow_change_price', $item->allow_change_price) ? 'checked' : '' }} />
                                    <label class="form-check-label fw-semibold text-gray-800 fs-7 cursor-pointer" for="allowChangePrice">
                                        <i class="bi bi-pencil-square text-primary me-1"></i> Bisa Ubah Harga Jual saat Transaksi
                                    </label>
                                    <div class="form-text fs-9 text-muted ms-6">Jika dicentang, kasir POS dan staf Faktur AR dapat mengetik & mengubah harga jual langsung.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-semibold text-gray-700">Status Keaktifan</label>
                                <div class="form-check form-check-solid p-3 rounded bg-light border border-gray-200">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" value="1" id="itemIsActive" {{ old('is_active', $item->is_active) ? 'checked' : '' }} />
                                    <label class="form-check-label fw-semibold text-gray-800 fs-7 cursor-pointer" for="itemIsActive">
                                        Barang Aktif Dijual / Digunakan
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label fs-7 fw-semibold text-gray-700">Deskripsi / Catatan Produk</label>
                                <textarea name="description" class="form-control form-control-solid form-control-sm" rows="2">{{ old('description', $item->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Konversi Multi Satuan -->
                    <div class="tab-pane fade" id="tab_item_units" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="fw-bold text-gray-800 m-0 fs-6">Konversi Multi-Satuan (Satuan Bertingkat)</h4>
                                <span class="text-muted fs-8">Daftarkan satuan tambahan untuk pembelian / penjualan grosir (Misal: 1 DUS = 24 PCS, 1 BALL = 240 PCS)</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-light-primary fw-bold" onclick="addUnitConvRow()">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Satuan Lain
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                                <thead class="table-light">
                                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                        <th style="width: 25%;">Satuan Tambahan</th>
                                        <th style="width: 25%;">Faktor Pengali ke Satuan Dasar</th>
                                        <th style="width: 20%;">Override Harga Jual (Rp)</th>
                                        <th style="width: 20%;">Override Harga Beli (Rp)</th>
                                        <th class="text-center" style="width: 10%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="unitConvBody">
                                    <!-- Dynamic rows -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 3: Limit & Harga Khusus per Cabang -->
                    <div class="tab-pane fade" id="tab_item_branch" role="tabpanel">
                        <div class="mb-3">
                            <h4 class="fw-bold text-gray-800 m-0 fs-6">Kustomisasi Limit Margin, Stok & Harga per Cabang</h4>
                            <span class="text-muted fs-8">Secara default, seluruh cabang mengikuti harga, HPP, limit margin & limit stok utama. Masukkan nilai di bawah jika cabang tertentu memiliki aturan khusus.</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                                <thead class="table-light">
                                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                        <th style="width: 25%;">Cabang Operasional</th>
                                        <th style="width: 20%;">Harga Jual Cabang (Rp)</th>
                                        <th style="width: 20%;">HPP Pokok Cabang (Rp)</th>
                                        <th style="width: 15%;">Limit Min Margin (%)</th>
                                        <th style="width: 10%;">Min Stok</th>
                                        <th class="text-center" style="width: 10%;">Dijual di Cabang</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($branches as $b)
                                    @php
                                        $bPrice = $item->branchPrices->where('branch_id', $b->id)->first();
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-gray-900 d-flex align-items-center gap-1">
                                                <i class="bi bi-shop text-primary"></i> {{ $b->name }}
                                                @if($b->is_main)
                                                    <span class="badge badge-light-info fs-9 py-0">PUSAT</span>
                                                @endif
                                            </div>
                                            <span class="text-muted fs-9">{{ $b->code }}</span>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="branch_prices[{{ $b->id }}][selling_price]" class="form-control form-control-solid form-control-sm" value="{{ $bPrice?->selling_price }}" placeholder="Ikuti Default" />
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="branch_prices[{{ $b->id }}][purchase_price]" class="form-control form-control-solid form-control-sm" value="{{ $bPrice?->purchase_price }}" placeholder="Ikuti Default" />
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" max="1000" name="branch_prices[{{ $b->id }}][min_margin_percent]" class="form-control form-control-solid form-control-sm" value="{{ $bPrice?->min_margin_percent }}" placeholder="Default Kategori" />
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="branch_prices[{{ $b->id }}][min_stock]" class="form-control form-control-solid form-control-sm" value="{{ $bPrice?->min_stock }}" placeholder="0" />
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-check-custom form-check-solid justify-content-center">
                                                <input type="hidden" name="branch_prices[{{ $b->id }}][is_available]" value="0" />
                                                <input class="form-check-input" type="checkbox" name="branch_prices[{{ $b->id }}][is_available]" value="1" {{ ($bPrice && !$bPrice->is_available) ? '' : 'checked' }} />
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 4: Pengaturan Akun GL Produk -->
                    <div class="tab-pane fade" id="tab_item_gl" role="tabpanel">
                        <div class="alert bg-light-info border border-info border-dashed d-flex align-items-center p-4 mb-4">
                            <i class="bi bi-info-circle-fill fs-2 text-info me-3"></i>
                            <div class="text-gray-800 fs-7">
                                <strong>Kustomisasi Akun GL Spesifik Produk:</strong> Opsi ini bersifat <em>opsional</em>. Jika dikosongkan, sistem akan otomatis menggunakan akun GL yang sudah dipetakan pada <strong>Kategori: {{ $item->category?->name ?? 'Default' }}</strong>.
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-semibold text-gray-700">Akun GL Persediaan (Inventory Asset)</label>
                                <select name="inventory_account_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Ikuti Default Kategori ({{ $item->category?->inventoryAccount?->name ?? 'Belum Diatur' }}) --" data-allow-clear="true">
                                    <option value=""></option>
                                    @foreach($inventoryAccounts as $ia)
                                        <option value="{{ $ia->id }}" {{ old('inventory_account_id', $item->inventory_account_id) == $ia->id ? 'selected' : '' }}>
                                            {{ $ia->code }} - {{ $ia->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-semibold text-gray-700">Akun GL HPP (Cost of Goods Sold)</label>
                                <select name="cogs_account_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Ikuti Default Kategori ({{ $item->category?->cogsAccount?->name ?? 'Belum Diatur' }}) --" data-allow-clear="true">
                                    <option value=""></option>
                                    @foreach($cogsAccounts as $ca)
                                        <option value="{{ $ca->id }}" {{ old('cogs_account_id', $item->cogs_account_id) == $ca->id ? 'selected' : '' }}>
                                            {{ $ca->code }} - {{ $ca->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-semibold text-gray-700">Akun GL Pendapatan (Sales Revenue)</label>
                                <select name="sales_account_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Ikuti Default Kategori ({{ $item->category?->salesAccount?->name ?? 'Belum Diatur' }}) --" data-allow-clear="true">
                                    <option value=""></option>
                                    @foreach($salesAccounts as $sa)
                                        <option value="{{ $sa->id }}" {{ old('sales_account_id', $item->sales_account_id) == $sa->id ? 'selected' : '' }}>
                                            {{ $sa->code }} - {{ $sa->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-semibold text-gray-700">Akun GL Pembelian / Unbilled GRN</label>
                                <select name="purchase_account_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Ikuti Default Kategori ({{ $item->category?->purchaseAccount?->name ?? 'Belum Diatur' }}) --" data-allow-clear="true">
                                    <option value=""></option>
                                    @foreach($purchaseAccounts as $pa)
                                        <option value="{{ $pa->id }}" {{ old('purchase_account_id', $item->purchase_account_id) == $pa->id ? 'selected' : '' }}>
                                            {{ $pa->code }} - {{ $pa->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer py-4 d-flex justify-content-end gap-2 border-top">
                <a href="{{ route('items.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-check2-circle me-1"></i> Perbarui Data Barang
                </button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    const allUnits = @json($units);
    const existingConversions = @json($item->unitConversions);
    let unitConvIndex = 0;

    function addUnitConvRow(unitId = '', factor = '', sellPrice = '', buyPrice = '') {
        const tr = document.createElement('tr');
        tr.id = `unit-conv-row-${unitConvIndex}`;

        let unitOptions = '<option value="">-- Pilih Satuan --</option>';
        allUnits.forEach(u => {
            const sel = unitId == u.id ? 'selected' : '';
            unitOptions += `<option value="${u.id}" ${sel}>${u.name} (${u.symbol})</option>`;
        });

        tr.innerHTML = `
            <td>
                <select name="conversions[${unitConvIndex}][unit_id]" class="form-select form-select-solid form-select-sm" required>
                    ${unitOptions}
                </select>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.0001" min="0.0001" name="conversions[${unitConvIndex}][conversion_factor]" class="form-control form-control-solid form-control-sm" value="${factor}" placeholder="Misal: 24" required />
                    <span class="input-group-text base-unit-hint bg-light">x Base Unit</span>
                </div>
            </td>
            <td>
                <input type="number" step="0.01" name="conversions[${unitConvIndex}][selling_price_override]" class="form-control form-control-solid form-control-sm" value="${sellPrice || ''}" placeholder="Auto (Faktor x Harga Jual)" />
            </td>
            <td>
                <input type="number" step="0.01" name="conversions[${unitConvIndex}][purchase_price_override]" class="form-control form-control-solid form-control-sm" value="${buyPrice || ''}" placeholder="Auto (Faktor x HPP)" />
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-icon btn-light-danger btn-sm" onclick="removeUnitConvRow(${unitConvIndex})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        document.getElementById('unitConvBody').appendChild(tr);
        unitConvIndex++;
    }

    function removeUnitConvRow(idx) {
        const row = document.getElementById(`unit-conv-row-${idx}`);
        if (row) row.remove();
    }

    function handleItemTypeChange() {
        const itemType = document.getElementById('itemTypeSelect').value;
        const purchaseCol = document.getElementById('purchasePriceCol');
        const marginStockSec = document.getElementById('marginAndStockSection');

        if (itemType === 'service') {
            if (purchaseCol) purchaseCol.style.display = 'none';
            if (marginStockSec) marginStockSec.style.display = 'none';
        } else {
            if (purchaseCol) purchaseCol.style.display = 'block';
            if (marginStockSec) marginStockSec.style.display = 'flex';
        }
        calculateMinSellingPrice();
    }

    function handleItemCategoryChange() {
        const select = document.getElementById('itemCategorySelect');
        const selectedOption = select.options[select.selectedIndex];
        const defaultMargin = selectedOption ? parseFloat(selectedOption.getAttribute('data-margin') || 0) : 0;
        const minMarginInput = document.getElementById('minMarginInput');
        const marginHelpText = document.getElementById('marginHelpText');

        if (marginHelpText) {
            marginHelpText.innerText = `Default margin kategori: ${defaultMargin}% (kosongkan jika ingin mengikuti kategori).`;
        }
        if (minMarginInput) {
            minMarginInput.placeholder = `Default: ${defaultMargin}%`;
        }
        calculateMinSellingPrice();
    }

    function calculateMinSellingPrice() {
        const itemType = document.getElementById('itemTypeSelect').value;
        if (itemType === 'service') {
            document.getElementById('minPricePreview').classList.add('d-none');
            return;
        }

        const cost = parseFloat(document.getElementById('purchasePriceInput').value || 0);
        const itemMarginVal = document.getElementById('minMarginInput').value;
        const select = document.getElementById('itemCategorySelect');
        const selectedOption = select ? select.options[select.selectedIndex] : null;
        const categoryMargin = selectedOption ? parseFloat(selectedOption.getAttribute('data-margin') || 0) : 0;
        
        const effectiveMargin = itemMarginVal !== '' && !isNaN(itemMarginVal) ? parseFloat(itemMarginVal) : categoryMargin;
        const minPreview = document.getElementById('minPricePreview');

        if (effectiveMargin > 0 && cost > 0) {
            const minPrice = cost * (1 + (effectiveMargin / 100));
            minPreview.innerText = `* Minimum Harga Jual (Margin ${effectiveMargin}%): Rp ${Math.round(minPrice).toLocaleString('id-ID')}`;
            minPreview.classList.remove('d-none');
        } else {
            minPreview.classList.add('d-none');
        }
    }

    function previewProductImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('previewImg').classList.remove('d-none');
                document.getElementById('placeholderImage').classList.add('d-none');
                document.getElementById('btnRemoveImage').classList.remove('d-none');
                document.getElementById('removeImageFlag').value = '0';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeSelectedImage() {
        const input = document.getElementById('imageInput');
        input.value = '';
        document.getElementById('previewImg').src = '#';
        document.getElementById('previewImg').classList.add('d-none');
        document.getElementById('placeholderImage').classList.remove('d-none');
        document.getElementById('btnRemoveImage').classList.add('d-none');
        document.getElementById('removeImageFlag').value = '1';
    }

    document.addEventListener('DOMContentLoaded', function() {
        handleItemCategoryChange();
        handleItemTypeChange();

        if (existingConversions && existingConversions.length > 0) {
            existingConversions.forEach(c => {
                addUnitConvRow(c.unit_id, c.conversion_factor, c.selling_price_override, c.purchase_price_override);
            });
        }
    });
</script>
@endsection
@endsection
