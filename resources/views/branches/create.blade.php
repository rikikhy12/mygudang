@extends('layouts.app')

@section('title', 'Tambah Cabang Baru')
@section('page_title', 'Tambah Cabang Baru')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Tambah Kantor Cabang Baru</h2>
            <p class="text-muted fs-7 mb-0">Daftarkan lokasi kantor cabang baru dan kelola unit gudang di bawahnya</p>
        </div>
        <a href="{{ route('branches.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Cabang
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-building-add text-primary fs-3"></i> Formulir Kantor Cabang Baru
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Silakan isi identitas unit cabang operasional dan konfigurasi rekening kas kasir
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('branches.store') }}">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Identitas & Informasi Kontak Cabang</h4>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Kode Cabang</label>
                        <input type="text" name="code" class="form-control form-control-solid form-control-sm" value="{{ old('code') }}" required placeholder="Contoh: CBG-JKT" />
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Nama Cabang</label>
                        <input type="text" name="name" class="form-control form-control-solid form-control-sm" value="{{ old('name') }}" required placeholder="Contoh: Cabang Jakarta Barat" />
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Nomor Telepon</label>
                        <input type="text" name="phone" class="form-control form-control-solid form-control-sm" value="{{ old('phone') }}" placeholder="021-xxxxxxx / 08xxxxxxxx" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Email Kantor</label>
                        <input type="email" name="email" class="form-control form-control-solid form-control-sm" value="{{ old('email') }}" placeholder="cabang@domain.com" />
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Alamat Lengkap</label>
                        <textarea name="address" class="form-control form-control-solid form-control-sm" rows="3" placeholder="Alamat lengkap gedung, jalan, kota...">{{ old('address') }}</textarea>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Pengaturan Akun Kas POS & Keuangan Cabang</h4>
                </div>

                <div class="card bg-light-primary border-primary border-dashed mb-5">
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fs-8 fw-bold text-gray-800">Akun Kas Laci Kasir POS (COA Default)</label>
                                <select name="cash_account_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="Pilih Akun Kas Cabang...">
                                    <option value=""></option>
                                    @foreach($cashAccounts as $acc)
                                        <option value="{{ $acc->id }}" {{ old('cash_account_id') == $acc->id ? 'selected' : '' }}>
                                            [{{ $acc->code }}] {{ $acc->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="text-muted fs-9 mt-1">Uang tunai kasir POS di cabang ini otomatis masuk ke akun kas ini saat buka shift.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fs-8 fw-bold text-gray-800">Akun Bank / Kas Penampung Setoran (Default Deposit)</label>
                                <select name="deposit_account_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="Pilih Rekening Setoran...">
                                    <option value=""></option>
                                    @foreach($cashAccounts as $acc)
                                        <option value="{{ $acc->id }}" {{ old('deposit_account_id') == $acc->id ? 'selected' : '' }}>
                                            [{{ $acc->code }}] {{ $acc->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="text-muted fs-9 mt-1">Rekening tujuan default saat kasir melakukan Setoran Kas (Cash Drop) atau Tutup Kasir.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">3</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Pengaturan Status & Kantor Pusat</h4>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check form-check-solid p-3 rounded bg-light border border-gray-200">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_main" value="1" id="is_main" {{ old('is_main') ? 'checked' : '' }} />
                            <label class="form-check-label fw-semibold text-gray-800 fs-7" for="is_main">
                                Set Sebagai Kantor Pusat (Headquarter)
                                <span class="d-block text-muted fs-8">Centang jika ini merupakan entitas kantor pusat utama perusahaan</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-check-solid p-3 rounded bg-light border border-gray-200">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', '1') ? 'checked' : '' }} />
                            <label class="form-check-label fw-semibold text-gray-800 fs-7" for="is_active">
                                Status Cabang Aktif
                                <span class="d-block text-muted fs-8">Cabang aktif dapat dipilih saat transaksi operasional dan mutasi gudang</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
                <a href="{{ route('branches.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary fw-bold">
                    <i class="bi bi-check2-circle me-1"></i> Simpan Cabang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
