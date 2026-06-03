<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class InvLokasi extends Model
{
    use HasFactory;

    protected $table = 'inv_lokasi';

    protected $fillable = [
        'nama_lokasi',
        'alamat',
        'jenis',
        'pic_nama',
        'pic_user_id',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function picUser()
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function units()
    {
        return $this->hasMany(InvUnit::class, 'lokasi_id');
    }

    public function kabels()
    {
        return $this->hasMany(InvKabel::class, 'lokasi_id');
    }

    public function qtys()
    {
        return $this->hasMany(InvQty::class, 'lokasi_id');
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeGudang(Builder $query): Builder
    {
        return $query->where('jenis', 'gudang_utama');
    }

    public function scopePop(Builder $query): Builder
    {
        return $query->where('jenis', 'pop_distribusi');
    }
}
