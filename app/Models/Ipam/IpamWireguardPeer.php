<?php

namespace App\Models\Ipam;

use Illuminate\Database\Eloquent\Model;

class IpamWireguardPeer extends Model
{
    protected $table = 'ipam_wireguard_peers';

    protected $fillable = [
        'router_id',
        'interface_name',
        'public_key',
        'allowed_address',
        'endpoint_address',
        'endpoint_port',
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
