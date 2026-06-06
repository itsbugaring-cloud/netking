<?php

namespace App\Models\Ipam;

use Illuminate\Database\Eloquent\Model;

class IpamWireguardInterface extends Model
{
    protected $table = 'ipam_wireguard_interfaces';

    protected $fillable = [
        'router_id',
        'name',
        'listen_port',
        'public_key',
        'disabled',
        'comment',
    ];

    protected $casts = [
        'disabled' => 'boolean',
    ];

    public function router()
    {
        return $this->belongsTo(IpamRouter::class, 'router_id');
    }
}
