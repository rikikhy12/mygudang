@extends('layouts.app')

@section('title', 'Terbitkan Work Order (SPK) Baru')
@section('page_title', 'Terbitkan Work Order (SPK) Baru')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Terbitkan Work Order (SPK) Baru</h2>
            <p class="text-muted fs-7 mb-0">Perintahkan divisi manufaktur untuk mengeksekusi resep formula BOM menjadi barang jadi</p>
        </div>
        <a href="{{ route('manufacturing.work_orders.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar SPK
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-gear-wide-connected text-primary fs-3"></i> Surat Perintah Kerja Produksi (Work Order)
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Silakan isi nomor SPK, jadwal, formula resep BOM, target output, serta gudang bahan & barang jadi
                </span>
            </div>
        </div>

        <form action="{{ route('manufacturing.work_orders.store') }}" method="POST">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Nomor Dokumen & Jadwal Produksi</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <label class="form-label required fs-7 fw-semibold text-gray-700">Nomor SPK (WO Number)</label>
                        <input type="text" name="wo_number" class="form-control form-control-solid form-control-sm @error('wo_number') is-invalid @enderror" value="{{ old('wo_number', $autoWoNumber) }}" required placeholder="Nomor SPK..." />
                        @error('wo_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required fs-7 fw-semibold text-gray-700">Tanggal Mulai Produksi</label>
                        <input type="date" name="start_date" class="form-control form-control-solid form-control-sm" value="{{ old('start_date', date('Y-m-d')) }}" required />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Target Tanggal Selesai (Due Date)</label>
                        <input type="date" name="due_date" class="form-control form-control-solid form-control-sm" value="{{ old('due_date', date('Y-m-d', strtotime('+3 days'))) }}" />
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Formula BOM & Jalur Routing Produksi</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <label class="form-label required fs-7 fw-semibold text-gray-700">Pilih Formula BOM (Barang Target)</label>
                        <select name="bom_id" id="bomSelect" class="form-select form-select-solid form-select-sm @error('bom_id') is-invalid @enderror" data-control="select2" data-placeholder="-- Pilih Bill of Materials --" required onchange="onBomSelected(this)">
                            <option value="">-- Pilih Bill of Materials --</option>
                            @foreach($boms as $bom)
                                <option value="{{ $bom->id }}" data-yield="{{ $bom->yield_quantity }}" data-unit="{{ $bom->item?->unit?->symbol }}">
                                    {{ $bom->code }} - {{ $bom->name }} (Menghasilkan: {{ $bom->item?->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('bom_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Jalur Routing Produksi (Tahapan Proses)</label>
                        <select name="routing_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Tanpa Routing (Langsung Selesai) --">
                            <option value="">-- Tanpa Routing (Langsung Selesai) --</option>
                            @foreach($routings as $rtg)
                                <option value="{{ $rtg->id }}" {{ old('routing_id') == $rtg->id ? 'selected' : '' }}>
                                    {{ $rtg->code }} - {{ $rtg->name }} ({{ $rtg->steps->count() }} Tahapan)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">3</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Target Output & Alokasi Pergudangan</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <label class="form-label required fs-7 fw-semibold text-gray-700">Target Jumlah Produksi (Target Qty)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.01" min="0.01" name="target_quantity" id="targetQtyInput" class="form-control form-control-solid form-control-sm text-end fw-bold @error('target_quantity') is-invalid @enderror" value="{{ old('target_quantity', '10.00') }}" required />
                            <span class="input-group-text bg-light border-0 text-muted" id="targetUnitLabel">Unit</span>
                        </div>
                        @error('target_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required fs-7 fw-semibold text-gray-700">Gudang Asal Bahan Baku</label>
                        <select name="raw_material_warehouse_id" class="form-select form-select-solid form-select-sm" data-control="select2" required>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                        <span class="text-muted fs-9">Bahan baku akan dipotong dari gudang ini</span>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required fs-7 fw-semibold text-gray-700">Gudang Tujuan Hasil Produksi</label>
                        <select name="finished_good_warehouse_id" class="form-select form-select-solid form-select-sm" data-control="select2" required>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                        <span class="text-muted fs-9">Stok jadi akan ditambahkan ke gudang ini</span>
                    </div>
                </div>

                <!-- Section 4 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">4</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Instruksi & Catatan Khusus Produksi</h4>
                </div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Catatan SPK / SOP Operator</label>
                        <textarea name="notes" class="form-control form-control-solid form-control-sm" rows="3" placeholder="Instruksi khusus, nomor batch, atau prioritas pengerjaan...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('manufacturing.work_orders.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-file-earmark-check me-1"></i> Terbitkan Work Order
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function onBomSelected(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        const unit = opt.getAttribute('data-unit') || 'Unit';
        document.getElementById('targetUnitLabel').innerText = unit;
    }
</script>
@endsection
