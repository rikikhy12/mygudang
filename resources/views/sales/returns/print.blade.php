@extends('layouts.print')

@section('title', 'Cetak Nota Retur Penjualan ' . $return->return_number)
@section('toolbar_title', 'Nota Retur Penjualan (Sales Return Note)')

@section('doc_title', 'NOTA RETUR PENJUALAN')
@section('doc_number', $return->return_number)

@section('doc_status')
    @if($return->status === 'completed')
        <span class="doc-status-badge badge-success">Selesai (COMPLETED)</span>
    @elseif($return->status === 'draft')
        <span class="doc-status-badge badge-warning">Draft</span>
    @else
        <span class="doc-status-badge badge-danger">Dibatalkan</span>
    @endif
@endsection

@section('doc_content')
    <div class="party-section">
        <div class="party-box">
            <div class="party-title">Pelanggan / Customer</div>
            <div class="party-name">{{ $return->customer->name ?? '-' }}</div>
            <div class="party-detail">
                <div><strong>Kode:</strong> {{ $return->customer->code ?? '-' }}</div>
                <div><strong>Telepon:</strong> {{ $return->customer->phone ?? '-' }}</div>
                <div><strong>Alamat:</strong> {{ $return->customer->address ?? '-' }}</div>
            </div>
        </div>

        <div class="party-box">
            <div class="party-title">Detail Dokumen Retur</div>
            <div class="party-detail">
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Tanggal Retur:</span>
                    <strong>{{ $return->return_date->format('d/m/Y') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Gudang Penerima:</span>
                    <strong style="color: #16a34a;">{{ $return->warehouse->name ?? '-' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Faktur AR Terkait:</span>
                    <strong>{{ $return->salesInvoice ? $return->salesInvoice->invoice_number : 'Retur Langsung' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Metode Kompensasi:</span>
                    <strong>{{ $return->refund_method === 'potong_piutang' ? 'Potong Piutang AR' : 'Pengembalian Kas Refund' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <table class="doc-table">
        <thead>
            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                <th class="text-center" style="width: 36px;">No</th>
                <th class="text-left">Nama & Kode Barang yang Dikembalikan</th>
                <th class="text-center" style="width: 80px;">Satuan</th>
                <th class="text-center" style="width: 70px;">Qty</th>
                <th class="text-right" style="width: 130px;">Harga Satuan</th>
                <th class="text-right" style="width: 140px;">Subtotal (Rp)</th>
                <th class="text-left" style="width: 160px;">Alasan Retur</th>
            </tr>
        </thead>
        <tbody>
            @foreach($return->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <div style="font-weight: 700; color: #0f172a;">{{ $item->item->name }}</div>
                    <div style="font-size: 11px; color: #64748b;">Kode: {{ $item->item->code }}</div>
                </td>
                <td class="text-center">{{ $item->unit->symbol ?? $item->item->unit->symbol ?? 'PCS' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                <td style="font-size: 11px; color: #475569;">{{ $item->reason ?: '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-section" style="display: flex; justify-content: space-between; margin-top: 15px;">
        <div style="max-width: 55%; font-size: 12px; color: #475569;">
            @if($return->notes)
                <div><strong>Catatan:</strong> {{ $return->notes }}</div>
            @endif
            <div style="margin-top: 8px; font-style: italic;">
                * Barang telah diterima dan dimasukkan kembali ke persediaan stok gudang.
            </div>
        </div>

        <div style="width: 320px;">
            <table style="width: 100%; font-size: 13px;">
                <tr>
                    <td style="padding: 3px 0; color: #64748b;">Subtotal:</td>
                    <td style="padding: 3px 0; text-align: right; font-weight: 600;">Rp {{ number_format($return->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($return->tax_amount > 0)
                <tr>
                    <td style="padding: 3px 0; color: #64748b;">PPN:</td>
                    <td style="padding: 3px 0; text-align: right; font-weight: 600;">Rp {{ number_format($return->tax_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr style="border-top: 1px solid #cbd5e1;">
                    <td style="padding: 6px 0; font-weight: 700; color: #0f172a; font-size: 14px;">Total Retur:</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #d97706; font-size: 15px;">Rp {{ number_format($return->grand_total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="signature-section" style="margin-top: 40px; display: flex; justify-content: space-between; text-align: center;">
        <div style="width: 200px;">
            <div style="font-size: 12px; color: #64748b; margin-bottom: 50px;">Diserahkan Oleh (Pelanggan)</div>
            <div style="border-top: 1px solid #0f172a; font-weight: 700;">( {{ $return->customer->name ?? 'Pelanggan' }} )</div>
        </div>
        <div style="width: 200px;">
            <div style="font-size: 12px; color: #64748b; margin-bottom: 50px;">Petugas Penerima Gudang</div>
            <div style="border-top: 1px solid #0f172a; font-weight: 700;">( {{ $return->createdBy->name ?? 'Staff Gudang' }} )</div>
        </div>
        <div style="width: 200px;">
            <div style="font-size: 12px; color: #64748b; margin-bottom: 50px;">Disetujui Oleh (Finance / Spv)</div>
            <div style="border-top: 1px solid #0f172a; font-weight: 700;">( ..................................... )</div>
        </div>
    </div>
@endsection
