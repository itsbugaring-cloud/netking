<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedamanCalculation extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'name',
        'inputs',
        'results',
        'notes',
    ];

    protected $casts = [
        'inputs'  => 'array',
        'results' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Status label based on link margin
     */
    public function getStatusLabelAttribute(): string
    {
        $margin = $this->results['margin'] ?? null;
        if ($margin === null) return 'unknown';
        if ($margin >= 6)  return 'ok';
        if ($margin >= 0)  return 'warning';
        return 'fail';
    }
}
