@extends('layouts.app')

@section('title', 'Transfer ' . $transfer->transfer_number)
@section('page_title', 'Detail Transfer Antar Gudang: ' . $transfer->transfer_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('inventory.transfers.index') }}" class="btn btn-sm btn-light-primary fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Transfer
        </a>
    </div>
    <button class="btn btn-sm btn-light-info" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Cetak Bukti Transfer
    </button>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title fs-4 fw-bold text-gray-900 m-0">{{ $transfer->transfer_number }}</h3>
            <div class="fs-7 text-muted mt-1">
                Dibuat oleh: <strong class="text-gray-800">{{ $transfer->creator->name }}</strong> pada {{ $transfer->transfer_date->format('d F Y') }}
            </div>
        </div>
        <div>
            <span class="badge badge-light-success fw-bold px-3 py-2">Completed</span>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="bg-light p-3 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase">Gudang Asal (Pengirim)</div>
                    <div class="fs-6 fw-bold text-gray-900 mt-1">{{ $transfer->fromWarehouse->name }}</div>
                    <div class="fs-7 text-muted mt-1">{{ $transfer->fromWarehouse->location ?: '-' }}</div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="bg-light-primary p-3 rounded-3 h-100 border border-primary border-dashed">
                    <div class="fs-8 fw-bold text-primary text-uppercase">Gudang Tujuan (Penerima)</div>
                    <div class="fs-6 fw-bold text-gray-900 mt-1">{{ $transfer->toWarehouse->name }}</div>
                    <div class="fs-7 text-muted mt-1">{{ $transfer->toWarehouse->location ?: '-' }}</div>
                </div>
            </div>
        </div>

        @if($transfer->notes)
        <div class="alert alert-secondary p-3 mb-4 rounded-3 fs-7">
            <strong>Catatan:</strong> {{ $transfer->notes }}
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-3 py-3" style="width: 50px;">No.</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th class="text-center pe-3">Jumlah Dipindahkan</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @foreach($transfer->items as $idx => $item)
                    <tr>
                        <td class="ps-3">{{ $idx + 1 }}</td>
                        <td><code>{{ $item->item->code }}</code></td>
                        <td><strong class="text-gray-900">{{ $item->item->name }}</strong></td>
                        <td class="text-center pe-3 fw-bold text-primary fs-6">
                            {{ number_format($item->quantity, 0) }} {{ $item->item->unit->symbol }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
