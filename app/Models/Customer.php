<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Builder;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'partner_id',
        'area_id',
        'odp_id',
        'odp_port',
        'package_id',
        'name',
        'username',
        'pppoe_user',
        'pppoe_pass',
        'portal_password',
        'remote_ip',
        'local_address',
        'ont_sn',
        'package_price',
        'billing_start_date',
        'phone',
        'address',
        'status',
        'error_message',
        'pppoe_pending_enable',
        'last_login_at',
    ];

    protected $hidden = [
        'pppoe_pass',
        'portal_password',
    ];

    protected $casts = [
        'package_price' => 'decimal:2',
        'billing_start_date' => 'date',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the password for authentication (customer portal).
     * Required by Authenticatable - uses portal_password instead of password.
     */
    public function getAuthPassword()
    {
        return $this->portal_password;
    }

    // ─── Relationships ───────────────────────────────────────

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function odp()
    {
        return $this->belongsTo(Odp::class);
    }

    public function ont()
    {
        return $this->hasOne(Ont::class, 'customer_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function devices()
    {
        return $this->hasMany(CustomerDevice::class);
    }

    public function scopeForAreaPppoe(Builder $query, int $areaId, string $pppoeUser): Builder
    {
        return $query
            ->where('area_id', $areaId)
            ->whereRaw('LOWER(TRIM(pppoe_user)) = ?', [mb_strtolower(trim($pppoeUser))]);
    }

    public function getPppoeLabelAttribute(): string
    {
        $areaName = $this->area?->name;
        return $areaName ? "{$areaName} / {$this->pppoe_user}" : (string) $this->pppoe_user;
    }
}
