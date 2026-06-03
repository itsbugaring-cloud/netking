<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvLogTransaksi extends Model
{
    use HasFactory;

    protected $table = 'inv_log_transaksi';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'tipe',
        'referensi_tabel',
        'referensi_id',
        'lokasi_asal_id',
        'lokasi_tujuan_id',
        'kuantitas',
        'keterangan',
        'detail_lama',
        'detail_baru',
        'created_at',
    ];

    protected $casts = [
        'detail_lama' => 'array',
        'detail_baru' => 'array',
        'created_at'  => 'datetime',
        'kuantitas'   => 'decimal:2',
    ];

    const TIPE_LABELS = [
        'masuk_baru'   => 'Masuk Baru',
        'mutasi'       => 'Mutasi',
        'potong_kabel' => 'Potong Kabel',
        'pasang'       => 'Pasang',
        'retur'        => 'Retur',
        'barang_keluar'=> 'Barang Keluar',
        'penyesuaian'  => 'Penyesuaian',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lokasiAsal()
    {
        return $this->belongsTo(InvLokasi::class, 'lokasi_asal_id');
    }

    public function lokasiTujuan()
    {
        return $this->belongsTo(InvLokasi::class, 'lokasi_tujuan_id');
    }
}
