<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherBatch extends Model
{
    protected $fillable = [
        'name',
        'type',
        'duration_days',
        'speed_limit',
        'price',
        'profile',
        'prefix',
        'total',
        'used',
        'area_id',
        'created_by',
    ];

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class, 'batch_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getAvailableCountAttribute(): int
    {
        return $this->total - $this->used;
    }
}
