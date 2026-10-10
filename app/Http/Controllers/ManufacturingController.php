<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\BomItem;
use App\Models\Item;
use App\Models\Routing;
use App\Models\RoutingStep;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\WorkOrder;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManufacturingController extends Controller
{
    // ==========================================
    // 1. BILL OF MATERIALS (BOM)
    // ==========================================

    public function bomIndex()
    {
        $boms = Bom::with(['item.unit', 'items.item.unit'])
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('manufacturing.boms.index', compact('boms'));
    }

    public function bomCreate()
    {
        // Items that can have BOM: bundles, manufactured goods, or finished goods
        $items = Item::whereIn('item_type', ['bundle', 'manufactured_good', 'finished_good'])
            ->orWhereNull('item_type')
            ->orderBy('name')
            ->get();

        // Material items: raw materials, super products, finished goods
        $materials = Item::where('is_active', true)
            ->where(function ($q) {
                $q->whereIn('item_type', ['raw_material', 'super_product', 'finished_good'])
                    ->orWhereNull('item_type');
            })
            ->orderBy('name')
            ->get();

        $units = Unit::orderBy('name')->get();
        $autoCode = 'BOM-'.str_pad(Bom::count() + 1, 4, '0', STR_PAD_LEFT);

        return view('manufacturing.boms.create', compact('items', 'materials', 'units', 'autoCode'));
    }

    public function bomStore(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'unique:boms,code'],
            'name' => ['required', 'string', 'max:255'],
            'item_id' => ['required', 'exists:items,id'],
            'yield_quantity' => ['required', 'numeric', 'min:0.0001'],
            'overhead_cost' => ['nullable', 'numeric', 'min:0'],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request) {
            $bom = Bom::create([
                'code' => $request->code,
                'name' => $request->name,
                'item_id' => $request->item_id,
                'yield_quantity' => $request->yield_quantity,
                'overhead_cost' => $request->overhead_cost ?? 0,
                'labor_cost' => $request->labor_cost ?? 0,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
            ]);

            foreach ($request->items as $row) {
                $material = Item::find($row['item_id']);
                BomItem::create([
                    'bom_id' => $bom->id,
                    'item_id' => $row['item_id'],
                    'unit_id' => $material?->unit_id,
                    'quantity' => $row['quantity'],
                    'unit_cost' => $row['unit_cost'] ?? ($material?->purchase_price ?? 0),
                ]);
            }
        });

        return redirect()->route('manufacturing.boms.index')->with('success', 'Bill of Materials (BOM) berhasil disimpan!');
    }

    public function bomEdit(Bom $bom)
    {
        $bom->load(['items.item', 'item']);

        $items = Item::whereIn('item_type', ['bundle', 'manufactured_good', 'finished_good'])
            ->orWhereNull('item_type')
            ->orderBy('name')
            ->get();

        $materials = Item::where('is_active', true)
            ->where(function ($q) {
                $q->whereIn('item_type', ['raw_material', 'super_product', 'finished_good'])
                    ->orWhereNull('item_type');
            })
            ->orderBy('name')
            ->get();

        $units = Unit::orderBy('name')->get();

        return view('manufacturing.boms.edit', compact('bom', 'items', 'materials', 'units'));
    }

    public function bomUpdate(Request $request, Bom $bom)
    {
        $request->validate([
            'code' => ['required', 'string', 'unique:boms,code,'.$bom->id],
            'name' => ['required', 'string', 'max:255'],
            'item_id' => ['required', 'exists:items,id'],
            'yield_quantity' => ['required', 'numeric', 'min:0.0001'],
            'overhead_cost' => ['nullable', 'numeric', 'min:0'],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $bom) {
            $bom->update([
                'code' => $request->code,
                'name' => $request->name,
                'item_id' => $request->item_id,
                'yield_quantity' => $request->yield_quantity,
                'overhead_cost' => $request->overhead_cost ?? 0,
                'labor_cost' => $request->labor_cost ?? 0,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
            ]);

            $bom->items()->delete();
            foreach ($request->items as $row) {
                $material = Item::find($row['item_id']);
                BomItem::create([
                    'bom_id' => $bom->id,
                    'item_id' => $row['item_id'],
                    'unit_id' => $material?->unit_id,
                    'quantity' => $row['quantity'],
                    'unit_cost' => $row['unit_cost'] ?? ($material?->purchase_price ?? 0),
                ]);
            }
        });

        return redirect()->route('manufacturing.boms.index')->with('success', 'BOM berhasil diperbarui!');
    }

    public function bomDestroy(Bom $bom)
    {
        if ($bom->workOrders()->exists()) {
            return back()->with('error', 'BOM tidak dapat dihapus karena sudah memiliki Work Order.');
        }

        $bom->delete();

        return redirect()->route('manufacturing.boms.index')->with('success', 'BOM berhasil dihapus.');
    }

    // ==========================================
    // 2. ROUTINGS & STEPS (A -> B -> C -> D -> E)
    // ==========================================

    public function routingIndex()
    {
        $routings = Routing::with('steps')
            ->withCount('steps')
            ->orderBy('name')
            ->paginate(15);

        return view('manufacturing.routings.index', compact('routings'));
    }

    public function routingCreate()
    {
        $autoCode = 'RTG-'.str_pad(Routing::count() + 1, 3, '0', STR_PAD_LEFT);

        return view('manufacturing.routings.create', compact('autoCode'));
    }

    public function routingStore(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'unique:routings,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.name' => ['required', 'string', 'max:255'],
            'steps.*.description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request) {
            $routing = Routing::create([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
            ]);

            $totalSteps = count($request->steps);
            foreach ($request->steps as $index => $stepData) {
                RoutingStep::create([
                    'routing_id' => $routing->id,
                    'step_number' => $index + 1,
                    'name' => $stepData['name'],
                    'description' => $stepData['description'] ?? null,
                    'is_final_step' => ($index + 1 === $totalSteps),
                ]);
            }
        });

        return redirect()->route('manufacturing.routings.index')->with('success', 'Jalur Routing Produksi berhasil dibuat!');
    }

    public function routingEdit(Routing $routing)
    {
        $routing->load('steps');

        return view('manufacturing.routings.edit', compact('routing'));
    }

    public function routingUpdate(Request $request, Routing $routing)
    {
        $request->validate([
            'code' => ['required', 'string', 'unique:routings,code,'.$routing->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.name' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $routing) {
            $routing->update([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
            ]);

            $routing->steps()->delete();
            $totalSteps = count($request->steps);
            foreach ($request->steps as $index => $stepData) {
                RoutingStep::create([
                    'routing_id' => $routing->id,
                    'step_number' => $index + 1,
                    'name' => $stepData['name'],
                    'description' => $stepData['description'] ?? null,
                    'is_final_step' => ($index + 1 === $totalSteps),
                ]);
            }
        });

        return redirect()->route('manufacturing.routings.index')->with('success', 'Jalur Routing Produksi berhasil diperbarui!');
    }

    public function routingDestroy(Routing $routing)
    {
        if ($routing->workOrders()->exists()) {
            return back()->with('error', 'Routing tidak dapat dihapus karena sedang digunakan dalam Work Order.');
        }

        $routing->delete();

        return redirect()->route('manufacturing.routings.index')->with('success', 'Routing berhasil dihapus.');
    }

    // ==========================================
    // 3. WORK ORDERS (SPK PRODUKSI)
    // ==========================================

    public function workOrderIndex(Request $request)
    {
        $query = WorkOrder::with(['item.unit', 'bom', 'routing', 'currentStep', 'finishedGoodWarehouse', 'rawMaterialWarehouse'])
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $workOrders = $query->paginate(15);

        return view('manufacturing.work_orders.index', compact('workOrders'));
    }

    public function workOrderCreate()
    {
        $boms = Bom::with(['item.unit', 'items.item.unit'])->where('is_active', true)->orderBy('name')->get();
        $routings = Routing::with('steps')->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        $autoWoNumber = 'WO-'.date('Ymd').'-'.str_pad(WorkOrder::count() + 1, 4, '0', STR_PAD_LEFT);

        return view('manufacturing.work_orders.create', compact('boms', 'routings', 'warehouses', 'autoWoNumber'));
    }

    public function workOrderStore(Request $request)
    {
        $request->validate([
            'wo_number' => ['required', 'string', 'unique:work_orders,wo_number'],
            'bom_id' => ['required', 'exists:boms,id'],
            'routing_id' => ['nullable', 'exists:routings,id'],
            'target_quantity' => ['required', 'numeric', 'min:0.01'],
            'raw_material_warehouse_id' => ['required', 'exists:warehouses,id'],
            'finished_good_warehouse_id' => ['required', 'exists:warehouses,id'],
            'start_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string'],
        ]);

        $bom = Bom::findOrFail($request->bom_id);
        $firstStepId = null;

        if ($request->routing_id) {
            $routing = Routing::with('steps')->find($request->routing_id);
            $firstStep = $routing?->steps?->first();
            $firstStepId = $firstStep?->id;
        }

        $workOrder = WorkOrder::create([
            'wo_number' => $request->wo_number,
            'item_id' => $bom->item_id,
            'bom_id' => $bom->id,
            'routing_id' => $request->routing_id,
            'current_step_id' => $firstStepId,
            'target_quantity' => $request->target_quantity,
            'produced_quantity' => 0,
            'raw_material_warehouse_id' => $request->raw_material_warehouse_id,
            'finished_good_warehouse_id' => $request->finished_good_warehouse_id,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'status' => 'in_progress',
            'created_by_id' => auth()->id(),
            'notes' => $request->notes,
        ]);

        return redirect()->route('manufacturing.work_orders.show', $workOrder->id)
            ->with('success', "Work Order {$workOrder->wo_number} berhasil diterbitkan dan siap diproduksi!");
    }

    public function workOrderShow(WorkOrder $workOrder)
    {
        $workOrder->load([
            'item.unit',
            'bom.items.item.unit',
            'routing.steps',
            'currentStep',
            'rawMaterialWarehouse',
            'finishedGoodWarehouse',
            'journal.entries.account',
            'createdBy',
        ]);

        return view('manufacturing.work_orders.show', compact('workOrder'));
    }

    /**
     * Advance to the next routing step in production pipeline (A -> B -> C -> D -> E)
     */
    public function advanceStep(WorkOrder $workOrder, InventoryService $inventoryService)
    {
        if ($workOrder->status === 'completed') {
            return back()->with('error', 'Work Order ini sudah selesai.');
        }

        if (! $workOrder->routing) {
            // If no routing steps defined, directly complete the work order
            $inventoryService->processWorkOrderCompletion($workOrder, (float) $workOrder->target_quantity, auth()->id());

            return back()->with('success', "Work Order {$workOrder->wo_number} berhasil diselesaikan dan stok telah ditambahkan!");
        }

        $nextStep = $workOrder->getNextStep();

        if (! $nextStep) {
            // Already at final step -> Complete the production and add finished good stock!
            $inventoryService->processWorkOrderCompletion($workOrder, (float) $workOrder->target_quantity, auth()->id());

            return back()->with('success', "Tahap final selesai! Work Order {$workOrder->wo_number} telah rampung, bahan baku dipotong dan stok barang jadi bertambah di gudang tujuan.");
        }

        $workOrder->current_step_id = $nextStep->id;
        $workOrder->save();

        if ($nextStep->is_final_step) {
            // If next step is marked final, optionally can advance directly or prompt completion
            return back()->with('success', "Produksi berpindah ke tahap final: [{$nextStep->name}]. Selesaikan tahap ini untuk menambah stok barang jadi.");
        }

        return back()->with('success', "Produksi berhasil dilanjutkan ke tahap berikutnya: [{$nextStep->name}].");
    }

    /**
     * Force complete the work order and trigger stock + accounting entries.
     */
    public function completeWorkOrder(Request $request, WorkOrder $workOrder, InventoryService $inventoryService)
    {
        if ($workOrder->status === 'completed') {
            return back()->with('error', 'Work order ini sudah berstatus selesai.');
        }

        $producedQty = $request->filled('produced_quantity') ? (float) $request->produced_quantity : (float) $workOrder->target_quantity;

        try {
            $inventoryService->processWorkOrderCompletion($workOrder, $producedQty, auth()->id());

            return back()->with('success', "Produksi selesai! {$producedQty} unit barang jadi berhasil dimasukkan ke stok gudang.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses penyelesaian produksi: '.$e->getMessage());
        }
    }
}
