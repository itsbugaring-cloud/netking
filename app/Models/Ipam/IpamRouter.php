<?php

namespace App\Models\Ipam;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IpamRouter extends Model
{
    protected $table = 'ipam_routers';

    protected $fillable = [
        'device_name',
        'wireguard_ip',
        'auth_username',
        'auth_password',
        'auth_source',
        'connection_status',
        'last_error',
        'last_scanned_at',
        'mapped_olt_id',
        'is_online',
        'last_ping_at',
    ];

    protected $hidden = [
        'auth_password',
    ];

    protected $casts = [
        'auth_password' => 'encrypted',
        'is_online' => 'boolean',
        'last_scanned_at' => 'datetime',
        'last_ping_at' => 'datetime',
    ];

    public function ipPools(): HasMany
    {
        return $this->hasMany(IpamIpPool::class, 'router_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(IpamRouterAddress::class, 'router_id');
    }

    public function routes(): HasMany
    {
        return $this->hasMany(IpamRouterRoute::class, 'router_id');
    }

    public function wireguardInterfaces(): HasMany
    {
        return $this->hasMany(IpamWireguardInterface::class, 'router_id');
    }

    public function wireguardPeers(): HasMany
    {
        return $this->hasMany(IpamWireguardPeer::class, 'router_id');
    }

    public function mappedOlt(): BelongsTo
    {
        return $this->belongsTo(IpamOlt::class, 'mapped_olt_id');
    }
}
