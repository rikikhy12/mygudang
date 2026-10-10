@extends('layouts.app')

@section('title', 'Saldo Stok Fisik')
@section('page_title', 'Ringkasan Saldo Stok Fisik & Valuasi Persediaan')

@section('content')
<div class="card card-flush shadow-sm">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-boxes fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Saldo Persediaan & Valuasi Stok Gudang</h3>
                <span class="text-muted fs-8 fw-semibold">Monitoring stok fisik per gudang, batas minimum buffer stock, dan nilai rupiah persediaan</span>
            </div>
        </div>

        <div class="card-toolbar d-flex gap-2">
            <a href="{{ route('inventory.adjustments.create') }}" class="btn btn-light-warning btn-sm fw-bold">
                <i class="bi bi-sliders me-1"></i> Penyesuaian Opname
            </a>
            <a href="{{ route('inventory.transfers.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-arrow-left-right me-1"></i> Transfer Antar Gudang
            </a>
        </div>
    </div>

    <div class="card-body p-4 pt-0">
        <!-- Warehouse Select Form -->
        <div class="bg-light p-3 rounded-3 mb-4 mt-3">
            <form method="GET" action="{{ route('inventory.overview') }}" class="d-flex flex-wrap align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-7 fw-bold text-gray-700">Filter Gudang:</span>
                    <select name="warehouse_id" class="form-select form-select-sm w-250px" onchange="this.form.submit()">
                        <option value="">-- Semua Gudang (Konsolidasi) --</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ $warehouseId == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->code }})</option>
                        @endforeach
                    </select>
                </div>
                @if($warehouseId)
                    <a href="{{ route('inventory.overview') }}" class="btn btn-light-danger btn-sm">
                        <i class="bi bi-x-circle me-1"></i> Reset Gudang
                    </a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>Kode SKU</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Stok Tersedia</th>
                        <th>Batas Min</th>
                        <th>Harga Pokok (HPP)</th>
                        <th>Total Nilai Valuasi</th>
                        <th>Status</th>
                        <th class="text-end min-w-80px no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($stockData as $data)
                    @php 
                        $item = $data['item'];
                        $stock = $data['stock'];
                        $val = $data['valuation'];
                    @endphp
                    <tr>
                        <td><code>{{ $item->code }}</code></td>
                        <td><span class="text-gray-900 fw-bold fs-6">{{ $item->name }}</span></td>
                        <td><span class="badge badge-light-primary fw-bold">{{ $item->category->name }}</span></td>
                        <td>{{ $item->unit->symbol }}</td>
                        <td>
                            <span class="fw-bolder fs-6 {{ $stock <= $item->min_stock ? 'text-danger' : 'text-success' }}">
                                {{ $item->getFormattedStockBreakdown($stock) }}
                            </span>
                        </td>
                        <td>{{ number_format($item->min_stock, 0) }}</td>
                        <td>Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                        <td><span class="text-gray-900 fw-bolder">Rp {{ number_format($val, 0, ',', '.') }}</span></td>
                        <td>
                            @if($stock <= 0)
                                <span class="badge badge-light-danger fw-bold">Habis</span>
                            @elseif($stock <= $item->min_stock)
                                <span class="badge badge-light-warning fw-bold">Menipis</span>
                            @else
                                <span class="badge badge-light-success fw-bold">Aman</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('items.stock-card', $item->id) }}">
                                            <i class="bi bi-card-list text-info me-2"></i> Kartu Stok
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
