<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiberRoute extends Model
{
    protected $fillable = [
        'name',
        'area_id',
        'coordinates',
        'color',
        'type',
        'notes',
    ];

    protected $casts = [
        'coordinates' => 'array',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
