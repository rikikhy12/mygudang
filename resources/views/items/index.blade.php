@extends('layouts.app')

@section('title', 'Master Barang & Jasa')
@section('page_title', 'Master Data Barang, Jasa & Produk')

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-box-seam-fill fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Katalog Master Barang & Jasa</h3>
                <span class="text-muted fs-8 fw-semibold">Kelola persediaan barang, produk jadi, bahan baku, dan tarif layanan jasa</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('items.import') }}" class="btn btn-light-success btn-sm fw-bold">
                <i class="bi bi-file-earmark-excel-fill me-1"></i> Import Excel / CSV
            </a>
            <a href="{{ route('items.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-plus-circle me-1"></i> Tambah Barang / Jasa
            </a>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('items.index') }}" class="d-flex flex-wrap align-items-center gap-2 mb-4 bg-light p-3 rounded-3">
            <div style="min-width: 200px;">
                <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ ($categoryId ?? request('category_id')) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 180px;">
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Tipe --</option>
                    <option value="finished_good" {{ ($type ?? request('type')) == 'finished_good' ? 'selected' : '' }}>Barang Jadi</option>
                    <option value="manufactured_good" {{ ($type ?? request('type')) == 'manufactured_good' ? 'selected' : '' }}>Hasil Produksi</option>
                    <option value="service" {{ ($type ?? request('type')) == 'service' ? 'selected' : '' }}>Jasa</option>
                </select>
            </div>

            @if(!empty($categoryId ?? request('category_id')) || !empty($type ?? request('type')) || !empty($brandId ?? request('brand_id')) || !empty($search ?? request('search')))
                <a href="{{ route('items.index') }}" class="btn btn-light-danger btn-sm">
                    <i class="bi bi-x-circle me-1"></i> Reset Filter
                </a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-3 py-3">Kode / SKU</th>
                        <th>Nama Barang / Jasa</th>
                        <th>Kategori & Tipe</th>
                        <th class="text-center">Stok</th>
                        <th class="text-end">Harga Beli</th>
                        <th class="text-end">Harga Jual</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3 no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @forelse($items as $item)
                    <tr>
                        <td class="ps-3">
                            <span class="text-gray-900 fw-bold">{{ $item->code }}</span>
                            @if($item->barcode)
                                <div class="text-muted fs-8"><i class="bi bi-upc-scan me-1"></i>{{ $item->barcode }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px symbol-2by3 bg-light rounded-2 me-3 overflow-hidden d-flex align-items-center justify-content-center flex-shrink-0 border border-gray-200" style="width: 40px; height: 40px;">
                                    @if($item->image_url)
                                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-100 h-100 object-fit-cover" />
                                    @else
                                        <i class="bi bi-box-seam text-gray-400 fs-4"></i>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-gray-900 fw-bold fs-6 d-block">{{ $item->name }}</span>
                                    @if($item->brand)
                                        <span class="badge badge-light-info fs-9 py-0 px-2"><i class="bi bi-patch-check me-1"></i>{{ $item->brand->name }}</span>
                                    @endif
                                    @if($item->description)
                                        <div class="text-muted fs-9 text-truncate" style="max-width: 220px;">{{ $item->description }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $item->category->name ?? '-' }}</div>
                            @if($item->is_service)
                                <span class="badge badge-light-warning fs-8">Jasa</span>
                            @elseif($item->is_manufactured)
                                <span class="badge badge-light-info fs-8">Produksi</span>
                            @else
                                <span class="badge badge-light-primary fs-8">Barang Jadi</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($item->is_service)
                                <span class="text-muted fs-8">N/A (Jasa)</span>
                            @else
                                <span class="fw-bold fs-6 {{ $item->current_stock <= $item->min_stock ? 'text-danger' : 'text-gray-900' }}">
                                    {{ number_format($item->current_stock, 0) }}
                                </span>
                                <span class="text-muted fs-8">{{ $item->unit->symbol ?? '' }}</span>
                                @if($item->current_stock <= $item->min_stock && $item->min_stock > 0)
                                    <div class="badge badge-light-danger fs-9">Stok Menipis</div>
                                @endif
                            @endif
                        </td>
                        <td class="text-end">Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold text-gray-900">
                            <div>Rp {{ number_format($item->selling_price, 0, ',', '.') }}</div>
                            @if($item->allow_change_price)
                                <span class="badge badge-light-primary fs-9" title="Harga jual dapat diubah di POS & Faktur AR"><i class="bi bi-pencil-square me-1"></i>Bisa Ubah</span>
                            @endif
                            @if($item->isPhysical())
                                @php
                                    $effectiveMargin = $item->getEffectiveMinMarginPercent();
                                    $minPrice = $item->getMinimumSellingPrice();
                                @endphp
                                @if($effectiveMargin > 0)
                                    <span class="badge badge-light-warning fs-9" title="Harga Jual Min: Rp {{ number_format($minPrice, 0, ',', '.') }}">
                                        Limit: {{ number_format($effectiveMargin, 0) }}%
                                    </span>
                                @endif
                            @else
                                <span class="badge badge-light-secondary fs-9">Bebas (Jasa)</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($item->is_active)
                                <span class="badge badge-light-success fs-8">Aktif</span>
                            @else
                                <span class="badge badge-light-danger fs-8">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('items.edit', $item->id) }}">
                                            <i class="bi bi-pencil-square text-warning me-2"></i> Edit Barang
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('items.duplicate', $item->id) }}">
                                            <i class="bi bi-files text-primary me-2"></i> Salin / Duplikasi
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('items.stock-card', $item->id) }}">
                                            <i class="bi bi-card-list text-info me-2"></i> Kartu Stok
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form action="{{ route('items.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus barang {{ $item->name }}?');">
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
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            Belum ada master barang/jasa yang terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
