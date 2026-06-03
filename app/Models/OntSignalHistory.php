<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OntSignalHistory extends Model
{
    protected $fillable = [
        'ont_id', 'customer_id',
        'rx_power', 'tx_power',
        'quality', 'source', 'ont_status',
        'recorded_at',
    ];

    protected $casts = [
        'rx_power'    => 'float',
        'tx_power'    => 'float',
        'recorded_at' => 'datetime',
    ];

    public function ont(): BelongsTo
    {
        return $this->belongsTo(Ont::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** Auto-classify quality from rx_power */
    public static function qualityFromRx(?float $rx): string
    {
        if ($rx === null) return 'unknown';
        if ($rx > -10)    return 'too_strong';
        if ($rx >= -25)   return 'good';
        if ($rx >= -27)   return 'fair';
        if ($rx >= -30)   return 'weak';
        return 'critical';
    }
}
