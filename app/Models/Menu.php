<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'section',
        'route',
        'url',
        'icon',
        'badge',
        'badge_color',
        'permission_name',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->where('is_active', true)->orderBy('order');
    }

    public function getResolvedUrlAttribute(): string
    {
        if ($this->route && \Illuminate\Support\Facades\Route::has($this->route)) {
            return route($this->route);
        }

        return $this->url ?: '#';
    }

    public function isActiveRoute(): bool
    {
        if (! $this->route) {
            return false;
        }

        if (request()->routeIs($this->route)) {
            return true;
        }

        // If route is resource index (e.g. sales.so.index or items.index), match resource prefix (sales.so.*, items.*)
        if (str_ends_with($this->route, '.index')) {
            $prefix = substr($this->route, 0, -6);

            return request()->routeIs($prefix . '.*');
        }

        return request()->routeIs($this->route . '.*');
    }
}
