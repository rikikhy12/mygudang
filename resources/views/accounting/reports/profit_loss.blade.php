@extends('layouts.app')

@section('title', 'Laporan Laba Rugi (Profit & Loss)')
@section('page_title', 'Laporan Laba Rugi Komprehensif (Income Statement / Profit & Loss)')

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
    .subtotal-row {
        background-color: #f1f5f9;
        font-weight: 700;
        border-top: 1px solid #cbd5e1;
        border-bottom: 2px solid #94a3b8;
    }
    .grand-total-row {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #ffffff;
        font-weight: 800;
        border-radius: 0.75rem;
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
    <!-- Filter & Action Bar -->
    <div class="report-card p-4" id="filterSection">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <h2 class="fs-4 fw-bold text-gray-900 mb-1">
                    <i class="bi bi-file-earmark-bar-graph text-primary me-2"></i> Laporan Laba Rugi (Profit & Loss)
                </h2>
                <p class="text-muted fs-7 mb-0">Evaluasi kinerja pendapatan, harga pokok penjualan, beban operasional, dan laba bersih perusahaan</p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <form method="GET" action="{{ route('accounting.reports.profit-loss') }}" class="d-flex align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-1 fs-7">
                        <span class="text-muted fw-semibold">Dari:</span>
                        <input type="date" name="start_date" id="startDateInput" class="form-control form-control-sm" style="width: 140px;" value="{{ $startDate }}">
                    </div>

                    <div class="d-flex align-items-center gap-1 fs-7">
                        <span class="text-muted fw-semibold">Sampai:</span>
                        <input type="date" name="end_date" id="endDateInput" class="form-control form-control-sm" style="width: 140px;" value="{{ $endDate }}">
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

        <!-- Quick Filter Buttons -->
        <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
            <span class="text-muted fs-8 fw-semibold align-self-center me-1">Periode Cepat:</span>
            <button type="button" class="btn btn-xs btn-light fs-8 py-1 px-3" onclick="setPeriod('this_month')">Bulan Ini</button>
            <button type="button" class="btn btn-xs btn-light fs-8 py-1 px-3" onclick="setPeriod('last_month')">Bulan Lalu</button>
            <button type="button" class="btn btn-xs btn-light fs-8 py-1 px-3" onclick="setPeriod('this_quarter')">Kuartal Ini</button>
            <button type="button" class="btn btn-xs btn-light fs-8 py-1 px-3" onclick="setPeriod('this_year')">Tahun Berjalan (YTD)</button>
        </div>
    </div>

    <!-- Summary KPI Ribbon -->
    <div class="row g-3">
        <div class="col-xl-3 col-sm-6">
            <div class="report-card p-3 border-start border-4 border-primary">
                <span class="text-muted fs-8 fw-bold text-uppercase">Total Pendapatan</span>
                <div class="fs-4 fw-bolder text-gray-900 mt-1">Rp {{ number_format($report['total_revenue'], 0, ',', '.') }}</div>
                <span class="badge badge-light-primary fs-9 mt-1"><i class="bi bi-arrow-up-right me-1"></i> Gross Revenue</span>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="report-card p-3 border-start border-4 border-warning">
                <span class="text-muted fs-8 fw-bold text-uppercase">Harga Pokok Penjualan</span>
                <div class="fs-4 fw-bolder text-warning mt-1">Rp {{ number_format($report['total_cogs'], 0, ',', '.') }}</div>
                <span class="badge badge-light-warning fs-9 mt-1">COGS & Biaya Pokok</span>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="report-card p-3 border-start border-4 border-danger">
                <span class="text-muted fs-8 fw-bold text-uppercase">Beban Operasional</span>
                <div class="fs-4 fw-bolder text-danger mt-1">Rp {{ number_format($report['total_expense'], 0, ',', '.') }}</div>
                <span class="badge badge-light-danger fs-9 mt-1">Total Biaya Usaha</span>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="report-card p-3 border-start border-4 {{ $report['net_profit'] >= 0 ? 'border-success' : 'border-danger' }}">
                <span class="text-muted fs-8 fw-bold text-uppercase">Laba Bersih (Net Profit)</span>
                <div class="fs-4 fw-bolder {{ $report['net_profit'] >= 0 ? 'text-success' : 'text-danger' }} mt-1">
                    Rp {{ number_format($report['net_profit'], 0, ',', '.') }}
                </div>
                @php 
                    $margin = $report['total_revenue'] > 0 ? ($report['net_profit'] / $report['total_revenue']) * 100 : 0; 
                @endphp
                <span class="badge {{ $report['net_profit'] >= 0 ? 'badge-light-success text-success' : 'badge-light-danger text-danger' }} fs-9 mt-1">
                    Margin: {{ number_format($margin, 1) }}%
                </span>
            </div>
        </div>
    </div>

    <!-- Full-Width Statement Card -->
    <div class="report-card">
        <!-- Formal Report Header -->
        <div class="p-4 text-center border-bottom bg-light bg-opacity-50">
            <div class="w-40px h-40px rounded-3 bg-primary text-white d-inline-flex align-items-center justify-content-center mb-2 shadow-sm">
                <i class="bi bi-bar-chart-line-fill fs-4"></i>
            </div>
            <h3 class="fs-4 fw-bolder text-gray-900 mb-1 text-uppercase tracking-wide">LAPORAN LABA RUGI KOMPREHENSIF</h3>
            <div class="fs-7 text-muted fw-semibold">
                Periode Pelaporan: <strong class="text-gray-800">{{ date('d F Y', strtotime($startDate)) }}</strong> s/d <strong class="text-gray-800">{{ date('d F Y', strtotime($endDate)) }}</strong>
            </div>
            <span class="badge badge-light-secondary fs-8 mt-2">Mata Uang: Indonesian Rupiah (IDR)</span>
        </div>

        <div class="p-4">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                    <!-- 1. PENDAPATAN OPERASIONAL -->
                    <thead>
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                            <th class="ps-4 py-3 fs-7 fw-bold text-uppercase text-primary" style="width: 70%;">
                                <i class="bi bi-caret-right-fill me-1"></i> I. PENDAPATAN OPERASIONAL (REVENUE)
                            </th>
                            <th class="text-end pe-4 py-3 fs-7 fw-bold text-uppercase text-primary" style="width: 30%;">
                                Nominal (Rp)
                            </th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-700">
                        @forelse($report['revenues'] as $rev)
                        <tr>
                            <td class="ps-5">
                                <code class="text-primary fw-bold me-2">{{ $rev['code'] }}</code>
                                <span>{{ $rev['name'] }}</span>
                            </td>
                            <td class="text-end pe-4 fw-bold text-gray-900">
                                Rp {{ number_format($rev['amount'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="ps-5 text-muted fst-italic py-3">
                                Tidak ada transaksi pendapatan pada periode yang dipilih.
                            </td>
                        </tr>
                        @endforelse
                        <tr class="subtotal-row text-primary">
                            <td class="ps-4 py-3 text-uppercase">TOTAL PENDAPATAN OPERASIONAL</td>
                            <td class="text-end pe-4 py-3 fs-6">
                                Rp {{ number_format($report['total_revenue'], 0, ',', '.') }}
                            </td>
                        </tr>

                        <!-- Spacer -->
                        <tr><td colspan="2" class="py-3 border-0"></td></tr>

                        <!-- 2. HARGA POKOK PENJUALAN (HPP) -->
                        <tr class="section-header-row">
                            <th class="ps-4 py-3 fs-7 fw-bold text-uppercase text-warning">
                                <i class="bi bi-caret-right-fill me-1"></i> II. HARGA POKOK PENJUALAN (COST OF GOODS SOLD)
                            </th>
                            <th class="text-end pe-4 py-3 fs-7 fw-bold text-uppercase text-warning">
                                Nominal (Rp)
                            </th>
                        </tr>
                        @forelse($report['cogs'] as $cg)
                        <tr>
                            <td class="ps-5">
                                <code class="text-warning fw-bold me-2">{{ $cg['code'] }}</code>
                                <span>{{ $cg['name'] }}</span>
                            </td>
                            <td class="text-end pe-4 fw-bold text-gray-900">
                                Rp {{ number_format($cg['amount'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="ps-5 text-muted fst-italic py-3">
                                Tidak ada catatan HPP pada periode ini.
                            </td>
                        </tr>
                        @endforelse
                        <tr class="subtotal-row text-warning">
                            <td class="ps-4 py-3 text-uppercase">TOTAL HARGA POKOK PENJUALAN (HPP)</td>
                            <td class="text-end pe-4 py-3 fs-6">
                                (Rp {{ number_format($report['total_cogs'], 0, ',', '.') }})
                            </td>
                        </tr>

                        <!-- 3. LABA KOTOR (GROSS PROFIT) -->
                        <tr class="bg-light-info text-info fw-bold fs-6 border-top border-bottom border-info">
                            <td class="ps-4 py-3">
                                <i class="bi bi-check2-circle me-1"></i> LABA KOTOR (GROSS PROFIT)
                            </td>
                            <td class="text-end pe-4 py-3 fs-5 fw-bolder">
                                Rp {{ number_format($report['gross_profit'], 0, ',', '.') }}
                            </td>
                        </tr>

                        <!-- Spacer -->
                        <tr><td colspan="2" class="py-3 border-0"></td></tr>

                        <!-- 4. BEBAN OPERASIONAL & UMUM -->
                        <tr class="section-header-row">
                            <th class="ps-4 py-3 fs-7 fw-bold text-uppercase text-danger">
                                <i class="bi bi-caret-right-fill me-1"></i> III. BEBAN OPERASIONAL & UMUM (EXPENSES)
                            </th>
                            <th class="text-end pe-4 py-3 fs-7 fw-bold text-uppercase text-danger">
                                Nominal (Rp)
                            </th>
                        </tr>
                        @forelse($report['expenses'] as $exp)
                        <tr>
                            <td class="ps-5">
                                <code class="text-danger fw-bold me-2">{{ $exp['code'] }}</code>
                                <span>{{ $exp['name'] }}</span>
                            </td>
                            <td class="text-end pe-4 fw-bold text-gray-900">
                                Rp {{ number_format($exp['amount'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="ps-5 text-muted fst-italic py-3">
                                Tidak ada beban operasional tercatat pada periode ini.
                            </td>
                        </tr>
                        @endforelse
                        <tr class="subtotal-row text-danger">
                            <td class="ps-4 py-3 text-uppercase">TOTAL BEBAN OPERASIONAL</td>
                            <td class="text-end pe-4 py-3 fs-6">
                                (Rp {{ number_format($report['total_expense'], 0, ',', '.') }})
                            </td>
                        </tr>

                        <!-- Spacer -->
                        <tr><td colspan="2" class="py-3 border-0"></td></tr>

                        <!-- 5. LABA BERSIH (NET PROFIT) -->
                        <tr class="{{ $report['net_profit'] >= 0 ? 'bg-success' : 'bg-danger' }} text-white fw-bold fs-5">
                            <td class="ps-4 py-4 rounded-start">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-award-fill fs-3 me-2"></i>
                                    <div>
                                        <div class="fs-5 fw-bolder text-uppercase">LABA BERSIH PERIODE BERJALAN (NET PROFIT)</div>
                                        <div class="fs-8 text-white-50 fw-normal">Laba Kotor dikurangi Total Beban Operasional</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end pe-4 py-4 rounded-end fs-3 fw-bolder">
                                Rp {{ number_format($report['net_profit'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    function setPeriod(type) {
        const today = new Date();
        let start = new Date();
        let end = new Date();

        if (type === 'this_month') {
            start = new Date(today.getFullYear(), today.getMonth(), 1);
            end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        } else if (type === 'last_month') {
            start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            end = new Date(today.getFullYear(), today.getMonth(), 0);
        } else if (type === 'this_quarter') {
            const quarter = Math.floor((today.getMonth() / 3));
            start = new Date(today.getFullYear(), quarter * 3, 1);
            end = new Date(today.getFullYear(), (quarter + 1) * 3, 0);
        } else if (type === 'this_year') {
            start = new Date(today.getFullYear(), 0, 1);
            end = new Date(today.getFullYear(), 11, 31);
        }

        const formatDate = d => d.toISOString().split('T')[0];
        document.getElementById('startDateInput').value = formatDate(start);
        document.getElementById('endDateInput').value = formatDate(end);
        document.getElementById('startDateInput').form.submit();
    }
</script>
@endsection
@endsection
