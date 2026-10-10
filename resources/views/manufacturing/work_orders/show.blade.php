@extends('layouts.app')

@section('title', 'Detail Work Order: ' . $workOrder->wo_number)
@section('page_title', 'Detail Work Order: ' . $workOrder->wo_number)

@section('content')
<div class="d-flex flex-column gap-5">
    
    <!-- Top Status Banner -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="w-45px h-45px rounded bg-light-primary d-flex align-items-center justify-content-center text-primary fs-3">
                        <i class="bi bi-gear-wide-connected"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h2 class="fw-bolder text-gray-900 m-0 fs-3">{{ $workOrder->wo_number }}</h2>
                            @if($workOrder->status === 'completed')
                                <span class="badge badge-success fw-bold">PRODUKSI SELESAI</span>
                            @elseif($workOrder->status === 'in_progress')
                                <span class="badge badge-primary fw-bold">SEDANG BERJALAN</span>
                            @elseif($workOrder->status === 'cancelled')
                                <span class="badge badge-danger fw-bold">DIBATALKAN</span>
                            @else
                                <span class="badge badge-secondary fw-bold">DRAFT</span>
                            @endif
                        </div>
                        <span class="text-muted fs-7">
                            Barang Target: <strong class="text-gray-800">{{ $workOrder->item?->name }}</strong> ({{ $workOrder->item?->code }}) &bull; Dibuat oleh: {{ $workOrder->createdBy?->name ?? 'Admin' }}
                        </span>
                    </div>
                </div>

                <div class="d-flex gap-2 align-items-center flex-wrap">
                    @if($workOrder->status !== 'completed')
                        @if($workOrder->routing && $workOrder->routing->steps->count() > 0)
                            @php
                                $nextStep = $workOrder->getNextStep();
                            @endphp
                            <form action="{{ route('manufacturing.work_orders.advance_step', $workOrder->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm fw-bold">
                                    <i class="bi bi-arrow-right-circle me-1"></i>
                                    @if($nextStep)
                                        Lanjut ke: {{ $nextStep->name }}
                                    @else
                                        Selesaikan Tahap Final & Tambah Stok
                                    @endif
                                </button>
                            </form>
                        @endif

                        <button type="button" class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#completeWoModal">
                            <i class="bi bi-check2-all me-1"></i> Selesaikan Produksi
                        </button>
                    @endif
                    <a href="{{ route('manufacturing.work_orders.index') }}" class="btn btn-light-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Visual Production Routing Pipeline Steps (A -> B -> C -> D -> E) -->
    @if($workOrder->routing && $workOrder->routing->steps->count() > 0)
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h4 class="card-title fw-bolder text-gray-800 m-0 fs-5">
                <i class="bi bi-bezier2 text-primary me-2"></i>Alur Tahapan Routing: {{ $workOrder->routing->name }}
            </h4>
        </div>
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between position-relative gap-2 flex-wrap">
                @php
                    $currentStepNum = $workOrder->currentStep ? $workOrder->currentStep->step_number : 1;
                    if ($workOrder->status === 'completed') {
                        $currentStepNum = 999;
                    }
                @endphp
                @foreach($workOrder->routing->steps as $step)
                    @php
                        $isPast = $workOrder->status === 'completed' || $step->step_number < $currentStepNum;
                        $isCurrent = $workOrder->status !== 'completed' && $step->step_number == $currentStepNum;
                        $isFuture = $workOrder->status !== 'completed' && $step->step_number > $currentStepNum;
                    @endphp
                    <div class="d-flex flex-column align-items-center text-center p-3 rounded {{ $isCurrent ? 'bg-light-primary border border-primary border-2 shadow-xs' : ($isPast ? 'bg-light-success' : 'bg-light') }}" style="flex: 1; min-width: 140px;">
                        <div class="w-35px h-35px rounded-circle d-flex align-items-center justify-content-center fw-bold fs-7 mb-2 {{ $isPast ? 'bg-success text-white' : ($isCurrent ? 'bg-primary text-white animate-pulse' : 'bg-secondary text-gray-600') }}">
                            @if($isPast)
                                <i class="bi bi-check-lg"></i>
                            @else
                                {{ $step->step_number }}
                            @endif
                        </div>
                        <div class="fw-bold fs-8 {{ $isCurrent ? 'text-primary' : ($isPast ? 'text-success' : 'text-gray-700') }}">
                            {{ $step->name }}
                        </div>
                        <span class="text-muted fs-9 mt-1">
                            @if($step->is_final_step)
                                <span class="badge badge-light-danger fs-9">Tahap Akhir (Stok)</span>
                            @else
                                {{ $step->description ?? 'Proses kerja' }}
                            @endif
                        </span>
                    </div>

                    @if(!$loop->last)
                        <div class="d-none d-md-block text-gray-400 fs-4">
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Information & Component Details -->
    <div class="row g-5">
        <!-- Left: WO Specifications -->
        <div class="col-md-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title fw-bolder text-gray-800 m-0 fs-5">Informasi SPK</h4>
                </div>
                <div class="card-body p-4">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                        <tr>
                            <td class="text-muted fw-semibold w-40">Target Jumlah:</td>
                            <td class="fw-bolder text-gray-900 fs-6">{{ number_format($workOrder->target_quantity, 2) }} {{ $workOrder->item?->unit?->symbol }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Jumlah Selesai:</td>
                            <td class="fw-bolder {{ $workOrder->status === 'completed' ? 'text-success' : 'text-gray-600' }} fs-6">
                                {{ number_format($workOrder->produced_quantity, 2) }} {{ $workOrder->item?->unit?->symbol }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Formula BOM:</td>
                            <td class="fw-bold text-primary">{{ $workOrder->bom?->code }} - {{ $workOrder->bom?->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Gudang Bahan Baku:</td>
                            <td><span class="badge badge-light-warning fw-bold">{{ $workOrder->rawMaterialWarehouse?->name }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Gudang Barang Jadi:</td>
                            <td><span class="badge badge-light-success fw-bold">{{ $workOrder->finishedGoodWarehouse?->name }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Tgl Mulai:</td>
                            <td class="text-gray-800 fw-bold">{{ $workOrder->start_date?->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Tgl Target (Due):</td>
                            <td class="text-gray-800">{{ $workOrder->due_date ? $workOrder->due_date->format('d M Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Catatan:</td>
                            <td class="text-gray-700">{{ $workOrder->notes ?? '-' }}</td>
                        </tr>
                    </table>

                    @if($workOrder->journal)
                    <div class="separator my-4"></div>
                    <div class="bg-light-primary p-3 rounded">
                        <div class="fw-bold text-primary fs-7 mb-1">
                            <i class="bi bi-journal-check me-1"></i> Jurnal Akuntansi Terbentuk:
                        </div>
                        <a href="{{ route('accounting.journals.show', $workOrder->journal_id) }}" class="fw-bolder text-primary fs-7 text-hover-underline">
                            {{ $workOrder->journal->journal_number }} &rarr;
                        </a>
                        <div class="text-muted fs-9 mt-1">Debit Persediaan Barang Jadi &bull; Kredit Persediaan Bahan Baku</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Required Materials Breakdown -->
        <div class="col-md-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title fw-bolder text-gray-800 m-0 fs-5">Kebutuhan Bahan Baku (BOM Calculation)</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                            <thead class="table-light">
                                <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                    <th class="ps-3">Bahan Baku</th>
                                    <th class="text-center">Kebutuhan per Unit</th>
                                    <th class="text-center">Total Kebutuhan Batch</th>
                                    <th class="text-end pe-3">Est. Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $yield = (float) ($workOrder->bom?->yield_quantity ?: 1);
                                    $multiplier = (float) $workOrder->target_quantity / $yield;
                                    $totalEstimatedBatchCost = 0;
                                @endphp
                                @if($workOrder->bom)
                                    @foreach($workOrder->bom->items as $bomItem)
                                        @php
                                            $batchQty = (float) $bomItem->quantity * $multiplier;
                                            $cost = (float) ($bomItem->unit_cost ?: ($bomItem->item?->purchase_price ?? 0));
                                            $lineCost = $batchQty * $cost;
                                            $totalEstimatedBatchCost += $lineCost;
                                        @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-bold text-gray-900">{{ $bomItem->item?->name }}</div>
                                                <span class="text-muted fs-9">{{ $bomItem->item?->code }}</span>
                                            </td>
                                            <td class="text-center">
                                                {{ number_format($bomItem->quantity, 4) }} {{ $bomItem->item?->unit?->symbol }}
                                            </td>
                                            <td class="text-center fw-bolder text-primary">
                                                {{ number_format($batchQty, 4) }} {{ $bomItem->item?->unit?->symbol }}
                                            </td>
                                            <td class="text-end pe-3 fw-bold text-gray-800">
                                                Rp {{ number_format($lineCost, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                            <tfoot>
                                <tr class="bg-light fw-bolder text-gray-900">
                                    <td colspan="3" class="ps-3 text-end">Total Estimasi Biaya Bahan:</td>
                                    <td class="text-end pe-3 text-success">
                                        Rp {{ number_format($totalEstimatedBatchCost, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Selesaikan Produksi -->
<div class="modal fade" id="completeWoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('manufacturing.work_orders.complete', $workOrder->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bolder text-gray-900">Konfirmasi Penyelesaian Produksi</h5>
                    <button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal">
                        <i class="bi bi-x fs-2"></i>
                    </button>
                </div>
                <div class="modal-body py-4">
                    <p class="fs-7 text-gray-700 mb-3">
                        Menyelesaikan Work Order ini akan otomatis melakukan:
                    </p>
                    <ul class="fs-7 text-gray-600 mb-4 ps-4">
                        <li>Memotong stok seluruh bahan baku di gudang: <strong>{{ $workOrder->rawMaterialWarehouse?->name }}</strong>.</li>
                        <li>Menambah stok barang jadi ({{ $workOrder->item?->name }}) di gudang: <strong>{{ $workOrder->finishedGoodWarehouse?->name }}</strong>.</li>
                        <li>Membukukan otomatis Jurnal Akuntansi HPP & Persediaan.</li>
                    </ul>

                    <div class="mb-3">
                        <label class="form-label required fs-7 fw-bold">Realisasi Jumlah Barang Jadi yang Dihasilkan</label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.01" min="0.01" name="produced_quantity" class="form-control form-control-sm" value="{{ $workOrder->target_quantity }}" required />
                            <span class="input-group-text">{{ $workOrder->item?->unit?->symbol }}</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-success fw-bold">
                        <i class="bi bi-check2-circle me-1"></i> Ya, Selesaikan & Masukkan Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
