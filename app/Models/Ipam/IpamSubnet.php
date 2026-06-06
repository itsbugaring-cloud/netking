<?php

namespace App\Models\Ipam;

use Illuminate\Database\Eloquent\Model;

class IpamSubnet extends Model
{
    protected $table = 'ipam_subnets';

    protected $fillable = [
        'network_address',
        'prefix_length',
        'name',
        'description',
        'vlan_id',
        'location',
    ];
}
