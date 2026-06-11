<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OntAssignmentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'previous_customer_id',
        'ont_id',
        'inv_unit_id',
        'serial_number',
        'action',
        'source',
        'created_by_user_id',
        'notes',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function previousCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'previous_customer_id');
    }

    public function ont(): BelongsTo
    {
        return $this->belongsTo(Ont::class);
    }

    public function invUnit(): BelongsTo
    {
        return $this->belongsTo(InvUnit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public static function record(array $data): self
    {
        return static::create([
            'customer_id' => $data['customer_id'] ?? null,
            'previous_customer_id' => $data['previous_customer_id'] ?? null,
            'ont_id' => $data['ont_id'] ?? null,
            'inv_unit_id' => $data['inv_unit_id'] ?? null,
            'serial_number' => (string) ($data['serial_number'] ?? ''),
            'action' => (string) ($data['action'] ?? 'linked'),
            'source' => (string) ($data['source'] ?? 'system'),
            'created_by_user_id' => $data['created_by_user_id'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }
}
