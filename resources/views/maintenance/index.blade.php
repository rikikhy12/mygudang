@extends('layouts.app')

@section('title', 'Backup & Reset Transaksi')
@section('page_title', 'Pemeliharaan Sistem: Backup & Reset Transaksi')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fs-4 fw-bold text-gray-900 mb-1">Backup & Reset Transaksi Sistem</h2>
        <p class="text-muted fs-7 mb-0">Kelola cadangan database (backup) dan pembersihan data transaksi operasional tanpa menghapus master data</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-light-primary btn-sm fw-semibold">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
    </a>
</div>

@if(session('success'))
<div class="alert alert-success d-flex align-items-center p-4 mb-4 rounded-3 border-0 shadow-sm">
    <i class="bi bi-check-circle-fill fs-2x text-success me-3"></i>
    <div class="d-flex flex-column">
        <h5 class="fw-bold mb-1 text-success">Operasi Berhasil</h5>
        <span class="fs-7 text-gray-800">{{ session('success') }}</span>
    </div>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger d-flex align-items-center p-4 mb-4 rounded-3 border-0 shadow-sm">
    <i class="bi bi-exclamation-triangle-fill fs-2x text-danger me-3"></i>
    <div class="d-flex flex-column">
        <h5 class="fw-bold mb-1 text-danger">Operasi Gagal</h5>
        <span class="fs-7 text-gray-800">{{ session('error') }}</span>
    </div>
</div>
@endif

