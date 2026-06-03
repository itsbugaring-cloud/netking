<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number',
        'customer_id',
        'assigned_to',
        'subject',
        'description',
        'priority',
        'status',
        'category',
        'contact_name',
        'contact_phone',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // Priority colors
    public static array $priorityColors = [
        'low'      => 'success',
        'medium'   => 'warning',
        'high'     => 'danger',
        'critical' => 'dark',
    ];

    // Status colors
    public static array $statusColors = [
        'open'        => 'primary',
        'in_progress' => 'warning',
        'resolved'    => 'success',
        'closed'      => 'secondary',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    public function publicReplies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->where('is_internal', false);
    }

    // Generate unique ticket number
    public static function generateNumber(): string
    {
        $prefix = 'TK-' . date('Ym') . '-';
        $last = static::where('ticket_number', 'like', $prefix . '%')
            ->orderByDesc('id')->first();
        $next = $last ? (intval(substr($last->ticket_number, -4)) + 1) : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
