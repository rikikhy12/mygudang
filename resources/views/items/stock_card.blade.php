@extends('layouts.app')

@section('title', 'Kartu Stok - ' . $item->name)
@section('page_title', 'Kartu Riwayat Mutasi Stok')

@section('content')
<div class="d-flex flex-column gap-5">
    
    <div class="d-flex align-items-center justify-content-between">
        <a href="{{ route('items.index') }}" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog Barang
        </a>
    </div>

    <!-- Header Item Details -->
    <div class="card card-flush shadow-sm">
        <div class="card-body p-6">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-4">
                <div class="d-flex align-items-center gap-4">
                    <div class="symbol symbol-50px symbol-circle bg-light-primary">
                        <span class="symbol-label text-primary"><i class="bi bi-box-seam fs-2x"></i></span>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h2 class="text-gray-900 fw-bolder fs-3 mb-0">{{ $item->name }}</h2>
                            <code>{{ $item->code }}</code>
                        </div>
                        <div class="text-muted fs-7 mt-1">
                            Kategori: <strong class="text-gray-800">{{ $item->category->name }}</strong> | Satuan: <strong class="text-gray-800">{{ $item->unit->name }} ({{ $item->unit->symbol }})</strong>
                        </div>
                    </div>
                </div>

                <div class="bg-light-success border border-success border-dashed rounded px-6 py-3 text-end">
                    <div class="text-success fw-bold fs-7 text-uppercase">Saldo Stok Fisik Realtime</div>
                    <div class="text-success fw-bolder fs-2hx">
                        {{ number_format($item->current_stock, 0) }} <span class="fs-5">{{ $item->unit->symbol }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="card card-flush shadow-sm">
        <div class="card-header align-items-center py-5">
            <div class="card-title">
                <h3 class="card-label fw-bolder text-gray-800 fs-4">
                    <i class="bi bi-card-list text-primary me-2 fs-3"></i> Buku Mutasi Kartu Stok (Inventory Ledger)
                </h3>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                    <thead>
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                            <th>Tanggal</th>
                            <th>No. Referensi</th>
                            <th>Tipe Dokumen</th>
                            <th>Gudang</th>
                            <th>Masuk (In)</th>
                            <th>Keluar (Out)</th>
                            <th>Saldo Akhir</th>
                            <th>Biaya Pokok (HPP)</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @forelse($ledgers as $ledger)
                        <tr>
                            <td>{{ $ledger->transaction_date->format('d/m/Y') }}</td>
                            <td><span class="text-gray-800 fw-bold">{{ $ledger->reference_no }}</span></td>
                            <td>
                                <span class="badge badge-light-primary fw-bold">{{ $ledger->reference_type }}</span>
                            </td>
                            <td>{{ $ledger->warehouse->name }}</td>
                            <td>
                                @if($ledger->type == 'in')
                                    <span class="text-success fw-bolder fs-6">+{{ number_format($ledger->quantity, 0) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($ledger->type == 'out')
                                    <span class="text-danger fw-bolder fs-6">-{{ number_format($ledger->quantity, 0) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-gray-900 fw-bolder fs-6">
                                    {{ number_format($ledger->current_stock_after, 0) }} {{ $item->unit->symbol }}
                                </span>
                            </td>
                            <td>Rp {{ number_format($ledger->unit_cost, 0, ',', '.') }}</td>
                            <td class="text-muted fs-7">{{ $ledger->notes }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-10 text-muted">Belum ada catatan mutasi stok untuk barang ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-4">
                {{ $ledgers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
