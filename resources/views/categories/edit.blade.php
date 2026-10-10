@extends('layouts.app')

@section('title', 'Edit Kategori: ' . $category->name)
@section('page_title', 'Edit Kategori & GL Setting: ' . $category->name)

@section('content')
<div class="d-flex flex-column gap-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Edit Kategori & GL Mapping</h2>
            <p class="text-muted fs-7 mb-0">Perbarui hierarki parent, tipe kategori, serta konfigurasi akun buku besar default</p>
        </div>
        <a href="{{ route('categories.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kategori
        </a>
    </div>

    <div class="card card-flush shadow-sm">
        <form method="POST" action="{{ route('categories.update', $category->id) }}">
            @csrf
            @method('PUT')
            
            <div class="card-header border-bottom py-4 px-6 d-flex flex-wrap align-items-center justify-content-between">
                <div class="card-title m-0">
                    <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square text-primary fs-3"></i> Edit Kategori: {{ $category->name }} <span class="badge badge-light-primary fs-8"><code>{{ $category->code }}</code></span>
                    </h3>
                    <span class="text-muted fs-7 fw-normal d-block mt-1">
                        Perbarui hierarki parent, tipe kategori, serta konfigurasi akun buku besar default
                    </span>
                </div>
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-6 fw-bold mt-2 mt-md-0">
                    <li class="nav-item">
                        <a class="nav-link text-active-primary active py-2" data-bs-toggle="tab" href="#tab_category_info">
                            <i class="bi bi-info-circle me-1"></i> Informasi Kategori
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary py-2" data-bs-toggle="tab" href="#tab_category_gl">
                            <i class="bi bi-diagram-3 me-1"></i> Setting Akun GL Default
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-6">
                <div class="tab-content" id="categoryTabContent">
                    <!-- Tab 1: Info Kategori -->
                    <div class="tab-pane fade show active" id="tab_category_info" role="tabpanel">
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-semibold text-gray-700 required">Kode Kategori</label>
                                <input type="text" name="code" class="form-control form-control-solid form-control-sm" value="{{ old('code', $category->code) }}" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-semibold text-gray-700 required">Nama Kategori</label>
                                <input type="text" name="name" class="form-control form-control-solid form-control-sm" value="{{ old('name', $category->name) }}" required />
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-semibold text-gray-700">Kategori Induk (Parent Category)</label>
                                <select name="parent_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Kategori Utama (Tanpa Induk) --" data-allow-clear="true">
                                    <option value=""></option>
                                    @foreach($parentCategories as $pCat)
                                        <option value="{{ $pCat->id }}" {{ old('parent_id', $category->parent_id) == $pCat->id ? 'selected' : '' }}>
                                            {{ $pCat->hierarchy_name }} ({{ $pCat->code }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text fs-8 text-muted">Pilih jika kategori ini merupakan sub-kategori turunan.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-semibold text-gray-700 required">Tipe Kategori Barang</label>
                                <select name="type" id="categoryTypeSelect" class="form-select form-select-solid form-select-sm" data-control="select2" data-hide-search="true" required onchange="handleTypeChange()">
                                    <option value="finished_good" {{ old('type', $category->type) == 'finished_good' ? 'selected' : '' }}>Barang Siap Jadi (Finished Goods / Merchandise)</option>
                                    <option value="manufactured_good" {{ old('type', $category->type) == 'manufactured_good' ? 'selected' : '' }}>Barang Hasil Produksi (Manufacturing Assembly)</option>
                                    <option value="service" {{ old('type', $category->type) == 'service' ? 'selected' : '' }}>Jasa / Service (Non-Inventory / Tanpa Stok Fisik)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-4 mb-4" id="marginLimitSection">
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-semibold text-gray-700">Limit Margin Penjualan Minimum (%)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" min="0" max="1000" name="min_margin_percent" class="form-control form-control-solid form-control-sm" value="{{ old('min_margin_percent', $category->min_margin_percent ?? 0) }}" placeholder="contoh: 10 untuk margin minimal 10%" />
                                    <span class="input-group-text bg-light">%</span>
                                </div>
                                <div class="form-text fs-8 text-muted">Batas minimal keuntungan di atas HPP. Penjualan di bawah persentase ini akan otomatis dicegah/ditolak oleh sistem.</div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label fs-7 fw-semibold text-gray-700">Deskripsi Kategori</label>
                                <textarea name="description" class="form-control form-control-solid form-control-sm" rows="3">{{ old('description', $category->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Setting Akun GL Default -->
                    <div class="tab-pane fade" id="tab_category_gl" role="tabpanel">
                        <div class="alert bg-light-primary border border-primary border-dashed d-flex align-items-center p-4 mb-4">
                            <i class="bi bi-info-circle-fill fs-2 text-primary me-3"></i>
                            <div class="text-gray-800 fs-7">
                                <strong>Integrasi Akun Buku Besar (GL Default):</strong> Akun yang ditentukan di sini akan menjadi akun default untuk semua produk dalam kategori ini. Produk individual dapat mengubah akun GL-nya jika memiliki perlakuan akuntansi khusus.
                            </div>
                        </div>

                        <div id="physicalInventoryGlSection">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fs-7 fw-semibold text-gray-700">GL Persediaan (Inventory Asset)</label>
                                    <select name="inventory_account_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Pilih Akun Persediaan --" data-allow-clear="true">
                                        <option value=""></option>
                                        @foreach($inventoryAccounts as $ia)
                                            <option value="{{ $ia->id }}" {{ old('inventory_account_id', $category->inventory_account_id) == $ia->id ? 'selected' : '' }}>{{ $ia->code }} - {{ $ia->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text fs-8 text-muted">Didebit saat GRN masuk gudang, dikredit saat Surat Jalan (DO) keluar.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-7 fw-semibold text-gray-700">GL HPP (Cost of Goods Sold)</label>
                                    <select name="cogs_account_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Pilih Akun HPP --" data-allow-clear="true">
                                        <option value=""></option>
                                        @foreach($cogsAccounts as $ca)
                                            <option value="{{ $ca->id }}" {{ old('cogs_account_id', $category->cogs_account_id) == $ca->id ? 'selected' : '' }}>{{ $ca->code }} - {{ $ca->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text fs-8 text-muted">Didebit saat DO / Pengiriman barang ke customer.</div>
                                </div>
                            </div>

                            <div class="row g-4 mb-4">
                                <div class="col-md-12">
                                    <label class="form-label fs-7 fw-semibold text-gray-700">GL Pembelian / Unbilled GRN (Accrued AP)</label>
                                    <select name="purchase_account_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Pilih Akun Pembelian / Unbilled GRN --" data-allow-clear="true">
                                        <option value=""></option>
                                        @foreach($purchaseAccounts as $pa)
                                            <option value="{{ $pa->id }}" {{ old('purchase_account_id', $category->purchase_account_id) == $pa->id ? 'selected' : '' }}>{{ $pa->code }} - {{ $pa->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text fs-8 text-muted">Dikredit saat GRN, didebit saat Faktur Tagihan AP terbit.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label fs-7 fw-semibold text-gray-700 required">GL Penjualan / Pendapatan (Sales Revenue)</label>
                                <select name="sales_account_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Pilih Akun Pendapatan Penjualan --" required>
                                    <option value=""></option>
                                    @foreach($salesAccounts as $sa)
                                        <option value="{{ $sa->id }}" {{ old('sales_account_id', $category->sales_account_id) == $sa->id ? 'selected' : '' }}>{{ $sa->code }} - {{ $sa->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text fs-8 text-muted">Dikredit saat Faktur Penjualan (AR) atau Transaksi Kasir POS diterbitkan.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer py-4 d-flex justify-content-end gap-2 border-top">
                <a href="{{ route('categories.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-check2-circle me-1"></i> Perbarui Kategori & GL Setting
                </button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    function handleTypeChange() {
        const type = document.getElementById('categoryTypeSelect').value;
        const physicalSection = document.getElementById('physicalInventoryGlSection');
        const marginSection = document.getElementById('marginLimitSection');
        if (type === 'service') {
            if (physicalSection) physicalSection.style.display = 'none';
            if (marginSection) marginSection.style.display = 'none';
        } else {
            if (physicalSection) physicalSection.style.display = 'block';
            if (marginSection) marginSection.style.display = 'block';
        }
    }
    document.addEventListener('DOMContentLoaded', handleTypeChange);
</script>
@endsection
@endsection
