@extends('layouts.app')

@section('title', 'Voucher ' . $trx->transaction_number)
@section('page_title', 'Detail Voucher Transaksi Kas / Bank')

@section('content')
<div class="d-flex flex-column gap-5 col-xl-9 col-lg-11 mx-auto">
    
    <div class="d-flex align-items-center justify-content-between">
        <a href="{{ route('finance.index') }}" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Mutasi Kas
        </a>

        <a href="{{ route('finance.print', $trx->id) }}" target="_blank" class="btn btn-info btn-sm text-white">
            <i class="bi bi-printer me-1"></i> Cetak Bukti Voucher
        </a>
    </div>

    <div class="card card-flush shadow-sm">
        <div class="card-header pt-6">
            <div class="card-title">
                <div>
                    <h3 class="fw-bolder text-gray-900 fs-3 mb-1">{{ $trx->transaction_number }}</h3>
                    <div class="text-muted fs-7">
                        Tanggal: <strong class="text-gray-800">{{ $trx->transaction_date->format('d F Y') }}</strong> | Oleh: <strong class="text-gray-800">{{ $trx->creator->name }}</strong>
                    </div>
                </div>
            </div>
            <div class="card-toolbar">
                @if($trx->type == 'cash_in')
                    <span class="badge badge-light-success fs-7 fw-bold px-4 py-2">Kwitansi Kas Masuk</span>
                @elseif($trx->type == 'cash_out')
                    <span class="badge badge-light-danger fs-7 fw-bold px-4 py-2">Voucher Kas Keluar</span>
                @else
                    <span class="badge badge-light-info fs-7 fw-bold px-4 py-2">Bukti Transfer Kas</span>
                @endif
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="row g-5 mb-6">
                <div class="col-md-6">
                    <div class="p-5 rounded bg-light border border-gray-200">
                        <span class="text-muted fw-bold fs-8 text-uppercase">Akun Rekening Kas/Bank</span>
                        <div class="text-gray-900 fw-bolder fs-5 mt-1">{{ $trx->account->code }} - {{ $trx->account->name }}</div>
                        @if($trx->targetAccount)
                            <div class="text-primary fs-7 fw-bold mt-2">&rarr; Rekening Tujuan: {{ $trx->targetAccount->code }} - {{ $trx->targetAccount->name }}</div>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-5 rounded {{ $trx->type == 'cash_in' ? 'bg-light-success border-success' : ($trx->type == 'cash_out' ? 'bg-light-danger border-danger' : 'bg-light-info border-info') }} border border-dashed">
                        <span class="text-muted fw-bold fs-8 text-uppercase">Total Nominal Voucher</span>
                        <div class="fw-bolder fs-2hx mt-1 {{ $trx->type == 'cash_in' ? 'text-success' : ($trx->type == 'cash_out' ? 'text-danger' : 'text-info') }}">
                            Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                        </div>
                        <div class="text-muted fs-8 mt-1">Ref Eksternal: {{ $trx->reference_no ?: '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="p-4 rounded bg-light border border-gray-200 mb-6">
                <strong class="text-gray-800">Keterangan / Keperluan:</strong> <span class="text-gray-700">{{ $trx->description }}</span>
                @if($trx->contact)
                    <div class="text-muted fs-7 mt-1">Kontak Rekanan: <strong class="text-gray-800">{{ $trx->contact->name }}</strong></div>
                @endif
            </div>

            <!-- Breakdown items -->
            @if($trx->items->count() > 0)
            <div class="table-responsive mb-6">
                <h4 class="fw-bolder text-gray-800 fs-6 mb-3">Rincian Alokasi Akun (Contra Entries)</h4>
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                    <thead>
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                            <th>No.</th>
                            <th>Akun Alokasi</th>
                            <th>Memo / Rincian</th>
                            <th class="text-end">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach($trx->items as $idx => $item)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><strong class="text-gray-800">{{ $item->account->code }}</strong> - {{ $item->account->name }}</td>
                            <td>{{ $item->notes ?: '-' }}</td>
                            <td class="text-end text-gray-900 fw-bold">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Journal Ref -->
            @if($trx->journal)
            <div class="border-top pt-4 d-flex align-items-center justify-content-between">
                <span class="text-muted fs-7">Jurnal Akuntansi Terkait:</span>
                <a href="{{ route('accounting.journals.show', $trx->journal_id) }}" class="btn btn-light-primary btn-sm">
                    <i class="bi bi-journal-check me-1"></i> {{ $trx->journal->journal_number }}
                </a>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
