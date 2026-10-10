@extends('layouts.app')

@section('title', 'Daftarkan Aset Tetap')
@section('page_title', 'Formulir Pendaftaran Aset Tetap Baru')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Registrasi Aset Tetap (Fixed Asset)</h2>
            <p class="text-muted fs-7 mb-0">Daftarkan inventaris aset perusahaan dengan jadwal depresiasi garis lurus otomatis</p>
        </div>
        <a href="{{ route('assets.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Aset
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-laptop text-primary fs-3"></i> Formulir Registrasi Aset Tetap
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Silakan isi identitas aset, kategori GL, nilai perolehan, masa manfaat, dan akun kas pembayaran
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('assets.store') }}">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Identitas & Kategori Aset</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Kode Unik Aset</label>
                        <input type="text" name="asset_code" class="form-control form-control-solid form-control-sm" value="{{ old('asset_code') }}" required placeholder="Contoh: AST-PC-002, AST-VEH-001" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Nama Aset Tetap</label>
                        <input type="text" name="name" class="form-control form-control-solid form-control-sm" value="{{ old('name') }}" required placeholder="Contoh: Komputer Server Dell PowerEdge" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Kategori Aset (Setting GL)</label>
                        <select name="category_id" id="assetCategory" class="form-select form-select-solid form-select-sm" data-control="select2" required>
                            <option value="">-- Pilih Kategori Aset --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" data-years="{{ $cat->useful_life_years }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }} (Masa Manfaat {{ $cat->useful_life_years }} Thn)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Parameter Perolehan & Depresiasi</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tanggal Perolehan</label>
                        <input type="date" name="acquisition_date" class="form-control form-control-solid form-control-sm" value="{{ old('acquisition_date', date('Y-m-d')) }}" required />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Metode Penyusutan</label>
                        <select name="depreciation_method" id="depreciationMethod" class="form-select form-select-solid form-select-sm" data-control="select2" required>
                            <option value="straight_line" {{ old('depreciation_method', 'straight_line') == 'straight_line' ? 'selected' : '' }}>Garis Lurus (Straight Line)</option>
                            <option value="declining_balance" {{ old('depreciation_method') == 'declining_balance' ? 'selected' : '' }}>Saldo Menurun Ganda (Double Declining)</option>
                            <option value="none" {{ old('depreciation_method') == 'none' ? 'selected' : '' }}>Tidak Disusutkan (Tanah / Land)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Nilai Perolehan (Rp)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-0 text-muted">Rp</span>
                            <input type="number" step="0.01" name="acquisition_cost" id="acquisitionCost" class="form-control form-control-solid form-control-sm" value="{{ old('acquisition_cost') }}" required placeholder="Harga..." />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Nilai Residu (Rp)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-0 text-muted">Rp</span>
                            <input type="number" step="0.01" name="salvage_value" id="salvageValue" class="form-control form-control-solid form-control-sm" value="{{ old('salvage_value', 0) }}" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Masa Manfaat (Bln)</label>
                        <input type="number" name="useful_life_months" id="lifeMonths" class="form-control form-control-solid form-control-sm" value="{{ old('useful_life_months', 48) }}" required />
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">3</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Pembayaran Kas & Spesifikasi Aset</h4>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Akun Pembayaran Kas/Bank (Auto-Jurnal)</label>
                        <select name="payment_account_id" class="form-select form-select-solid form-select-sm" data-control="select2">
                            <option value="">-- Tanpa Jurnal Pembayaran Kas (Saldo Awal / Non-Kas) --</option>
                            @foreach($paymentAccounts as $pa)
                                <option value="{{ $pa->id }}" {{ old('payment_account_id') == $pa->id ? 'selected' : '' }}>{{ $pa->code }} - {{ $pa->name }}</option>
                            @endforeach
                        </select>
                        <span class="fs-8 text-muted mt-1 d-block">Sistem akan otomatis menjurnal: Debit Akun Aset Tetap vs Kredit Kas/Bank.</span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Catatan / Spesifikasi Aset</label>
                        <textarea name="notes" class="form-control form-control-solid form-control-sm" rows="2" placeholder="Nomor seri unit, lokasi penempatan, penanggung jawab...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="alert alert-primary d-flex align-items-center p-4 mb-0 rounded-3">
                    <i class="bi bi-calculator-fill fs-2 text-primary me-3"></i>
                    <div>
                        <span class="text-gray-800 fw-semibold fs-7">Estimasi Beban Penyusutan Bulan Pertama:</span>
                        <span class="text-primary fw-bold fs-4 ms-2" id="deprPreview">Rp 0</span>
                        <span class="text-muted fs-8 d-block mt-0.5" id="deprFormulaDesc">(Metode Garis Lurus: [Nilai Perolehan - Residu] / Bulan)</span>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('assets.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-check2-circle me-1"></i> Daftarkan & Posting Aset
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function updateDeprPreview() {
        const cost = parseFloat(document.getElementById('acquisitionCost').value) || 0;
        const salvage = parseFloat(document.getElementById('salvageValue').value) || 0;
        const months = parseInt(document.getElementById('lifeMonths').value) || 1;
        const method = document.getElementById('depreciationMethod').value;
        
        let depr = 0;
        let desc = '';

        if (method === 'none') {
            depr = 0;
            desc = '(Tidak Disusutkan / Nilai Buku Tetap)';
        } else if (method === 'declining_balance') {
            const monthlyRate = 2 / Math.max(1, months);
            depr = Math.max(0, Math.min(cost * monthlyRate, cost - salvage));
            desc = '(Metode Saldo Menurun Ganda: Nilai Buku Saat Ini x [2 / Bulan Masa Manfaat])';
        } else {
            depr = Math.max(0, (cost - salvage) / months);
            desc = '(Metode Garis Lurus: [Nilai Perolehan - Residu] / Bulan Masa Manfaat)';
        }

        document.getElementById('deprPreview').innerText = 'Rp ' + Number(Math.round(depr)).toLocaleString('id-ID');
        document.getElementById('deprFormulaDesc').innerText = desc;
    }

    document.getElementById('assetCategory').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const years = opt.getAttribute('data-years');
        if (years) {
            document.getElementById('lifeMonths').value = parseInt(years) * 12;
            updateDeprPreview();
        }
    });

    document.getElementById('depreciationMethod').addEventListener('change', updateDeprPreview);
    document.getElementById('acquisitionCost').addEventListener('input', updateDeprPreview);
    document.getElementById('salvageValue').addEventListener('input', updateDeprPreview);
    document.getElementById('lifeMonths').addEventListener('input', updateDeprPreview);

    document.addEventListener('DOMContentLoaded', updateDeprPreview);
</script>
@endsection
