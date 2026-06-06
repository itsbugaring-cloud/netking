<?php

namespace App\Models\Ipam;

use Illuminate\Database\Eloquent\Model;

class IpamOlt extends Model
{
    protected $table = 'ipam_olts';

    protected $fillable = [
        'name',
        'ip_address',
    ];

    public function routers()
    {
        return $this->hasMany(IpamRouter::class, 'mapped_olt_id');
    }
}
