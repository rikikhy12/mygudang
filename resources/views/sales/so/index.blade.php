@extends('layouts.app')

@section('title', 'Sales Orders (SO)')
@section('page_title', 'Sales Orders (Pesanan Penjualan)')

@section('content')
<div class="card card-flush shadow-sm">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-bag-check-fill fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Daftar Pesanan Penjualan (Sales Orders)</h3>
                <span class="text-muted fs-8 fw-semibold">Monitoring pesanan masuk dari pelanggan, jadwal pengiriman, dan status realisasi</span>
            </div>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('sales.so.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-plus-circle me-1"></i> Buat Sales Order Baru
            </a>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>No. SO</th>
                        <th>Pelanggan (Customer)</th>
                        <th>Tanggal Pesanan</th>
                        <th>Target Kirim</th>
                        <th>Grand Total</th>
                        <th>Status Pengiriman</th>
                        <th class="text-end min-w-140px no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($orders as $so)
                    <tr>
                        <td>
                            <a href="{{ route('sales.so.show', $so->id) }}" class="text-gray-800 fw-bold text-hover-primary">
                                {{ $so->so_number }}
                            </a>
                        </td>
                        <td><span class="text-gray-800 fw-semibold">{{ $so->customer->name }}</span></td>
                        <td>{{ $so->order_date->format('d/m/Y') }}</td>
                        <td>{{ $so->expected_delivery_date ? $so->expected_delivery_date->format('d/m/Y') : '-' }}</td>
                        <td><span class="text-gray-900 fw-bolder">Rp {{ number_format($so->grand_total, 0, ',', '.') }}</span></td>
                        <td>
                            @if($so->status == 'completed')
                                <span class="badge badge-light-success fw-bold">Delivered Full</span>
                            @elseif($so->status == 'processing')
                                <span class="badge badge-light-warning fw-bold">Partial Delivered</span>
                            @elseif($so->status == 'approved')
                                <span class="badge badge-light-primary fw-bold">Approved / Open</span>
                            @else
                                <span class="badge badge-light-secondary fw-bold">{{ ucfirst($so->status) }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('sales.so.show', $so->id) }}">
                                            <i class="bi bi-eye text-info me-2"></i> Lihat Detail SO
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('sales.so.duplicate', $so->id) }}">
                                            <i class="bi bi-files text-primary me-2"></i> Salin / Duplikasi
                                        </a>
                                    </li>
                                    @if(in_array($so->status, ['approved', 'processing']))
                                    <li>
                                        <a class="dropdown-item py-2 text-success" href="{{ route('sales.do.create', ['so_id' => $so->id]) }}">
                                            <i class="bi bi-truck text-success me-2"></i> Kirim Surat Jalan (DO)
                                        </a>
                                    </li>
                                    @endif
                                    @if($so->status == 'draft' || $so->status == 'cancelled')
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form action="{{ route('sales.so.destroy', $so->id) }}" method="POST" onsubmit="return confirm('Hapus / batalkan Sales Order {{ $so->so_number }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item py-2 text-danger">
                                                <i class="bi bi-trash text-danger me-2"></i> Hapus SO
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
