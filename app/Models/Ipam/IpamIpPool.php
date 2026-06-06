<?php

namespace App\Models\Ipam;

use Illuminate\Database\Eloquent\Model;

class IpamIpPool extends Model
{
    protected $table = 'ipam_ip_pools';

    protected $fillable = [
        'router_id',
        'pool_name',
        'ranges',
    ];

    public function router()
    {
        return $this->belongsTo(IpamRouter::class, 'router_id');
    }
}
