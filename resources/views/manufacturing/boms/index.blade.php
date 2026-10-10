@extends('layouts.app')

@section('title', 'Bill of Materials (BOM)')
@section('page_title', 'Bill of Materials (BOM)')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-diagram-2-fill fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Daftar Bill of Materials (BOM)</h3>
                <span class="text-muted fs-8 fw-semibold">Formula resep komponen bahan baku untuk paket bundling dan barang hasil manufaktur</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('manufacturing.boms.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-plus-lg me-1"></i> Buat BOM Baru
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-4">Kode BOM</th>
                        <th>Nama Formula</th>
                        <th>Barang Jadi / Paket</th>
                        <th>Tipe Barang</th>
                        <th class="text-center">Jumlah Komponen</th>
                        <th class="text-end">Est. Biaya Bahan</th>
                        <th class="text-end">Total HPP / Unit</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4 no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @forelse($boms as $bom)
                    <tr>
                        <td class="ps-4 fw-bold text-primary">{{ $bom->code }}</td>
                        <td class="fw-bold text-gray-800">{{ $bom->name }}</td>
                        <td>
                            <div class="fw-bold text-gray-900">{{ $bom->item?->name ?? '-' }}</div>
                            <span class="text-muted fs-8">Hasil: {{ number_format($bom->yield_quantity, 2) }} {{ $bom->item?->unit?->symbol }}</span>
                        </td>
                        <td>
                            @if($bom->item?->item_type === 'bundle')
                                <span class="badge badge-light-warning fw-bold">Paket / Bundle</span>
                            @elseif($bom->item?->item_type === 'manufactured_good')
                                <span class="badge badge-light-primary fw-bold">Manufaktur (WO)</span>
                            @else
                                <span class="badge badge-light-info fw-bold">{{ ucfirst(str_replace('_', ' ', $bom->item?->item_type ?? 'Barang')) }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge badge-light-secondary fs-8 fw-bold">{{ $bom->items_count }} item</span>
                        </td>
                        <td class="text-end fw-bold text-gray-700">
                            Rp {{ number_format($bom->calculateMaterialCost(), 0, ',', '.') }}
                        </td>
                        <td class="text-end fw-bolder text-success">
                            Rp {{ number_format($bom->calculateTotalCost(), 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if($bom->is_active)
                                <span class="badge badge-light-success">Aktif</span>
                            @else
                                <span class="badge badge-light-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('manufacturing.boms.edit', $bom->id) }}">
                                            <i class="bi bi-pencil-square text-primary me-2"></i> Edit Resep BOM
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form action="{{ route('manufacturing.boms.destroy', $bom->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus resep BOM {{ $bom->code }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item py-2 text-danger">
                                                <i class="bi bi-trash text-danger me-2"></i> Hapus BOM
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-6 text-muted">
                            <i class="bi bi-inbox fs-2x mb-2 d-block"></i>
                            Belum ada Bill of Materials (BOM) terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">
            {{ $boms->links() }}
        </div>
    </div>
</div>
@endsection
