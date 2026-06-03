<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class InvUnitPhoto extends Model
{
    use HasFactory;

    protected $table = 'inv_unit_photos';

    public $timestamps = false;

    protected $fillable = [
        'unit_id',
        'path',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function unit()
    {
        return $this->belongsTo(InvUnit::class, 'unit_id');
    }

    // ─── Accessors ───────────────────────────────────────────

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
