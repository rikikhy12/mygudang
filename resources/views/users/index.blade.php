@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page_title', 'Kelola Pengguna Sistem')

@section('content')
<div class="card card-flush shadow-sm">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-person-gear fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Daftar Pengguna & Akses Role</h3>
                <span class="text-muted fs-8 fw-semibold">Kelola akun staf, otorisasi login, dan penugasan hak akses role</span>
            </div>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-person-plus-fill me-1"></i> Tambah Pengguna Baru
            </a>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>Nama Pengguna</th>
                        <th>Alamat Email</th>
                        <th>Role Akses</th>
                        <th>Tanggal Terdaftar</th>
                        <th class="text-end min-w-100px pe-3 no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($users as $u)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-35px symbol-circle me-3">
                                    <span class="symbol-label bg-light-primary text-primary fw-bolder fs-6">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 fw-bold text-hover-primary mb-1">{{ $u->name }}</span>
                                    @if($u->id === auth()->id())
                                        <span class="badge badge-light-info fw-bold fs-8 w-fit-content">Akun Anda</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $u->email }}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($u->roles as $role)
                                    <span class="badge badge-light-primary fw-bold">{{ $role->display_name }}</span>
                                @empty
                                    <span class="badge badge-light-secondary">Tanpa Role</span>
                                @endforelse
                            </div>
                        </td>
                        <td>{{ $u->created_at->format('d M Y, H:i') }}</td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('users.edit', $u->id) }}">
                                            <i class="bi bi-pencil-square text-warning me-2"></i> Edit Pengguna
                                        </a>
                                    </li>
                                    @if($u->id !== auth()->id())
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Hapus akun pengguna {{ $u->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item py-2 text-danger">
                                                <i class="bi bi-trash text-danger me-2"></i> Hapus Pengguna
                                            </button>
                                        </form>
                                    </li>
                                    @endif
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
