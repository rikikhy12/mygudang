@extends('layouts.app')

@section('title', 'Edit Akun COA')
@section('page_title', 'Perbarui Akun COA')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Edit Akun Buku Besar (COA)</h2>
            <p class="text-muted fs-7 mb-0">Perbarui klasifikasi akun, nama akun, saldo normal, atau status keaktifan</p>
        </div>
        <a href="{{ route('coa.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Akun
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary fs-3"></i> Edit Akun COA: {{ $coa->name }} <span class="badge badge-light-primary fs-8"><code>{{ $coa->code }}</code></span>
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Perbarui klasifikasi akun, nama akun, saldo normal, struktur induk, dan kategori arus kas
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('coa.update', $coa->id) }}">
            @csrf
            @method('PUT')
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Identitas & Klasifikasi Akun</h4>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Kode Akun</label>
                        <input type="text" name="code" class="form-control form-control-solid form-control-sm" value="{{ old('code', $coa->code) }}" required placeholder="contoh: 1004" />
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Nama Akun</label>
                        <input type="text" name="name" class="form-control form-control-solid form-control-sm" value="{{ old('name', $coa->name) }}" required placeholder="Nama akun..." />
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tipe / Klasifikasi Akun</label>
                        <select name="type" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="Pilih Tipe Akun" data-hide-search="true" required>
                            <option value=""></option>
                            <option value="asset" {{ old('type', $coa->type) == 'asset' ? 'selected' : '' }}>Aset (1xxx)</option>
                            <option value="liability" {{ old('type', $coa->type) == 'liability' ? 'selected' : '' }}>Kewajiban / Hutang (2xxx)</option>
                            <option value="equity" {{ old('type', $coa->type) == 'equity' ? 'selected' : '' }}>Ekuitas / Modal (3xxx)</option>
                            <option value="revenue" {{ old('type', $coa->type) == 'revenue' ? 'selected' : '' }}>Pendapatan (4xxx)</option>
                            <option value="cogs" {{ old('type', $coa->type) == 'cogs' ? 'selected' : '' }}>Harga Pokok Penjualan (5xxx)</option>
                            <option value="expense" {{ old('type', $coa->type) == 'expense' ? 'selected' : '' }}>Beban Operasional (6xxx)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Saldo Normal</label>
                        <select name="normal_balance" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="Pilih Saldo Normal" data-hide-search="true" required>
                            <option value=""></option>
                            <option value="debit" {{ old('normal_balance', $coa->normal_balance) == 'debit' ? 'selected' : '' }}>DEBIT</option>
                            <option value="credit" {{ old('normal_balance', $coa->normal_balance) == 'credit' ? 'selected' : '' }}>CREDIT</option>
                        </select>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Struktur Induk & Kategori Arus Kas (Cash Flow)</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Akun Induk (Parent) - Opsional</label>
                        <select name="parent_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Tanpa Akun Induk (Top Level) --" data-allow-clear="true">
                            <option value=""></option>
                            @foreach($allAccounts as $a)
                                <option value="{{ $a->id }}" {{ old('parent_id', $coa->parent_id) == $a->id ? 'selected' : '' }}>{{ $a->code }} - {{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Kategori Arus Kas (Cash Flow)</label>
                        <select name="cash_flow_category" class="form-select form-select-solid form-select-sm" data-control="select2" data-hide-search="true" required>
                            <option value="operating" {{ old('cash_flow_category', $coa->cash_flow_category ?? 'operating') == 'operating' ? 'selected' : '' }}>
                                Aktivitas Operasi (Operating) - Penjualan, HPP, Beban Rutin
                            </option>
                            <option value="investing" {{ old('cash_flow_category', $coa->cash_flow_category) == 'investing' ? 'selected' : '' }}>
                                Aktivitas Investasi (Investing) - Aset Tetap, Peralatan, Investasi
                            </option>
                            <option value="financing" {{ old('cash_flow_category', $coa->cash_flow_category) == 'financing' ? 'selected' : '' }}>
                                Aktivitas Pendanaan (Financing) - Modal, Saham, Prive, Pinjaman Bank
                            </option>
                            <option value="none" {{ old('cash_flow_category', $coa->cash_flow_category) == 'none' ? 'selected' : '' }}>
                                Non-Arus Kas / Tidak Berpengaruh Langsung
                            </option>
                        </select>
                        <span class="fs-8 text-muted mt-1 d-block">Digunakan untuk klasifikasi otomatis pada Laporan Arus Kas (Cash Flow Statement).</span>
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">3</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Pengaturan Kas/Bank, Status & Keterangan</h4>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="form-check form-check-solid p-3 rounded-3 bg-light border border-gray-200">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_cash_or_bank" value="1" id="is_cash_or_bank" {{ old('is_cash_or_bank', $coa->is_cash_or_bank) ? 'checked' : '' }} />
                            <label class="form-check-label fw-semibold text-gray-800 fs-7" for="is_cash_or_bank">
                                Rekening Kas / Bank Likuid
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-check-solid p-3 rounded-3 bg-light border border-gray-200">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $coa->is_active) ? 'checked' : '' }} />
                            <label class="form-check-label fw-semibold text-gray-800 fs-7" for="is_active">
                                Status Akun Aktif
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Keterangan / Catatan</label>
                        <textarea name="description" class="form-control form-control-solid form-control-sm" rows="2" placeholder="Keterangan fungsi akun...">{{ old('description', $coa->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('coa.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-check2-circle me-1"></i> Perbarui Akun COA
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

