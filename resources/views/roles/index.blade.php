@extends('layouts.app')

@section('title', 'Role & Hak Akses')
@section('page_title', 'Kelola Role & Hak Akses')

@section('content')
<div class="card card-flush shadow-sm">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-shield-lock-fill fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Daftar Role & Matriks Hak Akses</h3>
                <span class="text-muted fs-8 fw-semibold">Kelola level perizinan, matriks otorisasi modul, dan proteksi fitur pengguna</span>
            </div>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-shield-plus me-1"></i> Tambah Role Baru
            </a>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>Nama & Keterangan Role</th>
                        <th>Kode Internal</th>
                        <th>Jumlah Pengguna</th>
                        <th>Jumlah Hak Akses</th>
                        <th class="text-end min-w-100px pe-3 no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($roles as $role)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-35px symbol-circle me-3">
                                    <span class="symbol-label bg-light-info text-info fw-bolder fs-6">
                                        <i class="bi bi-shield-shaded"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 fw-bold fs-6">{{ $role->display_name }}</span>
                                    <span class="text-muted fs-7">{{ $role->description ?? 'Tidak ada keterangan' }}</span>
                                </div>
                            </div>
                        </td>
                        <td><code>{{ $role->name }}</code></td>
                        <td>
                            <span class="badge badge-light-primary fw-bold fs-7">{{ $role->users_count }} Pengguna</span>
                        </td>
                        <td>
                            <span class="badge badge-light-success fw-bold fs-7">{{ $role->permissions->count() }} Izin Terhubung</span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('roles.edit', $role->id) }}">
                                            <i class="bi bi-pencil-square text-warning me-2"></i> Edit Hak Akses
                                        </a>
                                    </li>
                                    @if($role->name !== 'admin')
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Hapus role {{ $role->display_name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item py-2 text-danger">
                                                <i class="bi bi-trash text-danger me-2"></i> Hapus Role
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-10 text-muted">Belum ada role terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
