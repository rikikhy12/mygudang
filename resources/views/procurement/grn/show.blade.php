@extends('layouts.app')

@section('title', 'GRN ' . $grn->grn_number)
@section('page_title', 'Detail Penerimaan Barang: ' . $grn->grn_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('procurement.grn.index') }}" class="btn btn-sm btn-light-primary fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Penerimaan
        </a>
    </div>

    <div class="d-flex gap-2">
        @if($grn->invoices->count() == 0)
            <a href="{{ route('procurement.invoices.create', ['grn_id' => $grn->id]) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-receipt me-1"></i> Buat Faktur Tagihan (AP)
            </a>
        @endif
        <a href="{{ route('procurement.grn.print', $grn->id) }}" target="_blank" class="btn btn-sm btn-info text-white">
            <i class="bi bi-printer me-1"></i> Cetak Bukti Penerimaan
        </a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title fs-4 fw-bold text-gray-900 m-0">{{ $grn->grn_number }}</h3>
            <div class="fs-7 text-muted mt-1">
                Diterima oleh: <strong class="text-gray-800">{{ $grn->receivedBy->name }}</strong> pada {{ $grn->receipt_date->format('d F Y') }}
            </div>
        </div>
        <div>
            <span class="badge badge-light-success fw-bold px-3 py-2">Status: Posted</span>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="bg-light p-3 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase">Supplier / Pemasok</div>
                    <div class="fs-6 fw-bold text-gray-900 mt-1">{{ $grn->supplier->name }}</div>
                    <div class="fs-7 text-muted mt-1">Surat Jalan: <strong>{{ $grn->delivery_note_number ?: '-' }}</strong></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-light p-3 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase">Lokasi Masuk & Dokumen Induk</div>
                    <div class="fs-6 fw-bold text-gray-900 mt-1">{{ $grn->warehouse->name }}</div>
                    <div class="fs-7 text-muted mt-1">
                        Referensi PO: 
                        @if($grn->purchaseOrder)
                            <a href="{{ route('procurement.po.show', $grn->purchase_order_id) }}" class="text-primary fw-bold">{{ $grn->purchaseOrder->po_number }}</a>
                        @else
                            <span class="badge badge-light-primary fw-semibold"><i class="bi bi-lightning-charge me-1"></i> Direct (Tanpa PO)</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-light-primary p-3 rounded-3 h-100 border border-primary border-dashed">
                    <div class="fs-8 fw-bold text-primary text-uppercase">Jurnal Akuntansi Otomatis</div>
                    @if($grn->journal)
                        <div class="fs-6 fw-bold text-success mt-1">
                            <a href="{{ route('accounting.journals.show', $grn->journal_id) }}" class="text-success">{{ $grn->journal->journal_number }}</a>
                        </div>
                        <div class="fs-7 text-muted mt-1">Nilai Jurnal: Rp {{ number_format($grn->journal->total_debit, 0, ',', '.') }}</div>
                    @else
                        <div class="fs-7 text-muted mt-1">Tidak ada jurnal tercatat</div>
                    @endif
                </div>
            </div>
        </div>

        @if($grn->notes)
        <div class="alert alert-secondary p-3 mb-4 rounded-3 fs-7">
            <strong>Catatan Penerimaan:</strong> {{ $grn->notes }}
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-3 py-3" style="width: 50px;">No.</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th class="text-center">Jumlah Fisik Diterima</th>
                        <th class="text-center">Koli</th>
                        <th class="text-end">Harga Pokok Perolehan</th>
                        <th class="text-end pe-3">Total Nilai Masuk</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @php $totalGRNCost = 0; @endphp
                    @foreach($grn->items as $idx => $item)
                    @php $totalGRNCost += $item->total_cost; @endphp
                    <tr>
                        <td class="ps-3">{{ $idx + 1 }}</td>
                        <td><code>{{ $item->item->code }}</code></td>
                        <td><strong class="text-gray-900">{{ $item->item->name }}</strong></td>
                        <td class="text-center text-success fw-bold">
                            {{ (float)$item->quantity == (int)$item->quantity ? number_format($item->quantity, 0) : number_format($item->quantity, 2) }} {{ $item->item->unit->symbol }}
                        </td>
                        <td class="text-center fw-bold text-primary">{{ $item->koli ?: '-' }}</td>
                        <td class="text-end">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                        <td class="text-end pe-3 fw-bold text-gray-900">Rp {{ number_format($item->total_cost, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr class="bg-light fw-bold">
                        <td colspan="6" class="text-end fs-7 text-muted ps-3">Total Nilai Perolehan Persediaan:</td>
                        <td class="text-end pe-3 text-primary fs-6">Rp {{ number_format($totalGRNCost, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
