@extends('layouts.app')

@section('title', 'Tambah Merk / Brand Baru')
@section('page_title', 'Tambah Merk Produk Baru')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Tambah Merk / Brand Produk Baru</h2>
            <p class="text-muted fs-7 mb-0">Daftarkan merk produk baru untuk klasifikasi master item & inventaris</p>
        </div>
        <a href="{{ route('brands.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Merk
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-patch-plus text-primary fs-3"></i> Formulir Merk Baru
                </h3>
                <span class="text-muted fs-7 fw-normal d-block mt-1">
                    Daftarkan merk produk baru untuk klasifikasi master item & inventaris
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('brands.store') }}">
            @csrf
            
            <div class="card-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Kode Merk (Opsional)</label>
                        <input type="text" name="code" class="form-control form-control-solid form-control-sm" value="{{ old('code') }}" placeholder="Auto generate jika kosong" />
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Nama Merk / Brand</label>
                        <input type="text" name="name" class="form-control form-control-solid form-control-sm" value="{{ old('name') }}" required placeholder="Contoh: Asus / Unilever / Indofood" />
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Deskripsi / Keterangan Merk</label>
                        <textarea name="description" class="form-control form-control-solid form-control-sm" rows="3" placeholder="Keterangan merk produk atau profil produsen...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check form-check-solid p-3 rounded bg-light border border-gray-200">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', '1') ? 'checked' : '' }} />
                            <label class="form-check-label fw-semibold text-gray-800 fs-7" for="is_active">
                                Status Merk Aktif
                                <span class="d-block text-muted fs-8">Merk aktif dapat dipilih saat input master barang dan filter laporan</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
                <a href="{{ route('brands.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary fw-bold">
                    <i class="bi bi-check2-circle me-1"></i> Simpan Merk
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
