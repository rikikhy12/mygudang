@extends('layouts.app')

@section('title', 'Tambah Gudang Baru')
@section('page_title', 'Tambah Gudang / Warehouse')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Tambah Gudang / Lokasi Penyimpanan</h2>
            <p class="text-muted fs-7 mb-0">Daftarkan lokasi gudang fisik dan pilih kantor cabang pemilik gudang</p>
        </div>
        <a href="{{ route('warehouses.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Gudang
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-building-add text-primary fs-3"></i> Formulir Gudang Baru
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Tentukan kantor cabang pemilik gudang dan alamat fisik penyimpanan stok
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('warehouses.store') }}">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Identitas & Afiliasi Cabang Gudang</h4>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Cabang (Branch)</label>
                        <select name="branch_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="Pilih Cabang Pemilik..." required>
                            <option value=""></option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->code }} - {{ $b->name }} {{ $b->is_main ? '(PUSAT)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Kode Gudang</label>
                        <input type="text" name="code" class="form-control form-control-solid form-control-sm" value="{{ old('code') }}" required placeholder="Contoh: WH-JKT-01" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Nama Gudang</label>
                        <input type="text" name="name" class="form-control form-control-solid form-control-sm" value="{{ old('name') }}" required placeholder="Contoh: Gudang Utama Transit" />
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Lokasi & Alamat Fisik</h4>
                </div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Lokasi / Alamat Lengkap Gudang</label>
                        <textarea name="location" class="form-control form-control-solid form-control-sm" rows="3" placeholder="Alamat fisik pergudangan, kawasan industri, kota...">{{ old('location') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
                <a href="{{ route('warehouses.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary fw-bold">
                    <i class="bi bi-check2-circle me-1"></i> Simpan Gudang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
