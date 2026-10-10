@extends('layouts.app')

@section('title', 'Kategori Barang & Setting GL')
@section('page_title', 'Kategori Barang & Integrasi GL Akuntansi')

@section('content')
<div class="card card-flush shadow-sm">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-tags-fill fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Kategori Barang & Integrasi GL Akuntansi</h3>
                <span class="text-muted fs-8 fw-semibold">Kelola hierarki kategori parent/sub, klasifikasi tipe produk, dan pemetaan otomatis jurnal GL</span>
            </div>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-plus-circle me-1"></i> Tambah Kategori Baru
            </a>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>Nama & Kode Kategori</th>
                        <th>Kategori Induk (Parent)</th>
                        <th>Tipe</th>
                        <th>Limit Margin Min</th>
                        <th>GL Persediaan</th>
                        <th>GL HPP</th>
                        <th>GL Pembelian</th>
                        <th>GL Penjualan</th>
                        <th>Jml Barang</th>
                        <th class="text-end min-w-100px pe-3 no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($categories as $cat)
                    <tr>
                        <td>
                            <span class="text-gray-800 fw-bold fs-6">{{ $cat->name }}</span>
                            <div class="text-muted fs-8"><code>{{ $cat->code }}</code></div>
                        </td>
                        <td>
                            @if($cat->parent)
                                <span class="badge badge-light-primary fw-bold">
                                    <i class="bi bi-diagram-2 me-1"></i> {{ $cat->parent->name }}
                                </span>
                            @else
                                <span class="badge badge-light-secondary fw-semibold text-muted">Kategori Utama</span>
                            @endif
                        </td>
                        <td>
                            @if($cat->type == 'service')
                                <span class="badge badge-light-info fw-bold"><i class="bi bi-gear me-1"></i> Jasa</span>
                            @elseif($cat->type == 'finished_good')
                                <span class="badge badge-light-success fw-bold"><i class="bi bi-box-seam me-1"></i> Barang Jadi</span>
                            @elseif($cat->type == 'manufactured_good')
                                <span class="badge badge-light-primary fw-bold"><i class="bi bi-tools me-1"></i> Hasil Produksi</span>
                            @endif
                        </td>
                        <td>
                            @if($cat->type == 'service')
                                <span class="badge badge-light-secondary fs-9">Bebas (Jasa)</span>
                            @elseif($cat->min_margin_percent > 0)
                                <span class="badge badge-light-warning fw-bold fs-8">
                                    <i class="bi bi-shield-lock me-1"></i> Min {{ number_format($cat->min_margin_percent, 0) }}%
                                </span>
                            @else
                                <span class="badge badge-light-secondary fs-9">0% (Tanpa Limit)</span>
                            @endif
                        </td>
                        <td>
                            @if($cat->inventoryAccount)
                                <div class="fw-bold text-gray-800 fs-8">{{ $cat->inventoryAccount->code }}</div>
                                <div class="text-muted fs-9">{{ $cat->inventoryAccount->name }}</div>
                            @else
                                <span class="text-muted fs-8">-</span>
                            @endif
                        </td>
                        <td>
                            @if($cat->cogsAccount)
                                <div class="fw-bold text-gray-800 fs-8">{{ $cat->cogsAccount->code }}</div>
                                <div class="text-muted fs-9">{{ $cat->cogsAccount->name }}</div>
                            @else
                                <span class="text-muted fs-8">-</span>
                            @endif
                        </td>
                        <td>
                            @if($cat->purchaseAccount)
                                <div class="fw-bold text-gray-800 fs-8">{{ $cat->purchaseAccount->code }}</div>
                                <div class="text-muted fs-9">{{ $cat->purchaseAccount->name }}</div>
                            @else
                                <span class="text-muted fs-8">-</span>
                            @endif
                        </td>
                        <td>
                            @if($cat->salesAccount)
                                <div class="fw-bold text-gray-800 fs-8">{{ $cat->salesAccount->code }}</div>
                                <div class="text-muted fs-9">{{ $cat->salesAccount->name }}</div>
                            @else
                                <span class="text-muted fs-8">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-light-secondary fw-bold">{{ $cat->items_count }} Item</span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('categories.edit', $cat->id) }}">
                                            <i class="bi bi-pencil-square text-warning me-2"></i> Edit Kategori
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form action="{{ route('categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Hapus kategori {{ $cat->name }}?');">
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
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
