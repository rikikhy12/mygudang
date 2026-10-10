@extends('layouts.print')

@section('title', 'Cetak Sales Order ' . $so->so_number)
@section('toolbar_title', 'Sales Order Penjualan (SO)')

@section('doc_title', 'SALES ORDER')
@section('doc_number', $so->so_number)

@section('doc_status')
    @if($so->status === 'delivered')
        <span class="doc-status-badge badge-success">Selesai (Delivered)</span>
    @elseif($so->status === 'confirmed')
        <span class="doc-status-badge badge-info">Dikonfirmasi (Confirmed)</span>
    @else
        <span class="doc-status-badge badge-warning">Draft</span>
    @endif
@endsection

@section('doc_content')
    <div class="party-section">
        <div class="party-box">
            <div class="party-title">Pelanggan (Customer)</div>
            <div class="party-name">{{ $so->customer->name }}</div>
            <div class="party-detail">
                <div><strong>Kode:</strong> {{ $so->customer->code }}</div>
                <div><strong>Alamat:</strong> {{ $so->customer->address ?: '-' }}</div>
                <div><strong>Telepon:</strong> {{ $so->customer->phone ?: '-' }}</div>
            </div>
        </div>

        <div class="party-box">
            <div class="party-title">Detail Pesanan Penjualan</div>
            <div class="party-detail">
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Tanggal Order:</span>
                    <strong>{{ $so->order_date->format('d/m/Y') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Target Kirim:</span>
                    <strong>{{ $so->delivery_date ? $so->delivery_date->format('d/m/Y') : '-' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Sales Executive:</span>
                    <strong>{{ $so->salesPerson ? $so->salesPerson->name : 'Staff Sales' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <table class="doc-table">
        <thead>
            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                <th class="text-center" style="width: 36px;">No</th>
                <th class="text-left">Nama Barang / Layanan</th>
                <th class="text-center" style="width: 80px;">Satuan</th>
                <th class="text-center" style="width: 70px;">Qty</th>
                <th class="text-center" style="width: 70px;">Koli</th>
                <th class="text-right" style="width: 130px;">Harga Satuan</th>
                <th class="text-right" style="width: 140px;">Subtotal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($so->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <div style="font-weight: 700; color: #0f172a;">{{ $item->item->name }}</div>
                    <div style="font-size: 11px; color: #64748b;">Kode SKU: {{ $item->item->code }}</div>
                </td>
                <td class="text-center">{{ $item->item->unit ? $item->item->unit->symbol : 'PCS' }}</td>
                <td class="text-center font-bold">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                <td class="text-center font-bold" style="color: #2563eb;">{{ $item->koli ?: '-' }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-container">
        <div class="notes-box">
            <strong>Catatan & Syarat Penjualan:</strong>
            <div>{{ $so->notes ?: 'Harga berlaku sesuai kesepakatan. Pembayaran sesuai jatuh tempo pada faktur.' }}</div>
            <div class="terbilang-text">
                Terbilang: # {{ \App\Helpers\NumberHelper::terbilang($so->grand_total) }} Rupiah #
            </div>
        </div>

        <table class="totals-table">
            <tr>
                <td class="text-left" style="color: #64748b;">Subtotal:</td>
                <td class="text-right font-semibold">Rp {{ number_format($so->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($so->tax_amount > 0 || $so->tax_rate > 0)
            <tr>
                <td class="text-left" style="color: #64748b;">PPN ({{ $so->tax_rate ?? 0 }}%):</td>
                <td class="text-right font-semibold">Rp {{ number_format($so->tax_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td class="text-left">GRAND TOTAL:</td>
                <td class="text-right">Rp {{ number_format($so->grand_total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="signatures-grid">
        <div class="sig-box">
            <div class="sig-title">Disetujui Pelanggan (Customer)</div>
            <div class="sig-name">( &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )</div>
            <div class="sig-role">Tanda Tangan & Cap</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Sales Representative</div>
            <div class="sig-name">{{ $so->salesPerson ? $so->salesPerson->name : 'Staff Sales' }}</div>
            <div class="sig-role">Bagian Penjualan</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Disetujui Management</div>
            <div class="sig-name">Sales Manager</div>
            <div class="sig-role">MYGUDANG ERP</div>
        </div>
    </div>
@endsection
