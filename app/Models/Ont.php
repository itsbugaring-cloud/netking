<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ont extends Model
{
    use HasFactory;

    protected $fillable = [
        'olt_id',
        'area_id',
        'customer_id',
        'serial_number',
        'pon_port',
        'olt_port_index',
        'description',
        'status',
        'rx_power',
        'tx_power',
        'distance',
        'firmware_version',
        'equipment_id',
        'last_synced_at',
    ];

    protected $casts = [
        'rx_power'       => 'float',
        'tx_power'       => 'float',
        'last_synced_at' => 'datetime',
    ];

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Signal quality label based on Rx power (dBm)
     */
    public function getSignalQualityAttribute(): string
    {
        if ($this->rx_power === null) return 'unknown';
        if ($this->rx_power >= -20) return 'excellent';
        if ($this->rx_power >= -25) return 'good';
        if ($this->rx_power >= -27) return 'fair';
        return 'weak';
    }
}
