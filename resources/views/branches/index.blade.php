@extends('layouts.app')

@section('title', 'Master Cabang (Branch)')
@section('page_title', 'Master Cabang Perusahaan')

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
                <i class="bi bi-geo-alt-fill fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Daftar Cabang Perusahaan</h3>
                <span class="text-muted fs-8 fw-semibold">Kelola unit kerja kantor pusat, cabang operasional, dan kepemilikan gudang</span>
            </div>
        </div>
        <a href="{{ route('branches.create') }}" class="btn btn-primary btn-sm fw-bold">
            <i class="bi bi-plus-circle me-1"></i> Tambah Cabang Baru
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-4" style="width: 130px;">Kode Cabang</th>
                        <th>Nama Cabang</th>
                        <th>Tipe Cabang</th>
                        <th>Akun Kas POS & Setoran</th>
                        <th>Kontak & Email</th>
                        <th>Alamat Operasional</th>
                        <th class="text-center">Gudang</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4 no-sort" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-700">
                    @forelse($branches as $branch)
                        <tr>
                            <td class="ps-4">
                                <span class="badge badge-light-primary fw-bold fs-7">{{ $branch->code }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-gray-900 fs-6">{{ $branch->name }}</div>
                                @if($branch->is_main)
                                    <span class="badge badge-light-info fs-9 fw-semibold">PUSAT</span>
                                @endif
                            </td>
                            <td>
                                @if($branch->is_main)
                                    <span class="badge badge-primary fw-bold">Kantor Pusat</span>
                                @else
                                    <span class="badge badge-light-secondary text-muted fw-semibold">Kantor Cabang</span>
                                @endif
                            </td>
                            <td>
                                @if($branch->cashAccount)
                                    <div class="text-gray-900 fs-8 fw-bold">
                                        <i class="bi bi-wallet2 text-success me-1"></i> {{ $branch->cashAccount->name }}
                                    </div>
                                @else
                                    <span class="badge badge-light-warning fs-9 text-muted">Belum Diatur</span>
                                @endif
                                @if($branch->depositAccount)
                                    <div class="text-muted fs-9">
                                        <i class="bi bi-bank text-info me-1"></i> Setoran: {{ $branch->depositAccount->name }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($branch->phone)
                                    <div class="text-gray-800 fs-7"><i class="bi bi-telephone text-muted me-1"></i>{{ $branch->phone }}</div>
                                @endif
                                @if($branch->email)
                                    <div class="text-muted fs-8"><i class="bi bi-envelope text-muted me-1"></i>{{ $branch->email }}</div>
                                @endif
                                @if(!$branch->phone && !$branch->email)
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-gray-700 fs-7 text-truncate" style="max-width: 200px;">
                                    {{ $branch->address ?? '-' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light-secondary fs-8 fw-semibold">
                                    {{ $branch->warehouses_count ?? $branch->warehouses->count() }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($branch->is_active)
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
                                            <a class="dropdown-item py-2" href="{{ route('branches.edit', $branch->id) }}">
                                                <i class="bi bi-pencil-square text-primary me-2"></i> Edit Cabang
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('branches.create') }}?copy_id={{ $branch->id }}">
                                                <i class="bi bi-files text-info me-2"></i> Salin / Duplikasi
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <form action="{{ route('branches.destroy', $branch->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus cabang {{ $branch->name }}? Pastikan tidak ada gudang terafiliasi.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item py-2 text-danger">
                                                    <i class="bi bi-trash text-danger me-2"></i> Hapus Cabang
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                                Belum ada data cabang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
