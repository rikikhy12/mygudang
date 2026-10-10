@extends('layouts.app')

@section('title', 'Routing Proses Produksi')
@section('page_title', 'Routing Proses Produksi')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-bezier2 fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Jalur & Tahapan Routing Produksi</h3>
                <span class="text-muted fs-8 fw-semibold">Definisi urutan workflow proses manufaktur dari penimbangan, perakitan hingga quality control</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('manufacturing.routings.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-plus-lg me-1"></i> Buat Routing Baru
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-4">Kode Routing</th>
                        <th>Nama Jalur Produksi</th>
                        <th>Alur Tahapan (Steps)</th>
                        <th class="text-center">Total Tahap</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @forelse($routings as $routing)
                    <tr>
                        <td class="ps-4 fw-bold text-primary">{{ $routing->code }}</td>
                        <td>
                            <div class="fw-bold text-gray-900">{{ $routing->name }}</div>
                            <span class="text-muted fs-8">{{ $routing->description ?? '-' }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                @foreach($routing->steps as $step)
                                    <span class="badge {{ $step->is_final_step ? 'badge-light-success border border-success' : 'badge-light-primary' }} fs-8 py-1 px-2">
                                        {{ $step->step_number }}. {{ $step->name }}
                                        @if($step->is_final_step)
                                            <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                        @endif
                                    </span>
                                    @if(!$loop->last)
                                        <i class="bi bi-arrow-right text-gray-400 fs-9"></i>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-light-secondary fw-bold fs-8">{{ $routing->steps_count }} Langkah</span>
                        </td>
                        <td class="text-center">
                            @if($routing->is_active)
                                <span class="badge badge-light-success">Aktif</span>
                            @else
                                <span class="badge badge-light-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('manufacturing.routings.edit', $routing->id) }}" class="btn btn-icon btn-light-primary btn-sm" title="Edit Routing">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('manufacturing.routings.destroy', $routing->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus jalur routing ini?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-light-danger btn-sm" title="Hapus Routing">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-6 text-muted">
                            <i class="bi bi-inbox fs-2x mb-2 d-block"></i>
                            Belum ada jalur routing produksi terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">
            {{ $routings->links() }}
        </div>
    </div>
</div>
@endsection
