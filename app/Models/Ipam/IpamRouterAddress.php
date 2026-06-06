<?php

namespace App\Models\Ipam;

use Illuminate\Database\Eloquent\Model;

class IpamRouterAddress extends Model
{
    protected $table = 'ipam_router_addresses';

    protected $fillable = [
        'router_id',
        'address',
        'network',
        'interface',
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
