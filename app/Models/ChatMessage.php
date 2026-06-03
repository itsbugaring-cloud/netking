<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ChatMessage extends Model
{
    protected $fillable = [
        'customer_id',
        'partner_id',
        'sender_type',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeConversation(Builder $query, int $customerId, int $partnerId): Builder
    {
        return $query->where('customer_id', $customerId)
                     ->where('partner_id', $partnerId);
    }
}
