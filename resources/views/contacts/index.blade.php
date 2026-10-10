@extends('layouts.app')

@section('title', 'Master Kontak & Rekanan')
@section('page_title', 'Master Supplier & Pelanggan (Customer)')

@section('content')
@if(session('success'))
    <div class="alert alert-success d-flex align-items-center p-3 mb-4 rounded-3 fs-7">
        <i class="bi bi-check-circle-fill text-success fs-4 me-2"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-people-fill fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Direktori Rekanan Bisnis (Kontak)</h3>
                <span class="text-muted fs-8 fw-semibold">Kelola data pelanggan (customer), pemasok (supplier), termin & limit piutang/hutang</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('contacts.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-person-plus-fill me-1"></i> Tambah Kontak Baru
            </a>
        </div>
    </div>

    <div class="card-body p-4 pt-0">
        <!-- Filter Tabs -->
        <div class="d-flex flex-wrap gap-2 mb-4 border-bottom pb-4 pt-3">
            <a href="{{ route('contacts.index') }}" class="btn btn-sm {{ !$type ? 'btn-primary fw-bold' : 'btn-light' }}">Semua Kontak</a>
            <a href="{{ route('contacts.index', ['type' => 'customer']) }}" class="btn btn-sm {{ $type == 'customer' ? 'btn-primary fw-bold' : 'btn-light' }}">
                <i class="bi bi-people me-1"></i> Pelanggan (Customer)
            </a>
            <a href="{{ route('contacts.index', ['type' => 'supplier']) }}" class="btn btn-sm {{ $type == 'supplier' ? 'btn-primary fw-bold' : 'btn-light' }}">
                <i class="bi bi-truck me-1"></i> Pemasok (Supplier)
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-4" style="width: 130px;">Kode</th>
                        <th>Nama Kontak / Perusahaan</th>
                        <th>Tipe Rekanan</th>
                        <th>Kontak Telepon & Email</th>
                        <th>Alamat Utama</th>
                        <th>Termin Bayar</th>
                        <th>Batas Piutang (Limit)</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4 no-sort" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-700">
                    @forelse($contacts as $contact)
                        <tr>
                            <td class="ps-4">
                                <span class="badge badge-light-primary fw-bold fs-7">{{ $contact->code }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-gray-900 fs-6">{{ $contact->name }}</div>
                                @if($contact->company_name)
                                    <div class="text-muted fs-8">{{ $contact->company_name }}</div>
                                @endif
                            </td>
                            <td>
                                @if($contact->type === 'customer')
                                    <span class="badge badge-light-info fw-bold"><i class="bi bi-people me-1"></i> Customer</span>
                                @elseif($contact->type === 'supplier')
                                    <span class="badge badge-light-success fw-bold"><i class="bi bi-truck me-1"></i> Supplier</span>
                                @else
                                    <span class="badge badge-light-warning fw-bold"><i class="bi bi-arrow-left-right me-1"></i> Keduanya</span>
                                @endif
                            </td>
                            <td>
                                @if($contact->phone)
                                    <div class="text-gray-800 fs-7"><i class="bi bi-telephone text-muted me-1"></i>{{ $contact->phone }}</div>
                                @endif
                                @if($contact->email)
                                    <div class="text-muted fs-8"><i class="bi bi-envelope text-muted me-1"></i>{{ $contact->email }}</div>
                                @endif
                                @if(!$contact->phone && !$contact->email)
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-gray-700 fs-7 text-truncate" style="max-width: 200px;">
                                    {{ $contact->address ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-light-secondary fs-8 fw-semibold">
                                    {{ $contact->payment_term_days ?? 0 }} Hari (TOP)
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-gray-900">
                                    Rp {{ number_format($contact->credit_limit ?? 0, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($contact->is_active)
                                    <span class="badge badge-light-success fs-8 fw-semibold">Aktif</span>
                                @else
                                    <span class="badge badge-light-danger fs-8 fw-semibold">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-gear-fill me-1"></i> Aksi
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('contacts.edit', $contact->id) }}">
                                                <i class="bi bi-pencil-square text-primary me-2"></i> Edit Kontak
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('contacts.create') }}?copy_id={{ $contact->id }}">
                                                <i class="bi bi-files text-info me-2"></i> Salin / Duplikasi
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <form action="{{ route('contacts.destroy', $contact->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kontak {{ $contact->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item py-2 text-danger">
                                                    <i class="bi bi-trash text-danger me-2"></i> Hapus Kontak
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                                Belum ada data kontak terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
