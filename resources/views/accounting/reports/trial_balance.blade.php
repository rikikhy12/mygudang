@extends('layouts.app')

@section('title', 'Neraca Saldo (Trial Balance)')
@section('page_title', 'Laporan Neraca Saldo (Trial Balance)')

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <form method="GET" action="{{ route('accounting.reports.trial-balance') }}" class="d-flex align-items-center gap-2">
            <div class="d-flex align-items-center gap-2 fs-7 text-muted">
                <span>Per Tanggal:</span>
                <input type="date" name="as_of_date" class="form-control form-control-sm" style="width: 150px;" value="{{ $asOfDate }}">
            </div>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="bi bi-filter me-1"></i> Tampilkan
            </button>
        </form>

        <button class="btn btn-sm btn-light-info" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Neraca Saldo
        </button>
    </div>

    <div class="card-body p-4">
        <div class="bg-light border border-gray-200 rounded-3 p-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="fs-5 fw-bold text-gray-900 m-0">Neraca Saldo Per {{ date('d F Y', strtotime($asOfDate)) }}</h4>
                <div class="fs-7 text-muted mt-1">Ringkasan keseimbangan saldo debit dan kredit seluruh bagan akun (COA)</div>
            </div>

            <div>
                @if($report['is_balanced'])
                    <span class="badge badge-light-success fw-bold px-3 py-2">
                        <i class="bi bi-check-circle-fill me-1 text-success"></i> SEIMBANG (BALANCED)
                    </span>
                @else
                    <span class="badge badge-light-danger fw-bold px-3 py-2">
                        <i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> TIDAK SEIMBANG
                    </span>
                @endif
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-3 py-3" style="width: 15%;">Kode Akun</th>
                        <th style="width: 45%;">Nama Akun COA</th>
                        <th style="width: 15%;">Klasifikasi</th>
                        <th class="text-end" style="width: 12.5%;">Saldo Debit (Rp)</th>
                        <th class="text-end pe-3" style="width: 12.5%;">Saldo Kredit (Rp)</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @foreach($report['accounts'] as $acc)
                    <tr>
                        <td class="ps-3"><code>{{ $acc['code'] }}</code></td>
                        <td>
                            <a href="{{ route('accounting.reports.general-ledger', ['account_id' => $acc['id']]) }}" class="text-primary fw-bold">
                                {{ $acc['name'] }}
                            </a>
                        </td>
                        <td><span class="badge badge-light fw-semibold">{{ ucfirst($acc['type']) }}</span></td>
                        <td class="text-end fw-bold text-success">
                            {{ $acc['debit'] > 0 ? number_format($acc['debit'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-end pe-3 fw-bold text-primary">
                            {{ $acc['credit'] > 0 ? number_format($acc['credit'], 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="bg-light fw-bold fs-6">
                        <td colspan="3" class="text-end ps-3 text-gray-800">TOTAL NERACA SALDO:</td>
                        <td class="text-end text-success fs-5">Rp {{ number_format($report['total_debit'], 0, ',', '.') }}</td>
                        <td class="text-end pe-3 text-primary fs-5">Rp {{ number_format($report['total_credit'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
