@extends('layouts.app')

@section('title', 'Master Chart of Accounts (COA)')
@section('page_title', 'Master Bagan Akun Standar (COA)')

@section('content')
<div class="card card-flush shadow-sm">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-diagram-3-fill fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Daftar Chart of Accounts (COA)</h3>
                <span class="text-muted fs-8 fw-semibold">Kelola bagan akun buku besar, klasifikasi aktiva, pasiva, dan ekuitas</span>
            </div>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('coa.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-plus-circle me-1"></i> Tambah Akun COA
            </a>
        </div>
    </div>

    <div class="card-body pt-0">
        <!-- Filter Tabs -->
        <div class="d-flex flex-wrap gap-2 mb-6 border-bottom pb-4">
            <a href="{{ route('coa.index') }}" class="btn btn-sm {{ !$type ? 'btn-primary' : 'btn-light' }}">Semua Akun</a>
            <a href="{{ route('coa.index', ['type' => 'asset']) }}" class="btn btn-sm {{ $type == 'asset' ? 'btn-primary' : 'btn-light' }}">Aset (1xxx)</a>
            <a href="{{ route('coa.index', ['type' => 'liability']) }}" class="btn btn-sm {{ $type == 'liability' ? 'btn-primary' : 'btn-light' }}">Kewajiban (2xxx)</a>
            <a href="{{ route('coa.index', ['type' => 'equity']) }}" class="btn btn-sm {{ $type == 'equity' ? 'btn-primary' : 'btn-light' }}">Ekuitas (3xxx)</a>
            <a href="{{ route('coa.index', ['type' => 'revenue']) }}" class="btn btn-sm {{ $type == 'revenue' ? 'btn-primary' : 'btn-light' }}">Pendapatan (4xxx)</a>
            <a href="{{ route('coa.index', ['type' => 'cogs']) }}" class="btn btn-sm {{ $type == 'cogs' ? 'btn-primary' : 'btn-light' }}">HPP (5xxx)</a>
            <a href="{{ route('coa.index', ['type' => 'expense']) }}" class="btn btn-sm {{ $type == 'expense' ? 'btn-primary' : 'btn-light' }}">Beban (6xxx)</a>
        </div>

        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Klasifikasi</th>
                        <th>Saldo Normal</th>
                        <th>Kategori Arus Kas</th>
                        <th>Kas / Bank?</th>
                        <th>Status</th>
                        <th class="text-end min-w-100px pe-3 no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($accounts as $acc)
                    <tr>
                        <td><code>{{ $acc->code }}</code></td>
                        <td>
                            <span class="text-gray-800 fw-bold fs-6">{{ $acc->name }}</span>
                            @if($acc->parent)
                                <div class="text-muted fs-8">Induk: {{ $acc->parent->code }} - {{ $acc->parent->name }}</div>
                            @endif
                        </td>
                        <td>
                            @switch($acc->type)
                                @case('asset') <span class="badge badge-light-info fw-bold">Aset</span> @break
                                @case('liability') <span class="badge badge-light-warning fw-bold">Kewajiban</span> @break
                                @case('equity') <span class="badge badge-light-primary fw-bold">Ekuitas</span> @break
                                @case('revenue') <span class="badge badge-light-success fw-bold">Pendapatan</span> @break
                                @case('cogs') <span class="badge badge-light-danger fw-bold">HPP</span> @break
                                @case('expense') <span class="badge badge-light-danger fw-bold">Beban Operasional</span> @break
                            @endswitch
                        </td>
                        <td>
                            <span class="badge {{ $acc->normal_balance == 'debit' ? 'badge-light-primary' : 'badge-light-secondary' }} fw-bold">
                                {{ strtoupper($acc->normal_balance) }}
                            </span>
                        </td>
                        <td>
                            @switch($acc->cash_flow_category)
                                @case('investing')
                                    <span class="badge badge-light-warning fw-semibold">Investasi</span>
                                    @break
                                @case('financing')
                                    <span class="badge badge-light-primary fw-semibold">Pendanaan</span>
                                    @break
                                @case('none')
                                    <span class="badge badge-light-secondary fw-semibold text-muted">Non-Arus Kas</span>
                                    @break
                                @default
                                    <span class="badge badge-light-success fw-semibold">Operasi</span>
                            @endswitch
                        </td>
                        <td>
                            @if($acc->is_cash_or_bank)
                                <span class="badge badge-light-success fw-bold"><i class="bi bi-check2 text-success me-1"></i> Ya</span>
                            @else
                                <span class="text-muted fs-7">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $acc->is_active ? 'badge-light-success' : 'badge-light-danger' }} fw-bold">
                                {{ $acc->is_active ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('coa.edit', $acc->id) }}">
                                            <i class="bi bi-pencil-square text-warning me-2"></i> Edit Akun
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form action="{{ route('coa.destroy', $acc->id) }}" method="POST" onsubmit="return confirm('Hapus akun {{ $acc->code }} - {{ $acc->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item py-2 text-danger">
                                                <i class="bi bi-trash text-danger me-2"></i> Hapus
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
