<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'speed_down',
        'speed_up',
        'price',
        'type',
        'description',
        'mikrotik_profile',
        'radius_group',
        'is_active',
        'area_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'speed_down' => 'integer',
        'speed_up' => 'integer',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get formatted speed string (e.g., "10M/5M")
     */
    public function getSpeedLabelAttribute(): string
    {
        return $this->speed_down . 'M/' . $this->speed_up . 'M';
    }

    /**
     * Get formatted price (e.g., "Rp 150.000")
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Get MikroTik rate-limit string (e.g., "10M/5M" = rx/tx = down/up)
     * MikroTik format: rx-rate/tx-rate (download/upload)
     */
    public function getMikrotikRateLimitAttribute(): string
    {
        return $this->speed_down . 'M/' . $this->speed_up . 'M';
    }

    /**
     * Scope: only active packages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
