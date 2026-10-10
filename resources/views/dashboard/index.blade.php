@extends('layouts.app')

@section('title', 'Executive Dashboard')
@section('page_title', 'Executive Dashboard')

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endsection

@section('content')
<!-- 1. Top Metric Cards (Metronic 8 Clean Style) -->
<div class="row g-5 g-xl-8 mb-5 mb-xl-8">
    <!-- Total Kas & Bank -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush shadow-sm h-100">
            <div class="card-body p-5">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-gray-500 fs-7 fw-semibold">Total Kas & Bank</span>
                    <div class="symbol symbol-30px symbol-circle bg-light-primary">
                        <span class="symbol-label"><i class="bi bi-wallet2 text-primary fs-6"></i></span>
                    </div>
                </div>
                <div class="fs-2 fw-bold text-gray-900 mb-2">
                    Rp {{ number_format($totalCash, 0, ',', '.') }}
                </div>
                <div class="d-flex justify-content-between align-items-center text-gray-500 fs-8 fw-semibold">
                    <span>Likuiditas Kas:</span>
                    <span class="badge badge-light-success fs-9 fw-bold">Real-Time</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Piutang Usaha (AR) -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush shadow-sm h-100">
            <div class="card-body p-5">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-gray-500 fs-7 fw-semibold">Piutang Usaha (AR)</span>
                    <div class="symbol symbol-30px symbol-circle bg-light-info">
                        <span class="symbol-label"><i class="bi bi-receipt-cutoff text-info fs-6"></i></span>
                    </div>
                </div>
                <div class="fs-2 fw-bold text-gray-900 mb-2">
                    Rp {{ number_format($totalAR, 0, ',', '.') }}
                </div>
                <div class="d-flex justify-content-between align-items-center text-gray-500 fs-8 fw-semibold">
                    <span>Tagihan Pelanggan:</span>
                    <span class="badge badge-light-primary fs-9 fw-bold">AR Aktif</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Hutang Usaha (AP) -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush shadow-sm h-100">
            <div class="card-body p-5">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-gray-500 fs-7 fw-semibold">Hutang Usaha (AP)</span>
                    <div class="symbol symbol-30px symbol-circle bg-light-danger">
                        <span class="symbol-label"><i class="bi bi-receipt text-danger fs-6"></i></span>
                    </div>
                </div>
                <div class="fs-2 fw-bold text-gray-900 mb-2">
                    Rp {{ number_format($totalAP, 0, ',', '.') }}
                </div>
                <div class="d-flex justify-content-between align-items-center text-gray-500 fs-8 fw-semibold">
                    <span>Kewajiban Vendor:</span>
                    <span class="badge badge-light-warning fs-9 fw-bold">AP Terbuka</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Nilai Persediaan -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush shadow-sm h-100">
            <div class="card-body p-5">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-gray-500 fs-7 fw-semibold">Nilai Persediaan</span>
                    <div class="symbol symbol-30px symbol-circle bg-light-success">
                        <span class="symbol-label"><i class="bi bi-boxes text-success fs-6"></i></span>
                    </div>
                </div>
                <div class="fs-2 fw-bold text-gray-900 mb-2">
                    Rp {{ number_format($totalInventory, 0, ',', '.') }}
                </div>
                <div class="d-flex justify-content-between align-items-center text-gray-500 fs-8 fw-semibold">
                    <span>Valuasi Fisik:</span>
                    <span class="badge badge-light-info fs-9 fw-bold">Stok Gudang</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. Middle Overview Cards (Metronic 8 Style) -->
