@extends('layouts.app')

@section('title', 'Edit Gudang')
@section('page_title', 'Edit Data Gudang / Warehouse')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Edit Gudang: {{ $warehouse->name }}</h2>
            <p class="text-muted fs-7 mb-0">Perbarui informasi gudang dan cabang afiliasinya</p>
        </div>
        <a href="{{ route('warehouses.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Gudang
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary fs-3"></i> Formulir Perubahan Data Gudang: {{ $warehouse->name }}
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Perbarui lokasi penyimpanan barang dan status operasional gudang
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('warehouses.update', $warehouse->id) }}">
            @csrf
            @method('PUT')
            
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
                                <option value="{{ $b->id }}" {{ old('branch_id', $warehouse->branch_id) == $b->id ? 'selected' : '' }}>
                                    {{ $b->code }} - {{ $b->name }} {{ $b->is_main ? '(PUSAT)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Kode Gudang</label>
                        <input type="text" name="code" class="form-control form-control-solid form-control-sm" value="{{ old('code', $warehouse->code) }}" required />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Nama Gudang</label>
                        <input type="text" name="name" class="form-control form-control-solid form-control-sm" value="{{ old('name', $warehouse->name) }}" required />
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Lokasi & Alamat Fisik</h4>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Lokasi / Alamat Lengkap Gudang</label>
                        <textarea name="location" class="form-control form-control-solid form-control-sm" rows="3">{{ old('location', $warehouse->location) }}</textarea>
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">3</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Pengaturan Status Gudang</h4>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check form-check-solid p-3 rounded bg-light border border-gray-200">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }} />
                            <label class="form-check-label fw-semibold text-gray-800 fs-7" for="is_active">
                                Status Gudang Aktif
                                <span class="d-block text-muted fs-8">Gudang aktif dapat digunakan dalam transaksi pembelian, penjualan & transfer stok</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
                <a href="{{ route('warehouses.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary fw-bold">
                    <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
