@extends('layouts.app')

@section('title', 'Faktur ' . $invoice->invoice_number)
@section('page_title', 'Detail Faktur Penjualan: ' . $invoice->invoice_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('sales.invoices.index') }}" class="btn btn-sm btn-light-primary fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Faktur
        </a>
    </div>

    <div class="d-flex gap-2">
        @if($invoice->status !== 'paid')
            <a href="{{ route('sales.invoices.edit', $invoice->id) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-pencil-square me-1"></i> Edit Faktur
            </a>
        @endif
        @if($invoice->remaining_balance > 0)
            <a href="{{ route('finance.cash-in.create', ['sales_invoice_id' => $invoice->id]) }}" class="btn btn-sm btn-success">
                <i class="bi bi-cash me-1"></i> Terima Pembayaran Kas Masuk
            </a>
        @endif
        <a href="{{ route('sales.invoices.print', $invoice->id) }}" target="_blank" class="btn btn-sm btn-info text-white">
            <i class="bi bi-printer me-1"></i> Cetak Faktur Penjualan
        </a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title fs-4 fw-bold text-gray-900 m-0">{{ $invoice->invoice_number }}</h3>
            <div class="fs-7 text-muted mt-1">
                Tanggal Faktur: <strong>{{ $invoice->invoice_date->format('d F Y') }}</strong> | Jatuh Tempo: <strong>{{ $invoice->due_date->format('d F Y') }}</strong>
            </div>
        </div>
        <div>
            @if($invoice->status == 'paid')
                <span class="badge badge-light-success fw-bold px-3 py-2">Lunas (Paid)</span>
            @elseif($invoice->status == 'partial')
                <span class="badge badge-light-warning fw-bold px-3 py-2">Sebagian (Partial)</span>
            @else
                <span class="badge badge-light-danger fw-bold px-3 py-2">Belum Dibayar (Unpaid)</span>
            @endif
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="bg-light p-3 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase">Ditagihkan Kepada</div>
                    <div class="fs-6 fw-bold text-gray-900 mt-1">{{ $invoice->customer->name }}</div>
                    <div class="fs-7 text-muted mt-1">{{ $invoice->customer->address ?: '-' }}</div>
                    <div class="fs-7 text-muted">NPWP: {{ $invoice->customer->tax_number ?: '-' }}</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-light p-3 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase">Dokumen Referensi</div>
                    <div class="fs-7 text-gray-700 mt-2">
                        SO: @if($invoice->salesOrder)<a href="{{ route('sales.so.show', $invoice->sales_order_id) }}" class="text-primary fw-bold">{{ $invoice->salesOrder->so_number }}</a>@else - @endif
                    </div>
                    <div class="fs-7 text-gray-700 mt-1">
                        DO: @if($invoice->deliveryOrder)<a href="{{ route('sales.do.show', $invoice->delivery_order_id) }}" class="text-primary fw-bold">{{ $invoice->deliveryOrder->do_number }}</a>@else - @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-light-primary p-3 rounded-3 h-100 border border-primary border-dashed">
                    <div class="fs-8 fw-bold text-primary text-uppercase">Jurnal Piutang Usaha</div>
                    @if($invoice->journal)
                        <div class="fs-6 fw-bold text-success mt-1">
                            <a href="{{ route('accounting.journals.show', $invoice->journal_id) }}" class="text-success">{{ $invoice->journal->journal_number }}</a>
                        </div>
                        <div class="fs-7 text-muted mt-1">Terposting ke Buku Besar</div>
                    @else
                        <div class="fs-7 text-muted mt-1">-</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-3 py-3" style="width: 50px;">No.</th>
                        <th>Kode & Nama Barang / Layanan</th>
                        <th class="text-center">Jumlah (Qty)</th>
                        <th class="text-center">Koli</th>
                        <th class="text-end">Harga Jual Satuan</th>
                        <th class="text-end pe-3">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @foreach($invoice->items as $idx => $item)
                    <tr>
                        <td class="ps-3">{{ $idx + 1 }}</td>
                        <td><strong class="text-gray-900">{{ $item->item->code }}</strong> - {{ $item->item->name }}</td>
                        <td class="text-center">{{ number_format($item->quantity, 0) }} {{ $item->item->unit->symbol }}</td>
                        <td class="text-center fw-bold text-primary">{{ $item->koli ?: '-' }}</td>
                        <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-end pe-3 fw-bold text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <div class="card bg-light border border-gray-200 p-3" style="min-width: 340px;">
                <div class="d-flex justify-content-between align-items-center mb-2 fs-7 text-muted">
                    <span>Subtotal Penjualan:</span>
                    <span class="text-gray-800 fw-semibold">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2 fs-7 text-muted">
                    <span>PPN:</span>
                    <span class="text-gray-800 fw-semibold">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-top fs-7 text-gray-800 fw-bold">
                    <span>Total Faktur:</span>
                    <span>Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2 fs-7 text-success fw-semibold">
                    <span>Sudah Diterima:</span>
                    <span>Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top fs-6 fw-bold text-primary">
                    <span>Sisa Piutang:</span>
                    <span>Rp {{ number_format($invoice->remaining_balance, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
