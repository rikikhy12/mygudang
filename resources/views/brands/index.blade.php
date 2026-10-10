@extends('layouts.app')

@section('title', 'Master Merk / Brand')
@section('page_title', 'Master Merk Produk')

@section('content')
@if(session('success'))
    <div class="alert alert-success d-flex align-items-center p-3 mb-4 rounded-3 fs-7">
        <i class="bi bi-check-circle-fill text-success fs-4 me-2"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger d-flex align-items-center p-3 mb-4 rounded-3 fs-7">
        <i class="bi bi-exclamation-triangle-fill text-danger fs-4 me-2"></i>
        <div>{{ session('error') }}</div>
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-patch-check-fill fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Daftar Merk & Produsen (Brands)</h3>
                <span class="text-muted fs-8 fw-semibold">Kelola label merk dagang dan produsen untuk pengelompokan barang</span>
            </div>
        </div>
        <a href="{{ route('brands.create') }}" class="btn btn-primary btn-sm fw-bold">
            <i class="bi bi-plus-circle me-1"></i> Tambah Merk Baru
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-4" style="width: 140px;">Kode Merk</th>
                        <th>Nama Merk / Brand</th>
                        <th>Deskripsi / Keterangan</th>
                        <th class="text-center">Jumlah Produk</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4 no-sort" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-700">
                    @forelse($brands as $brand)
                        <tr>
                            <td class="ps-4">
                                <span class="badge badge-light-primary fw-bold fs-7">{{ $brand->code }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-gray-900 fs-6">{{ $brand->name }}</div>
                            </td>
                            <td>
                                <div class="text-gray-700 fs-7 text-truncate" style="max-width: 300px;">
                                    {{ $brand->description ?? '-' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light-secondary fs-8 fw-semibold">
                                    {{ $brand->items_count ?? $brand->items->count() }} Produk
                                </span>
                            </td>
                            <td class="text-center">
                                @if($brand->is_active)
                                    <span class="badge badge-light-success fs-8 fw-semibold">Aktif</span>
                                @else
                                    <span class="badge badge-light-danger fs-8 fw-semibold">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-gear-fill me-1"></i> Aksi
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('brands.edit', $brand->id) }}">
                                                <i class="bi bi-pencil-square text-primary me-2"></i> Edit Merk
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('brands.create') }}?copy_id={{ $brand->id }}">
                                                <i class="bi bi-files text-info me-2"></i> Salin / Duplikasi
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <form action="{{ route('brands.destroy', $brand->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus merk {{ $brand->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item py-2 text-danger">
                                                    <i class="bi bi-trash text-danger me-2"></i> Hapus Merk
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                                Belum ada data merk terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