<div class="row g-4 mb-4">
    <!-- Card 1: Database Backup -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-4 d-flex align-items-center">
                <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                    <i class="bi bi-cloud-arrow-down-fill fs-3 text-primary"></i>
                </div>
                <div>
                    <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Backup Database</h3>
                    <span class="text-muted fs-8 fw-semibold">Unduh snapshot database lengkap (.sql)</span>
                </div>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <p class="text-muted fs-7 mb-3">
                        Unduh salinan lengkap database sistem ERP untuk pengarsipan aman atau cadangan berkala.
                    </p>

                    <div class="bg-light rounded-3 p-3 mb-4 border border-dashed border-gray-300">
                        <div class="d-flex justify-content-between align-items-center mb-2 fs-7">
                            <span class="text-muted fw-semibold">Driver:</span>
                            <span class="badge badge-light-primary fw-bold text-uppercase">{{ $dbConnection }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 fs-7">
                            <span class="text-muted fw-semibold">Database:</span>
                            <span class="text-gray-800 fw-bold fs-8 font-monospace">{{ basename($dbName) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center fs-7">
                            <span class="text-muted fw-semibold">Waktu:</span>
                            <span class="text-gray-800 fw-bold">{{ date('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('maintenance.backup', ['format' => 'sql']) }}" class="btn btn-primary fw-bold py-2">
                        <i class="bi bi-filetype-sql fs-5 me-1"></i> Download Backup (.sql)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 1.5: Import SQL Backup -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-4 d-flex align-items-center">
                <div class="w-40px h-40px rounded-3 bg-light-success text-success d-flex align-items-center justify-content-center me-3 shadow-xs">
                    <i class="bi bi-cloud-arrow-up-fill fs-3 text-success"></i>
                </div>
                <div>
                    <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Import / Restore SQL</h3>
                    <span class="text-muted fs-8 fw-semibold">Unggah dan jalankan file database .sql</span>
                </div>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <form method="POST" action="{{ route('maintenance.import-sql') }}" enctype="multipart/form-data" id="importSqlForm">
                    @csrf
                    <p class="text-muted fs-7 mb-3">
                        Pulihkan database atau import data dari file <code>.sql</code> hasil backup sebelumnya.
                    </p>

                    <div class="mb-4">
                        <label class="form-label fs-8 fw-bold text-gray-700 required">Pilih File SQL (.sql)</label>
                        <input type="file" name="sql_file" accept=".sql,.txt" class="form-control form-control-solid form-control-sm" required />
                        <div class="form-text fs-9 text-muted mt-1">Maksimal ukuran file: 50MB.</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success fw-bold py-2" onclick="return confirm('Apakah Anda yakin ingin mengeksekusi import file SQL ini ke database?')">
                            <i class="bi bi-upload fs-5 me-1"></i> Eksekusi Import SQL
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Card 2: Reset Transaksi -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 border-top border-3 border-danger h-100">
            <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <div class="w-40px h-40px rounded-3 bg-light-danger text-danger d-flex align-items-center justify-content-center me-3 shadow-xs">
                        <i class="bi bi-trash3-fill fs-3 text-danger"></i>
                    </div>
                    <div>
                        <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Reset Transaksi</h3>
                        <span class="text-muted fs-8 fw-semibold">Hapus transaksi tanpa merusak master</span>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="alert alert-light-warning border border-warning border-dashed d-flex align-items-start p-3 mb-4 rounded-3">
                    <i class="bi bi-shield-exclamation text-warning fs-3 me-3 mt-1"></i>
                    <div class="fs-7 text-gray-800">
                        <strong class="text-gray-900 d-block mb-1">Proteksi Master Data Aktif:</strong>
                        Fitur ini <strong>HANYA</strong> menghapus data riwayat transaksi operasional (Penjualan, Pembelian, POS, Stok, Kas/Bank, dan Jurnal). Seluruh Master Data akun COA, barang, kontak, gudang, dan pengguna <strong>TETAP UTUH & AMAN</strong>.
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <!-- Column 1: Yang akan di-reset -->
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 bg-light-danger bg-opacity-10 border-danger border-opacity-25 h-100">
                            <h6 class="fw-bold text-danger fs-7 mb-2 d-flex align-items-center">
                                <i class="bi bi-x-circle-fill text-danger me-1"></i> Data yang Akan Dihapus:
                            </h6>
                            <ul class="fs-8 text-gray-700 ps-3 mb-0 list-unstyled">
                                <li class="mb-1">• <strong>Pembelian:</strong> PR ({{ $transactionCounts['purchase_requisitions'] }}), PO ({{ $transactionCounts['purchase_orders'] }}), GRN ({{ $transactionCounts['goods_receipts'] }}), Tagihan AP ({{ $transactionCounts['purchase_invoices'] }})</li>
                                <li class="mb-1">• <strong>Penjualan:</strong> SO ({{ $transactionCounts['sales_orders'] }}), DO ({{ $transactionCounts['delivery_orders'] }}), Faktur AR & POS ({{ $transactionCounts['sales_invoices'] }})</li>
                                <li class="mb-1">• <strong>Inventory:</strong> Mutasi Stok ({{ $transactionCounts['stock_ledgers'] }}), Opname ({{ $transactionCounts['stock_adjustments'] }}), Transfer ({{ $transactionCounts['stock_transfers'] }})</li>
                                <li class="mb-1">• <strong>Keuangan:</strong> Kas/Bank ({{ $transactionCounts['cash_transactions'] }}), Jurnal ({{ $transactionCounts['journals'] }}), Penyusutan ({{ $transactionCounts['asset_depreciations'] }})</li>
                                <li class="mt-2 text-danger fw-bold">• Saldo stok seluruh barang dikembalikan ke 0.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Column 2: Yang tetap aman -->
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 bg-light-success bg-opacity-10 border-success border-opacity-25 h-100">
                            <h6 class="fw-bold text-success fs-7 mb-2 d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-1"></i> Master Data Tetap Aman:
                            </h6>
                            <ul class="fs-8 text-gray-700 ps-3 mb-0 list-unstyled">
                                <li class="mb-1">• <strong>Bagan Akun (COA):</strong> {{ $masterCounts['accounts'] }} Akun</li>
                                <li class="mb-1">• <strong>Master Barang & Jasa:</strong> {{ $masterCounts['items'] }} Produk/Jasa</li>
                                <li class="mb-1">• <strong>Kontak Partner:</strong> {{ $masterCounts['contacts'] }} Supplier & Customer</li>
                                <li class="mb-1">• <strong>Master Gudang:</strong> {{ $masterCounts['warehouses'] }} Lokasi Gudang</li>
                                <li class="mb-1">• <strong>Master Aset Tetap:</strong> {{ $masterCounts['fixed_assets'] }} Aset</li>
                                <li class="mb-1">• <strong>Pengguna & Role:</strong> {{ $masterCounts['users'] }} Akun User</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2">
                    <div>
                        <span class="text-muted fs-8">Total Data Transaksi Aktif:</span>
                        <span class="badge badge-light-danger fw-bolder fs-7 ms-1">{{ number_format($totalTransactions, 0) }} Rekord</span>
                    </div>
                    <button type="button" class="btn btn-danger fw-bold btn-sm px-4" data-bs-toggle="modal" data-bs-target="#resetConfirmModal">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Transaksi Saja
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Keamanan Reset Transaksi -->
<div class="modal fade" id="resetConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3 bg-danger text-white">
                <h5 class="modal-title fw-bold text-white">
                    <i class="bi bi-exclamation-octagon-fill me-2"></i> Konfirmasi Reset Data Transaksi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('maintenance.reset-transactions') }}" id="resetForm">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-gray-800 fs-7 mb-3">
                        Apakah Anda yakin ingin menghapus seluruh data transaksi operasional? 
                    </p>
                    <div class="alert alert-danger p-3 mb-3 fs-8">
                        <strong>Perhatian:</strong> Data transaksi yang telah dihapus <strong>tidak dapat dikembalikan</strong> kecuali Anda memiliki file cadangan backup. Master data (COA, Barang, Kontak, Gudang, User) akan tetap terjaga.
                    </div>

                    <label class="form-label fw-bold text-gray-700 fs-7 required">
                        Ketik kata <span class="text-danger">RESET TRANSAKSI</span> untuk melanjutkan:
                    </label>
                    <input type="text" name="confirmation" id="confirmationInput" class="form-control form-control-solid form-control-sm" placeholder="Ketik RESET TRANSAKSI di sini..." autocomplete="off" required oninput="checkConfirmation(this.value)">
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-light btn-sm fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitReset" class="btn btn-danger btn-sm fw-bold px-4" disabled>
                        <i class="bi bi-trash3-fill me-1"></i> Hapus & Reset Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
    function checkConfirmation(val) {
        const btn = document.getElementById('btnSubmitReset');
        if (val.trim().toUpperCase() === 'RESET TRANSAKSI') {
            btn.removeAttribute('disabled');
        } else {
            btn.setAttribute('disabled', 'disabled');
        }
    }
</script>
@endsection
@endsection
