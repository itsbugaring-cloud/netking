<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Odp extends Model
{
    protected $table = 'odps';
    protected $fillable = [
        'name',
        'code',
        'area_id',
        'latitude',
        'longitude',
        'address',
        'max_capacity',
        'used_capacity',
        'odp_type',
        'status',
        'notes',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'odp_id');
    }

    public function getAvailableSlotsAttribute(): int
    {
        return max(0, $this->max_capacity - $this->used_capacity);
    }

    public function getUsagePercentAttribute(): int
    {
        if ($this->max_capacity === 0) return 0;
        return (int) round(($this->used_capacity / $this->max_capacity) * 100);
    }

    public function isFull(): bool
    {
        return $this->used_capacity >= $this->max_capacity;
    }
}
