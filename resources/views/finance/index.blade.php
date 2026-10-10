@extends('layouts.app')

@section('title', 'Finance - Kas & Bank')
@section('page_title', 'Finance: Manajemen Kas & Bank')

@section('content')
<!-- Kas / Bank Account Balances -->
<div class="row g-5 g-xl-8 mb-6">
    @foreach($accountsSummary as $summary)
    <div class="col-xl-4 col-md-6">
        <div class="card card-flush h-100 shadow-sm">
            <div class="card-body d-flex align-items-center p-6">
                <div class="symbol symbol-45px symbol-circle bg-light-primary me-4">
                    <span class="symbol-label text-primary"><i class="bi bi-bank fs-2"></i></span>
                </div>
                <div>
                    <span class="text-muted fw-bold fs-8 text-uppercase">Kode: <code>{{ $summary['account']->code }}</code></span>
                    <div class="text-gray-900 fw-bolder fs-5">{{ $summary['account']->name }}</div>
                    <div class="text-primary fw-bolder fs-3 mt-1">Rp {{ number_format($summary['balance'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card card-flush shadow-sm">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-wallet2 fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Mutasi Kas & Rekening Bank</h3>
                <span class="text-muted fs-8 fw-semibold">Pencatatan kas masuk, kas keluar operasional, dan transfer antar rekening</span>
            </div>
        </div>

        <div class="card-toolbar d-flex gap-2">
            <a href="{{ route('finance.cash-in.create') }}" class="btn btn-light-success btn-sm fw-bold">
                <i class="bi bi-arrow-down-left-circle-fill me-1"></i> Kas Masuk
            </a>
            <a href="{{ route('finance.cash-out.create') }}" class="btn btn-light-danger btn-sm fw-bold">
                <i class="bi bi-arrow-up-right-circle-fill me-1"></i> Kas Keluar
            </a>
            <a href="{{ route('finance.transfer.create') }}" class="btn btn-light-info btn-sm fw-bold">
                <i class="bi bi-arrow-left-right me-1"></i> Transfer Bank
            </a>
        </div>
    </div>

    <div class="card-body p-4 pt-0">
        <!-- Filter Tabs -->
        <div class="d-flex flex-wrap gap-2 mb-4 border-bottom pb-4 pt-3">
            <a href="{{ route('finance.index') }}" class="btn btn-sm {{ !$type ? 'btn-primary fw-bold' : 'btn-light' }}">Semua Transaksi</a>
            <a href="{{ route('finance.index', ['type' => 'cash_in']) }}" class="btn btn-sm {{ $type == 'cash_in' ? 'btn-primary fw-bold' : 'btn-light' }}">Kas Masuk</a>
            <a href="{{ route('finance.index', ['type' => 'cash_out']) }}" class="btn btn-sm {{ $type == 'cash_out' ? 'btn-primary fw-bold' : 'btn-light' }}">Kas Keluar</a>
            <a href="{{ route('finance.index', ['type' => 'transfer']) }}" class="btn btn-sm {{ $type == 'transfer' ? 'btn-primary fw-bold' : 'btn-light' }}">Transfer Kas</a>
        </div>
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Akun Kas / Bank</th>
                        <th>Kontak Rekanan</th>
                        <th>Deskripsi / Keperluan</th>
                        <th>Total Nominal</th>
                        <th>Jurnal</th>
                        <th class="text-end min-w-80px no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($transactions as $trx)
                    <tr>
                        <td>
                            <a href="{{ route('finance.show', $trx->id) }}" class="text-gray-800 fw-bold text-hover-primary">
                                {{ $trx->transaction_number }}
                            </a>
                        </td>
                        <td>{{ $trx->transaction_date->format('d/m/Y') }}</td>
                        <td>
                            @if($trx->type == 'cash_in')
                                <span class="badge badge-light-success fw-bold"><i class="bi bi-arrow-down-left text-success me-1"></i> Kas Masuk</span>
                            @elseif($trx->type == 'cash_out')
                                <span class="badge badge-light-danger fw-bold"><i class="bi bi-arrow-up-right text-danger me-1"></i> Kas Keluar</span>
                            @else
                                <span class="badge badge-light-info fw-bold"><i class="bi bi-arrow-left-right text-info me-1"></i> Transfer</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-gray-800 fw-bold">{{ $trx->account->name }}</span>
                            @if($trx->targetAccount)
                                <div class="text-primary fs-8 fw-semibold">&rarr; Ke: {{ $trx->targetAccount->name }}</div>
                            @endif
                        </td>
                        <td>{{ $trx->contact ? $trx->contact->name : '-' }}</td>
                        <td class="fs-7">{{ \Illuminate\Support\Str::limit($trx->description, 35) }}</td>
                        <td>
                            <span class="fw-bolder fs-6 {{ $trx->type == 'cash_in' ? 'text-success' : ($trx->type == 'cash_out' ? 'text-danger' : 'text-info') }}">
                                {{ $trx->type == 'cash_in' ? '+' : ($trx->type == 'cash_out' ? '-' : '') }}Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            @if($trx->journal)
                                <a href="{{ route('accounting.journals.show', $trx->journal_id) }}" class="badge badge-light-primary fw-bold">
                                    <i class="bi bi-journal-check me-1"></i> {{ $trx->journal->journal_number }}
                                </a>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('finance.show', $trx->id) }}">
                                            <i class="bi bi-eye text-primary me-2"></i> Lihat Rincian
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
