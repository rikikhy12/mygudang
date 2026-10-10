@extends('layouts.print')

@section('title', 'Cetak Tagihan ' . $invoice->invoice_number)
@section('toolbar_title', 'Faktur Tagihan Pembelian (Accounts Payable)')

@section('doc_title', 'FAKTUR TAGIHAN PEMBELIAN')
@section('doc_number', $invoice->invoice_number)

@section('doc_status')
    @if($invoice->status === 'paid')
        <span class="doc-status-badge badge-success">Lunas (PAID)</span>
    @elseif($invoice->status === 'partial')
        <span class="doc-status-badge badge-warning">Sebagian (PARTIAL)</span>
    @else
        <span class="doc-status-badge badge-danger">Belum Bayar (UNPAID)</span>
    @endif
@endsection

@section('doc_content')
    <div class="party-section">
        <div class="party-box">
            <div class="party-title">Supplier / Pemasok</div>
            <div class="party-name">{{ $invoice->supplier->name }}</div>
            <div class="party-detail">
                <div><strong>Kode:</strong> {{ $invoice->supplier->code }}</div>
                <div><strong>No. Faktur Supplier:</strong> {{ $invoice->supplier_invoice_number ?: '-' }}</div>
                <div><strong>Telepon:</strong> {{ $invoice->supplier->phone ?: '-' }}</div>
                <div><strong>NPWP:</strong> {{ $invoice->supplier->tax_number ?: '-' }}</div>
            </div>
        </div>

        <div class="party-box">
            <div class="party-title">Detail Dokumen & Hutang</div>
            <div class="party-detail">
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Tanggal Tagihan:</span>
                    <strong>{{ $invoice->invoice_date->format('d/m/Y') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Jatuh Tempo:</span>
                    <strong style="color: #b91c1c;">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Dokumen Penerimaan (GRN):</span>
                    <strong>{{ $invoice->goodsReceipt ? $invoice->goodsReceipt->grn_number : '-' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Purchase Order (PO):</span>
                    <strong>{{ $invoice->purchaseOrder ? $invoice->purchaseOrder->po_number : '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <table class="doc-table">
        <thead>
            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                <th class="text-center" style="width: 36px;">No</th>
                <th class="text-left">Deskripsi Barang Masuk</th>
                <th class="text-center" style="width: 80px;">Satuan</th>
                <th class="text-center" style="width: 70px;">Qty</th>
                <th class="text-center" style="width: 70px;">Koli</th>
                <th class="text-right" style="width: 130px;">Harga Beli</th>
                <th class="text-right" style="width: 140px;">Subtotal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <div style="font-weight: 700; color: #0f172a;">{{ $item->item->name }}</div>
                    <div style="font-size: 11px; color: #64748b;">Kode: {{ $item->item->code }}</div>
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
            <strong>Catatan Verifikasi Pembayaran:</strong>
            <div>{{ $invoice->notes ?: 'Tagihan telah diverifikasi dengan bukti fisik penerimaan barang di gudang (GRN).' }}</div>
            <div class="terbilang-text">
                Terbilang: # {{ \App\Helpers\NumberHelper::terbilang($invoice->grand_total) }} Rupiah #
            </div>
        </div>

        <table class="totals-table">
            <tr>
                <td class="text-left" style="color: #64748b;">Subtotal:</td>
                <td class="text-right font-semibold">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($invoice->tax_amount > 0 || $invoice->tax_rate > 0)
            <tr>
                <td class="text-left" style="color: #64748b;">PPN Masukan ({{ $invoice->tax_rate ?? 0 }}%):</td>
                <td class="text-right font-semibold">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td class="text-left">TOTAL TAGIHAN:</td>
                <td class="text-right">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
            </tr>
            @if($invoice->paid_amount > 0)
            <tr>
                <td class="text-left" style="color: #166534; font-weight: 600;">Sudah Dibayar:</td>
                <td class="text-right" style="color: #166534; font-weight: 700;">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-left" style="color: #b91c1c; font-weight: 700;">Sisa Hutang:</td>
                <td class="text-right" style="color: #b91c1c; font-weight: 800;">Rp {{ number_format($invoice->remaining_balance, 0, ',', '.') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="signatures-grid">
        <div class="sig-box">
            <div class="sig-title">Verifikator Tagihan (AP)</div>
            <div class="sig-name">{{ $invoice->createdBy ? $invoice->createdBy->name : 'Staff Akuntansi' }}</div>
            <div class="sig-role">Accounts Payable Officer</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Pemeriksa Keuangan</div>
            <div class="sig-name">Head of Accounting</div>
            <div class="sig-role">Finance & Accounting</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Disetujui Pembayaran</div>
            <div class="sig-name">Finance Director</div>
            <div class="sig-role">Otorisasi Pembayaran</div>
        </div>
    </div>
@endsection
