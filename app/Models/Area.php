<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'router_ip',
        'vlan_pppoe',
        'vlan_mgmt',
        'latitude',
        'longitude',
        'router_user',
        'router_pass',
        'router_identity',
        'ip_pool_start',
        'ip_pool_end',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected $hidden = [
        'router_pass',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function ipPools(): HasMany
    {
        return $this->hasMany(AreaIpPool::class)->orderBy('sort_order');
    }

    public function odps(): HasMany
    {
        return $this->hasMany(\App\Models\Odp::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(\App\Models\Package::class);
    }
}
