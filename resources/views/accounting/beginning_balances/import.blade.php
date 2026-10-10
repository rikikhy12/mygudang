@extends('layouts.app')

@section('title', 'Import Saldo Awal Sistem')
@section('page_title', 'Import Saldo Awal dari File Excel / CSV')

@section('content')
<div class="d-flex flex-column gap-5">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Import Saldo Awal Neraca & Persediaan</h2>
            <p class="text-muted fs-7 mb-0">Impor saldo awal stok gudang atau bagan akun COA sekaligus dari spreadsheet</p>
        </div>
        <a href="{{ route('accounting.beginning-balances.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Saldo Awal
        </a>
    </div>

    <div class="row g-5">
        <!-- Option 1: Saldo Awal Stok Gudang -->
        <div class="col-lg-6">
            <div class="card card-flush shadow-sm h-100 border-top border-3 border-success">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-5">
                            <i class="bi bi-boxes text-success me-2"></i> Import Saldo Awal Stok Gudang
                        </span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Input kuantitas on-hand & harga pokok (HPP) per gudang</span>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert bg-light-success border border-success border-dashed p-4 mb-4">
                        <div class="text-gray-800 fs-7">
                            Sistem akan mencatat mutasi stok awal ke masing-masing gudang serta otomatis menjurnal penambahan akun persediaan terhadap modal saldo awal (3001).
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <a href="{{ route('accounting.beginning-balances.import.template.stock') }}" class="btn btn-light-success btn-sm w-100 py-2 fw-bold">
                            <i class="bi bi-download me-2"></i> Download Template Stok (.csv)
                        </a>
                    </div>

                    <form action="{{ route('accounting.beginning-balances.import.preview') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="stock" />
                        <div class="mb-4">
                            <label class="form-label fs-7 fw-semibold text-gray-700 required">Upload File Saldo Stok</label>
                            <input type="file" name="file" class="form-control form-control-sm" accept=".csv,.txt" required />
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success btn-sm px-4">
                                <i class="bi bi-table me-1"></i> Preview Tabel Saldo Stok
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Option 2: Saldo Awal Akun COA -->
        <div class="col-lg-6">
            <div class="card card-flush shadow-sm h-100 border-top border-3 border-primary">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-5">
                            <i class="bi bi-diagram-3-fill text-primary me-2"></i> Import Saldo Awal Neraca Akun (COA)
                        </span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Input posisi debit/kredit saldo awal kas, bank, piutang, modal, dll</span>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert bg-light-primary border border-primary border-dashed p-4 mb-4">
                        <div class="text-gray-800 fs-7">
                            Template CSV sudah otomatis memuat seluruh daftar akun COA aktif di sistem Anda. Cukup isi angka debit dan kreditnya.
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <a href="{{ route('accounting.beginning-balances.import.template.account') }}" class="btn btn-light-primary btn-sm w-100 py-2 fw-bold">
                            <i class="bi bi-download me-2"></i> Download Template Akun COA (.csv)
                        </a>
                    </div>

                    <form action="{{ route('accounting.beginning-balances.import.preview') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="account" />
                        <div class="mb-4">
                            <label class="form-label fs-7 fw-semibold text-gray-700 required">Upload File Saldo Akun</label>
                            <input type="file" name="file" class="form-control form-control-sm" accept=".csv,.txt" required />
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-sm px-4">
                                <i class="bi bi-table me-1"></i> Preview Tabel Saldo Akun
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
