@extends('layouts.app')

@section('title', 'Purchase Orders (PO)')
@section('page_title', 'Purchase Orders (Pesanan Pembelian)')

@section('content')
<div class="card card-flush shadow-sm">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-cart-check-fill fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Daftar Pesanan Pembelian (Purchase Orders)</h3>
                <span class="text-muted fs-8 fw-semibold">Pemesanan barang ke supplier, persetujuan pengadaan, dan status penerimaan barang</span>
            </div>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('procurement.po.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-plus-circle me-1"></i> Buat PO Baru
            </a>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>No. PO</th>
                        <th>Supplier</th>
                        <th>Tanggal Order</th>
                        <th>Estimasi Tiba</th>
                        <th>Grand Total</th>
                        <th>Status Penerimaan</th>
                        <th class="text-end min-w-140px no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($orders as $po)
                    <tr>
                        <td>
                            <a href="{{ route('procurement.po.show', $po->id) }}" class="text-gray-800 fw-bold text-hover-primary">
                                {{ $po->po_number }}
                            </a>
                        </td>
                        <td><span class="text-gray-800 fw-semibold">{{ $po->supplier->name }}</span></td>
                        <td>{{ $po->order_date->format('d/m/Y') }}</td>
                        <td>{{ $po->expected_date ? $po->expected_date->format('d/m/Y') : '-' }}</td>
                        <td><span class="text-gray-900 fw-bolder">Rp {{ number_format($po->grand_total, 0, ',', '.') }}</span></td>
                        <td>
                            @if($po->status == 'received')
                                <span class="badge badge-light-success fw-bold">Received Full</span>
                            @elseif($po->status == 'partial_received')
                                <span class="badge badge-light-warning fw-bold">Partial Received</span>
                            @elseif($po->status == 'approved')
                                <span class="badge badge-light-primary fw-bold">Approved / Open</span>
                            @else
                                <span class="badge badge-light-secondary fw-bold">{{ ucfirst($po->status) }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('procurement.po.show', $po->id) }}">
                                            <i class="bi bi-eye text-info me-2"></i> Lihat Detail PO
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('procurement.po.duplicate', $po->id) }}">
                                            <i class="bi bi-files text-primary me-2"></i> Salin / Duplikasi
                                        </a>
                                    </li>
                                    @if($po->status != 'received' && $po->status != 'cancelled')
                                    <li>
                                        <a class="dropdown-item py-2 text-success" href="{{ route('procurement.grn.create', ['po_id' => $po->id]) }}">
                                            <i class="bi bi-box-arrow-in-down text-success me-2"></i> Buat Penerimaan GRN
                                        </a>
                                    </li>
                                    @endif
                                    @if($po->status == 'draft' || $po->status == 'cancelled')
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form action="{{ route('procurement.po.destroy', $po->id) }}" method="POST" onsubmit="return confirm('Hapus / batalkan pesanan pembelian {{ $po->po_number }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item py-2 text-danger">
                                                <i class="bi bi-trash text-danger me-2"></i> Hapus PO
                                            </button>
                                        </form>
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
