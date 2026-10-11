@extends('layouts.app')

@section('title', 'Mutasi Kartu Stok')
@section('page_title', 'Laporan Mutasi Kartu Stok Persediaan')

@section('content')
<div class="card card-flush shadow-sm">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-card-list fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Buku Kartu Stok Persediaan (Stock Ledger)</h3>
                <span class="text-muted fs-8 fw-semibold">Audit trail pergerakan kuantitas masuk, keluar, dan sisa saldo stok per gudang</span>
            </div>
        </div>
    </div>

    <div class="card-body p-4 pt-0">
        <!-- Filter Form -->
        <div class="bg-light p-3 rounded-3 mb-4 mt-3">
            <form method="GET" action="{{ route('inventory.movements') }}" class="d-flex flex-wrap align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-7 fw-bold text-gray-700">Barang:</span>
                    <select name="item_id" class="form-select form-select-sm w-220px" onchange="this.form.submit()">
                        <option value="">-- Semua Barang --</option>
                        @foreach($items as $i)
                            <option value="{{ $i->id }}" {{ $itemId == $i->id ? 'selected' : '' }}>{{ $i->code }} - {{ $i->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="fs-7 fw-bold text-gray-700">Gudang:</span>
                    <select name="warehouse_id" class="form-select form-select-sm w-200px" onchange="this.form.submit()">
                        <option value="">-- Semua Gudang --</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ $warehouseId == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if($itemId || $warehouseId)
                    <a href="{{ route('inventory.movements') }}" class="btn btn-light-danger btn-sm">
                        <i class="bi bi-x-circle me-1"></i> Reset Filter
                    </a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>Tanggal</th>
                        <th>Kode SKU</th>
                        <th>Nama Barang</th>
                        <th>Gudang</th>
                        <th>Tipe Dokumen</th>
                        <th>No. Referensi</th>
                        <th>Masuk (In)</th>
                        <th>Keluar (Out)</th>
                        <th>Saldo Akhir</th>
                        <th>HPP Satuan</th>
                        <th>Total Biaya</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($ledgers as $l)
                    <tr>
                        <td>{{ $l->transaction_date->format('d/m/Y') }}</td>
                        <td><code>{{ $l->item->code }}</code></td>
                        <td><span class="text-gray-900 fw-bold">{{ $l->item->name }}</span></td>
                        <td>{{ $l->warehouse->name }}</td>
                        <td><span class="badge badge-light-primary fw-bold">{{ $l->reference_type }}</span></td>
                        <td>{{ $l->reference_no ?: '-' }}</td>
                        <td>
                            @if($l->type == 'in')
                                <div class="text-success fw-bolder">+{{ number_format($l->quantity, 2, ',', '.') }} {{ $l->item->unit->symbol }}</div>
                                @if($l->koli > 0)
                                    <span class="badge badge-light-success fs-9 fw-bold">+{{ number_format($l->koli, 0) }} Bal/Koli</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($l->type == 'out')
                                <div class="text-danger fw-bolder">-{{ number_format($l->quantity, 2, ',', '.') }} {{ $l->item->unit->symbol }}</div>
                                @if($l->koli > 0)
                                    <span class="badge badge-light-danger fs-9 fw-bold">-{{ number_format($l->koli, 0) }} Bal/Koli</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-gray-900 fw-bolder">
                                {{ number_format($l->current_stock_after, 2, ',', '.') }} {{ $l->item->unit->symbol }}
                            </div>
                            @if($l->current_koli_after > 0)
                                <span class="badge badge-light-primary fs-9 fw-bold">{{ number_format($l->current_koli_after, 0) }} Bal/Koli</span>
                            @endif
                        </td>
                        <td>Rp {{ number_format($l->unit_cost, 0, ',', '.') }}</td>
                        <td><span class="text-gray-900 fw-bolder">Rp {{ number_format($l->total_cost, 0, ',', '.') }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                            Belum ada riwayat mutasi stok pada filter yang dipilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
