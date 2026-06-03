<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AreaIpPool extends Model
{
    protected $fillable = [
        'area_id',
        'pool_name',
        'ip_pool_start',
        'ip_pool_end',
        'sort_order',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
