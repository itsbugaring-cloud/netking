<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    protected $fillable = [
        'customer_id',
        'periode_bulan',
        'periode_tahun',
        'jumlah',
        'metode',
        'rekening_tujuan',
        'bukti_path',
        'bukti_original_name',
        'status',
        'approved_by_user_id',
        'approved_at',
        'reject_reason',
        'catatan',
        'created_by_user_id',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'approved_at' => 'datetime',
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
    ];

    protected $appends = ['bukti_url'];

    // ─── Relationships ───────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // ─── Accessors ───────────────────────────────────────────

    public function getBuktiUrlAttribute(): ?string
    {
        return $this->bukti_path
            ? Storage::disk('public')->url($this->bukti_path)
            : null;
    }

    // ─── Actions ─────────────────────────────────────────────

    public function approve(int $userId, ?int $periodeBulan = null, ?int $periodeTahun = null): void
    {
        $data = [
            'status' => 'approved',
            'approved_by_user_id' => $userId,
            'approved_at' => now(),
        ];

        if ($periodeBulan !== null) {
            $data['periode_bulan'] = $periodeBulan;
        }
        if ($periodeTahun !== null) {
            $data['periode_tahun'] = $periodeTahun;
        }

        $this->update($data);
    }

    public function reject(string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'reject_reason' => $reason,
        ]);
    }
}
