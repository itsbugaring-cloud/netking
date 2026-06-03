<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvMasterBarang extends Model
{
    use HasFactory;

    protected $table = 'inv_master_barang';

    protected $fillable = [
        'kategori_id',
        'merek',
        'tipe',
        'jenis_penghitungan',
        'deskripsi',
        'harga_default',
    ];

    protected $casts = [
        'harga_default' => 'decimal:2',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function kategori()
    {
        return $this->belongsTo(InvKategori::class, 'kategori_id');
    }

    public function units()
    {
        return $this->hasMany(InvUnit::class, 'master_barang_id');
    }

    public function kabels()
    {
        return $this->hasMany(InvKabel::class, 'master_barang_id');
    }

    public function qtys()
    {
        return $this->hasMany(InvQty::class, 'master_barang_id');
    }

    // ─── Accessors ───────────────────────────────────────────

    public function getNamaLengkapAttribute(): string
    {
        return "{$this->merek} {$this->tipe}";
    }
}
