@extends('layouts.print')

@section('title', 'Cetak Faktur ' . $invoice->invoice_number)
@section('toolbar_title', 'Faktur Penjualan (Accounts Receivable Invoice)')

@section('doc_title', 'FAKTUR PENJUALAN')
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
    <!-- Party Information -->
    <div class="party-section">
        <div class="party-box">
            <div class="party-title">Ditagihkan Kepada (Pelanggan)</div>
            <div class="party-name">{{ $invoice->customer->name }}</div>
            <div class="party-detail">
                <div><strong>Kode:</strong> {{ $invoice->customer->code }}</div>
                <div><strong>Alamat:</strong> {{ $invoice->customer->address ?: '-' }}</div>
                <div><strong>Telepon:</strong> {{ $invoice->customer->phone ?: '-' }}</div>
                <div><strong>NPWP:</strong> {{ $invoice->customer->tax_number ?: '-' }}</div>
            </div>
        </div>

        <div class="party-box">
            <div class="party-title">Detail Faktur & Pengiriman</div>
            <div class="party-detail">
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Tanggal Faktur:</span>
                    <strong>{{ $invoice->invoice_date->format('d/m/Y') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Jatuh Tempo:</span>
                    <strong style="color: #b91c1c;">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>No. Surat Jalan (DO):</span>
                    <strong>{{ $invoice->deliveryOrder ? $invoice->deliveryOrder->do_number : '-' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>No. Sales Order (SO):</span>
                    <strong>{{ $invoice->salesOrder ? $invoice->salesOrder->so_number : '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table class="doc-table">
        <thead>
            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                <th class="text-center" style="width: 36px;">No</th>
                <th class="text-left">Deskripsi Produk / Jasa</th>
                <th class="text-center" style="width: 80px;">Satuan</th>
                <th class="text-center" style="width: 70px;">Qty</th>
                <th class="text-center" style="width: 70px;">Koli</th>
                <th class="text-right" style="width: 130px;">Harga Satuan</th>
                <th class="text-right" style="width: 140px;">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <div style="font-weight: 700; color: #0f172a;">{{ $item->item->name }}</div>
                    <div style="font-size: 11px; color: #64748b;">SKU: {{ $item->item->code }}</div>
                </td>
                <td class="text-center">{{ $item->item->unit ? $item->item->unit->symbol : 'PCS' }}</td>
                <td class="text-center font-bold">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                <td class="text-center font-bold" style="color: #2563eb;">{{ $item->koli ?: '-' }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-right" style="font-weight: 700;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals & Notes -->
    <div class="summary-container">
        <div class="notes-box">
            <strong>Catatan & Pembayaran Transfer:</strong>
            <div>{{ $invoice->notes ?: 'Pembayaran ditransfer ke rekening Bank BCA 123-456-7890 a/n RIZAL STORE - MYGUDANG ERP.' }}</div>
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
                <td class="text-left" style="color: #64748b;">PPN ({{ $invoice->tax_rate ?? 0 }}%):</td>
                <td class="text-right font-semibold">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td class="text-left">TOTAL FAKTUR:</td>
                <td class="text-right">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
            </tr>
            @if($invoice->paid_amount > 0)
            <tr>
                <td class="text-left" style="color: #166534; font-weight: 600;">Sudah Dibayar:</td>
                <td class="text-right" style="color: #166534; font-weight: 700;">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-left" style="color: #b91c1c; font-weight: 700;">Sisa Tagihan:</td>
                <td class="text-right" style="color: #b91c1c; font-weight: 800;">Rp {{ number_format($invoice->remaining_balance, 0, ',', '.') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Signatures Section -->
    <div class="signatures-grid">
        <div class="sig-box">
            <div class="sig-title">Diterima Oleh (Customer)</div>
            <div class="sig-name">( &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )</div>
            <div class="sig-role">Cap & Tanda Tangan</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Finance & Billing</div>
            <div class="sig-name">{{ $invoice->createdBy ? $invoice->createdBy->name : 'Staff Keuangan' }}</div>
            <div class="sig-role">Bagian Keuangan</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Hormat Kami,</div>
            <div class="sig-name">Authorized Signature</div>
            <div class="sig-role">MYGUDANG ERP Management</div>
        </div>
    </div>
@endsection
