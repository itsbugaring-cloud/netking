<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'type',
        'brand',
        'model',
        'serial_number',
        'status',
        'assigned_at',
        'returned_at',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'date',
        'returned_at' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'ont'      => 'ONT',
            'router'   => 'Router',
            'cable'    => 'Kabel FO',
            'adapter'  => 'Adapter',
            'splitter' => 'Splitter',
            default    => 'Lainnya',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'   => '<span class="badge bg-success-subtle text-success">Active</span>',
            'returned' => '<span class="badge bg-secondary-subtle text-secondary">Returned</span>',
            'damaged'  => '<span class="badge bg-danger-subtle text-danger">Damaged</span>',
            'lost'     => '<span class="badge bg-warning-subtle text-warning">Lost</span>',
            default    => '<span class="badge bg-light text-dark">Unknown</span>',
        };
    }
}
