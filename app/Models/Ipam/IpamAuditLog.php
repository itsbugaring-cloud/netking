<?php

namespace App\Models\Ipam;

use Illuminate\Database\Eloquent\Model;

class IpamAuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'ipam_audit_logs';

    protected $fillable = [
        'actor',
        'action',
        'target_type',
        'target_id',
        'detail',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
