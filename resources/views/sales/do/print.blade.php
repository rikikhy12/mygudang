@extends('layouts.print')

@section('title', 'Cetak Surat Jalan ' . $do->do_number)
@section('toolbar_title', 'Surat Jalan Pengiriman (Delivery Order)')

@section('doc_title', 'SURAT JALAN PENGIRIMAN')
@section('doc_number', $do->do_number)

@section('doc_status')
    @if($do->status === 'posted')
        <span class="doc-status-badge badge-success">Terkirim (POSTED)</span>
    @else
        <span class="doc-status-badge badge-warning">Draft</span>
    @endif
@endsection

@section('doc_content')
    <div class="party-section">
        <div class="party-box">
            <div class="party-title">Alamat Penerima / Pengiriman</div>
            <div class="party-name">{{ $do->customer->name }}</div>
            <div class="party-detail">
                <div><strong>Alamat Tujuan:</strong> {{ $do->shipping_address ?: ($do->customer->address ?: '-') }}</div>
                <div><strong>Telepon:</strong> {{ $do->customer->phone ?: '-' }}</div>
            </div>
        </div>

        <div class="party-box">
            <div class="party-title">Detail Pengiriman & Ekspedisi</div>
            <div class="party-detail">
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Tanggal Kirim:</span>
                    <strong>{{ $do->delivery_date->format('d/m/Y') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Gudang Asal:</span>
                    <strong>{{ $do->warehouse ? $do->warehouse->name : 'Gudang Utama' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>No. Sales Order (SO):</span>
                    <strong>{{ $do->salesOrder ? $do->salesOrder->so_number : '-' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Sopir / Kurir / No. Pol:</span>
                    <strong>{{ $do->driver_name ?: '-' }} ({{ $do->vehicle_no ?: '-' }})</strong>
                </div>
            </div>
        </div>
    </div>

    <table class="doc-table">
        <thead>
            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                <th class="text-center" style="width: 36px;">No</th>
                <th class="text-left">Kode & Nama Barang</th>
                <th class="text-center" style="width: 100px;">Satuan</th>
                <th class="text-center" style="width: 100px;">Qty Dikirim</th>
                <th class="text-center" style="width: 90px;">Koli</th>
                <th class="text-left" style="width: 200px;">Keterangan / Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($do->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <div style="font-weight: 700; color: #0f172a;">{{ $item->item->name }}</div>
                    <div style="font-size: 11px; color: #64748b;">Kode SKU: {{ $item->item->code }}</div>
                </td>
                <td class="text-center">{{ $item->item->unit ? $item->item->unit->symbol : 'PCS' }}</td>
                <td class="text-center" style="font-weight: 800; font-size: 13px;">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                <td class="text-center" style="font-weight: 800; color: #2563eb;">{{ $item->koli ?: '-' }}</td>
                <td style="color: #475569; font-size: 11.5px;">Baik, lengkap dan tersegel pabrik</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-container">
        <div class="notes-box" style="width: 100%;">
            <strong>Instruksi & Syarat Penerimaan Barang:</strong>
            <div>1. Harap barang diperiksa dengan seksama saat serah terima di lokasi.</div>
            <div>2. Komplain atas kekurangan / cacat barang maksimal 1 x 24 jam sejak surat jalan ditandatangani.</div>
            <div>3. Surat jalan ini merupakan bukti sah penyerahan fisik barang dari pengangkut ke penerima.</div>
        </div>
    </div>

    <div class="signatures-grid">
        <div class="sig-box">
            <div class="sig-title">Penerima Barang (Customer)</div>
            <div class="sig-name">( &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )</div>
            <div class="sig-role">Nama Terang & Cap Stempel</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Pengemudi / Ekspedisi</div>
            <div class="sig-name">{{ $do->driver_name ?: '( Sopir / Kurir )' }}</div>
            <div class="sig-role">Pengangkut Barang</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Bagian Gudang (Warehouse)</div>
            <div class="sig-name">{{ $do->createdByUser ? $do->createdByUser->name : 'Staff Logistik' }}</div>
            <div class="sig-role">Petugas Pengeluaran Barang</div>
        </div>
    </div>
@endsection
