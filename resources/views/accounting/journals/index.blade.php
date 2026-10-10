@extends('layouts.app')

@section('title', 'Jurnal Umum (General Journal)')
@section('page_title', 'Jurnal Umum Akuntansi (Double-Entry General Journal)')

@section('content')
<div class="card card-flush shadow-sm">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-journal-bookmark-fill fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Buku Jurnal Umum (General Journal)</h3>
                <span class="text-muted fs-8 fw-semibold">Audit trail pembukuan double-entry (Debit & Kredit) dari seluruh transaksi ERP</span>
            </div>
        </div>

        <div class="card-toolbar">
            <a href="{{ route('accounting.journals.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-plus-circle me-1"></i> Input Jurnal Manual
            </a>
        </div>
    </div>

    <div class="card-body p-4 pt-0">
        <!-- Filter Form -->
        <div class="bg-light p-3 rounded-3 mb-4 mt-3">
            <form method="GET" action="{{ route('accounting.journals.index') }}" class="d-flex flex-wrap align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-7 fw-bold text-gray-700">Dari:</span>
                    <input type="date" name="start_date" class="form-control form-control-sm w-150px" value="{{ $startDate }}">
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-7 fw-bold text-gray-700">Sampai:</span>
                    <input type="date" name="end_date" class="form-control form-control-sm w-150px" value="{{ $endDate }}">
                </div>
                <select name="reference_type" class="form-select form-select-sm w-180px" onchange="this.form.submit()">
                    <option value="">-- Semua Tipe Ref --</option>
                    @foreach($refTypes as $rt)
                        <option value="{{ $rt }}" {{ $refType == $rt ? 'selected' : '' }}>{{ $rt }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-light-primary btn-sm fw-bold">
                    <i class="bi bi-funnel-fill me-1"></i> Filter Periode
                </button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>No. Jurnal</th>
                        <th>Tanggal</th>
                        <th>Tipe Ref</th>
                        <th>No. Ref</th>
                        <th>Keterangan / Memo</th>
                        <th>Total Debit</th>
                        <th>Total Kredit</th>
                        <th>Status</th>
                        <th class="text-end min-w-80px no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($journals as $jrn)
                    <tr>
                        <td>
                            <a href="{{ route('accounting.journals.show', $jrn->id) }}" class="text-gray-800 fw-bold text-hover-primary">
                                {{ $jrn->journal_number }}
                            </a>
                        </td>
                        <td>{{ $jrn->transaction_date->format('d/m/Y') }}</td>
                        <td><span class="badge badge-light-primary fw-bold">{{ $jrn->reference_type }}</span></td>
                        <td>{{ $jrn->reference_no ?: '-' }}</td>
                        <td class="fs-7">{{ \Illuminate\Support\Str::limit($jrn->description, 40) }}</td>
                        <td><span class="text-success fw-bolder">Rp {{ number_format($jrn->total_debit, 0, ',', '.') }}</span></td>
                        <td><span class="text-primary fw-bolder">Rp {{ number_format($jrn->total_credit, 0, ',', '.') }}</span></td>
                        <td>
                            <span class="badge badge-light-success fw-bold"><i class="bi bi-check2-circle me-1"></i> Balanced</span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('accounting.journals.show', $jrn->id) }}">
                                            <i class="bi bi-eye text-info me-2"></i> Lihat Detail Jurnal
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
