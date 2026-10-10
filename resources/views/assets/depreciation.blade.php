@extends('layouts.app')

@section('title', 'Penyusutan Aset Otomatis')
@section('page_title', 'Proses Eksekusi Penyusutan Aset Bulanan')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Penyusutan Aset Tetap Otomatis</h2>
            <p class="text-muted fs-7 mb-0">Hitung dan posting otomatis jurnal amortisasi / depresiasi periode bulanan</p>
        </div>
        <a href="{{ route('assets.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Aset
        </a>
    </div>

    <!-- Execution Form Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-calculator-fill text-primary fs-3"></i> Jalankan Penyusutan Aset Periode Bulanan
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Tentukan periode dan tanggal jurnal penyusutan untuk seluruh aset aktif yang terdaftar
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('assets.depreciation.process') }}">
            @csrf
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Parameter Eksekusi Depresiasi</h4>
                </div>

                <div class="row g-3 align-items-end mb-4">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Periode Penyusutan (YYYY-MM)</label>
                        <input type="month" name="period" class="form-control form-control-solid form-control-sm" value="{{ $currentPeriod }}" required />
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tanggal Posting Jurnal</label>
                        <input type="date" name="depreciation_date" class="form-control form-control-solid form-control-sm" value="{{ date('Y-m-d') }}" required />
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-sm btn-primary w-100" onclick="return confirm('Proses penyusutan untuk seluruh aset aktif periode ini?');">
                            <i class="bi bi-play-circle-fill me-1"></i> Posting Jurnal Penyusutan
                        </button>
                    </div>
                </div>

                <div class="alert alert-primary d-flex align-items-center p-4 mb-0 rounded-3 fs-7">
                    <i class="bi bi-info-circle-fill text-primary fs-3 me-3"></i>
                    <div>
                        Sistem akan mengalkulasi beban garis lurus untuk seluruh aset aktif (<strong>{{ $activeAssets->count() }} Aset</strong>) dan membentuk Jurnal Umum Otomatis (Debit: <code>Beban Penyusutan Aset</code> vs Kredit: <code>Akumulasi Penyusutan</code>).
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- History Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary fs-3"></i> Riwayat Transaksi Penyusutan Aset
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Daftar log proses penyusutan aset yang telah dibukukan ke buku besar
                </span>
            </div>
        </div>

        <div class="card-body p-6">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                    <thead>
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                            <th class="ps-3 py-3">Periode</th>
                            <th>Kode Aset</th>
                            <th>Nama Aset</th>
                            <th>Tanggal Eksekusi</th>
                            <th class="text-end">Nominal Penyusutan</th>
                            <th class="text-end pe-3">Jurnal Akuntansi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 fw-semibold">
                        @forelse($history as $h)
                        <tr>
                            <td class="ps-3"><span class="text-gray-900 fw-bold">{{ $h->period }}</span></td>
                            <td><code>{{ $h->asset->asset_code }}</code></td>
                            <td><span class="text-gray-900 fw-bold">{{ $h->asset->name }}</span></td>
                            <td>{{ $h->depreciation_date->format('d/m/Y') }}</td>
                            <td class="text-end text-danger fw-bold">Rp {{ number_format($h->amount, 0, ',', '.') }}</td>
                            <td class="text-end pe-3">
                                @if($h->journal)
                                    <a href="{{ route('accounting.journals.show', $h->journal_id) }}" class="badge badge-light-success fw-bold">
                                        <i class="bi bi-journal-check me-1"></i> {{ $h->journal->journal_number }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Belum ada riwayat proses penyusutan aset.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

