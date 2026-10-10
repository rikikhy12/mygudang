@extends('layouts.app')

@section('title', 'Jurnal ' . $journal->journal_number)
@section('page_title', 'Detail Jurnal Umum: ' . $journal->journal_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('accounting.journals.index') }}" class="btn btn-sm btn-light-primary fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Jurnal Umum
        </a>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('accounting.journals.print', $journal->id) }}" target="_blank" class="btn btn-sm btn-info text-white">
            <i class="bi bi-printer me-1"></i> Cetak Bukti Jurnal
        </a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title fs-4 fw-bold text-gray-900 m-0">{{ $journal->journal_number }}</h3>
            <div class="fs-7 text-muted mt-1">
                Tanggal: <strong>{{ $journal->transaction_date->format('d F Y') }}</strong> | Input oleh: <strong>{{ $journal->creator ? $journal->creator->name : 'Sistem' }}</strong>
            </div>
        </div>
        <div>
            <span class="badge badge-light-success fw-bold px-3 py-2">Status: Posted (Balanced)</span>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="bg-light p-3 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase">Tipe Modul / Sumber</div>
                    <div class="fs-6 fw-bold text-gray-900 mt-1">{{ $journal->reference_type }}</div>
                    <div class="fs-7 text-muted mt-1">Ref No: <strong>{{ $journal->reference_no ?: '-' }}</strong></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-light-success p-3 rounded-3 h-100 border border-success border-dashed">
                    <div class="fs-8 fw-bold text-success text-uppercase">Total Nilai Debit</div>
                    <div class="fs-4 fw-bold text-success mt-1">
                        Rp {{ number_format($journal->total_debit, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-light-primary p-3 rounded-3 h-100 border border-primary border-dashed">
                    <div class="fs-8 fw-bold text-primary text-uppercase">Total Nilai Kredit</div>
                    <div class="fs-4 fw-bold text-primary mt-1">
                        Rp {{ number_format($journal->total_credit, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-secondary p-3 mb-4 rounded-3 fs-7">
            <strong>Keterangan / Memo Jurnal:</strong> {{ $journal->description }}
        </div>

        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-3 py-3" style="width: 50px;">No.</th>
                        <th>Kode Akun</th>
                        <th>Nama Akun (COA)</th>
                        <th>Klasifikasi</th>
                        <th>Memo Baris</th>
                        <th class="text-end">Debit (Rp)</th>
                        <th class="text-end pe-3">Kredit (Rp)</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @foreach($journal->entries as $idx => $entry)
                    <tr>
                        <td class="ps-3">{{ $idx + 1 }}</td>
                        <td><code>{{ $entry->account->code }}</code></td>
                        <td>
                            <strong class="text-gray-900">{{ $entry->account->name }}</strong>
                        </td>
                        <td><span class="badge badge-light fw-semibold text-gray-700">{{ ucfirst($entry->account->type) }}</span></td>
                        <td class="fs-8 text-muted">{{ $entry->memo ?: '-' }}</td>
                        <td class="text-end fw-bold text-success">
                            {{ $entry->type == 'debit' ? number_format($entry->amount, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-end pe-3 fw-bold text-primary">
                            {{ $entry->type == 'credit' ? number_format($entry->amount, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="bg-light fw-bold fs-7">
                        <td colspan="5" class="text-end ps-3 text-gray-800">TOTAL:</td>
                        <td class="text-end text-success fs-6">Rp {{ number_format($journal->total_debit, 0, ',', '.') }}</td>
                        <td class="text-end pe-3 text-primary fs-6">Rp {{ number_format($journal->total_credit, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
