@extends('layouts.app')

@section('title', 'Purchase Requisition (PR)')
@section('page_title', 'Purchase Requisitions (Permintaan Pembelian)')

@section('content')
<div class="card card-flush shadow-sm">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-file-earmark-text fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Daftar Permintaan Pembelian (PR)</h3>
                <span class="text-muted fs-8 fw-semibold">Pengajuan kebutuhan barang/jasa dari divisi internal dan alur verifikasi approval</span>
            </div>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('procurement.pr.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-plus-circle me-1"></i> Buat PR Baru
            </a>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>No. PR</th>
                        <th>Tanggal Permintaan</th>
                        <th>Departemen</th>
                        <th>Pemohon (Requester)</th>
                        <th>Status Approval</th>
                        <th class="text-end min-w-120px no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($requisitions as $pr)
                    <tr>
                        <td>
                            <a href="{{ route('procurement.pr.show', $pr->id) }}" class="text-gray-800 fw-bold text-hover-primary">
                                {{ $pr->pr_number }}
                            </a>
                        </td>
                        <td>{{ $pr->request_date->format('d/m/Y') }}</td>
                        <td>{{ $pr->department ?: '-' }}</td>
                        <td><span class="text-gray-800 fw-semibold">{{ $pr->requester->name }}</span></td>
                        <td>
                            @if($pr->status == 'approved')
                                <span class="badge badge-light-success fw-bold">Approved</span>
                            @elseif($pr->status == 'pending_approval')
                                <span class="badge badge-light-warning fw-bold">Pending Approval</span>
                            @elseif($pr->status == 'rejected')
                                <span class="badge badge-light-danger fw-bold">Rejected</span>
                            @elseif($pr->status == 'completed')
                                <span class="badge badge-light-info fw-bold">Completed in PO</span>
                            @else
                                <span class="badge badge-light-secondary fw-bold">Draft</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('procurement.pr.show', $pr->id) }}">
                                            <i class="bi bi-eye text-info me-2"></i> Lihat Detail
                                        </a>
                                    </li>
                                    @if($pr->status == 'approved')
                                    <li>
                                        <a class="dropdown-item py-2 text-success" href="{{ route('procurement.po.create', ['pr_id' => $pr->id]) }}">
                                            <i class="bi bi-cart-plus text-success me-2"></i> Terbitkan PO
                                        </a>
                                    </li>
                                    @endif
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
