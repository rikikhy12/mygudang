@extends('layouts.app')

@section('title', 'Edit Aset Tetap')
@section('page_title', 'Perbarui Data Aset Tetap')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Edit Master Aset Tetap</h2>
            <p class="text-muted fs-7 mb-0">Perbarui informasi status, masa manfaat, atau kategori aset</p>
        </div>
        <a href="{{ route('assets.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Aset
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary fs-3"></i> Edit Aset: {{ $asset->name }} (<code>{{ $asset->asset_code }}</code>)
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Perbarui data identitas aset, kategori GL, status, dan nilai residu/masa manfaat
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('assets.update', $asset->id) }}">
            @csrf
            @method('PUT')
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Identitas & Status Aset</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Kode Aset</label>
                        <input type="text" class="form-control form-control-solid form-control-sm bg-light" value="{{ $asset->asset_code }}" disabled readonly />
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Nama Aset Tetap</label>
                        <input type="text" name="name" class="form-control form-control-solid form-control-sm" value="{{ old('name', $asset->name) }}" required placeholder="Nama aset..." />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Kategori Aset (Setting GL)</label>
                        <select name="category_id" class="form-select form-select-solid form-select-sm" data-control="select2" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $asset->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Metode Penyusutan</label>
                        <select name="depreciation_method" class="form-select form-select-solid form-select-sm" data-control="select2" required>
                            <option value="straight_line" {{ old('depreciation_method', $asset->depreciation_method) == 'straight_line' ? 'selected' : '' }}>Garis Lurus (Straight Line)</option>
                            <option value="declining_balance" {{ old('depreciation_method', $asset->depreciation_method) == 'declining_balance' ? 'selected' : '' }}>Saldo Menurun Ganda (Double Declining)</option>
                            <option value="none" {{ old('depreciation_method', $asset->depreciation_method) == 'none' ? 'selected' : '' }}>Tidak Disusutkan (Tanah / Land)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Status Aset</label>
                        <select name="status" class="form-select form-select-solid form-select-sm" data-control="select2" required>
                            <option value="active" {{ old('status', $asset->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="disposed" {{ old('status', $asset->status) == 'disposed' ? 'selected' : '' }}>Dijual / Dilepas (Disposed)</option>
                            <option value="written_off" {{ old('status', $asset->status) == 'written_off' ? 'selected' : '' }}>Dihapusbukan (Written Off)</option>
                        </select>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Parameter Nilai & Masa Manfaat</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Nilai Perolehan Awal</label>
                        <input type="text" class="form-control form-control-solid form-control-sm bg-light" value="Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}" disabled readonly />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Nilai Residu / Sisa (Rp)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-0 text-muted">Rp</span>
                            <input type="number" step="0.01" name="salvage_value" class="form-control form-control-solid form-control-sm" value="{{ old('salvage_value', $asset->salvage_value) }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Masa Manfaat (Bulan)</label>
                        <input type="number" name="useful_life_months" class="form-control form-control-solid form-control-sm" value="{{ old('useful_life_months', $asset->useful_life_months) }}" required />
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">3</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Catatan / Spesifikasi Aset</h4>
                </div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Catatan / Spesifikasi Aset</label>
                        <textarea name="notes" class="form-control form-control-solid form-control-sm" rows="3" placeholder="Catatan aset...">{{ old('notes', $asset->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('assets.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-check2-circle me-1"></i> Perbarui Data Aset
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

