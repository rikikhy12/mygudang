@extends('layouts.app')

@section('title', 'Preview Data Import Barang')
@section('page_title', 'Preview Data Import Master Barang')

@section('content')
<div class="d-flex flex-column gap-5">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Preview & Konfirmasi Import Barang</h2>
            <p class="text-muted fs-7 mb-0">Periksa tabel data hasil pembacaan file sebelum menyimpannya ke database</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('items.import') }}" class="btn btn-light btn-sm fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Upload Ulang File Lain
            </a>
            <form action="{{ route('items.import.store') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-sm fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i> Konfirmasi & Simpan ke Database
                </button>
            </form>
        </div>
    </div>

    <!-- Summary KPI Badges -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card bg-light-primary border border-primary border-dashed p-4">
                <div class="fs-8 fw-bold text-primary text-uppercase">Total Baris Terbaca</div>
                <div class="fs-2 fw-bolder text-gray-900 mt-1">{{ count($rows) }} Item</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light-success border border-success border-dashed p-4">
                <div class="fs-8 fw-bold text-success text-uppercase">Item Valid Siap Import</div>
                <div class="fs-2 fw-bolder text-gray-900 mt-1">{{ count(array_filter($rows, fn($r) => $r['status'] !== 'error')) }} Item</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light-warning border border-warning border-dashed p-4">
                <div class="fs-8 fw-bold text-warning text-uppercase">Item Perlu Perhatian</div>
                <div class="fs-2 fw-bolder text-gray-900 mt-1">{{ count(array_filter($rows, fn($r) => $r['status'] === 'warning' || $r['status'] === 'error')) }} Item</div>
            </div>
        </div>
    </div>

    <!-- Table Preview Card -->
    <div class="card card-flush shadow-sm">
        <div class="card-header pt-4">
            <h3 class="card-title fs-5 fw-bold text-gray-900">
                <i class="bi bi-table text-primary me-2"></i> Tabel Data Preview
            </h3>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                    <thead>
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                            <th>Status</th>
                            <th>Kode SKU</th>
                            <th>Barcode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Merk</th>
                            <th>Satuan</th>
                            <th>Tipe</th>
                            <th class="text-end">Harga Beli</th>
                            <th class="text-end">Harga Jual</th>
                            <th>Catatan Validasi</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-700">
                        @foreach($rows as $r)
                        <tr>
                            <td>
                                @if($r['status'] == 'valid')
                                    <span class="badge badge-light-success fw-bold"><i class="bi bi-check-circle me-1"></i> Siap</span>
                                @elseif($r['status'] == 'warning')
                                    <span class="badge badge-light-warning fw-bold"><i class="bi bi-exclamation-triangle me-1"></i> Update</span>
                                @else
                                    <span class="badge badge-light-danger fw-bold"><i class="bi bi-x-circle me-1"></i> Error</span>
                                @endif
                            </td>
                            <td><code>{{ $r['code'] }}</code></td>
                            <td><span class="text-muted fs-8">{{ $r['barcode'] ?: '-' }}</span></td>
                            <td><span class="text-gray-900 fw-bold">{{ $r['name'] }}</span></td>
                            <td><span class="badge badge-light-secondary">{{ $r['category'] ?: 'Umum' }}</span></td>
                            <td><span class="badge badge-light-info">{{ $r['brand'] ?: '-' }}</span></td>
                            <td><span class="badge badge-light-primary">{{ $r['unit'] }}</span></td>
                            <td><span class="text-muted fs-8">{{ $r['item_type'] }}</span></td>
                            <td class="text-end">Rp {{ number_format($r['purchase_price'], 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-gray-900">Rp {{ number_format($r['selling_price'], 0, ',', '.') }}</td>
                            <td>
                                @if(!empty($r['messages']))
                                    @foreach($r['messages'] as $msg)
                                        <div class="fs-8 {{ $r['status'] == 'error' ? 'text-danger' : ($r['status'] == 'warning' ? 'text-warning' : 'text-muted') }}">
                                            &bull; {{ $msg }}
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-success fs-8">Valid</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-4 d-flex justify-content-between align-items-center border-top">
            <a href="{{ route('items.import') }}" class="btn btn-light btn-sm">Batal / Upload Ulang</a>
            <form action="{{ route('items.import.store') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="bi bi-check2-circle me-1"></i> Konfirmasi & Simpan {{ count($rows) }} Barang ke Database
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
