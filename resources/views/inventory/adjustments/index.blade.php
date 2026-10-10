@extends('layouts.app')

@section('title', 'Penyesuaian Stok (Opname)')
@section('page_title', 'Penyesuaian Stok Fisik / Stock Opname')

@section('content')
<div class="card card-flush shadow-sm">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <h3 class="card-label fw-bolder text-gray-800 fs-4">
                <i class="bi bi-sliders text-primary me-2 fs-3"></i> Daftar Penyesuaian Stok (Opname)
            </h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('inventory.adjustments.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Catat Opname Baru
            </a>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>No. Penyesuaian</th>
                        <th>Gudang</th>
                        <th>Tanggal Opname</th>
                        <th>Alasan / Keterangan</th>
                        <th>Petugas</th>
                        <th>Jurnal Penyesuaian</th>
                        <th class="text-end min-w-80px no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($adjustments as $adj)
                    <tr>
                        <td>
                            <a href="{{ route('inventory.adjustments.show', $adj->id) }}" class="text-gray-800 fw-bold text-hover-primary">
                                {{ $adj->adjustment_number }}
                            </a>
                        </td>
                        <td><span class="text-gray-900 fw-bold">{{ $adj->warehouse->name }}</span></td>
                        <td>{{ $adj->adjustment_date->format('d/m/Y') }}</td>
                        <td>{{ $adj->reason }}</td>
                        <td>{{ $adj->adjustedBy->name }}</td>
                        <td>
                            @if($adj->journal)
                                <a href="{{ route('accounting.journals.show', $adj->journal_id) }}" class="badge badge-light-success fw-bold">
                                    <i class="bi bi-journal-check me-1"></i> {{ $adj->journal->journal_number }}
                                </a>
                            @else
                                <span class="badge badge-light-secondary">No Journal</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('inventory.adjustments.show', $adj->id) }}">
                                            <i class="bi bi-eye text-info me-2"></i> Lihat Detail
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
