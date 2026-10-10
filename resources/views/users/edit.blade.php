@extends('layouts.app')

@section('title', 'Edit Pengguna')
@section('page_title', 'Perbarui Data Pengguna')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Edit Pengguna</h2>
            <p class="text-muted fs-7 mb-0">Perbarui profil nama pengguna, email, kata sandi, atau penugasan role</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pengguna
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary fs-3"></i> Edit Pengguna: {{ $user->name }}
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Perbarui profil identitas pengguna, kata sandi baru (opsional), dan peran akses
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('users.update', $user->id) }}">
            @csrf
            @method('PUT')
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Informasi Akun & Kredensial</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control form-control-solid form-control-sm" value="{{ old('name', $user->name) }}" required placeholder="Nama lengkap karyawan..." />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Alamat Email</label>
                        <input type="email" name="email" class="form-control form-control-solid form-control-sm" value="{{ old('email', $user->email) }}" required placeholder="nama@perusahaan.com" />
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Kata Sandi Baru (Opsional)</label>
                        <input type="password" name="password" class="form-control form-control-solid form-control-sm" placeholder="Kosongkan jika tidak ingin mengubah kata sandi..." />
                        <div class="form-text fs-8 text-muted mt-1">Biarkan kosong jika kata sandi tidak ingin diperbarui.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Persentase Komisi Sales (%) <span class="badge badge-light-secondary fs-9">Opsional</span></label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.01" min="0" max="100" name="commission_rate" class="form-control form-control-solid" value="{{ old('commission_rate', $user->commission_rate) }}" placeholder="Contoh: 2.5" />
                            <span class="input-group-text bg-light">%</span>
                        </div>
                        <div class="form-text fs-8 text-muted mt-1">Digunakan untuk menghitung komisi penjualan otomatis jika user ini adalah Sales.</div>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Penugasan Role / Hak Akses</h4>
                </div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required mb-3">Pilih Role Akses</label>
                        @php
                            $userRoleIds = old('roles', $user->roles->pluck('id')->toArray());
                        @endphp
                        <div class="row g-3">
                            @foreach($roles as $role)
                            <div class="col-md-6">
                                <label class="d-flex align-items-center justify-content-between p-4 rounded-3 bg-light border border-gray-200 cursor-pointer h-100">
                                    <div class="d-flex flex-column me-3">
                                        <span class="fw-bold text-gray-800 fs-7">{{ $role->display_name }}</span>
                                        <span class="text-muted fs-8">{{ $role->description ?? 'Akses modul standar' }}</span>
                                    </div>
                                    <div class="form-check form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" {{ in_array($role->id, $userRoleIds) ? 'checked' : '' }} />
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-check2-circle me-1"></i> Perbarui Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

