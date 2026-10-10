@extends('layouts.print')

@php
    $typeLabel = match($transaction->type) {
        'cash_in' => 'BUKTI KAS / BANK MASUK (RECEIPT VOUCHER)',
        'cash_out' => 'BUKTI KAS / BANK KELUAR (PAYMENT VOUCHER)',
        'transfer' => 'BUKTI TRANSFER ANTAR REKENING KAS / BANK',
        default => 'BUKTI TRANSAKSI KEUANGAN',
    };
    $totalAmount = $transaction->total_amount ?: ($transaction->items ? $transaction->items->sum('amount') : 0);
@endphp

@section('title', 'Cetak Voucher ' . $transaction->transaction_number)
@section('toolbar_title', $typeLabel)

@section('doc_title', $typeLabel)
@section('doc_number', $transaction->transaction_number)

@section('doc_status')
    <span class="doc-status-badge badge-success">POSTED / VALID</span>
@endsection

@section('doc_content')
    <div class="party-section">
        <div class="party-box">
            <div class="party-title">Akun Utama Kas / Bank</div>
            <div class="party-name">{{ $transaction->account ? $transaction->account->name : '-' }}</div>
            <div class="party-detail">
                <div><strong>Kode Akun:</strong> {{ $transaction->account ? $transaction->account->code : '-' }}</div>
                <div><strong>Pihak Terkait (Kontak):</strong> {{ $transaction->contact ? $transaction->contact->name : '-' }}</div>
            </div>
        </div>

        <div class="party-box">
            <div class="party-title">Informasi Voucher Transaksi</div>
            <div class="party-detail">
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Tanggal Transaksi:</span>
                    <strong>{{ $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') : '-' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Tipe Transaksi:</span>
                    <strong style="text-transform: uppercase;">{{ str_replace('_', ' ', $transaction->type) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Dibuat Oleh:</span>
                    <strong>{{ $transaction->creator ? $transaction->creator->name : 'Staff Keuangan' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <table class="doc-table">
        <thead>
            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                <th class="text-center" style="width: 36px;">No</th>
                <th class="text-left">Akun Lawan & Kategori</th>
                <th class="text-left">Keterangan / Uraian Transaksi</th>
                <th class="text-right" style="width: 170px;">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @if($transaction->items && $transaction->items->count() > 0)
                @foreach($transaction->items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <div style="font-weight: 700; color: #0f172a;">{{ $item->account ? $item->account->name : '-' }}</div>
                        <div style="font-size: 11px; color: #64748b;">Kode Akun: {{ $item->account ? $item->account->code : '-' }}</div>
                    </td>
                    <td>{{ $item->notes ?: ($transaction->notes ?: '-') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td class="text-center">1</td>
                    <td>
                        <div style="font-weight: 700; color: #0f172a;">{{ $transaction->toAccount ? $transaction->toAccount->name : 'Akun Kas/Bank Transfer' }}</div>
                        <div style="font-size: 11px; color: #64748b;">Kode: {{ $transaction->toAccount ? $transaction->toAccount->code : '-' }}</div>
                    </td>
                    <td>{{ $transaction->notes ?: 'Transfer internal kas & bank' }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="summary-container">
        <div class="notes-box">
            <strong>Catatan / Memo:</strong>
            <div>{{ $transaction->notes ?: 'Transaksi keuangan telah dicatat dan diverifikasi oleh bagian akuntansi.' }}</div>
            <div class="terbilang-text">
                Terbilang: # {{ \App\Helpers\NumberHelper::terbilang($totalAmount) }} Rupiah #
            </div>
        </div>

        <table class="totals-table">
            <tr class="grand-total">
                <td class="text-left">TOTAL VOUCHER:</td>
                <td class="text-right">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="signatures-grid">
        <div class="sig-box">
            <div class="sig-title">Diserahkan / Diterima Pihak Luar</div>
            <div class="sig-name">( &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )</div>
            <div class="sig-role">Penyetor / Penerima</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Kasir / Treasury Officer</div>
            <div class="sig-name">{{ $transaction->creator ? $transaction->creator->name : 'Kasir' }}</div>
            <div class="sig-role">Bagian Kasir / Keuangan</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Diperiksa / Disetujui</div>
            <div class="sig-name">Finance Supervisor</div>
            <div class="sig-role">Head of Finance</div>
        </div>
    </div>
@endsection
