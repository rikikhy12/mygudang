@extends('layouts.app')

@section('title', 'Import Data Master Barang')
@section('page_title', 'Import Master Barang dari Excel / CSV')

@section('content')
<div class="d-flex flex-column gap-5">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Import Master Barang & Jasa</h2>
            <p class="text-muted fs-7 mb-0">Tambahkan atau perbarui katalog produk secara massal dengan file Excel atau CSV</p>
        </div>
        <a href="{{ route('items.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog Barang
        </a>
    </div>

    <div class="row g-5">
        <!-- Step 1: Download Template -->
        <div class="col-lg-5">
            <div class="card card-flush shadow-sm h-100">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-5">1. Download Template Import</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Gunakan template resmi agar format kolom sesuai</span>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert bg-light-primary border border-primary border-dashed d-flex align-items-center p-4 mb-4">
                        <i class="bi bi-file-earmark-spreadsheet-fill fs-2 text-primary me-3"></i>
                        <div class="text-gray-800 fs-7">
                            Template CSV sudah dilengkapi contoh data pengisian, termasuk SKU, Barcode, Kategori, Merk, Satuan, Tipe, Harga Beli, dan Harga Jual.
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('items.import.template') }}" class="btn btn-success btn-sm d-flex align-items-center justify-content-center py-3">
                            <i class="bi bi-download fs-4 me-2"></i> Download Template CSV (Excel Compatible)
                        </a>
                    </div>

                    <div class="separator my-5"></div>

                    <h4 class="fs-7 fw-bold text-gray-800 mb-2">Panduan Pengisian Kolom:</h4>
                    <ul class="text-muted fs-8 ps-4 mb-0 d-flex flex-column gap-1">
                        <li><strong>kode_barang</strong>: Kode unik SKU (Wajib diisi, contoh: BRG-001)</li>
                        <li><strong>barcode</strong>: Kode barcode fisik (Opsional)</li>
                        <li><strong>nama_barang</strong>: Nama produk/jasa (Wajib diisi)</li>
                        <li><strong>kategori</strong>: Nama kategori (Jika belum ada, otomatis dibuat)</li>
                        <li><strong>merk</strong>: Nama merk/brand dagang (Opsional)</li>
                        <li><strong>satuan_dasar</strong>: PCS, KG, DUS, LTR, dll (Default: PCS)</li>
                        <li><strong>tipe_barang</strong>: finished_good, service, bundle, raw_material</li>
                        <li><strong>harga_beli & harga_jual</strong>: Angka nominal tanpa titik/koma</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Step 2: Upload File & Preview -->
        <div class="col-lg-7">
            <div class="card card-flush shadow-sm h-100">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-5">2. Upload File & Lihat Preview</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Pilih file yang telah diisi untuk divalidasi ke tabel preview</span>
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('items.import.preview') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-5">
                            <label class="form-label fs-7 fw-semibold text-gray-700 required">Pilih File CSV / Excel</label>
                            <input type="file" name="file" class="form-control form-control-sm" accept=".csv,.txt" required />
                            <div class="form-text fs-8 text-muted mt-2">Maksimal ukuran file: 5 MB. Format didukung: <code>.csv</code></div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-5 border-top pt-4">
                            <button type="submit" class="btn btn-primary btn-sm px-4">
                                <i class="bi bi-table me-1"></i> Upload & Tampilkan Preview Tabel Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
