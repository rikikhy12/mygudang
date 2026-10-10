@extends('layouts.app')

@section('title', 'Work Orders (SPK Produksi)')
@section('page_title', 'Work Orders (SPK Produksi)')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-gear-wide-connected fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Surat Perintah Kerja (SPK / Work Order)</h3>
                <span class="text-muted fs-8 fw-semibold">Monitoring realisasi manufaktur, tahapan produksi bertahap, dan otomatisasi jurnal HPP</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('manufacturing.work_orders.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-plus-lg me-1"></i> Terbitkan SPK Baru
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-4">No. SPK (WO)</th>
                        <th>Barang Target</th>
                        <th>BOM & Routing</th>
                        <th class="text-center">Target Qty</th>
                        <th class="text-center">Hasil Qty</th>
                        <th>Gudang Tujuan</th>
                        <th>Progres Tahapan</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @forelse($workOrders as $wo)
                    <tr>
                        <td class="ps-4">
                            <a href="{{ route('manufacturing.work_orders.show', $wo->id) }}" class="fw-bold text-primary text-hover-underline">
                                {{ $wo->wo_number }}
                            </a>
                            <div class="text-muted fs-8">{{ $wo->start_date?->format('d/m/Y') }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-gray-900">{{ $wo->item?->name }}</div>
                            <span class="badge badge-light-secondary fs-9">{{ $wo->item?->code }}</span>
                        </td>
                        <td>
                            <div class="text-gray-800 fw-bold">{{ $wo->bom?->name }}</div>
                            <span class="text-muted fs-8">{{ $wo->routing?->name ?? 'Tanpa Routing (Instan)' }}</span>
                        </td>
                        <td class="text-center fw-bold">
                            {{ number_format($wo->target_quantity, 2) }} {{ $wo->item?->unit?->symbol }}
                        </td>
                        <td class="text-center fw-bolder {{ $wo->status === 'completed' ? 'text-success' : 'text-muted' }}">
                            {{ number_format($wo->produced_quantity, 2) }} {{ $wo->item?->unit?->symbol }}
                        </td>
                        <td>
                            <span class="badge badge-light-info fw-bold">{{ $wo->finishedGoodWarehouse?->name }}</span>
                        </td>
                        <td>
                            @if($wo->status === 'completed')
                                <span class="badge badge-light-success fw-bold">
                                    <i class="bi bi-check2-all text-success me-1"></i> Selesai (Stok Bertambah)
                                </span>
                            @elseif($wo->currentStep)
                                <span class="badge badge-light-primary fw-bold">
                                    <i class="bi bi-arrow-repeat text-primary me-1"></i> {{ $wo->currentStep->name }}
                                </span>
                            @else
                                <span class="badge badge-light-warning fw-bold">Dalam Proses</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($wo->status === 'completed')
                                <span class="badge badge-light-success">Completed</span>
                            @elseif($wo->status === 'in_progress')
                                <span class="badge badge-light-primary">In Progress</span>
                            @elseif($wo->status === 'cancelled')
                                <span class="badge badge-light-danger">Cancelled</span>
                            @else
                                <span class="badge badge-light-secondary">Draft</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('manufacturing.work_orders.show', $wo->id) }}" class="btn btn-sm btn-light-primary py-1 px-3">
                                Buka SPK <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-6 text-muted">
                            <i class="bi bi-inbox fs-2x mb-2 d-block"></i>
                            Belum ada Work Order (SPK) yang dibuat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">
            {{ $workOrders->links() }}
        </div>
    </div>
</div>
@endsection
