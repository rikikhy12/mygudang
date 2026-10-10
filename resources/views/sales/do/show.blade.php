@extends('layouts.app')

@section('title', 'Surat Jalan ' . $do->do_number)
@section('page_title', 'Detail Surat Jalan: ' . $do->do_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('sales.do.index') }}" class="btn btn-sm btn-light-primary fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Surat Jalan
        </a>
    </div>

    <div class="d-flex gap-2">
        @if($do->invoices->count() == 0)
            <a href="{{ route('sales.invoices.create', ['do_id' => $do->id]) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-receipt-cutoff me-1"></i> Buat Faktur Penjualan (AR)
            </a>
        @endif
        <a href="{{ route('sales.do.print', $do->id) }}" target="_blank" class="btn btn-sm btn-info text-white">
            <i class="bi bi-printer me-1"></i> Cetak Surat Jalan
        </a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title fs-4 fw-bold text-gray-900 m-0">{{ $do->do_number }}</h3>
            <div class="fs-7 text-muted mt-1">
                Dikeluarkan oleh: <strong class="text-gray-800">{{ $do->dispatchedBy->name }}</strong> pada {{ $do->delivery_date->format('d F Y') }}
            </div>
        </div>
        <div>
            <span class="badge badge-light-success fw-bold px-3 py-2">Status: Posted (Terkirim)</span>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="bg-light p-3 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase">Penerima / Pelanggan</div>
                    <div class="fs-6 fw-bold text-gray-900 mt-1">{{ $do->customer->name }}</div>
                    <div class="fs-7 text-muted mt-1">{{ $do->customer->address ?: 'Alamat belum disetel' }}</div>
                    <div class="fs-7 text-muted">Telp: {{ $do->customer->phone ?: '-' }}</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-light p-3 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase">Gudang & Kurir Armada</div>
                    <div class="fs-6 fw-bold text-gray-900 mt-1">{{ $do->warehouse->name }}</div>
                    <div class="fs-7 text-gray-700 mt-1">
                        Driver: <strong>{{ $do->driver_name ?: '-' }}</strong> ({{ $do->vehicle_number ?: '-' }})
                    </div>
                    <div class="fs-7 text-muted mt-1">
                        Referensi SO: <a href="{{ route('sales.so.show', $do->sales_order_id) }}" class="text-primary fw-bold">{{ $do->salesOrder->so_number }}</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-light-primary p-3 rounded-3 h-100 border border-primary border-dashed">
                    <div class="fs-8 fw-bold text-primary text-uppercase">Jurnal HPP Otomatis</div>
                    @if($do->journal)
                        <div class="fs-6 fw-bold text-success mt-1">
                            <a href="{{ route('accounting.journals.show', $do->journal_id) }}" class="text-success">{{ $do->journal->journal_number }}</a>
                        </div>
                        <div class="fs-7 text-muted mt-1">Nilai HPP: Rp {{ number_format($do->journal->total_debit, 0, ',', '.') }}</div>
                    @else
                        <div class="fs-7 text-muted mt-1">-</div>
                    @endif
                </div>
            </div>
        </div>

        @if($do->notes)
        <div class="alert alert-secondary p-3 mb-4 rounded-3 fs-7">
            <strong>Catatan Surat Jalan:</strong> {{ $do->notes }}
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-3 py-3" style="width: 50px;">No.</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th class="text-center">Jumlah Fisik Dikirim</th>
                        <th class="text-center">Koli</th>
                        <th class="text-end">Biaya HPP Satuan</th>
                        <th class="text-end pe-3">Total Beban HPP</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @php $totalHpp = 0; @endphp
                    @foreach($do->items as $idx => $item)
                    @php $totalHpp += $item->total_cost; @endphp
                    <tr>
                        <td class="ps-3">{{ $idx + 1 }}</td>
                        <td><code>{{ $item->item->code }}</code></td>
                        <td><strong class="text-gray-900">{{ $item->item->name }}</strong></td>
                        <td class="text-center fw-bold text-primary">
                            {{ number_format($item->quantity, 0) }} {{ $item->item->unit->symbol }}
                        </td>
                        <td class="text-center fw-bold text-primary">{{ $item->koli ?: '-' }}</td>
                        <td class="text-end">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                        <td class="text-end pe-3 fw-bold text-gray-900">Rp {{ number_format($item->total_cost, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr class="bg-light fw-bold">
                        <td colspan="5" class="text-end fs-7 text-muted ps-3">Total Nilai HPP Barang Keluar:</td>
                        <td class="text-end pe-3 text-danger fs-6">Rp {{ number_format($totalHpp, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
