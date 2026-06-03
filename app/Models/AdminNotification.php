<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'icon', 'color', 'link', 'read',
    ];

    protected $casts = [
        'read' => 'boolean',
    ];

    public static function notify(string $type, string $title, string $message, string $icon = 'bx-bell', string $color = 'blue', ?string $link = null, ?int $userId = null): self
    {
        // Keep only last 200
        if (static::count() > 200) {
            static::orderBy('id')->limit(static::count() - 200)->delete();
        }

        return static::create([
            'user_id' => $userId,
            'type' => $type, 'title' => $title, 'message' => $message,
            'icon' => $icon, 'color' => $color, 'link' => $link,
        ]);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('user_id')->orWhere('user_id', $userId);
        });
    }
}

