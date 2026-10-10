@extends('layouts.app')

@section('title', 'Transfer Antar Gudang')
@section('page_title', 'Transfer Persediaan Antar Gudang')

@section('content')
<div class="card card-flush shadow-sm">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <h3 class="card-label fw-bolder text-gray-800 fs-4">
                <i class="bi bi-arrow-left-right text-primary me-2 fs-3"></i> Daftar Transfer Barang Antar Lokasi
            </h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('inventory.transfers.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Buat Transfer Baru
            </a>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>No. Transfer</th>
                        <th>Dari Gudang Asal</th>
                        <th>Ke Gudang Tujuan</th>
                        <th>Tanggal Transfer</th>
                        <th>Dibuat Oleh</th>
                        <th>Status</th>
                        <th class="text-end min-w-80px no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($transfers as $trf)
                    <tr>
                        <td>
                            <a href="{{ route('inventory.transfers.show', $trf->id) }}" class="text-gray-800 fw-bold text-hover-primary">
                                {{ $trf->transfer_number }}
                            </a>
                        </td>
                        <td><span class="text-gray-900 fw-semibold">{{ $trf->fromWarehouse->name }}</span></td>
                        <td><span class="text-primary fw-bold">{{ $trf->toWarehouse->name }}</span></td>
                        <td>{{ $trf->transfer_date->format('d/m/Y') }}</td>
                        <td>{{ $trf->creator->name }}</td>
                        <td><span class="badge badge-light-success fw-bold">Completed</span></td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('inventory.transfers.show', $trf->id) }}">
                                            <i class="bi bi-eye text-info me-2"></i> Lihat Detail
                                        </a>
                                    </li>
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
