@extends('layouts.app')

@section('title', 'Laporan Aging Hutang (AP Aging)')
@section('page_title', 'Laporan Umur Hutang Usaha (AP Aging Report)')

@section('content')
<div class="card card-flush shadow-sm mb-4">
    <div class="card-header align-items-center py-4 gap-2 gap-md-5">
        <div class="card-title">
            <h3 class="card-label fw-bolder text-gray-800 fs-4">
                <i class="bi bi-clock-history text-danger me-2 fs-3"></i> Laporan Aging Hutang Usaha (Accounts Payable)
            </h3>
        </div>
        <div class="card-toolbar d-flex gap-2">
            <form method="GET" action="{{ route('accounting.reports.ap-aging') }}" class="d-flex align-items-center gap-2">
                <label class="fs-7 fw-semibold text-gray-700 text-nowrap">Per Tanggal:</label>
                <input type="date" name="as_of_date" class="form-control form-control-sm w-150px" value="{{ $asOfDate }}" onchange="this.form.submit()">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-filter me-1"></i> Tampilkan
                </button>
            </form>
            <button class="btn btn-sm btn-light-info fw-semibold" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Aging Summary Cards -->
    <div class="card-body pt-0">
        <div class="row g-3 mb-4">
            <div class="col-md-2 col-6">
                <div class="border border-gray-300 rounded p-3 bg-light text-center">
                    <span class="fs-8 fw-bold text-muted text-uppercase d-block">Belum Jatuh Tempo</span>
                    <span class="fs-5 fw-bolder text-success">Rp {{ number_format($report['grand_totals']['current'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="border border-gray-300 rounded p-3 bg-light text-center">
                    <span class="fs-8 fw-bold text-muted text-uppercase d-block">1 - 30 Hari</span>
                    <span class="fs-5 fw-bolder text-warning">Rp {{ number_format($report['grand_totals']['days_1_30'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="border border-gray-300 rounded p-3 bg-light text-center">
                    <span class="fs-8 fw-bold text-muted text-uppercase d-block">31 - 60 Hari</span>
                    <span class="fs-5 fw-bolder text-warning">Rp {{ number_format($report['grand_totals']['days_31_60'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="border border-gray-300 rounded p-3 bg-light text-center">
                    <span class="fs-8 fw-bold text-muted text-uppercase d-block">61 - 90 Hari</span>
                    <span class="fs-5 fw-bolder text-danger">Rp {{ number_format($report['grand_totals']['days_61_90'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="border border-gray-300 rounded p-3 bg-light text-center">
                    <span class="fs-8 fw-bold text-muted text-uppercase d-block">> 90 Hari</span>
                    <span class="fs-5 fw-bolder text-danger">Rp {{ number_format($report['grand_totals']['days_over_90'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-2 col-12">
                <div class="border border-danger rounded p-3 bg-light-danger text-center">
                    <span class="fs-8 fw-bold text-danger text-uppercase d-block">Total Hutang (AP)</span>
                    <span class="fs-5 fw-bolder text-danger">Rp {{ number_format($report['grand_totals']['total'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Aging Table Breakdown -->
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-3 py-3" style="min-width: 200px;">Nama Supplier / Pemasok</th>
                        <th class="text-end py-3">Belum Tempo</th>
                        <th class="text-end py-3">1 - 30 Hari</th>
                        <th class="text-end py-3">31 - 60 Hari</th>
                        <th class="text-end py-3">61 - 90 Hari</th>
                        <th class="text-end py-3">> 90 Hari</th>
                        <th class="text-end pe-3 py-3" style="min-width: 140px;">Total Saldo Hutang</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-700">
                    @forelse($report['suppliers'] as $sIdx => $sGroup)
                    <tr class="bg-hover-light-danger cursor-pointer border-top" data-bs-toggle="collapse" data-bs-target="#invoicesSupp{{ $sIdx }}">
                        <td class="ps-3">
                            <i class="bi bi-chevron-down text-danger me-2 fs-8"></i>
                            <span class="fw-bolder text-gray-900 fs-6">{{ $sGroup['supplier']->name }}</span>
                            <span class="text-muted fs-8">({{ $sGroup['supplier']->code }}) - {{ count($sGroup['invoices']) }} Tagihan</span>
                        </td>
                        <td class="text-end {{ $sGroup['current'] > 0 ? 'text-success fw-bold' : 'text-muted' }}">
                            Rp {{ number_format($sGroup['current'], 0, ',', '.') }}
                        </td>
                        <td class="text-end {{ $sGroup['days_1_30'] > 0 ? 'text-warning fw-bold' : 'text-muted' }}">
                            Rp {{ number_format($sGroup['days_1_30'], 0, ',', '.') }}
                        </td>
                        <td class="text-end {{ $sGroup['days_31_60'] > 0 ? 'text-warning fw-bold' : 'text-muted' }}">
                            Rp {{ number_format($sGroup['days_31_60'], 0, ',', '.') }}
                        </td>
                        <td class="text-end {{ $sGroup['days_61_90'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                            Rp {{ number_format($sGroup['days_61_90'], 0, ',', '.') }}
                        </td>
                        <td class="text-end {{ $sGroup['days_over_90'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                            Rp {{ number_format($sGroup['days_over_90'], 0, ',', '.') }}
                        </td>
                        <td class="text-end pe-3">
                            <span class="fw-bolder text-danger fs-6">Rp {{ number_format($sGroup['total'], 0, ',', '.') }}</span>
                        </td>
                    </tr>
                    
                    <!-- Invoice Breakdown Accordion -->
                    <tr class="collapse show" id="invoicesSupp{{ $sIdx }}">
                        <td colspan="7" class="p-0 bg-light">
                            <div class="p-3 ps-5">
                                <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                                    <thead class="bg-gray-100 text-muted fw-bold">
                                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                            <th>No. Tagihan Sistem</th>
                                            <th>No. Faktur Supplier</th>
                                            <th>Tgl Tagihan</th>
                                            <th>Jatuh Tempo</th>
                                            <th>Umur (Hari)</th>
                                            <th class="text-end">Nilai Tagihan</th>
                                            <th class="text-end">Sudah Dibayar</th>
                                            <th class="text-end">Sisa Hutang</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sGroup['invoices'] as $inv)
                                        <tr>
                                            <td>
                                                <a href="{{ route('procurement.invoices.show', $inv['id']) }}" class="fw-bold text-danger text-hover-underline">
                                                    {{ $inv['invoice_number'] }}
                                                </a>
                                            </td>
                                            <td>{{ $inv['supplier_invoice_number'] ?: '-' }}</td>
                                            <td>{{ $inv['invoice_date'] }}</td>
                                            <td><span class="{{ $inv['days_overdue'] > 0 ? 'text-danger fw-bold' : '' }}">{{ $inv['due_date'] }}</span></td>
                                            <td>
                                                @if($inv['days_overdue'] > 0)
                                                    <span class="badge badge-light-danger">{{ $inv['days_overdue'] }} Hari Lewat</span>
                                                @else
                                                    <span class="badge badge-light-success">Belum Jatuh Tempo</span>
                                                @endif
                                            </td>
                                            <td class="text-end">Rp {{ number_format($inv['grand_total'], 0, ',', '.') }}</td>
                                            <td class="text-end text-success">Rp {{ number_format($inv['paid_amount'], 0, ',', '.') }}</td>
                                            <td class="text-end fw-bold text-danger">Rp {{ number_format($inv['remaining'], 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('procurement.invoices.show', $inv['id']) }}" class="btn btn-xs btn-light-danger py-0 px-2">
                                                    Lihat
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-check2-circle text-success fs-2x d-block mb-2"></i>
                            Tidak ada saldo hutang supplier outstanding per tanggal {{ date('d/m/Y', strtotime($asOfDate)) }}. Semua tagihan telah lunas!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($report['suppliers']) > 0)
                <tfoot class="bg-light-danger fw-bolder text-gray-900 fs-7 border-top border-2 border-danger">
                    <tr>
                        <td class="ps-3 py-3">TOTAL KESELURUHAN HUTANG</td>
                        <td class="text-end py-3 text-success">Rp {{ number_format($report['grand_totals']['current'], 0, ',', '.') }}</td>
                        <td class="text-end py-3 text-warning">Rp {{ number_format($report['grand_totals']['days_1_30'], 0, ',', '.') }}</td>
                        <td class="text-end py-3 text-warning">Rp {{ number_format($report['grand_totals']['days_31_60'], 0, ',', '.') }}</td>
                        <td class="text-end py-3 text-danger">Rp {{ number_format($report['grand_totals']['days_61_90'], 0, ',', '.') }}</td>
                        <td class="text-end py-3 text-danger">Rp {{ number_format($report['grand_totals']['days_over_90'], 0, ',', '.') }}</td>
                        <td class="text-end pe-3 py-3 text-danger fs-5">Rp {{ number_format($report['grand_totals']['total'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
