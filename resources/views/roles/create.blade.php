@extends('layouts.app')

@section('title', 'Tambah Role & Hak Akses')
@section('page_title', 'Buat Role Pengguna Baru')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Tambah Role Pengguna & Matriks Hak Akses</h2>
            <p class="text-muted fs-7 mb-0">Tentukan wewenang dan izin akses fitur (Lihat, Tambah, Ubah, Hapus, Cetak) untuk role baru.</p>
        </div>
        <a href="{{ route('roles.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Role
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-plus text-primary fs-3"></i> Formulir Role Pengguna Baru
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Definisikan nama role dan konfigurasikan matriks hak akses per modul aplikasi
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('roles.store') }}">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Identitas & Deskripsi Role</h4>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Nama Role (Internal)</label>
                        <input type="text" name="name" class="form-control form-control-solid form-control-sm" value="{{ old('name') }}" required placeholder="contoh: kasir_toko, gudang_staff..." />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Nama Tampilan Role</label>
                        <input type="text" name="display_name" class="form-control form-control-solid form-control-sm" value="{{ old('display_name') }}" required placeholder="contoh: Kasir Toko & POS, Staff Logistik..." />
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Deskripsi Tugas / Fungsi Role</label>
                        <textarea name="description" class="form-control form-control-solid form-control-sm" rows="2" placeholder="Menjelaskan wewenang dan tanggung jawab role ini...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Matriks Hak Akses Modul (Permissions Grid)</h4>
                </div>

                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                        <span class="text-muted fs-7">Centang hak akses spesifik per modul: Lihat (View), Tambah (Add), Ubah (Edit), Hapus (Delete), dan Cetak (Print).</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-light-primary fw-bold" onclick="toggleAll(true)">
                                <i class="bi bi-check-all me-1"></i> Pilih Semua
                            </button>
                            <button type="button" class="btn btn-sm btn-light-danger fw-bold" onclick="toggleAll(false)">
                                <i class="bi bi-x me-1"></i> Batal Semua
                            </button>
                        </div>
                    </div>

                    @php
                        $rolePermissionIds = old('permissions', []);
                    @endphp

                    @foreach($matrix as $groupName => $moduleRows)
                    <div class="card bg-white border border-gray-300 rounded-3 mb-4 overflow-hidden">
                        <div class="card-header bg-light py-2 px-4 d-flex justify-content-between align-items-center min-h-40px">
                            <span class="fw-bolder text-gray-800 fs-7 text-uppercase">
                                <i class="bi bi-folder2-open text-primary me-1"></i> Modul: {{ $groupName }}
                            </span>
                            <button type="button" class="btn btn-xs btn-link text-primary p-0 fw-semibold fs-8" onclick="toggleGroup('{{ Str::slug($groupName) }}')">
                                Toggle Grup
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                                <thead>
                                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                        <th class="ps-4" style="min-width: 250px;">Nama Modul / Fitur</th>
                                        <th class="text-center" style="width: 100px;">
                                            <span class="cursor-pointer" onclick="toggleColumn('view', '{{ Str::slug($groupName) }}')">Lihat (View)</span>
                                        </th>
                                        <th class="text-center" style="width: 100px;">
                                            <span class="cursor-pointer" onclick="toggleColumn('create', '{{ Str::slug($groupName) }}')">Tambah (Add)</span>
                                        </th>
                                        <th class="text-center" style="width: 100px;">
                                            <span class="cursor-pointer" onclick="toggleColumn('edit', '{{ Str::slug($groupName) }}')">Ubah (Edit)</span>
                                        </th>
                                        <th class="text-center" style="width: 100px;">
                                            <span class="cursor-pointer" onclick="toggleColumn('delete', '{{ Str::slug($groupName) }}')">Hapus (Delete)</span>
                                        </th>
                                        <th class="text-center" style="width: 100px;">
                                            <span class="cursor-pointer" onclick="toggleColumn('print', '{{ Str::slug($groupName) }}')">Cetak (Print)</span>
                                        </th>
                                        <th class="text-center pe-4" style="width: 80px;">Baris</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700 fw-semibold group-{{ Str::slug($groupName) }}">
                                    @foreach($moduleRows as $row)
                                    @php $rowSlug = $row['key']; @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="w-25px h-25px rounded bg-light-primary text-primary d-flex align-items-center justify-content-center me-2 fs-7">
                                                    <i class="bi {{ $row['icon'] }}"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-bold text-gray-900 fs-7">{{ $row['label'] }}</span>
                                                    <span class="text-muted fs-9 d-block"><code>{{ $row['key'] }}</code></span>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        @foreach(['view', 'create', 'edit', 'delete', 'print'] as $act)
                                        <td class="text-center">
                                            @if(!empty($row['actions'][$act]))
                                                @php $p = $row['actions'][$act]; @endphp
                                                <div class="form-check form-check-sm form-check-custom form-check-solid d-inline-flex">
                                                    <input class="form-check-input perm-cb col-{{ $act }} row-{{ $rowSlug }}" type="checkbox" name="permissions[]" value="{{ $p->id }}" {{ in_array($p->id, $rolePermissionIds) ? 'checked' : '' }} title="{{ $p->display_name }}" />
                                                </div>
                                            @else
                                                <span class="text-muted fs-9">-</span>
                                            @endif
                                        </td>
                                        @endforeach

                                        <td class="text-center pe-4">
                                            <button type="button" class="btn btn-icon btn-xs btn-light-primary" title="Toggle Baris Ini" onclick="toggleRow('{{ $rowSlug }}')">
                                                <i class="bi bi-check2-square fs-8"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('roles.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary fw-bold">
                    <i class="bi bi-check2-circle me-1"></i> Simpan Role Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleAll(status) {
        document.querySelectorAll('.perm-cb').forEach(cb => cb.checked = status);
    }

    function toggleRow(rowSlug) {
        const cbs = document.querySelectorAll('.row-' + rowSlug);
        if (cbs.length === 0) return;
        const allChecked = Array.from(cbs).every(cb => cb.checked);
        cbs.forEach(cb => cb.checked = !allChecked);
    }

    function toggleGroup(groupSlug) {
        const cbs = document.querySelectorAll('.group-' + groupSlug + ' .perm-cb');
        if (cbs.length === 0) return;
        const allChecked = Array.from(cbs).every(cb => cb.checked);
        cbs.forEach(cb => cb.checked = !allChecked);
    }

    function toggleColumn(actionName, groupSlug) {
        const cbs = document.querySelectorAll('.group-' + groupSlug + ' .col-' + actionName);
        if (cbs.length === 0) return;
        const allChecked = Array.from(cbs).every(cb => cb.checked);
        cbs.forEach(cb => cb.checked = !allChecked);
    }
</script>
@endsection

