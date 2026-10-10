@extends('layouts.app')

@section('title', 'Laporan Rekap Komisi Sales')
@section('page_title', 'Laporan Rekap & Perhitungan Komisi Sales')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <!-- Header Action Bar -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Laporan & Rekap Komisi Sales</h2>
            <p class="text-muted fs-7 mb-0">Pantau omset penjualan per staf sales, persentase komisi, dan nominal komisi yang berhak dicairkan</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.reports.sales-summary') }}" class="btn btn-sm btn-light-primary fw-semibold">
                <i class="bi bi-bar-chart-line me-1"></i> Laporan Omset Penjualan
            </a>
            <button type="button" class="btn btn-sm btn-primary fw-bold" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('sales.reports.commissions') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fs-8 fw-bold text-gray-700">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control form-control-solid form-control-sm" value="{{ $startDate }}" />
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-8 fw-bold text-gray-700">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control form-control-solid form-control-sm" value="{{ $endDate }}" />
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-8 fw-bold text-gray-700">Staf Sales</label>
                    <select name="salesperson_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Semua Sales --">
                        <option value="">-- Semua Sales --</option>
                        @foreach($salespersons as $sp)
                            <option value="{{ $sp->id }}" {{ $salespersonId == $sp->id ? 'selected' : '' }}>
                                {{ $sp->name }} ({{ number_format($sp->commission_rate ?? 0, 1) }}%)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold w-100">
                        <i class="bi bi-filter me-1"></i> Filter Laporan
                    </button>
                    @if(request()->hasAny(['start_date', 'end_date', 'salesperson_id', 'branch_id']))
                        <a href="{{ route('sales.reports.commissions') }}" class="btn btn-light btn-sm fw-semibold">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-light-primary border-primary border-dashed">
                <div class="card-body p-4">
                    <div class="text-primary fw-bold fs-7 text-uppercase mb-1">Total Omset Penjualan (Subtotal)</div>
                    <div class="fs-2tx fw-bolder text-gray-900">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
                    <div class="text-muted fs-8 mt-1">Dari {{ number_format($totalInvoicesCount, 0) }} Faktur Penjualan Sales</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-light-success border-success border-dashed">
                <div class="card-body p-4">
                    <div class="text-success fw-bold fs-7 text-uppercase mb-1">Total Hak Komisi Sales</div>
                    <div class="fs-2tx fw-bolder text-success">Rp {{ number_format($totalCommission, 0, ',', '.') }}</div>
                    <div class="text-muted fs-8 mt-1">Estimasi komisi yang berhak dicairkan</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-light-info border-info border-dashed">
                <div class="card-body p-4">
                    <div class="text-info fw-bold fs-7 text-uppercase mb-1">Jumlah Sales Aktif Bertransaksi</div>
                    <div class="fs-2tx fw-bolder text-gray-900">{{ count($salesSummary) }} Orang</div>
                    <div class="text-muted fs-8 mt-1">Periode: {{ date('d M Y', strtotime($startDate)) }} - {{ date('d M Y', strtotime($endDate)) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary per Sales Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent border-bottom py-3">
            <h3 class="card-title fs-5 fw-bold text-gray-900 m-0">
                <i class="bi bi-person-badge text-primary me-2"></i> Rekapitulasi Komisi Per Tenaga Penjual (Salesperson)
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-6 gy-3 mb-0">
                    <thead class="table-light">
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light">
                            <th class="ps-6">Nama Staf Sales</th>
                            <th class="text-center">Rate Komisi (%)</th>
                            <th class="text-center">Jml Faktur</th>
                            <th class="text-end">Total Penjualan (Rp)</th>
                            <th class="text-end pe-6">Total Komisi (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-700 fs-7">
                        @forelse($salesSummary as $sum)
                        <tr>
                            <td class="ps-6">
                                <div class="fw-bold text-gray-900">{{ $sum->salesperson->name ?? 'Sales #'.$sum->salesperson_id }}</div>
                                <span class="text-muted fs-9">{{ $sum->salesperson->email ?? '' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light-primary fw-bold fs-8">{{ number_format($sum->avg_rate, 2) }}%</span>
                            </td>
                            <td class="text-center">{{ number_format($sum->total_invoices, 0) }} Faktur</td>
                            <td class="text-end fw-bold text-gray-900">
                                Rp {{ number_format($sum->total_subtotal, 0, ',', '.') }}
                            </td>
                            <td class="text-end pe-6 fw-bolder text-success fs-6">
                                Rp {{ number_format($sum->total_commission, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-muted">
                                Tidak ada data komisi sales pada periode filter yang dipilih.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detailed Invoices Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent border-bottom py-3">
            <h3 class="card-title fs-5 fw-bold text-gray-900 m-0">
                <i class="bi bi-receipt-cutoff text-success me-2"></i> Rincian Faktur Penjualan & Komisi
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-6 gy-3 mb-0">
                    <thead class="table-light">
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light">
                            <th class="ps-6">No. Faktur</th>
                            <th>Tanggal</th>
                            <th>Staf Sales</th>
                            <th>Pelanggan</th>
                            <th class="text-end">Subtotal Penjualan</th>
                            <th class="text-center">Rate (%)</th>
                            <th class="text-end">Nominal Komisi</th>
                            <th class="text-center pe-6">Status Faktur</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-700 fs-7">
                        @forelse($invoices as $inv)
                        <tr>
                            <td class="ps-6">
                                <a href="{{ route('sales.invoices.show', $inv->id) }}" class="fw-bold text-primary">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td>{{ $inv->invoice_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge badge-light-secondary fw-bold">{{ $inv->salesperson->name ?? '-' }}</span>
                            </td>
                            <td>{{ $inv->customer->name ?? '-' }}</td>
                            <td class="text-end fw-semibold text-gray-900">
                                Rp {{ number_format($inv->subtotal, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light-info fw-semibold">{{ number_format($inv->commission_rate, 2) }}%</span>
                            </td>
                            <td class="text-end fw-bold text-success">
                                Rp {{ number_format($inv->commission_amount, 0, ',', '.') }}
                            </td>
                            <td class="text-center pe-6">
                                @if($inv->status === 'paid')
                                    <span class="badge badge-light-success fw-bold">Lunas</span>
                                @elseif($inv->status === 'partial')
                                    <span class="badge badge-light-warning fw-bold">Sebagian</span>
                                @else
                                    <span class="badge badge-light-danger fw-bold">Belum Bayar</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 text-muted">
                                Belum ada faktur penjualan dengan tenaga sales.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($invoices->hasPages())
                <div class="card-footer py-3 px-6">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
