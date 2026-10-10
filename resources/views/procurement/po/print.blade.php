@extends('layouts.print')

@section('title', 'Cetak Purchase Order ' . $po->po_number)
@section('toolbar_title', 'Surat Pesanan Pembelian (Purchase Order)')

@section('doc_title', 'PURCHASE ORDER')
@section('doc_number', $po->po_number)

@section('doc_status')
    @if($po->status === 'received')
        <span class="doc-status-badge badge-success">Diterima (Received)</span>
    @elseif($po->status === 'approved')
        <span class="doc-status-badge badge-info">Disetujui (Approved)</span>
    @else
        <span class="doc-status-badge badge-warning">Draft</span>
    @endif
@endsection

@section('doc_content')
    <div class="party-section">
        <div class="party-box">
            <div class="party-title">Vendor / Supplier Dituju</div>
            <div class="party-name">{{ $po->supplier->name }}</div>
            <div class="party-detail">
                <div><strong>Kode:</strong> {{ $po->supplier->code }}</div>
                <div><strong>Alamat:</strong> {{ $po->supplier->address ?: '-' }}</div>
                <div><strong>Telepon:</strong> {{ $po->supplier->phone ?: '-' }}</div>
                <div><strong>NPWP:</strong> {{ $po->supplier->tax_number ?: '-' }}</div>
            </div>
        </div>

        <div class="party-box">
            <div class="party-title">Informasi Pesanan & Pengiriman</div>
            <div class="party-detail">
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Tanggal PO:</span>
                    <strong>{{ $po->order_date->format('d/m/Y') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Estimasi Kirim:</span>
                    <strong>{{ $po->expected_delivery_date ? $po->expected_delivery_date->format('d/m/Y') : '-' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Purchase Requisition (PR):</span>
                    <strong>{{ $po->purchaseRequisition ? $po->purchaseRequisition->pr_number : '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <table class="doc-table">
        <thead>
            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                <th class="text-center" style="width: 36px;">No</th>
                <th class="text-left">Deskripsi Barang / Material</th>
                <th class="text-center" style="width: 80px;">Satuan</th>
                <th class="text-center" style="width: 70px;">Qty</th>
                <th class="text-center" style="width: 70px;">Koli</th>
                <th class="text-right" style="width: 130px;">Harga Satuan</th>
                <th class="text-right" style="width: 140px;">Subtotal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->items as $idx => $item)
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
            <strong>Instruksi & Ketentuan Pembelian:</strong>
            <div>{{ $po->notes ?: '1. Cantumkan nomor PO ini pada Surat Jalan & Faktur Tagihan Anda.' }}</div>
            <div class="terbilang-text">
                Terbilang: # {{ \App\Helpers\NumberHelper::terbilang($po->grand_total) }} Rupiah #
            </div>
        </div>

        <table class="totals-table">
            <tr>
                <td class="text-left" style="color: #64748b;">Subtotal:</td>
                <td class="text-right font-semibold">Rp {{ number_format($po->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($po->tax_amount > 0 || $po->tax_rate > 0)
            <tr>
                <td class="text-left" style="color: #64748b;">PPN ({{ $po->tax_rate ?? 0 }}%):</td>
                <td class="text-right font-semibold">Rp {{ number_format($po->tax_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td class="text-left">TOTAL PO:</td>
                <td class="text-right">Rp {{ number_format($po->grand_total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="signatures-grid">
        <div class="sig-box">
            <div class="sig-title">Dipesan Oleh (Purchasing)</div>
            <div class="sig-name">{{ $po->createdByUser ? $po->createdByUser->name : 'Staff Pengadaan' }}</div>
            <div class="sig-role">Purchasing Department</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Disetujui (Finance / Director)</div>
            <div class="sig-name">Authorized Manager</div>
            <div class="sig-role">Procurement Approval</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Konfirmasi Supplier</div>
            <div class="sig-name">( &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )</div>
            <div class="sig-role">Tanda Tangan & Tanggal</div>
        </div>
    </div>
@endsection
