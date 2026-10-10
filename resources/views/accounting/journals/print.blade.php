@extends('layouts.print')

@section('title', 'Cetak Jurnal ' . $journal->journal_number)
@section('toolbar_title', 'Bukti Jurnal Umum (General Journal Voucher)')

@section('doc_title', 'BUKTI JURNAL UMUM (VOUCHER)')
@section('doc_number', $journal->journal_number)

@section('doc_status')
    <span class="doc-status-badge badge-success">POSTED / BALANCED</span>
@endsection

@section('doc_content')
    <div class="party-section">
        <div class="party-box">
            <div class="party-title">Referensi Transaksi</div>
            <div class="party-name">{{ $journal->reference_type }}</div>
            <div class="party-detail">
                <div><strong>No. Dokumen Terkait:</strong> {{ $journal->reference_number ?: '-' }}</div>
                <div><strong>Keterangan:</strong> {{ $journal->description }}</div>
            </div>
        </div>

        <div class="party-box">
            <div class="party-title">Detail Jurnal Akuntansi</div>
            <div class="party-detail">
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Tanggal Jurnal:</span>
                    <strong>{{ $journal->transaction_date ? \Carbon\Carbon::parse($journal->transaction_date)->format('d/m/Y') : '-' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Dibuat Oleh:</span>
                    <strong>{{ $journal->creator ? $journal->creator->name : 'Administrator' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Waktu Input:</span>
                    <strong>{{ $journal->created_at ? $journal->created_at->format('d/m/Y H:i') : '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <table class="doc-table">
        <thead>
            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                <th class="text-center" style="width: 36px;">No</th>
                <th class="text-left" style="width: 110px;">Kode COA</th>
                <th class="text-left">Nama Akun & Uraian Memo</th>
                <th class="text-right" style="width: 140px;">Debit (Rp)</th>
                <th class="text-right" style="width: 140px;">Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalDebit = 0; $totalCredit = 0; @endphp
            @foreach($journal->entries as $idx => $entry)
            @php 
                if ($entry->type === 'debit') { $totalDebit += $entry->amount; }
                else { $totalCredit += $entry->amount; }
            @endphp
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td style="font-family: monospace; font-weight: 700;">{{ $entry->account ? $entry->account->code : '-' }}</td>
                <td>
                    <div style="font-weight: 700; color: #0f172a; {{ $entry->type === 'credit' ? 'padding-left: 20px;' : '' }}">
                        {{ $entry->account ? $entry->account->name : '-' }}
                    </div>
                    @if($entry->memo)
                        <div style="font-size: 11px; color: #64748b; {{ $entry->type === 'credit' ? 'padding-left: 20px;' : '' }}">
                            {{ $entry->memo }}
                        </div>
                    @endif
                </td>
                <td class="text-right font-semibold">
                    {{ $entry->type === 'debit' ? 'Rp ' . number_format($entry->amount, 0, ',', '.') : '-' }}
                </td>
                <td class="text-right font-semibold">
                    {{ $entry->type === 'credit' ? 'Rp ' . number_format($entry->amount, 0, ',', '.') : '-' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-container">
        <div class="notes-box">
            <strong>Keterangan Ringkas:</strong>
            <div>{{ $journal->description }}</div>
            <div class="terbilang-text">
                Terbilang Total Debit: # {{ \App\Helpers\NumberHelper::terbilang($totalDebit) }} Rupiah #
            </div>
        </div>

        <table class="totals-table">
            <tr>
                <td class="text-left" style="color: #64748b;">Total Debit:</td>
                <td class="text-right font-bold">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-left" style="color: #64748b;">Total Kredit:</td>
                <td class="text-right font-bold">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
            </tr>
            <tr class="grand-total">
                <td class="text-left">STATUS BALANCE:</td>
                <td class="text-right" style="color: {{ $totalDebit == $totalCredit ? '#166534' : '#b91c1c' }};">
                    {{ $totalDebit == $totalCredit ? 'SEIMBANG (OK)' : 'TIDAK SEIMBANG' }}
                </td>
            </tr>
        </table>
    </div>

    <div class="signatures-grid">
        <div class="sig-box">
            <div class="sig-title">Dibuat Oleh (Bookkeeper)</div>
            <div class="sig-name">{{ $journal->creator ? $journal->creator->name : 'Staff Akuntansi' }}</div>
            <div class="sig-role">Input Jurnal</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Diperiksa (Accounting Senior)</div>
            <div class="sig-name">Senior Accountant</div>
            <div class="sig-role">Verifikasi Saldo Akun</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Disetujui (Finance Manager)</div>
            <div class="sig-name">Accounting Manager</div>
            <div class="sig-role">Otorisasi Buku Besar</div>
        </div>
    </div>
@endsection
