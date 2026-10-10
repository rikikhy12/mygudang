@extends(request('popup') || !empty($isPopup) ? 'layouts.popup' : 'layouts.app')

@section('title', 'Tambah Kontak Baru')
@section('page_title', 'Tambah Kontak Rekanan (' . ucfirst($type ?? 'Supplier') . ')')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    @if(!request('popup') && empty($isPopup))
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Tambah Kontak Rekanan Baru</h2>
            <p class="text-muted fs-7 mb-0">Daftarkan pelanggan (customer) atau pemasok (supplier) baru</p>
        </div>
        <a href="{{ route('contacts.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kontak
        </a>
    </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-person-plus-fill text-primary fs-3"></i> Formulir Kontak / Rekanan Baru
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Daftarkan rekanan bisnis pemasok (supplier) atau pelanggan (customer) dengan lengkap
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('contacts.store') }}">
            @csrf
            @if(request('popup') || !empty($isPopup))
                <input type="hidden" name="popup" value="1" />
            @endif
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Klasifikasi & Identitas Rekanan</h4>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tipe Kontak</label>
                        <select name="type" class="form-select form-select-solid form-select-sm" data-control="select2" data-hide-search="true" required>
                            <option value="supplier" {{ (old('type', $type ?? '') == 'supplier') ? 'selected' : '' }}>Pemasok (Supplier)</option>
                            <option value="customer" {{ (old('type', $type ?? '') == 'customer') ? 'selected' : '' }}>Pelanggan (Customer)</option>
                            <option value="both" {{ old('type') == 'both' ? 'selected' : '' }}>Keduanya (Customer & Supplier)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Kode Kontak</label>
                        <input type="text" name="code" class="form-control form-control-solid form-control-sm" value="{{ old('code', (isset($type) && $type === 'customer' ? 'CUS-' : 'SUP-') . date('ymd') . '-' . rand(100, 999)) }}" required placeholder="contoh: CUST-001 / SUP-001" />
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Nama Kontak / Perusahaan</label>
                        <input type="text" name="name" class="form-control form-control-solid form-control-sm" value="{{ old('name') }}" required placeholder="contoh: PT Sinar Abadi Nusantara" autofocus />
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Informasi Kontak & Pajak</h4>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Alamat Email</label>
                        <input type="email" name="email" class="form-control form-control-solid form-control-sm" value="{{ old('email') }}" placeholder="billing@rekanan.com" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Nomor Telepon</label>
                        <input type="text" name="phone" class="form-control form-control-solid form-control-sm" value="{{ old('phone') }}" placeholder="021-xxxxxxx / 0812xxxx" />
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Nomor Pokok Wajib Pajak (NPWP / Tax ID)</label>
                        <input type="text" name="tax_number" class="form-control form-control-solid form-control-sm" value="{{ old('tax_number') }}" placeholder="Format: 00.000.000.0-000.000" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Limit Minimum Margin Khusus Pelanggan (%) <span class="badge badge-light-secondary fs-9">Opsional</span></label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.01" min="0" max="100" name="min_margin_percent" class="form-control form-control-solid" value="{{ old('min_margin_percent') }}" placeholder="Contoh: 5 (Khusus VIP) / Kosongkan untuk default kategori" />
                            <span class="input-group-text bg-light">%</span>
                        </div>
                        <div class="form-text fs-9 text-muted">Jika diisi, penjualan ke pelanggan ini akan menggunakan batas margin khusus ini.</div>
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">3</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Alamat & Lokasi Faktur</h4>
                </div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Alamat Lengkap</label>
                        <textarea name="address" class="form-control form-control-solid form-control-sm" rows="3" placeholder="Alamat kantor / pengiriman faktur...">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 d-flex justify-content-end gap-2">
                @if(request('popup') || !empty($isPopup))
                    <button type="button" class="btn btn-sm btn-light" onclick="window.close()">Tutup Jendela</button>
                @else
                    <a href="{{ route('contacts.index') }}" class="btn btn-sm btn-light">Batal</a>
                @endif
                <button type="submit" class="btn btn-sm btn-primary fw-bold">
                    <i class="bi bi-check2-circle me-1"></i> Simpan & Kirim ke Transaksi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
