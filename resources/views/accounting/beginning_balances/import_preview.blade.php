@extends('layouts.app')

@section('title', 'Preview Data Import Saldo Awal')
@section('page_title', 'Preview Data Import Saldo Awal')

@section('content')
<div class="d-flex flex-column gap-5">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">
                Preview Saldo Awal {{ $type === 'stock' ? 'Stok Gudang' : 'Neraca Akun (COA)' }}
            </h2>
            <p class="text-muted fs-7 mb-0">Periksa tabel data saldo sebelum diposting ke kartu stok dan buku besar jurnal</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('accounting.beginning-balances.import') }}" class="btn btn-light btn-sm fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Upload Ulang File
            </a>
            <form action="{{ route('accounting.beginning-balances.import.store') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-sm fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i> Konfirmasi & Posting Saldo Awal
                </button>
            </form>
        </div>
    </div>

    <!-- Summary KPI Badges -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card bg-light-primary border border-primary border-dashed p-4">
                <div class="fs-8 fw-bold text-primary text-uppercase">Total Baris Terbaca</div>
                <div class="fs-2 fw-bolder text-gray-900 mt-1">{{ count($rows) }} Baris</div>
            </div>
        </div>
        @if($type === 'stock')
        <div class="col-md-4">
            <div class="card bg-light-success border border-success border-dashed p-4">
                <div class="fs-8 fw-bold text-success text-uppercase">Total Valuasi Persediaan</div>
                <div class="fs-2 fw-bolder text-gray-900 mt-1">Rp {{ number_format($totalValuation, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light-info border border-info border-dashed p-4">
                <div class="fs-8 fw-bold text-info text-uppercase">Baris Valid</div>
                <div class="fs-2 fw-bolder text-gray-900 mt-1">{{ count(array_filter($rows, fn($r) => $r['status'] === 'valid')) }} Baris</div>
            </div>
        </div>
        @else
        <div class="col-md-4">
            <div class="card bg-light-success border border-success border-dashed p-4">
                <div class="fs-8 fw-bold text-success text-uppercase">Total Debit</div>
                <div class="fs-2 fw-bolder text-gray-900 mt-1">Rp {{ number_format($totalDebit, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card {{ round($totalDebit - $totalCredit, 2) == 0 ? 'bg-light-success border-success' : 'bg-light-warning border-warning' }} border border-dashed p-4">
                <div class="fs-8 fw-bold {{ round($totalDebit - $totalCredit, 2) == 0 ? 'text-success' : 'text-warning' }} text-uppercase">
                    Total Kredit {{ round($totalDebit - $totalCredit, 2) == 0 ? '(Seimbang)' : '(Selisih Auto-Modal 3001)' }}
                </div>
                <div class="fs-2 fw-bolder text-gray-900 mt-1">Rp {{ number_format($totalCredit, 0, ',', '.') }}</div>
            </div>
        </div>
        @endif
    </div>

    <!-- Table Preview Card -->
    <div class="card card-flush shadow-sm">
        <div class="card-header pt-4">
            <h3 class="card-title fs-5 fw-bold text-gray-900">
                <i class="bi bi-table text-primary me-2"></i> Rincian Tabel Data
            </h3>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                    <thead>
                        @if($type === 'stock')
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                            <th>Status</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kode Gudang</th>
                            <th>Nama Gudang</th>
                            <th class="text-center">Jumlah Stok</th>
                            <th class="text-end">HPP Pokok</th>
                            <th class="text-end">Subtotal Valuasi</th>
                            <th>Tanggal</th>
                            <th>Catatan</th>
                        </tr>
                        @else
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                            <th>Status</th>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th>Tipe Akun</th>
                            <th class="text-end">Saldo Debit</th>
                            <th class="text-end">Saldo Kredit</th>
                            <th>Tanggal</th>
                            <th>Catatan</th>
                        </tr>
                        @endif
                    </thead>
                    <tbody class="fw-semibold text-gray-700">
                        @if($type === 'stock')
                            @foreach($rows as $r)
                            <tr>
                                <td>
                                    @if($r['status'] == 'valid')
                                        <span class="badge badge-light-success fw-bold"><i class="bi bi-check-circle me-1"></i> Valid</span>
                                    @else
                                        <span class="badge badge-light-danger fw-bold"><i class="bi bi-x-circle me-1"></i> Error</span>
                                    @endif
                                </td>
                                <td><code>{{ $r['item_code'] }}</code></td>
                                <td><span class="text-gray-900 fw-bold">{{ $r['item_name'] }}</span></td>
                                <td><code>{{ $r['warehouse_code'] }}</code></td>
                                <td><span class="badge badge-light-primary">{{ $r['warehouse_name'] }}</span></td>
                                <td class="text-center fw-bold fs-6">{{ number_format($r['quantity'], 0) }}</td>
                                <td class="text-end">Rp {{ number_format($r['cost'], 0, ',', '.') }}</td>
                                <td class="text-end fw-bold text-gray-900">Rp {{ number_format($r['subtotal'], 0, ',', '.') }}</td>
                                <td>{{ $r['date'] }}</td>
                                <td>
                                    @if(!empty($r['messages']))
                                        @foreach($r['messages'] as $msg)
                                            <div class="fs-8 text-danger">&bull; {{ $msg }}</div>
                                        @endforeach
                                    @else
                                        <span class="text-success fs-8">Siap Diposting</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        @else
                            @foreach($rows as $r)
                            <tr>
                                <td>
                                    @if($r['status'] == 'valid')
                                        <span class="badge badge-light-success fw-bold"><i class="bi bi-check-circle me-1"></i> Valid</span>
                                    @else
                                        <span class="badge badge-light-danger fw-bold"><i class="bi bi-x-circle me-1"></i> Error</span>
                                    @endif
                                </td>
                                <td><code>{{ $r['account_code'] }}</code></td>
                                <td><span class="text-gray-900 fw-bold">{{ $r['account_name'] }}</span></td>
                                <td><span class="badge badge-light-secondary">{{ ucfirst($r['account_type']) }}</span></td>
                                <td class="text-end fw-bold text-primary">Rp {{ number_format($r['debit'], 0, ',', '.') }}</td>
                                <td class="text-end fw-bold text-danger">Rp {{ number_format($r['credit'], 0, ',', '.') }}</td>
                                <td>{{ $r['date'] }}</td>
                                <td>
                                    @if(!empty($r['messages']))
                                        @foreach($r['messages'] as $msg)
                                            <div class="fs-8 text-danger">&bull; {{ $msg }}</div>
                                        @endforeach
                                    @else
                                        <span class="text-success fs-8">Siap Jurnal</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-4 d-flex justify-content-between align-items-center border-top">
            <a href="{{ route('accounting.beginning-balances.import') }}" class="btn btn-light btn-sm">Batal / Upload Ulang</a>
            <form action="{{ route('accounting.beginning-balances.import.store') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-sm px-4">
                    <i class="bi bi-check2-circle me-1"></i> Konfirmasi & Posting Saldo Awal
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
