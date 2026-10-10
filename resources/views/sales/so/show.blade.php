@extends('layouts.app')

@section('title', 'SO ' . $so->so_number)
@section('page_title', 'Detail Sales Order: ' . $so->so_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('sales.so.index') }}" class="btn btn-sm btn-light-primary fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar SO
        </a>
    </div>

    <div class="d-flex gap-2">
        @if(in_array($so->status, ['approved', 'processing']))
            <a href="{{ route('sales.do.create', ['so_id' => $so->id]) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-truck me-1"></i> Buat Surat Jalan (DO)
            </a>
        @endif
        <a href="{{ route('sales.so.print', $so->id) }}" target="_blank" class="btn btn-sm btn-info text-white">
            <i class="bi bi-printer me-1"></i> Cetak Sales Order
        </a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title fs-4 fw-bold text-gray-900 m-0">{{ $so->so_number }}</h3>
            <div class="fs-7 text-muted mt-1">
                Dibuat oleh: <strong class="text-gray-800">{{ $so->creator->name }}</strong> pada {{ $so->order_date->format('d F Y') }}
            </div>
        </div>
        <div>
            @if($so->status == 'completed')
                <span class="badge badge-light-success fw-bold px-3 py-2">Delivered Full</span>
            @elseif($so->status == 'processing')
                <span class="badge badge-light-warning fw-bold px-3 py-2">Partial Delivered</span>
            @else
                <span class="badge badge-light-primary fw-bold px-3 py-2">Approved / Open</span>
            @endif
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="bg-light p-3 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase">Pelanggan (Customer)</div>
                    <div class="fs-6 fw-bold text-gray-900 mt-1">{{ $so->customer->name }}</div>
                    <div class="fs-7 text-muted mt-1">{{ $so->customer->address ?: 'Alamat belum disetel' }}</div>
                    <div class="fs-7 text-muted">Telp: {{ $so->customer->phone ?: '-' }}</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-light p-3 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase">Informasi Pengiriman</div>
                    <div class="fs-7 text-gray-800 mt-2">Target Kirim: <strong>{{ $so->expected_delivery_date ? $so->expected_delivery_date->format('d/m/Y') : '-' }}</strong></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-light-primary p-3 rounded-3 h-100 border border-primary border-dashed">
                    <div class="fs-8 fw-bold text-primary text-uppercase">Total Transaksi</div>
                    <div class="fs-3 fw-bold text-primary mt-1">
                        Rp {{ number_format($so->grand_total, 0, ',', '.') }}
                    </div>
                    <div class="fs-8 text-muted">Termasuk PPN: Rp {{ number_format($so->tax_amount, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        @if($so->notes)
        <div class="alert alert-secondary p-3 mb-4 rounded-3 fs-7">
            <strong>Catatan Pesanan:</strong> {{ $so->notes }}
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-3 py-3" style="width: 50px;">No.</th>
                        <th>Kode & Nama Barang</th>
                        <th class="text-center">Qty Dipesan</th>
                        <th class="text-center">Koli</th>
                        <th class="text-center">Qty Terkirim</th>
                        <th class="text-center">Sisa Qty</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end pe-3">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @foreach($so->items as $idx => $item)
                    <tr>
                        <td class="ps-3">{{ $idx + 1 }}</td>
                        <td>
                            <strong class="text-gray-900">{{ $item->item->code }}</strong> - {{ $item->item->name }}
                        </td>
                        <td class="text-center">{{ number_format($item->quantity, 0) }} {{ $item->item->unit->symbol }}</td>
                        <td class="text-center fw-bold text-primary">{{ $item->koli ?: '-' }}</td>
                        <td class="text-center text-success fw-bold">{{ number_format($item->delivered_quantity, 0) }}</td>
                        <td class="text-center text-success fw-bold">{{ number_format($item->delivered_quantity, 0) }}</td>
                        <td class="text-center {{ $item->pending_quantity > 0 ? 'text-warning' : 'text-success' }} fw-bold">
                            {{ number_format($item->pending_quantity, 0) }}
                        </td>
                        <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-end pe-3 fw-bold text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <div class="card bg-light border border-gray-200 p-3" style="min-width: 320px;">
                <div class="d-flex justify-content-between align-items-center mb-2 fs-7 text-muted">
                    <span>Subtotal:</span>
                    <span class="text-gray-800 fw-semibold">Rp {{ number_format($so->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($so->discount_amount > 0)
                <div class="d-flex justify-content-between align-items-center mb-2 fs-7 text-danger">
                    <span>Diskon:</span>
                    <span class="fw-semibold">-Rp {{ number_format($so->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between align-items-center mb-2 fs-7 text-muted">
                    <span>PPN ({{ $so->tax_rate }}%):</span>
                    <span class="text-gray-800 fw-semibold">Rp {{ number_format($so->tax_amount, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top fs-6 fw-bold text-primary">
                    <span>Grand Total:</span>
                    <span>Rp {{ number_format($so->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- History Pengiriman Barang (DO) -->
@if($so->deliveryOrders->count() > 0)
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent border-bottom py-3">
        <h3 class="card-title fs-5 fw-bold text-gray-800 m-0">
            <i class="bi bi-truck text-primary me-2"></i> Histori Surat Jalan (DO) Terkait
        </h3>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-3 py-3">No. DO</th>
                        <th>Tanggal Kirim</th>
                        <th>Gudang Asal</th>
                        <th>Pengemudi / Nopol</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @foreach($so->deliveryOrders as $do)
                    <tr>
                        <td class="ps-3"><strong><a href="{{ route('sales.do.show', $do->id) }}" class="text-primary">{{ $do->do_number }}</a></strong></td>
                        <td>{{ $do->delivery_date->format('d/m/Y') }}</td>
                        <td>{{ $do->warehouse->name }}</td>
                        <td>{{ $do->driver_name ?: '-' }} ({{ $do->vehicle_number ?: '-' }})</td>
                        <td><span class="badge badge-light-success fw-bold">Posted</span></td>
                        <td class="text-end pe-3">
                            <a href="{{ route('sales.do.show', $do->id) }}" class="btn btn-sm btn-light-primary">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
