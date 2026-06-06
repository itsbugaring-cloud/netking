<?php

namespace App\Models\Ipam;

use Illuminate\Database\Eloquent\Model;

class IpamRouterRoute extends Model
{
    protected $table = 'ipam_router_routes';

    protected $fillable = [
        'router_id',
        'dst_address',
        'gateway',
        'distance',
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
