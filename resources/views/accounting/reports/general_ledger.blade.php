@extends('layouts.app')

@section('title', 'Buku Besar (General Ledger)')
@section('page_title', 'Laporan Buku Besar (General Ledger Detail)')

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-book-half fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Buku Besar Akun (General Ledger)</h3>
                <span class="text-muted fs-8 fw-semibold">Rincian mutasi debit/kredit dan saldo berjalan per akun buku besar</span>
            </div>
        </div>

        <button class="btn btn-sm btn-light-primary fw-bold" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Buku Besar
        </button>
    </div>

    <div class="card-body p-4 pt-0">
        <!-- Filter Form -->
        <div class="bg-light p-3 rounded-3 mb-4 mt-3">
            <form method="GET" action="{{ route('accounting.reports.general-ledger') }}" class="d-flex align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-7 fw-bold text-gray-700">Akun:</span>
                    <select name="account_id" class="form-select form-select-sm" style="min-width: 250px;" onchange="this.form.submit()">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>
                                {{ $acc->code }} - {{ $acc->name }} ({{ ucfirst($acc->type) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex align-items-center gap-1 fs-7 text-muted">
                    <span class="fw-bold text-gray-700">Dari:</span>
                    <input type="date" name="start_date" class="form-control form-control-sm" style="width: 140px;" value="{{ $startDate }}">
                </div>

                <div class="d-flex align-items-center gap-1 fs-7 text-muted">
                    <span class="fw-bold text-gray-700">Sampai:</span>
                    <input type="date" name="end_date" class="form-control form-control-sm" style="width: 140px;" value="{{ $endDate }}">
                </div>

                <button type="submit" class="btn btn-sm btn-primary fw-bold">
                    <i class="bi bi-filter me-1"></i> Tampilkan
                </button>
            </form>
        </div>
        @if($report)
        <!-- Header Account Info -->
        <div class="bg-light border border-gray-200 rounded-3 p-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="fs-8 text-muted fw-bold text-uppercase">Akun Buku Besar</div>
                <div class="fs-4 fw-bold text-gray-900 mt-1">{{ $report['account']->code }} - {{ $report['account']->name }}</div>
                <div class="fs-7 text-muted mt-1">
                    Klasifikasi: <strong class="text-gray-800">{{ ucfirst($report['account']->type) }}</strong> | Saldo Normal: <strong class="text-gray-800">{{ strtoupper($report['account']->normal_balance) }}</strong>
                </div>
            </div>

            <div class="text-end">
                <div class="fs-8 text-muted fw-bold text-uppercase">Saldo Awal Periode</div>
                <div class="fs-3 fw-bold text-primary mt-1">Rp {{ number_format($report['opening_balance'], 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-3 py-3">Tanggal</th>
                        <th>No. Jurnal</th>
                        <th>Referensi</th>
                        <th>Keterangan / Uraian</th>
                        <th class="text-end">Debit (Rp)</th>
                        <th class="text-end">Kredit (Rp)</th>
                        <th class="text-end pe-3">Saldo Berjalan</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    <tr class="bg-light-secondary fst-italic text-muted">
                        <td class="ps-3">{{ date('d/m/Y', strtotime($startDate)) }}</td>
                        <td colspan="3">Saldo Awal Periode (Sebelum {{ date('d/m/Y', strtotime($startDate)) }})</td>
                        <td class="text-end">-</td>
                        <td class="text-end">-</td>
                        <td class="text-end pe-3 fw-bold text-gray-900">Rp {{ number_format($report['opening_balance'], 0, ',', '.') }}</td>
                    </tr>

                    @forelse($report['entries'] as $entry)
                    <tr>
                        <td class="ps-3">{{ date('d/m/Y', strtotime($entry['date'])) }}</td>
                        <td><strong class="text-gray-900">{{ $entry['journal_number'] }}</strong></td>
                        <td><span class="badge badge-light fw-semibold">{{ $entry['reference'] }}</span></td>
                        <td>{{ $entry['description'] }}</td>
                        <td class="text-end fw-bold text-success">
                            {{ $entry['debit'] > 0 ? number_format($entry['debit'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-end fw-bold text-primary">
                            {{ $entry['credit'] > 0 ? number_format($entry['credit'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-end pe-3 fw-bold text-gray-900">
                            Rp {{ number_format($entry['balance'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Tidak ada transaksi pada periode ini.
                        </td>
                    </tr>
                    @endforelse

                    <tr class="bg-light fw-bold fs-7">
                        <td colspan="4" class="text-end ps-3 text-gray-800">Total Mutasi & Saldo Akhir:</td>
                        <td class="text-end text-success fs-6">Rp {{ number_format($report['total_debit'], 0, ',', '.') }}</td>
                        <td class="text-end text-primary fs-6">Rp {{ number_format($report['total_credit'], 0, ',', '.') }}</td>
                        <td class="text-end pe-3 text-primary fs-5 fw-bolder">Rp {{ number_format($report['closing_balance'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
