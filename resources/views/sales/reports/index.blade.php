@extends('layouts.app')

@section('title', 'Laporan Penjualan Per Cabang & Kategori')
@section('page_title', 'Laporan Analisis Penjualan')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    
    <!-- Header & Filter Bar -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center">
                <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                    <i class="bi bi-bar-chart-line-fill fs-3 text-primary"></i>
                </div>
                <div>
                    <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Laporan Penjualan (Sales Summary Report)</h3>
                    <span class="text-muted fs-8 fw-semibold">Analisis komprehensif omset per cabang, kontribusi kategori, dan rincian transaksi faktur</span>
                </div>
            </div>
            
            <button type="button" class="btn btn-sm btn-light-primary fw-bold" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Cetak Laporan
            </button>
        </div>

        <div class="card-body bg-light-subtle py-3 border-top">
            <form method="GET" action="{{ route('sales.reports.sales-summary') }}" class="row g-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fs-8 fw-bold text-gray-700 mb-1">Kantor Cabang</label>
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">-- Semua Cabang (Konsolidasi) --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>
                                {{ $b->code }} - {{ $b->name }} {{ $b->is_main ? '(PUSAT)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="form-label fs-8 fw-bold text-gray-700 mb-1">Kategori Produk</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ $categoryId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-sm-6">
                    <label class="form-label fs-8 fw-bold text-gray-700 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" />
                </div>

                <div class="col-md-2 col-sm-6">
                    <label class="form-label fs-8 fw-bold text-gray-700 mb-1">Tanggal Sampai</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" />
                </div>

                <div class="col-md-2 col-sm-12 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary fw-bold w-100">
                        <i class="bi bi-filter me-1"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('sales.reports.sales-summary') }}" class="btn btn-sm btn-light" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-4">
        <div class="col-md-3 col-sm-6">
            <div class="card bg-light-primary border border-primary border-dashed p-4 rounded-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase">Total Omset Penjualan</div>
                        <div class="fs-4 fw-bolder text-primary mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                    </div>
                    <div class="w-40px h-40px rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fs-4">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card bg-light-success border border-success border-dashed p-4 rounded-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase">Total Terbayar (Lunas)</div>
                        <div class="fs-4 fw-bolder text-success mt-1">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
                    </div>
                    <div class="w-40px h-40px rounded-circle bg-success text-white d-flex align-items-center justify-content-center fs-4">
                        <i class="bi bi-check2-all"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card bg-light-warning border border-warning border-dashed p-4 rounded-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase">Sisa Piutang (AR)</div>
                        <div class="fs-4 fw-bolder text-warning mt-1">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</div>
                    </div>
                    <div class="w-40px h-40px rounded-circle bg-warning text-white d-flex align-items-center justify-content-center fs-4">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card bg-light-info border border-info border-dashed p-4 rounded-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase">Total Transaksi Faktur</div>
                        <div class="fs-4 fw-bolder text-info mt-1">{{ number_format($totalTransactions) }} Faktur</div>
                    </div>
                    <div class="w-40px h-40px rounded-circle bg-info text-white d-flex align-items-center justify-content-center fs-4">
                        <i class="bi bi-receipt-cutoff"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mode Tampilan Radio Button Segmented Control -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-toggles text-primary fs-4"></i>
                <span class="fw-bold text-gray-800 fs-7">Fokus Tampilan:</span>
            </div>
            <div class="btn-group btn-group-sm" role="group" aria-label="Mode Tampilan Laporan">
                <input type="radio" class="btn-check" name="report_view_mode" id="viewModeAll" value="all" checked onchange="switchReportView('all')">
                <label class="btn btn-outline-primary fw-bold px-3 py-2" for="viewModeAll">
                    <i class="bi bi-grid-fill me-1"></i> Semua Rekap
                </label>

                <input type="radio" class="btn-check" name="report_view_mode" id="viewModeBranch" value="branch" onchange="switchReportView('branch')">
                <label class="btn btn-outline-primary fw-bold px-3 py-2" for="viewModeBranch">
                    <i class="bi bi-buildings-fill me-1"></i> Per Cabang
                </label>

                <input type="radio" class="btn-check" name="report_view_mode" id="viewModeCategory" value="category" onchange="switchReportView('category')">
                <label class="btn btn-outline-primary fw-bold px-3 py-2" for="viewModeCategory">
                    <i class="bi bi-tags-fill me-1"></i> Per Kategori
                </label>

                <input type="radio" class="btn-check" name="report_view_mode" id="viewModeTop" value="top" onchange="switchReportView('top')">
                <label class="btn btn-outline-primary fw-bold px-3 py-2" for="viewModeTop">
                    <i class="bi bi-trophy-fill me-1"></i> Top Produk
                </label>

                <input type="radio" class="btn-check" name="report_view_mode" id="viewModeInvoices" value="invoices" onchange="switchReportView('invoices')">
                <label class="btn btn-outline-primary fw-bold px-3 py-2" for="viewModeInvoices">
                    <i class="bi bi-receipt me-1"></i> Rincian Faktur
                </label>
            </div>
        </div>
    </div>

    <!-- Multi-Branch & Category Breakdown Grids -->
    <div class="row g-4" id="sectionBreakdowns">
        <!-- Branch Breakdown -->
        <div class="col-lg-6" id="reportCardBranch">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title fs-5 fw-bold text-gray-800 m-0">
                        <i class="bi bi-buildings text-primary me-2"></i> Rekap Penjualan Per Cabang
                    </h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                            <thead>
                                <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                    <th class="ps-4">Cabang</th>
                                    <th class="text-center">Jml Faktur</th>
                                    <th class="text-center">Total Qty</th>
                                    <th class="text-end">Total Penjualan (Rp)</th>
                                    <th class="text-end pe-4">Porsi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 fw-semibold">
                                @forelse($branchBreakdown as $b)
                                @php
                                    $pct = $totalRevenue > 0 ? ($b->total_sales / $totalRevenue) * 100 : 0;
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-bold text-gray-900">
                                        <span class="badge badge-light-primary fs-9 me-1">{{ $b->branch_code }}</span>
                                        {{ $b->branch_name }}
                                    </td>
                                    <td class="text-center">{{ $b->total_invoices }}</td>
                                    <td class="text-center">{{ number_format($b->total_qty) }}</td>
                                    <td class="text-end fw-bold text-primary">Rp {{ number_format($b->total_sales, 0, ',', '.') }}</td>
                                    <td class="text-end pe-4">
                                        <span class="badge badge-light-info fw-bolder">{{ number_format($pct, 1) }}%</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Tidak ada data transaksi per cabang.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Breakdown -->
        <div class="col-lg-6" id="reportCardCategory">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title fs-5 fw-bold text-gray-800 m-0">
                        <i class="bi bi-tags-fill text-warning me-2"></i> Rekap Penjualan Per Kategori
                    </h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                            <thead>
                                <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                    <th class="ps-4">Kategori Produk</th>
                                    <th class="text-center">Total Unit Terjual</th>
                                    <th class="text-end">Total Omset (Rp)</th>
                                    <th class="text-end pe-4">Porsi Omset</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 fw-semibold">
                                @forelse($categoryBreakdown as $c)
                                @php
                                    $pct = $totalRevenue > 0 ? ($c->total_sales / $totalRevenue) * 100 : 0;
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-bold text-gray-900">
                                        <i class="bi bi-tag-fill text-warning me-1"></i> {{ $c->category_name }}
                                    </td>
                                    <td class="text-center">{{ number_format($c->total_qty) }}</td>
                                    <td class="text-end fw-bold text-success">Rp {{ number_format($c->total_sales, 0, ',', '.') }}</td>
                                    <td class="text-end pe-4">
                                        <span class="badge badge-light-success fw-bolder">{{ number_format($pct, 1) }}%</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Tidak ada data kategori terjual.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products Table -->
    <div class="card shadow-sm border-0" id="reportCardTopProducts">
        <div class="card-header bg-white py-3">
            <h4 class="card-title fs-5 fw-bold text-gray-800 m-0">
                <i class="bi bi-trophy-fill text-warning me-2"></i> Top 10 Produk Terlaris
            </h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                    <thead>
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                            <th class="ps-4" style="width: 140px;">Kode Barang</th>
                            <th>Nama Barang / Produk</th>
                            <th>Kategori</th>
                            <th class="text-center">Total Kuantitas Terjual</th>
                            <th class="text-end pe-4">Total Penjualan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 fw-semibold">
                        @forelse($topProducts as $item)
                        <tr>
                            <td class="ps-4 fw-bold text-primary">{{ $item->item_code }}</td>
                            <td class="fw-bold text-gray-900">{{ $item->item_name }}</td>
                            <td><span class="badge badge-light-secondary fs-8">{{ $item->category_name }}</span></td>
                            <td class="text-center fw-bold">{{ number_format($item->total_qty) }}</td>
                            <td class="text-end fw-bolder text-primary pe-4">Rp {{ number_format($item->total_sales, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Tidak ada data produk terjual.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Invoices List Table -->
    <div class="card shadow-sm border-0" id="reportCardInvoices">
        <div class="card-header bg-white py-3">
            <h4 class="card-title fs-5 fw-bold text-gray-800 m-0">
                <i class="bi bi-list-columns-reverse text-primary me-2"></i> Daftar Rincian Faktur Penjualan (AR)
            </h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                    <thead>
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                            <th class="ps-4" style="width: 140px;">No. Faktur</th>
                            <th>Cabang</th>
                            <th>Pelanggan (Customer)</th>
                            <th>Tanggal Faktur</th>
                            <th>Status Bayar</th>
                            <th class="text-end">Total Faktur</th>
                            <th class="text-end">Terbayar</th>
                            <th class="text-center pe-4" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 fw-semibold">
                        @forelse($invoices as $inv)
                        <tr>
                            <td class="ps-4 fw-bold text-primary">
                                <a href="{{ route('sales.invoices.show', $inv->id) }}" class="text-primary fw-bold">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td>
                                @if($inv->branch)
                                    <span class="badge badge-light-primary fs-8">{{ $inv->branch->name }}</span>
                                @else
                                    <span class="text-muted fs-8">Pusat</span>
                                @endif
                            </td>
                            <td class="fw-bold text-gray-900">{{ $inv->customer?->name ?? '-' }}</td>
                            <td class="text-muted fs-8">{{ $inv->invoice_date?->format('d/m/Y') }}</td>
                            <td>
                                @if($inv->status === 'paid')
                                    <span class="badge badge-light-success fs-8">Lunas</span>
                                @elseif($inv->status === 'partial')
                                    <span class="badge badge-light-warning fs-8">Sebagian</span>
                                @else
                                    <span class="badge badge-light-danger fs-8">Belum Bayar</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-primary">Rp {{ number_format($inv->grand_total, 0, ',', '.') }}</td>
                            <td class="text-end text-success fw-bold">Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}</td>
                            <td class="text-center pe-4">
                                <a href="{{ route('sales.invoices.show', $inv->id) }}" class="btn btn-xs btn-light-primary fw-bold">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                Tidak ada transaksi faktur dalam periode yang dipilih.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($invoices->hasPages())
                <div class="px-4 py-3 border-top">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function switchReportView(mode) {
        const branchCard = document.getElementById('reportCardBranch');
        const categoryCard = document.getElementById('reportCardCategory');
        const sectionBreakdowns = document.getElementById('sectionBreakdowns');
        const topCard = document.getElementById('reportCardTopProducts');
        const invoicesCard = document.getElementById('reportCardInvoices');

        if (mode === 'all') {
            sectionBreakdowns.classList.remove('d-none');
            branchCard.classList.remove('d-none');
            branchCard.className = 'col-lg-6';
            categoryCard.classList.remove('d-none');
            categoryCard.className = 'col-lg-6';
            topCard.classList.remove('d-none');
            invoicesCard.classList.remove('d-none');
        } else if (mode === 'branch') {
            sectionBreakdowns.classList.remove('d-none');
            branchCard.classList.remove('d-none');
            branchCard.className = 'col-12';
            categoryCard.classList.add('d-none');
            topCard.classList.add('d-none');
            invoicesCard.classList.add('d-none');
        } else if (mode === 'category') {
            sectionBreakdowns.classList.remove('d-none');
            branchCard.classList.add('d-none');
            categoryCard.classList.remove('d-none');
            categoryCard.className = 'col-12';
            topCard.classList.add('d-none');
            invoicesCard.classList.add('d-none');
        } else if (mode === 'top') {
            sectionBreakdowns.classList.add('d-none');
            topCard.classList.remove('d-none');
            invoicesCard.classList.add('d-none');
        } else if (mode === 'invoices') {
            sectionBreakdowns.classList.add('d-none');
            topCard.classList.add('d-none');
            invoicesCard.classList.remove('d-none');
        }
    }
</script>
@endsection
