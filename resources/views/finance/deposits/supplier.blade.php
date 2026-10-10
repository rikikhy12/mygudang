@extends('layouts.app')

@section('title', 'Deposit Pemasok (Supplier)')
@section('page_title', 'Deposit Pemasok (Supplier)')

@section('content')
<div class="d-flex flex-column gap-5 w-100">
    
    <!-- KPI Metric Cards -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card bg-light-success border border-success border-dashed p-4 rounded-3 h-100 shadow-xs">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase">Total Deposit Disetor (Uang Muka)</div>
                        <div class="fs-3 fw-bolder text-success mt-1">Rp {{ number_format($totalDepositsIn, 0, ',', '.') }}</div>
                    </div>
                    <div class="w-45px h-45px rounded-circle bg-success text-white d-flex align-items-center justify-content-center fs-3">
                        <i class="bi bi-arrow-down-left"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light-danger border border-danger border-dashed p-4 rounded-3 h-100 shadow-xs">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase">Total Pemotongan Tagihan AP</div>
                        <div class="fs-3 fw-bolder text-danger mt-1">Rp {{ number_format($totalDepositsOut, 0, ',', '.') }}</div>
                    </div>
                    <div class="w-45px h-45px rounded-circle bg-danger text-white d-flex align-items-center justify-content-center fs-3">
                        <i class="bi bi-arrow-up-right"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light-primary border border-primary border-dashed p-4 rounded-3 h-100 shadow-xs">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase">Sisa Saldo Uang Muka di Supplier</div>
                        <div class="fs-3 fw-bolder text-primary mt-1">Rp {{ number_format($currentNetDeposit, 0, ',', '.') }}</div>
                    </div>
                    <div class="w-45px h-45px rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fs-3">
                        <i class="bi bi-wallet-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center">
                <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                    <i class="bi bi-building-check fs-3 text-primary"></i>
                </div>
                <div>
                    <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Kelola Deposit Pemasok (Supplier)</h3>
                    <span class="text-muted fs-8 fw-semibold">Pencatatan uang muka pembelian barang, saldo deposit ke vendor, dan pemotongan faktur tagihan AP</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('finance.deposits.customer') }}" class="btn btn-sm btn-light fw-bold">
                    <i class="bi bi-people-fill me-1"></i> Buka Deposit Customer
                </a>
                <a href="{{ route('finance.deposits.create', ['type' => 'supplier']) }}" class="btn btn-primary btn-sm fw-bold">
                    <i class="bi bi-plus-circle me-1"></i> Input Mutasi Deposit
                </a>
            </div>
        </div>
        
        <div class="card-body p-0">
            <!-- Tabs -->
            <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-6 fw-bold px-4 border-bottom">
                <li class="nav-item">
                    <a class="nav-link text-active-primary py-3 active" data-bs-toggle="tab" href="#tab_summary">
                        <i class="bi bi-truck me-1"></i> Ringkasan Saldo per Supplier
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-active-primary py-3" data-bs-toggle="tab" href="#tab_history">
                        <i class="bi bi-clock-history me-1"></i> Riwayat Mutasi Transaksi
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Tab Summary -->
                <div class="tab-pane fade show active" id="tab_summary" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                            <thead>
                                <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                    <th class="ps-4" style="width: 130px;">Kode</th>
                                    <th>Nama Pemasok (Supplier)</th>
                                    <th>Kontak & Alamat</th>
                                    <th class="text-end">Total Masuk (Setor UM)</th>
                                    <th class="text-end">Total Terpotong AP</th>
                                    <th class="text-end">Sisa Saldo Uang Muka</th>
                                    <th class="text-center pe-4 no-sort" style="width: 140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 fw-semibold">
                                @forelse($contacts as $contact)
                                @php
                                    $in = (float) $contact->deposits->where('type', 'supplier')->where('transaction_type', 'in')->sum('amount');
                                    $out = (float) $contact->deposits->where('type', 'supplier')->where('transaction_type', 'out')->sum('amount');
                                    $balance = $in - $out;
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">{{ $contact->code }}</td>
                                    <td class="fw-bold text-gray-900">{{ $contact->name }}</td>
                                    <td class="text-muted fs-8">
                                        <div><i class="bi bi-telephone me-1"></i> {{ $contact->phone ?? '-' }}</div>
                                        <div><i class="bi bi-geo-alt me-1"></i> {{ Str::limit($contact->address ?? '-', 35) }}</div>
                                    </td>
                                    <td class="text-end text-success fw-bold">Rp {{ number_format($in, 0, ',', '.') }}</td>
                                    <td class="text-end text-danger fw-bold">Rp {{ number_format($out, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        <span class="badge {{ $balance > 0 ? 'badge-light-success border border-success' : 'badge-light-secondary' }} fs-7 fw-bolder">
                                            Rp {{ number_format($balance, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <a href="{{ route('finance.deposits.create', ['type' => 'supplier', 'contact_id' => $contact->id]) }}" class="btn btn-xs btn-light-primary fw-bold">
                                            <i class="bi bi-plus-circle me-1"></i> Mutasi Deposit
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                                        Belum ada data deposit pemasok terdaftar.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab History -->
                <div class="tab-pane fade" id="tab_history" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                            <thead>
                                <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                    <th class="ps-4">No. Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Nama Pemasok</th>
                                    <th>Tipe Mutasi</th>
                                    <th>Metode / Akun Kas</th>
                                    <th class="text-end">Nominal (Rp)</th>
                                    <th>Keterangan</th>
                                    <th>Dicatat Oleh</th>
                                    <th class="text-center pe-4 no-sort">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 fw-semibold">
                                @forelse($deposits as $dep)
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">{{ $dep->deposit_number }}</td>
                                    <td>{{ date('d/m/Y', strtotime($dep->payment_date)) }}</td>
                                    <td class="fw-bold text-gray-900">{{ $dep->contact?->name ?? '-' }}</td>
                                    <td>
                                        @if($dep->transaction_type === 'in')
                                            <span class="badge badge-light-success fw-bold">
                                                <i class="bi bi-arrow-down-left me-1"></i> Masuk (Setor Uang Muka)
                                            </span>
                                        @else
                                            <span class="badge badge-light-danger fw-bold">
                                                <i class="bi bi-arrow-up-right me-1"></i> Terpakai (Potong Tagihan)
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-gray-800">{{ $dep->account?->name ?? 'Kas Utama' }}</div>
                                        <span class="text-muted fs-8 text-uppercase">{{ $dep->payment_method ?? 'Bank' }}</span>
                                    </td>
                                    <td class="text-end fw-bolder {{ $dep->transaction_type === 'in' ? 'text-success' : 'text-danger' }}">
                                        {{ $dep->transaction_type === 'in' ? '+' : '-' }} Rp {{ number_format($dep->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="text-muted fs-8" style="max-width: 200px;">
                                        {{ $dep->notes ?? '-' }}
                                    </td>
                                    <td class="fs-8 text-muted">
                                        {{ $dep->creator?->name ?? 'Admin' }}
                                    </td>
                                    <td class="text-center pe-4">
                                        <form action="{{ route('finance.deposits.destroy', $dep->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan & menghapus transaksi deposit {{ $dep->deposit_number }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-light-danger btn-xs" title="Hapus Mutasi">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                                        Belum ada riwayat transaksi deposit pemasok.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
