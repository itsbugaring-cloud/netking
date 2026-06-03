<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvKategori extends Model
{
    use HasFactory;

    protected $table = 'inv_kategori';

    protected $fillable = [
        'nama_kategori',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function masterBarangs()
    {
        return $this->hasMany(InvMasterBarang::class, 'kategori_id');
    }
}
