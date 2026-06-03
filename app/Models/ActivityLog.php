<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'subject_type', 'subject_id',
        'description', 'properties', 'ip_address',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(
        string $action,
        string $description,
        ?object $subject = null,
        ?array $properties = null
    ): self {
        $authenticatedUser = auth()->user();

        return static::create([
            // activity_logs.user_id references users.id, so customer-auth flows
            // must not write their guard ID here.
            'user_id'      => $authenticatedUser instanceof User ? $authenticatedUser->id : null,
            'action'       => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->id ?? null,
            'description'  => $description,
            'properties'   => $properties,
            'ip_address'   => request()?->ip(),
        ]);
    }

    /**
     * Legacy alias — called throughout codebase with old signature:
     * logActivity(action, description, userId, subjectClass, subjectId, properties)
     */
    public static function logActivity(
        string $action,
        string $description,
        $userId = null,
        $subjectClass = null,
        $subjectId = null,
        ?array $properties = null
    ): self {
        return static::create([
            'user_id'      => $userId ?? auth()->id(),
            'action'       => $action,
            'subject_type' => $subjectClass,
            'subject_id'   => $subjectId,
            'description'  => $description,
            'properties'   => $properties,
            'ip_address'   => request()?->ip(),
        ]);
    }
}