<div class="row g-5 g-xl-8 mb-5 mb-xl-8">
    <!-- Left: Rincian Operasional Finansial -->
    <div class="col-xl-6">
        <div class="card card-flush shadow-sm h-100">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <h3 class="card-label fw-bold text-gray-900 fs-5 mb-1">Rincian Operasional Finansial</h3>
                    <span class="text-gray-500 fs-8 fw-semibold">Status real-time transaksi penjualan, pembelian & laba berjalan</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('accounting.reports.profit-loss') }}" class="btn btn-sm btn-light-primary fw-semibold fs-8">
                        Laporan Laba Rugi
                    </a>
                </div>
            </div>
            <div class="card-body pt-2 pb-5">
                <div class="row g-3 mb-4">
                    <div class="col-6 col-sm-3">
                        <div class="bg-light-success rounded p-3 text-center">
                            <div class="text-success fs-5 fw-bold mb-1">Rp {{ number_format($monthSales, 0, ',', '.') }}</div>
                            <span class="text-gray-700 fs-8 fw-bold">Penjualan Bulan Ini</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="bg-light-primary rounded p-3 text-center">
                            <div class="text-primary fs-5 fw-bold mb-1">Rp {{ number_format($monthPurchases, 0, ',', '.') }}</div>
                            <span class="text-gray-700 fs-8 fw-bold">Pembelian Bulan Ini</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="bg-light-warning rounded p-3 text-center">
                            <div class="text-warning fs-5 fw-bold mb-1">Rp {{ number_format($monthExpenses, 0, ',', '.') }}</div>
                            <span class="text-gray-700 fs-8 fw-bold">Beban Operasional</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="bg-light-danger rounded p-3 text-center">
                            <div class="{{ $monthNetProfit >= 0 ? 'text-success' : 'text-danger' }} fs-5 fw-bold mb-1">Rp {{ number_format($monthNetProfit, 0, ',', '.') }}</div>
                            <span class="text-gray-700 fs-8 fw-bold">Laba Bersih</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="badge badge-light-primary fs-8 fw-bold"><i class="bi bi-circle-fill fs-9 text-primary me-1"></i> SO Berjalan: {{ $recentSales->count() }}</span>
                    <span class="badge badge-light-success fs-8 fw-bold"><i class="bi bi-circle-fill fs-9 text-success me-1"></i> PO Berjalan: {{ $recentPurchases->count() }}</span>
                    <span class="badge badge-light-warning fs-8 fw-bold"><i class="bi bi-circle-fill fs-9 text-warning me-1"></i> Jurnal Tercatat: {{ $recentJournals->count() }}</span>
                </div>

                <div class="separator separator-dashed mb-4"></div>

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <span class="text-gray-500 fs-8 fw-bold text-uppercase">Akses Cepat:</span>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('procurement.po.create') }}" class="btn btn-sm btn-light btn-active-light-primary fs-8 py-1 px-3 fw-bold">
                            <i class="bi bi-cart-plus me-1 text-primary"></i> Buat PO
                        </a>
                        <a href="{{ route('sales.so.create') }}" class="btn btn-sm btn-light btn-active-light-primary fs-8 py-1 px-3 fw-bold">
                            <i class="bi bi-bag-plus me-1 text-info"></i> Buat SO
                        </a>
                        <a href="{{ route('finance.cash-in.create') }}" class="btn btn-sm btn-light btn-active-light-success fs-8 py-1 px-3 fw-bold">
                            <i class="bi bi-arrow-down-left me-1 text-success"></i> Kas Masuk
                        </a>
                        <a href="{{ route('finance.cash-out.create') }}" class="btn btn-sm btn-light btn-active-light-danger fs-8 py-1 px-3 fw-bold">
                            <i class="bi bi-arrow-up-right me-1 text-danger"></i> Kas Keluar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Monitoring & Peringatan Stok Gudang -->
    <div class="col-xl-6">
        <div class="card card-flush shadow-sm h-100">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <h3 class="card-label fw-bold text-gray-900 fs-4 mb-1">
                        <i class="bi bi-shield-check text-success me-1"></i> Monitoring Stok Gudang
                    </h3>
                    <span class="text-gray-500 fs-7 fw-semibold">Monitoring persediaan barang, batas minimum & kebutuhan reorder</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('inventory.overview') }}" class="btn btn-sm btn-light-danger fw-semibold">
                        Monitoring Lengkap <i class="bi bi-chevron-right fs-8 ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body pt-2 pb-5">
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="badge badge-light-danger fs-8 fw-bold">Stok Habis: {{ $lowStockItems->where('current_stock', '<=', 0)->count() }}</span>
                    <span class="badge badge-light-warning fs-8 fw-bold">&le; Batas Min: {{ $lowStockItems->count() }}</span>
                    <span class="badge badge-light-success fs-8 fw-bold"><i class="bi bi-check2 me-1"></i> Status: Aman</span>
                </div>

                @if($lowStockItems->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                            <thead>
                                <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                    <th class="ps-3">SKU & Barang</th>
                                    <th class="text-center">Stok</th>
                                    <th class="text-center">Min</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-700">
                                @foreach($lowStockItems->take(4) as $lItem)
                                <tr>
                                    <td class="ps-3">
                                        <span class="text-gray-900 fw-bold">{{ $lItem->name }}</span>
                                        <div class="text-muted fs-9"><code>{{ $lItem->code }}</code></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $lItem->current_stock <= 0 ? 'badge-light-danger' : 'badge-light-warning' }} fw-bold fs-8">
                                            {{ number_format($lItem->current_stock, 0) }} {{ $lItem->unit->symbol ?? '' }}
                                        </span>
                                    </td>
                                    <td class="text-center text-muted">{{ number_format($lItem->min_stock, 0) }}</td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('procurement.po.create') }}" class="btn btn-xs btn-light-primary py-1 px-2 fs-9">Pesan</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="bg-light rounded p-5 text-center my-3">
                        <div class="symbol symbol-45px symbol-circle bg-light-success mb-3">
                            <i class="bi bi-check2-circle fs-2x text-success"></i>
                        </div>
                        <div class="fs-6 fw-bold text-gray-900 mb-1">Seluruh Stok Barang dalam Batas Aman</div>
                        <span class="text-gray-500 fs-7">Tidak ada persediaan barang yang berada di bawah batas minimum stok saat ini.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 3. Interactive Analytics Charts Section -->
