<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'wo_number',
        'item_id',
        'bom_id',
        'routing_id',
        'current_step_id',
        'target_quantity',
        'produced_quantity',
        'raw_material_warehouse_id',
        'finished_good_warehouse_id',
        'start_date',
        'due_date',
        'status', // draft, in_progress, completed, cancelled
        'journal_id',
        'created_by_id',
        'notes',
    ];

    protected $casts = [
        'target_quantity' => 'decimal:4',
        'produced_quantity' => 'decimal:4',
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function bom()
    {
        return $this->belongsTo(Bom::class);
    }

    public function routing()
    {
        return $this->belongsTo(Routing::class);
    }

    public function currentStep()
    {
        return $this->belongsTo(RoutingStep::class, 'current_step_id');
    }

    public function rawMaterialWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'raw_material_warehouse_id');
    }

    public function finishedGoodWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'finished_good_warehouse_id');
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function getNextStep(): ?RoutingStep
    {
        if (! $this->routing) {
            return null;
        }

        $steps = $this->routing->steps()->orderBy('step_number')->get();
        if ($steps->isEmpty()) {
            return null;
        }

        if (! $this->current_step_id) {
            return $steps->first();
        }

        $current = $steps->firstWhere('id', $this->current_step_id);
        if (! $current) {
            return $steps->first();
        }

        return $steps->firstWhere('step_number', '>', $current->step_number);
    }
}
