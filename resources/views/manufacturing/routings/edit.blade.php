@extends('layouts.app')

@section('title', 'Edit Routing Produksi')
@section('page_title', 'Edit Routing Produksi')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Edit Jalur Routing: {{ $routing->code }}</h2>
            <p class="text-muted fs-7 mb-0">Perbarui tahapan alur stasiun kerja produksi (Tahap A &rarr; B &rarr; C &rarr; D &rarr; E)</p>
        </div>
        <a href="{{ route('manufacturing.routings.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Routing
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary fs-3"></i> Edit Formulir Routing & Tahapan
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Perbarui identitas rute stasiun kerja pengerjaan SPK manufaktur
                </span>
            </div>
        </div>

        <form action="{{ route('manufacturing.routings.update', $routing->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Identitas & Pengaturan Jalur Routing</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-3">
                        <label class="form-label required fs-7 fw-semibold text-gray-700">Kode Routing</label>
                        <input type="text" name="code" class="form-control form-control-solid form-control-sm @error('code') is-invalid @enderror" value="{{ old('code', $routing->code) }}" required placeholder="Kode routing..." />
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required fs-7 fw-semibold text-gray-700">Nama Jalur Routing</label>
                        <input type="text" name="name" class="form-control form-control-solid form-control-sm @error('name') is-invalid @enderror" value="{{ old('name', $routing->name) }}" required placeholder="Nama routing..." />
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Status Keaktifan</label>
                        <div class="form-check form-check-solid p-2.5 rounded bg-light border border-gray-200">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" id="isActiveSwitch" value="1" {{ $routing->is_active ? 'checked' : '' }} />
                            <label class="form-check-label fw-semibold text-gray-800 fs-7" for="isActiveSwitch">
                                Routing Aktif
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Deskripsi / Catatan Routing</label>
                        <textarea name="description" class="form-control form-control-solid form-control-sm" rows="2" placeholder="Catatan alur kerja...">{{ old('description', $routing->description) }}</textarea>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                        <h4 class="fs-6 fw-bold text-gray-800 m-0">Urutan Tahapan Proses (Pos Kerja)</h4>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary fw-semibold" onclick="addStepRow()">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Tahapan
                    </button>
                </div>

                <div class="table-responsive rounded border mb-2">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0 no-datatable" id="stepsTable">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th style="width: 12%;" class="text-center">Tahapan</th>
                                <th style="width: 44%;">Nama Tahap / Pos Kerja <span class="text-danger">*</span></th>
                                <th style="width: 36%;">Instruksi / Catatan Standar</th>
                                <th class="text-center pe-4" style="width: 8%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="stepsBody">
                            <!-- Dynamic Rows -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('manufacturing.routings.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-check2-circle me-1"></i> Perbarui Jalur Routing
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let stepCount = 0;
    const existingSteps = @json($routing->steps);

    function addStepRow(name = '', desc = '') {
        const tr = document.createElement('tr');
        tr.id = `step-row-${stepCount}`;

        tr.innerHTML = `
            <td class="text-center fw-bold text-primary step-num">
                Tahap ${stepCount + 1}
            </td>
            <td>
                <input type="text" name="steps[${stepCount}][name]" class="form-control form-control-solid form-control-sm" value="${name}" placeholder="Contoh: Tahap A: Penimbangan" required />
            </td>
            <td>
                <input type="text" name="steps[${stepCount}][description]" class="form-control form-control-solid form-control-sm" value="${desc}" placeholder="Instruksi pengerjaan..." />
            </td>
            <td class="text-center pe-4">
                <button type="button" class="btn btn-icon btn-light-danger btn-sm" onclick="removeStepRow(${stepCount})" title="Hapus Tahapan">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        document.getElementById('stepsBody').appendChild(tr);
        stepCount++;
        reindexSteps();
    }

    function removeStepRow(idx) {
        const row = document.getElementById(`step-row-${idx}`);
        if (row) {
            row.remove();
            reindexSteps();
        }
    }

    function reindexSteps() {
        const rows = document.querySelectorAll('#stepsBody tr');
        rows.forEach((r, i) => {
            const numCell = r.querySelector('.step-num');
            if (numCell) {
                numCell.innerHTML = `<span class="badge badge-light-primary fw-bold">Tahap ${i + 1}</span>`;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (existingSteps && existingSteps.length > 0) {
            existingSteps.forEach(s => addStepRow(s.name, s.description));
        } else {
            addStepRow('Tahap A: Penimbangan Bahan Baku', 'Timbang bahan baku sesuai proporsi BOM');
        }
    });
</script>
@endsection
