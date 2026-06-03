<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvQty extends Model
{
    use HasFactory;

    protected $table = 'inv_qty';

    protected $fillable = [
        'master_barang_id',
        'lokasi_id',
        'jumlah',
        'harga_satuan',
        'catatan',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
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

    // ─── Accessors ───────────────────────────────────────────

    public function getNilaiTotalAttribute(): float
    {
        return (float) $this->jumlah * (float) $this->harga_satuan;
    }
}
