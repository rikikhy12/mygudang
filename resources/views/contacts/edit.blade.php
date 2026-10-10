@extends('layouts.app')

@section('title', 'Edit Kontak')
@section('page_title', 'Perbarui Kontak Rekanan')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Edit Kontak Rekanan: {{ $contact->name }}</h2>
            <p class="text-muted fs-7 mb-0">Perbarui profil rekanan bisnis, informasi kontak, dan status keaktifan</p>
        </div>
        <a href="{{ route('contacts.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kontak
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary fs-3"></i> Edit Kontak: {{ $contact->name }} <span class="badge badge-light-primary fs-8"><code>{{ $contact->code }}</code></span>
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Perbarui kontak rekanan bisnis, nomor pajak/NPWP, serta status keaktifan
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('contacts.update', $contact->id) }}">
            @csrf
            @method('PUT')
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Klasifikasi & Identitas Rekanan</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tipe Kontak</label>
                        <select name="type" class="form-select form-select-solid form-select-sm" data-control="select2" data-hide-search="true" required>
                            <option value="customer" {{ old('type', $contact->type) == 'customer' ? 'selected' : '' }}>Pelanggan (Customer)</option>
                            <option value="supplier" {{ old('type', $contact->type) == 'supplier' ? 'selected' : '' }}>Pemasok (Supplier)</option>
                            <option value="both" {{ old('type', $contact->type) == 'both' ? 'selected' : '' }}>Keduanya (Customer & Supplier)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Kode Kontak</label>
                        <input type="text" name="code" class="form-control form-control-solid form-control-sm" value="{{ old('code', $contact->code) }}" required placeholder="Kode unik kontak..." />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Nama Kontak / Perusahaan</label>
                        <input type="text" name="name" class="form-control form-control-solid form-control-sm" value="{{ old('name', $contact->name) }}" required placeholder="Nama lengkap rekanan..." />
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Informasi Kontak & Perpajakan</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Alamat Email</label>
                        <input type="email" name="email" class="form-control form-control-solid form-control-sm" value="{{ old('email', $contact->email) }}" placeholder="email@perusahaan.com..." />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Nomor Telepon / HP</label>
                        <input type="text" name="phone" class="form-control form-control-solid form-control-sm" value="{{ old('phone', $contact->phone) }}" placeholder="08xxxxxxxxxx..." />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-gray-700">NPWP / Tax ID</label>
                        <input type="text" name="tax_number" class="form-control form-control-solid form-control-sm" value="{{ old('tax_number', $contact->tax_number) }}" placeholder="Nomor NPWP jika ada..." />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Limit Margin Khusus (%)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.01" min="0" max="100" name="min_margin_percent" class="form-control form-control-solid" value="{{ old('min_margin_percent', $contact->min_margin_percent) }}" placeholder="Default Kategori" />
                            <span class="input-group-text bg-light">%</span>
                        </div>
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">3</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Alamat & Pengaturan Status</h4>
                </div>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Alamat Lengkap</label>
                        <textarea name="address" class="form-control form-control-solid form-control-sm" rows="3" placeholder="Alamat domisili atau operasional...">{{ old('address', $contact->address) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Status Keaktifan</label>
                        <div class="form-check form-check-solid p-3 rounded bg-light border border-gray-200">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" value="1" id="contactIsActive" {{ old('is_active', $contact->is_active) ? 'checked' : '' }} />
                            <label class="form-check-label fw-semibold text-gray-800 fs-7" for="contactIsActive">
                                Rekanan Aktif Beroperasi
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('contacts.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-check2-circle me-1"></i> Perbarui Kontak
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
