@extends('layouts.app')

@section('title', 'Master Aset Tetap')
@section('page_title', 'Manajemen Aset Tetap & Depresiasi')

@section('content')
<!-- KPI Summary Cards (Metronic 8 Clean Style) -->
<div class="row g-5 g-xl-8 mb-6">
    <div class="col-xl-4">
        <div class="card card-flush shadow-sm h-100">
            <div class="card-body p-6">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-gray-500 fs-7 fw-semibold">Total Nilai Perolehan</span>
                    <div class="symbol symbol-35px symbol-circle bg-light-primary">
                        <span class="symbol-label"><i class="bi bi-laptop text-primary fs-5"></i></span>
                    </div>
                </div>
                <div class="fs-2 fw-bold text-gray-900 mb-2">
                    Rp {{ number_format($totalCost, 0, ',', '.') }}
                </div>
                <div class="d-flex justify-content-between align-items-center text-gray-500 fs-8 fw-semibold">
                    <span>Biaya Kapitalisasi:</span>
                    <span class="badge badge-light-primary fs-9 fw-bold">Nilai Akuisisi</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-flush shadow-sm h-100">
            <div class="card-body p-6">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-gray-500 fs-7 fw-semibold">Akumulasi Penyusutan</span>
                    <div class="symbol symbol-35px symbol-circle bg-light-danger">
                        <span class="symbol-label"><i class="bi bi-hourglass-bottom text-danger fs-5"></i></span>
                    </div>
                </div>
                <div class="fs-2 fw-bold text-danger mb-2">
                    Rp {{ number_format($totalAccumulated, 0, ',', '.') }}
                </div>
                <div class="d-flex justify-content-between align-items-center text-gray-500 fs-8 fw-semibold">
                    <span>Total Depresiasi:</span>
                    <span class="badge badge-light-danger fs-9 fw-bold">Akumulasi</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-flush shadow-sm h-100">
            <div class="card-body p-6">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-gray-500 fs-7 fw-semibold">Nilai Buku Bersih (Net Book Value)</span>
                    <div class="symbol symbol-35px symbol-circle bg-light-success">
                        <span class="symbol-label"><i class="bi bi-shield-check text-success fs-5"></i></span>
                    </div>
                </div>
                <div class="fs-2 fw-bold text-success mb-2">
                    Rp {{ number_format($totalBookValue, 0, ',', '.') }}
                </div>
                <div class="d-flex justify-content-between align-items-center text-gray-500 fs-8 fw-semibold">
                    <span>Valuasi Terkini:</span>
                    <span class="badge badge-light-success fs-9 fw-bold">Aktif</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-flush shadow-sm">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-laptop fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Daftar Register Aset Tetap Perusahaan</h3>
                <span class="text-muted fs-8 fw-semibold">Inventarisasi aktiva tetap, nilai perolehan, umur ekonomis, dan kalkulasi penyusutan berkala</span>
            </div>
        </div>
        
        <div class="card-toolbar d-flex gap-2">
            <a href="{{ route('assets.depreciation') }}" class="btn btn-light-primary btn-sm fw-bold">
                <i class="bi bi-calculator-fill me-1"></i> Proses Penyusutan Otomatis
            </a>
            <a href="{{ route('assets.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-plus-circle me-1"></i> Tambah Aset Baru
            </a>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>Kode Aset</th>
                        <th>Nama Aset</th>
                        <th>Kategori</th>
                        <th>Metode</th>
                        <th>Tgl Perolehan</th>
                        <th>Nilai Perolehan</th>
                        <th>Akum. Penyusutan</th>
                        <th>Nilai Buku Saat Ini</th>
                        <th>Penyusutan / Bln</th>
                        <th>Status</th>
                        <th class="text-end min-w-100px no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($assets as $asset)
                    <tr>
                        <td><code>{{ $asset->asset_code }}</code></td>
                        <td>
                            <a href="{{ route('assets.show', $asset->id) }}" class="text-gray-800 fw-bold text-hover-primary fs-6">
                                {{ $asset->name }}
                            </a>
                        </td>
                        <td><span class="badge badge-light-primary fw-bold">{{ $asset->category->name }}</span></td>
                        <td>
                            @if($asset->depreciation_method == 'declining_balance')
                                <span class="badge badge-light-warning fw-bold fs-8" title="Saldo Menurun Ganda">Saldo Menurun</span>
                            @elseif($asset->depreciation_method == 'none')
                                <span class="badge badge-light-secondary fw-bold fs-8" title="Tidak Disusutkan">Non-Depresiasi</span>
                            @else
                                <span class="badge badge-light-info fw-bold fs-8" title="Garis Lurus">Garis Lurus</span>
                            @endif
                        </td>
                        <td>{{ $asset->acquisition_date->format('d/m/Y') }}</td>
                        <td>Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}</td>
                        <td class="text-danger">Rp {{ number_format($asset->accumulated_depreciation, 0, ',', '.') }}</td>
                        <td class="text-success fw-bolder">Rp {{ number_format($asset->current_book_value, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($asset->calculateMonthlyDepreciation(), 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $asset->status == 'active' ? 'badge-light-success' : 'badge-light-danger' }} fw-bold">
                                {{ ucfirst($asset->status) }}
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('assets.show', $asset->id) }}">
                                            <i class="bi bi-eye text-info me-2"></i> Lihat Detail
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('assets.edit', $asset->id) }}">
                                            <i class="bi bi-pencil-square text-warning me-2"></i> Edit Aset
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" onsubmit="return confirm('Hapus aset {{ $asset->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item py-2 text-danger">
                                                <i class="bi bi-trash text-danger me-2"></i> Hapus
                                            </button>
                                        </form>
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