<div class="row g-5 g-xl-8 mb-5 mb-xl-8">
    <!-- Chart 1: Sales vs Purchases Trend -->
    <div class="col-xl-8">
        <div class="card card-flush shadow-sm h-100">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <h3 class="card-label fw-bold text-gray-900 fs-4 mb-1">
                        <i class="bi bi-graph-up text-primary me-2"></i> Tren Bisnis: Penjualan vs Pembelian
                    </h3>
                    <span class="text-gray-500 fs-7 fw-semibold">Analisis komparasi omset penjualan pelanggan vs belanja pengadaan (6 Bulan Terakhir)</span>
                </div>
                <div class="card-toolbar">
                    <span class="badge badge-light-primary fs-8 fw-semibold px-3 py-1">6 Bulan</span>
                </div>
            </div>
            <div class="card-body pt-2">
                <div id="chartSalesPurchases" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Chart 2: Asset Composition Donut Chart -->
    <div class="col-xl-4">
        <div class="card card-flush shadow-sm h-100 d-flex flex-column justify-content-between">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <h3 class="card-label fw-bold text-gray-900 fs-4 mb-1">
                        <i class="bi bi-pie-chart-fill text-info me-2"></i> Komposisi Aset
                    </h3>
                    <span class="text-gray-500 fs-7 fw-semibold">Distribusi nilai Kas, Piutang, Persediaan & Aset Tetap</span>
                </div>
            </div>
            <div class="card-body pt-2 d-flex flex-column justify-content-between">
                <div id="chartAssetBreakdown" style="min-height: 230px;"></div>
                <div class="pt-3 border-top mt-2">
                    <div class="d-flex justify-content-between align-items-center text-muted fs-8">
                        <span>Total Aset Terdaftar:</span>
                        <strong class="text-gray-900 fs-7">Rp {{ number_format($totalCash + $totalAR + $totalInventory + $totalFixedAssets, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. Two Column Layout: Recent Sales & Recent GL Journals -->
<div class="row g-5 g-xl-8">
    <!-- Recent Sales Orders -->
    <div class="col-xl-6">
        <div class="card card-flush shadow-sm h-100">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <h3 class="card-label fw-bold text-gray-900 fs-4 mb-1">
                        <i class="bi bi-bag-check-fill text-primary me-2"></i> Pesanan Penjualan (SO) Terbaru
                    </h3>
                    <span class="text-gray-500 fs-7 fw-semibold">Daftar transaksi penjualan terdaftar terkini</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('sales.so.index') }}" class="btn btn-light-primary btn-sm fw-semibold">Lihat Semua</a>
                </div>
            </div>
            <div class="card-body pt-0 px-0">
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-6">No. Order</th>
                                <th>Pelanggan</th>
                                <th class="text-end">Grand Total</th>
                                <th class="text-center pe-6">Status</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-700">
                            @forelse($recentSales as $sOrder)
                            <tr>
                                <td class="ps-6">
                                    <a href="{{ route('sales.so.show', $sOrder->id) }}" class="text-gray-900 fw-bold text-hover-primary">
                                        {{ $sOrder->so_number }}
                                    </a>
                                </td>
                                <td>{{ $sOrder->customer->name ?? 'Walk-in Customer' }}</td>
                                <td class="text-end fw-bold text-gray-900">Rp {{ number_format($sOrder->grand_total, 0, ',', '.') }}</td>
                                <td class="text-center pe-6">
                                    @if($sOrder->status == 'completed')
                                        <span class="badge badge-light-success fs-9 fw-bold">Completed</span>
                                    @elseif($sOrder->status == 'processing')
                                        <span class="badge badge-light-warning fs-9 fw-bold">Processing</span>
                                    @else
                                        <span class="badge badge-light-info fs-9 fw-bold">{{ ucfirst($sOrder->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-muted fs-8">Belum ada transaksi penjualan terbaru.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent GL Journals -->
    <div class="col-xl-6">
        <div class="card card-flush shadow-sm h-100">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <h3 class="card-label fw-bold text-gray-900 fs-4 mb-1">
                        <i class="bi bi-journal-bookmark-fill text-success me-2"></i> Mutasi Jurnal Umum Terkini
                    </h3>
                    <span class="text-gray-500 fs-7 fw-semibold">Posting pembukuan akuntansi & jurnal transaksi</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('accounting.journals.index') }}" class="btn btn-light-success btn-sm fw-semibold">Lihat Semua</a>
                </div>
            </div>
            <div class="card-body pt-0 px-0">
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-6">No. Jurnal</th>
                                <th>Tanggal</th>
                                <th>Deskripsi</th>
                                <th class="text-end pe-6">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-700">
                            @forelse($recentJournals as $rJrn)
                            <tr>
                                <td class="ps-6">
                                    <a href="{{ route('accounting.journals.show', $rJrn->id) }}" class="text-gray-900 fw-bold text-hover-primary">
                                        <code>{{ $rJrn->journal_number }}</code>
                                    </a>
                                </td>
                                <td>{{ $rJrn->transaction_date->format('d/m/Y') }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($rJrn->description ?? '-', 30) }}</td>
                                <td class="text-end pe-6 fw-bold text-gray-900">Rp {{ number_format($rJrn->total_debit, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-muted fs-8">Belum ada jurnal transaksi tercatat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Sales vs Purchases Spline Area Chart
        const months = @json($chartMonths);
        const salesData = @json($chartSales);
        const purchasesData = @json($chartPurchases);

        const optionsSalesPurchases = {
            series: [{
                name: 'Penjualan (Sales)',
                data: salesData
            }, {
                name: 'Pembelian (Purchases)',
                data: purchasesData
            }],
            chart: {
                height: 310,
                type: 'area',
                toolbar: { show: false },
                fontFamily: 'Inter, system-ui, sans-serif'
            },
            colors: ['#2563eb', '#10b981'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.05,
                    stops: [0, 95, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                categories: months,
                labels: {
                    style: {
                        colors: '#64748b',
                        fontSize: '11px',
                        fontWeight: 500
                    }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(1) + ' M';
                        if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(0) + ' Jt';
                        return 'Rp ' + Number(val).toLocaleString('id-ID');
                    },
                    style: {
                        colors: '#64748b',
                        fontSize: '11px'
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return 'Rp ' + Number(val).toLocaleString('id-ID');
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                fontWeight: 600,
                fontSize: '12px',
                labels: { colors: '#334155' }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4
            }
        };

        const chart1 = new ApexCharts(document.querySelector("#chartSalesPurchases"), optionsSalesPurchases);
        chart1.render();

        // 2. Asset & Liquidity Breakdown Donut Chart
        const cashVal = {{ (float) $totalCash }};
        const arVal = {{ (float) $totalAR }};
        const invVal = {{ (float) $totalInventory }};
        const assetVal = {{ (float) $totalFixedAssets }};

        const seriesData = [cashVal, arVal, invVal, assetVal];
        const hasData = seriesData.some(v => v > 0);

        const optionsDonut = {
            series: hasData ? seriesData : [1, 1, 1, 1],
            labels: ['Kas & Bank', 'Piutang (AR)', 'Persediaan', 'Aset Tetap'],
            chart: {
                type: 'donut',
                height: 230,
                fontFamily: 'Inter, system-ui, sans-serif'
            },
            colors: ['#10b981', '#3b82f6', '#8b5cf6', '#64748b'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { fontSize: '12px', color: '#64748b' },
                            value: {
                                fontSize: '15px',
                                fontWeight: 700,
                                color: '#0f172a',
                                formatter: function (val) {
                                    return hasData ? 'Rp ' + Number(val).toLocaleString('id-ID') : 'Rp 0';
                                }
                            },
                            total: {
                                show: true,
                                label: 'Total Aset',
                                color: '#64748b',
                                fontSize: '11px',
                                formatter: function () {
                                    return hasData ? 'Rp ' + Number(cashVal + arVal + invVal + assetVal).toLocaleString('id-ID') : 'Rp 0';
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: {
                position: 'bottom',
                fontSize: '11px',
                fontWeight: 500,
                labels: { colors: '#475569' }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return 'Rp ' + Number(val).toLocaleString('id-ID');
                    }
                }
            }
        };

        const chart2 = new ApexCharts(document.querySelector("#chartAssetBreakdown"), optionsDonut);
        chart2.render();
    });
</script>
@endsection
@endsection
