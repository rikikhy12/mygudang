@extends('layouts.app')

@section('title', 'Laporan Neraca (Balance Sheet)')
@section('page_title', 'Laporan Posisi Keuangan (Neraca / Balance Sheet)')

@section('styles')
<style>
    .report-card {
        background: #ffffff;
        border: 1px solid #eef2f6;
        border-radius: 1.15rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .report-table tbody tr:hover {
        background-color: #f8fafc !important;
    }
    .section-header-row {
        background-color: #f8fafc;
        border-top: 2px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
    }
    @media print {
        #kt_app_sidebar, #kt_app_header, #filterSection, #printBtn, .sidebar-backdrop {
            display: none !important;
        }
        #kt_app_main {
            margin-left: 0 !important;
            width: 100% !important;
        }
        .report-card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">
    <!-- Filter Bar -->
    <div class="report-card p-4" id="filterSection">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <h2 class="fs-4 fw-bold text-gray-900 mb-1">
                    <i class="bi bi-file-earmark-spreadsheet text-primary me-2"></i> Neraca & Posisi Keuangan (Balance Sheet)
                </h2>
                <p class="text-muted fs-7 mb-0">Laporan aset, liabilitas, dan ekuitas modal perusahaan pada tanggal tertentu</p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <form method="GET" action="{{ route('accounting.reports.balance-sheet') }}" class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2 fs-7">
                        <span class="text-muted fw-semibold">Per Tanggal:</span>
                        <input type="date" name="as_of_date" class="form-control form-control-sm" style="width: 150px;" value="{{ $asOfDate }}" />
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold">
                        <i class="bi bi-filter me-1"></i> Tampilkan
                    </button>
                </form>

                <button class="btn btn-sm btn-light-primary fw-bold" id="printBtn" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Cetak / PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Summary KPI Ribbon -->
    <div class="row g-3">
        <div class="col-xl-4 col-md-6">
            <div class="report-card p-3 border-start border-4 border-primary">
                <span class="text-muted fs-8 fw-bold text-uppercase">Total Aset (Aktiva)</span>
                <div class="fs-4 fw-bolder text-primary mt-1">Rp {{ number_format($report['total_assets'], 0, ',', '.') }}</div>
                <span class="badge badge-light-primary fs-9 mt-1">Kas, Piutang, Stok & Aset Tetap</span>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="report-card p-3 border-start border-4 border-warning">
                <span class="text-muted fs-8 fw-bold text-uppercase">Total Kewajiban (Hutang)</span>
                <div class="fs-4 fw-bolder text-warning mt-1">Rp {{ number_format($report['total_liabilities'], 0, ',', '.') }}</div>
                <span class="badge badge-light-warning fs-9 mt-1">Hutang Usaha & Pajak</span>
            </div>
        </div>

        <div class="col-xl-4 col-md-12">
            <div class="report-card p-3 border-start border-4 border-info">
                <span class="text-muted fs-8 fw-bold text-uppercase">Total Ekuitas (Modal Bersih)</span>
                <div class="fs-4 fw-bolder text-info mt-1">Rp {{ number_format($report['total_equity'], 0, ',', '.') }}</div>
                <span class="badge badge-light-info fs-9 mt-1">Modal Disetor & Laba Ditahan</span>
            </div>
        </div>
    </div>

    <!-- Full-Width Statement Card -->
    <div class="report-card">
        <!-- Formal Header -->
        <div class="p-4 text-center border-bottom bg-light bg-opacity-50">
            <h3 class="fs-4 fw-bolder text-gray-900 mb-1 text-uppercase">NERACA / LAPORAN POSISI KEUANGAN</h3>
            <div class="fs-7 text-muted fw-semibold">
                Per Tanggal: <strong class="text-gray-800">{{ date('d F Y', strtotime($asOfDate)) }}</strong>
            </div>
            <div class="mt-2">
                @if($report['is_balanced'])
                    <span class="badge badge-light-success fs-7 fw-bold px-3 py-2 border border-success border-opacity-25">
                        <i class="bi bi-check2-circle text-success me-1"></i> NERACA SEIMBANG (ASSETS = LIABILITIES + EQUITY)
                    </span>
                @else
                    <span class="badge badge-light-danger fs-7 fw-bold px-3 py-2 border border-danger border-opacity-25">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> NERACA TIDAK SEIMBANG
                    </span>
                @endif
            </div>
        </div>

        <div class="p-4">
            <div class="row g-4 align-items-start">
                <!-- LEFT COLUMN: ASET -->
                <div class="col-lg-6">
                    <div class="card bg-light bg-opacity-25 border rounded-3 p-3">
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                                <thead>
                                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                        <th colspan="2" class="p-3 fw-bold fs-6 text-uppercase text-primary">
                                            <i class="bi bi-caret-right-fill me-1"></i> ASET (AKTIVA)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-700">
                                    @forelse($report['assets'] as $asset)
                                    <tr>
                                        <td class="ps-3 py-2">
                                            <code class="text-primary me-2">{{ $asset['code'] }}</code>
                                            <span>{{ $asset['name'] }}</span>
                                        </td>
                                        <td class="text-end pe-3 py-2 fw-bold text-gray-900">
                                            Rp {{ number_format($asset['amount'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">Tidak ada akun aset.</td>
                                    </tr>
                                    @endforelse

                                    <tr class="bg-primary text-white border-top">
                                        <td class="p-3 fw-bolder text-white fs-6">TOTAL ASET</td>
                                        <td class="p-3 text-end fw-bolder text-white fs-5">
                                            Rp {{ number_format($report['total_assets'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: LIABILITAS & EKUITAS -->
                <div class="col-lg-6">
                    <!-- Liabilitas -->
                    <div class="card bg-light bg-opacity-25 border rounded-3 p-3 mb-4">
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                                <thead>
                                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                        <th colspan="2" class="p-3 fw-bold fs-6 text-uppercase text-warning">
                                            <i class="bi bi-caret-right-fill me-1"></i> KEWAJIBAN / LIABILITAS (PASIVA)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-700">
                                    @forelse($report['liabilities'] as $liab)
                                    <tr>
                                        <td class="ps-3 py-2">
                                            <code class="text-warning me-2">{{ $liab['code'] }}</code>
                                            <span>{{ $liab['name'] }}</span>
                                        </td>
                                        <td class="text-end pe-3 py-2 fw-bold text-gray-900">
                                            Rp {{ number_format($liab['amount'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">Tidak ada akun kewajiban.</td>
                                    </tr>
                                    @endforelse

                                    <tr class="bg-light-warning text-warning border-top border-warning fw-bold">
                                        <td class="p-3 text-gray-900">TOTAL KEWAJIBAN</td>
                                        <td class="p-3 text-end text-gray-900 fs-6">
                                            Rp {{ number_format($report['total_liabilities'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Ekuitas -->
                    <div class="card bg-light bg-opacity-25 border rounded-3 p-3">
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                                <thead>
                                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                        <th colspan="2" class="p-3 fw-bold fs-6 text-uppercase text-info">
                                            <i class="bi bi-caret-right-fill me-1"></i> EKUITAS / MODAL
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-700">
                                    @foreach($report['equities'] as $eq)
                                    <tr>
                                        <td class="ps-3 py-2">
                                            <code class="text-info me-2">{{ $eq['code'] }}</code>
                                            <span>{{ $eq['name'] }}</span>
                                        </td>
                                        <td class="text-end pe-3 py-2 fw-bold text-gray-900">
                                            Rp {{ number_format($eq['amount'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach

                                    <tr class="bg-light-info text-info border-top border-info fw-bold">
                                        <td class="p-3 text-gray-900">TOTAL EKUITAS</td>
                                        <td class="p-3 text-end text-gray-900 fs-6">
                                            Rp {{ number_format($report['total_equity'], 0, ',', '.') }}
                                        </td>
                                    </tr>

                                    <tr class="bg-dark text-white border-top border-2">
                                        <td class="p-3 fw-bolder text-white fs-6">TOTAL LIABILITAS & EKUITAS</td>
                                        <td class="p-3 text-end fw-bolder text-white fs-5">
                                            Rp {{ number_format($report['total_liabilities_and_equity'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
