<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class InvUnit extends Model
{
    use HasFactory;

    protected $table = 'inv_units';

    protected $fillable = [
        'master_barang_id',
        'serial_number',
        'mac_address',
        'status',
        'nilai_aset',
        'lokasi_id',
        'penanggung_jawab',
        'penanggung_jawab_user_id',
        'catatan',
    ];

    protected $casts = [
        'nilai_aset' => 'decimal:2',
    ];

    const STATUS_LABELS = [
        'gudang'         => 'Gudang',
        'terpasang'      => 'Terpasang',
        'dibawa_teknisi' => 'Dibawa Teknisi',
        'rusak'          => 'Rusak',
        'rma'            => 'RMA',
        'terjual'        => 'Terjual',
        'hilang'         => 'Hilang',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function masterBarang()
    {
        return $this->belongsTo(InvMasterBarang::class, 'master_barang_id');
    }

    public function lokasi()
    {
        return $this->belongsTo(InvLokasi::class, 'lokasi_id');
    }

    public function penanggungJawabUser()
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_user_id');
    }

    public function photos()
    {
        return $this->hasMany(InvUnitPhoto::class, 'unit_id');
    }

    public function logTransaksis()
    {
        return $this->hasMany(InvLogTransaksi::class, 'referensi_id')
                    ->where('referensi_tabel', 'inv_units');
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeGudang(Builder $query): Builder
    {
        return $query->where('status', 'gudang');
    }

    public function scopeTerpasang(Builder $query): Builder
    {
        return $query->where('status', 'terpasang');
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
