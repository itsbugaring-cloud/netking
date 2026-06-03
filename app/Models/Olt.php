<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Olt extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_id',
        'name',
        'brand',
        'model',
        'ip_address',
        'snmp_community',
        'snmp_version',
        'snmp_username',
        'snmp_auth_pass',
        'ssh_user',
        'ssh_pass',
        'ssh_port',
        'ssh_enable_pass',
        'telnet_user',
        'telnet_pass',
        'telnet_port',
        'api_url',
        'api_token',
        'preferred_protocol',
        'status',
        'notes',
        'sync_status',
        'sync_message',
        'synced_at',
    ];

    protected $hidden = ['ssh_pass', 'snmp_auth_pass', 'api_token', 'telnet_pass'];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function onts()
    {
        return $this->hasMany(Ont::class);
    }

    public function getOnlineCountAttribute(): int
    {
        return $this->onts()->where('status', 'online')->count();
    }

    public function getOfflineCountAttribute(): int
    {
        return $this->onts()->where('status', 'offline')->count();
    }
}
