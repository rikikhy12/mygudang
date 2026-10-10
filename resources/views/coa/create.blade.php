@extends(request('popup') || !empty($isPopup) ? 'layouts.popup' : 'layouts.app')

@section('title', 'Tambah Akun COA')
@section('page_title', 'Tambah Akun COA Baru')

@section('content')
<div class="d-flex flex-column gap-4 {{ request('popup') || !empty($isPopup) ? 'w-100' : 'w-100' }}">
    @if(!request('popup') && empty($isPopup))
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Tambah Akun Buku Besar (COA)</h2>
            <p class="text-muted fs-7 mb-0">Daftarkan akun kode rekening baru dalam struktur bagan akun keuangan</p>
        </div>
        <a href="{{ route('coa.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Akun
        </a>
    </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-journal-plus text-primary fs-3"></i> Formulir Akun COA Baru
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Daftarkan akun kode rekening baru dalam struktur bagan akun keuangan dan klasifikasi arus kas
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('coa.store') }}">
            @csrf
            @if(request('popup') || !empty($isPopup))
                <input type="hidden" name="popup" value="1" />
            @endif
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Identitas & Klasifikasi Akun</h4>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Kode Akun</label>
                        <input type="text" name="code" class="form-control form-control-solid form-control-sm" value="{{ old('code') }}" required placeholder="contoh: 1004" />
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Nama Akun</label>
                        <input type="text" name="name" class="form-control form-control-solid form-control-sm" value="{{ old('name') }}" required placeholder="contoh: Kas Operasional Lapangan" />
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tipe / Klasifikasi Akun</label>
                        <select name="type" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="Pilih Tipe Akun" data-hide-search="true" required>
                            <option value=""></option>
                            <option value="asset" {{ old('type') == 'asset' ? 'selected' : '' }}>Aset (1xxx)</option>
                            <option value="liability" {{ old('type') == 'liability' ? 'selected' : '' }}>Kewajiban / Hutang (2xxx)</option>
                            <option value="equity" {{ old('type') == 'equity' ? 'selected' : '' }}>Ekuitas / Modal (3xxx)</option>
                            <option value="revenue" {{ old('type') == 'revenue' ? 'selected' : '' }}>Pendapatan (4xxx)</option>
                            <option value="cogs" {{ old('type') == 'cogs' ? 'selected' : '' }}>Harga Pokok Penjualan (5xxx)</option>
                            <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>Beban Operasional (6xxx)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Saldo Normal</label>
                        <select name="normal_balance" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="Pilih Saldo Normal" data-hide-search="true" required>
                            <option value=""></option>
                            <option value="debit" {{ old('normal_balance') == 'debit' ? 'selected' : '' }}>DEBIT</option>
                            <option value="credit" {{ old('normal_balance') == 'credit' ? 'selected' : '' }}>CREDIT</option>
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
                                <option value="{{ $a->id }}" {{ old('parent_id') == $a->id ? 'selected' : '' }}>{{ $a->code }} - {{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Kategori Arus Kas (Cash Flow)</label>
                        <select name="cash_flow_category" class="form-select form-select-solid form-select-sm" data-control="select2" data-hide-search="true" required>
                            <option value="operating" {{ old('cash_flow_category', 'operating') == 'operating' ? 'selected' : '' }}>
                                Aktivitas Operasi (Operating) - Penjualan, HPP, Beban Rutin
                            </option>
                            <option value="investing" {{ old('cash_flow_category') == 'investing' ? 'selected' : '' }}>
                                Aktivitas Investasi (Investing) - Aset Tetap, Peralatan, Investasi
                            </option>
                            <option value="financing" {{ old('cash_flow_category') == 'financing' ? 'selected' : '' }}>
                                Aktivitas Pendanaan (Financing) - Modal, Saham, Prive, Pinjaman Bank
                            </option>
                            <option value="none" {{ old('cash_flow_category') == 'none' ? 'selected' : '' }}>
                                Non-Arus Kas / Tidak Berpengaruh Langsung
                            </option>
                        </select>
                        <span class="fs-8 text-muted mt-1 d-block">Digunakan untuk klasifikasi otomatis pada Laporan Arus Kas (Cash Flow Statement).</span>
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">3</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Pengaturan Kas/Bank & Keterangan</h4>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <div class="form-check form-check-solid p-3 rounded-3 bg-light border border-gray-200">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_cash_or_bank" value="1" id="is_cash_or_bank" {{ old('is_cash_or_bank') ? 'checked' : '' }} />
                            <label class="form-check-label fw-semibold text-gray-800 fs-7" for="is_cash_or_bank">
                                Rekening Kas / Bank Likuid
                                <span class="d-block text-muted fs-8">Centang jika akun ini dapat dipilih pada transaksi Kas Masuk, Kas Keluar, dan POS</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Keterangan / Catatan</label>
                        <textarea name="description" class="form-control form-control-solid form-control-sm" rows="2" placeholder="Keterangan fungsi akun...">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                @if(request('popup') || !empty($isPopup))
                    <button type="button" class="btn btn-sm btn-light" onclick="window.close()">
                        <i class="bi bi-x-circle me-1"></i> Tutup Jendela
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-check2-circle me-1"></i> Simpan & Pilih ke Transaksi
                    </button>
                @else
                    <a href="{{ route('coa.index') }}" class="btn btn-sm btn-light">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Akun COA
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection

