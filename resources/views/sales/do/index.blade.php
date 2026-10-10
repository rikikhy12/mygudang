@extends('layouts.app')

@section('title', 'Surat Jalan (DO)')
@section('page_title', 'Surat Jalan Pengiriman Barang (Delivery Order)')

@section('content')
<div class="card card-flush shadow-sm">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-truck fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Daftar Surat Jalan (Delivery Order)</h3>
                <span class="text-muted fs-8 fw-semibold">Pengeluaran stok barang operasional dan bukti penyerahan barang ke ekspedisi/pelanggan</span>
            </div>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('sales.do.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-plus-circle me-1"></i> Terbitkan Surat Jalan Baru
            </a>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>No. Surat Jalan (DO)</th>
                        <th>No. SO Induk</th>
                        <th>Pelanggan (Customer)</th>
                        <th>Gudang Asal</th>
                        <th>Tanggal Kirim</th>
                        <th>Driver / Kurir</th>
                        <th>Jurnal HPP</th>
                        <th class="text-end min-w-140px no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($deliveries as $do)
                    <tr>
                        <td>
                            <a href="{{ route('sales.do.show', $do->id) }}" class="text-gray-800 fw-bold text-hover-primary">
                                {{ $do->do_number }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('sales.so.show', $do->sales_order_id) }}" class="text-primary fw-semibold">
                                {{ $do->salesOrder->so_number }}
                            </a>
                        </td>
                        <td><span class="text-gray-800 fw-semibold">{{ $do->customer->name }}</span></td>
                        <td>{{ $do->warehouse->name }}</td>
                        <td>{{ $do->delivery_date->format('d/m/Y') }}</td>
                        <td>{{ $do->driver_name ?: '-' }}</td>
                        <td>
                            @if($do->journal)
                                <a href="{{ route('accounting.journals.show', $do->journal_id) }}" class="badge badge-light-success fw-bold">
                                    <i class="bi bi-journal-check me-1"></i> {{ $do->journal->journal_number }}
                                </a>
                            @else
                                <span class="badge badge-light-secondary">No Journal</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('sales.do.show', $do->id) }}">
                                            <i class="bi bi-eye text-info me-2"></i> Lihat Detail DO
                                        </a>
                                    </li>
                                    @if($do->invoices->count() == 0)
                                    <li>
                                        <a class="dropdown-item py-2 text-primary" href="{{ route('sales.invoices.create', ['do_id' => $do->id]) }}">
                                            <i class="bi bi-receipt-cutoff text-primary me-2"></i> Buat Faktur (AR)
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
