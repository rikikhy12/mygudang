@extends('layouts.app')

@section('title', 'Catat Penerimaan Barang')
@section('page_title', 'Formulir Penerimaan Barang (Goods Receipt Note)')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Catat Penerimaan Barang (GRN)</h2>
            <p class="text-muted fs-7 mb-0">Verifikasi fisik barang masuk ke gudang dari pesanan pembelian (PO)</p>
        </div>
        <a href="{{ route('procurement.grn.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Penerimaan
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-box-arrow-in-down text-primary fs-3"></i> Formulir Penerimaan Barang (Goods Receipt Note)
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Silakan pilih nomor PO yang diterima, tentukan gudang tujuan, dan verifikasi kuantitas fisik
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('procurement.grn.store') }}">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Informasi Dokumen Penerimaan & Rujukan PO</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Pilih Purchase Order (PO)</label>
                        <select name="purchase_order_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Pilih PO --" required onchange="if(this.value) window.location.href='{{ route('procurement.grn.create') }}?po_id=' + this.value;">
                            <option value="">-- Pilih PO --</option>
                            @foreach($openOrders as $oPo)
                                <option value="{{ $oPo->id }}" {{ $selectedPo && $selectedPo->id == $oPo->id ? 'selected' : '' }}>
                                    {{ $oPo->po_number }} - {{ $oPo->supplier->name }} (Rp {{ number_format($oPo->grand_total, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Gudang Tujuan Masuk</label>
                        <select name="warehouse_id" class="form-select form-select-solid form-select-sm" data-control="select2" required>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tanggal Penerimaan Fisik</label>
                        <input type="date" name="receipt_date" class="form-control form-control-solid form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Nomor Surat Jalan / Delivery Note Supplier</label>
                        <input type="text" name="delivery_note_number" class="form-control form-control-solid form-control-sm" placeholder="Contoh: SJ/2026/09/8821">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Catatan Tambahan Penerimaan</label>
                        <input type="text" name="notes" class="form-control form-control-solid form-control-sm" placeholder="Kondisi fisik barang, segel kontainer...">
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">
                        Rincian Barang yang Diterima
                        @if($selectedPo)
                            <span class="text-muted fs-7 fw-normal ms-1">(Berdasarkan PO: <strong>{{ $selectedPo->po_number }}</strong>)</span>
                        @endif
                    </h4>
                </div>

                @if($selectedPo)
                <div class="table-responsive rounded border mb-4">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0 no-datatable">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-4 py-3">Kode & Nama Barang</th>
                                <th class="text-center">Total Dipesan</th>
                                <th class="text-center" style="width: 110px;">Koli (Ket)</th>
                                <th class="text-center">Sudah Diterima</th>
                                <th class="text-center">Sisa Belum Tiba</th>
                                <th class="text-end pe-4" style="width: 200px;">Jumlah Diterima Saat Ini</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-700">
                            @foreach($selectedPo->items as $idx => $pItem)
                            @if($pItem->pending_quantity > 0)
                            <tr>
                                <td class="ps-4">
                                    <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $pItem->item_id }}">
                                    <span class="text-gray-900 fw-bold">{{ $pItem->item->code }}</span> - {{ $pItem->item->name }}
                                </td>
                                <td class="text-center">{{ number_format($pItem->quantity, 0) }} {{ $pItem->item->unit->symbol }}</td>
                                <td>
                                    <input type="text" name="items[{{ $idx }}][koli]" class="form-control form-control-solid form-control-sm text-center" value="{{ $pItem->koli }}" placeholder="Koli">
                                </td>
                                <td class="text-center">{{ number_format($pItem->received_quantity, 0) }}</td>
                                <td class="text-center"><span class="badge badge-light-warning fw-bold">{{ number_format($pItem->pending_quantity, 0) }}</span></td>
                                <td class="pe-4">
                                    <input type="number" step="0.01" name="items[{{ $idx }}][quantity]" class="form-control form-control-solid form-control-sm text-end" value="{{ $pItem->pending_quantity }}" max="{{ $pItem->pending_quantity }}" min="0.01" required>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-dismissible bg-light-success d-flex align-items-center p-4 mb-2 border border-success border-dashed rounded-3">
                    <i class="bi bi-info-circle-fill fs-2 text-success me-3"></i>
                    <div class="d-flex flex-column text-gray-800 fs-7">
                        <strong>Catatan Otomasi Akuntansi:</strong>
                        <span>Menyimpan penerimaan ini akan langsung menambah saldo kartu stok fisik di gudang tujuan dan membentuk Jurnal Akuntansi otomatis (Debit Persediaan vs Kredit Hutang Penerimaan Belum Ditagih / Unbilled GRN).</span>
                    </div>
                </div>
                @else
                <div class="border rounded-3 text-center py-5 text-muted bg-light mb-2">
                    <i class="bi bi-cart fs-2x text-primary mb-2 d-block"></i>
                    <div class="fw-semibold fs-7">Silakan pilih Purchase Order (PO) di atas untuk memuat daftar barang yang akan diterima.</div>
                </div>
                @endif
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('procurement.grn.index') }}" class="btn btn-sm btn-light fw-semibold">Batal</a>
                @if($selectedPo)
                <button type="submit" class="btn btn-sm btn-primary fw-semibold px-5">
                    <i class="bi bi-box-arrow-in-down me-1"></i> Konfirmasi & Posting Penerimaan Barang
                </button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection
