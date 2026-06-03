<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvKabel extends Model
{
    use HasFactory;

    protected $table = 'inv_kabel';

    protected $fillable = [
        'master_barang_id',
        'id_haspel',
        'panjang_awal',
        'sisa_panjang',
        'nilai_per_meter',
        'lokasi_id',
        'penanggung_jawab',
        'penanggung_jawab_user_id',
        'catatan',
    ];

    protected $casts = [
        'panjang_awal'    => 'decimal:2',
        'sisa_panjang'    => 'decimal:2',
        'nilai_per_meter' => 'decimal:2',
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

    // ─── Accessors ───────────────────────────────────────────

    public function getNilaiTotalAttribute(): float
    {
        return (float) $this->sisa_panjang * (float) $this->nilai_per_meter;
    }

    public function getPorsiPakaiAttribute(): float
    {
        $awal = (float) $this->panjang_awal;

        if ($awal <= 0) {
            return 0.0;
        }

        $terpakai = $awal - (float) $this->sisa_panjang;

        return round(($terpakai / $awal) * 100, 2);
    }
}
