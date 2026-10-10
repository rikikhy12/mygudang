<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutingStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'routing_id',
        'step_number',
        'name',
        'description',
        'is_final_step',
    ];

    protected $casts = [
        'step_number' => 'integer',
        'is_final_step' => 'boolean',
    ];

    public function routing()
    {
        return $this->belongsTo(Routing::class);
    }
}
