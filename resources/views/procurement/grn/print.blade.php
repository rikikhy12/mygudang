@extends('layouts.print')

@section('title', 'Cetak Bukti Penerimaan ' . $grn->grn_number)
@section('toolbar_title', 'Bukti Penerimaan Barang (Goods Receipt Note)')

@section('doc_title', 'BUKTI PENERIMAAN BARANG')
@section('doc_number', $grn->grn_number)

@section('doc_status')
    @if($grn->status === 'posted')
        <span class="doc-status-badge badge-success">Diterima & Dijurnal (Posted)</span>
    @else
        <span class="doc-status-badge badge-warning">Draft</span>
    @endif
@endsection

@section('doc_content')
    <div class="party-section">
        <div class="party-box">
            <div class="party-title">Supplier / Pengirim</div>
            <div class="party-name">{{ $grn->supplier->name }}</div>
            <div class="party-detail">
                <div><strong>Kode:</strong> {{ $grn->supplier->code }}</div>
                <div><strong>Alamat:</strong> {{ $grn->supplier->address ?: '-' }}</div>
                <div><strong>Telepon:</strong> {{ $grn->supplier->phone ?: '-' }}</div>
            </div>
        </div>

        <div class="party-box">
            <div class="party-title">Detail Penerimaan Gudang</div>
            <div class="party-detail">
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Tanggal Masuk:</span>
                    <strong>{{ $grn->receipt_date->format('d/m/Y') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Gudang Penerima:</span>
                    <strong>{{ $grn->warehouse ? $grn->warehouse->name : 'Gudang Utama' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Purchase Order (PO):</span>
                    <strong>{{ $grn->purchaseOrder ? $grn->purchaseOrder->po_number : '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <table class="doc-table">
        <thead>
            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                <th class="text-center" style="width: 36px;">No</th>
                <th class="text-left">Kode & Nama Barang Diterima</th>
                <th class="text-center" style="width: 100px;">Satuan</th>
                <th class="text-center" style="width: 100px;">Qty Fisik Diterima</th>
                <th class="text-center" style="width: 90px;">Koli</th>
                <th class="text-left" style="width: 200px;">Keterangan & Kondisi Fisik</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grn->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <div style="font-weight: 700; color: #0f172a;">{{ $item->item->name }}</div>
                    <div style="font-size: 11px; color: #64748b;">Kode SKU: {{ $item->item->code }}</div>
                </td>
                <td class="text-center">{{ $item->item->unit ? $item->item->unit->symbol : 'PCS' }}</td>
                <td class="text-center" style="font-weight: 800; font-size: 13px;">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                <td class="text-center" style="font-weight: 800; color: #2563eb;">{{ $item->koli ?: '-' }}</td>
                <td style="color: #475569; font-size: 11.5px;">Fisik baik, sesuai spesifikasi Purchase Order</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-container">
        <div class="notes-box" style="width: 100%;">
            <strong>Catatan Petugas Gudang (GRN):</strong>
            <div>{{ $grn->notes ?: 'Barang telah dihitung, diuji sampling fisik, dan ditempatkan ke rak penyimpanan gudang.' }}</div>
        </div>
    </div>

    <div class="signatures-grid">
        <div class="sig-box">
            <div class="sig-title">Kurir / Driver Pengirim</div>
            <div class="sig-name">( &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )</div>
            <div class="sig-role">Ekspedisi / Supplier</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Petugas Penerima (Checker)</div>
            <div class="sig-name">{{ $grn->receivedBy ? $grn->receivedBy->name : 'Staff Gudang' }}</div>
            <div class="sig-role">Pemeriksa Barang</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Kepala Gudang (Warehouse Head)</div>
            <div class="sig-name">Warehouse Supervisor</div>
            <div class="sig-role">Otorisasi Masuk Stok</div>
        </div>
    </div>
@endsection
