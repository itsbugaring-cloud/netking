<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvPurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'inv_purchase_orders';

    protected $fillable = [
        'nomor_po',
        'keterangan',
        'status',
        'created_by',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function items()
    {
        return $this->hasMany(InvPurchaseOrderItem::class, 'po_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Static Helpers ──────────────────────────────────────

    public static function generateNomor(): string
    {
        $sequence = static::whereDate('created_at', today())->count() + 1;

        return 'PO-' . date('Ymd') . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
