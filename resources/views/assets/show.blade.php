@extends('layouts.app')

@section('title', 'Aset ' . $asset->asset_code)
@section('page_title', 'Detail Register Aset Tetap')

@section('content')
<div class="d-flex flex-column gap-5">
    
    <div class="d-flex align-items-center justify-content-between">
        <a href="{{ route('assets.index') }}" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Aset
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil-fill me-1"></i> Edit Aset
            </a>
        </div>
    </div>

    <!-- Header Card -->
    <div class="card card-flush shadow-sm">
        <div class="card-body p-6">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-4">
                <div class="d-flex align-items-center gap-4">
                    <div class="symbol symbol-50px symbol-circle bg-light-primary">
                        <span class="symbol-label text-primary"><i class="bi bi-laptop fs-2x"></i></span>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h2 class="text-gray-900 fw-bolder fs-3 mb-0">{{ $asset->name }}</h2>
                            <code>{{ $asset->asset_code }}</code>
                        </div>
                        <div class="text-muted fs-7 mt-1">
                            Kategori: <strong class="text-gray-800">{{ $asset->category->name }}</strong> | Tanggal Perolehan: <strong class="text-gray-800">{{ $asset->acquisition_date->format('d F Y') }}</strong> | Metode: <span class="badge badge-light-primary fw-bold">{{ $asset->depreciation_method_label }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge {{ $asset->depreciation_method == 'none' ? 'badge-light-secondary' : ($asset->depreciation_method == 'declining_balance' ? 'badge-light-warning' : 'badge-light-info') }} fs-7 fw-bold px-3 py-2">
                        {{ $asset->depreciation_method_label }}
                    </span>
                    <span class="badge {{ $asset->status == 'active' ? 'badge-light-success' : 'badge-light-danger' }} fs-7 fw-bold px-4 py-2">
                        Status: {{ ucfirst($asset->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3 Metrics -->
    <div class="row g-5">
        <div class="col-md-4">
            <div class="card card-flush p-6 bg-light border border-gray-200">
                <div class="text-muted fw-bold fs-7 text-uppercase">Harga Perolehan Awal</div>
                <div class="text-gray-900 fw-bolder fs-2hx mt-1">
                    Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}
                </div>
                <div class="text-muted fs-8 mt-1">Nilai Residu: Rp {{ number_format($asset->salvage_value, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-flush p-6 bg-light-danger border border-danger border-dashed">
                <div class="text-danger fw-bold fs-7 text-uppercase">Akumulasi Penyusutan</div>
                <div class="text-danger fw-bolder fs-2hx mt-1">
                    Rp {{ number_format($asset->accumulated_depreciation, 0, ',', '.') }}
                </div>
                <div class="text-danger fs-8 mt-1">Penyusutan Bulanan: Rp {{ number_format($asset->calculateMonthlyDepreciation(), 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-flush p-6 bg-light-success border border-success border-dashed">
                <div class="text-success fw-bold fs-7 text-uppercase">Nilai Buku Bersih (NBV)</div>
                <div class="text-success fw-bolder fs-2hx mt-1">
                    Rp {{ number_format($asset->current_book_value, 0, ',', '.') }}
                </div>
                <div class="text-success fs-8 mt-1">Masa Manfaat: {{ $asset->useful_life_months }} Bulan</div>
            </div>
        </div>
    </div>

    <!-- GL Mapping Card -->
    <div class="card card-flush shadow-sm">
        <div class="card-header py-4">
            <h3 class="card-title fw-bold fs-6 text-gray-800">
                <i class="bi bi-diagram-3-fill text-primary me-2"></i> Pemetaan Akun Akuntansi (GL Mapping)
            </h3>
        </div>
        <div class="card-body pt-0">
            <div class="row g-4">
                <div class="col-md-4">
                    <span class="text-muted fs-8 d-block">Akun Aset Tetap:</span>
                    <strong class="text-gray-800">{{ $asset->category->assetAccount->code }} - {{ $asset->category->assetAccount->name }}</strong>
                </div>
                <div class="col-md-4">
                    <span class="text-muted fs-8 d-block">Akun Beban Penyusutan:</span>
                    <strong class="text-gray-800">{{ $asset->category->depreciationExpenseAccount->code }} - {{ $asset->category->depreciationExpenseAccount->name }}</strong>
                </div>
                <div class="col-md-4">
                    <span class="text-muted fs-8 d-block">Akun Akumulasi Penyusutan:</span>
                    <strong class="text-gray-800">{{ $asset->category->accumulatedDepreciationAccount->code }} - {{ $asset->category->accumulatedDepreciationAccount->name }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Depreciation History -->
    <div class="card card-flush shadow-sm">
        <div class="card-header py-5">
            <h3 class="card-title fw-bolder text-gray-800 fs-5">
                <i class="bi bi-clock-history text-primary me-2"></i> Riwayat Eksekusi Penyusutan Bulanan
            </h3>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                    <thead>
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                            <th>Periode</th>
                            <th>Tanggal Eksekusi</th>
                            <th>Nilai Penyusutan</th>
                            <th>Jurnal Otomatis</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @forelse($asset->depreciations as $dep)
                        <tr>
                            <td><span class="text-gray-800 fw-bold">{{ $dep->period }}</span></td>
                            <td>{{ $dep->depreciation_date->format('d/m/Y') }}</td>
                            <td class="text-danger fw-bolder">Rp {{ number_format($dep->amount, 0, ',', '.') }}</td>
                            <td>
                                @if($dep->journal)
                                    <a href="{{ route('accounting.journals.show', $dep->journal_id) }}" class="badge badge-light-success fw-bold">
                                        <i class="bi bi-journal-check me-1"></i> {{ $dep->journal->journal_number }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 text-muted">Belum ada catatan riwayat penyusutan untuk aset ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
