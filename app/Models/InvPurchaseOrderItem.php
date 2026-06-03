<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvPurchaseOrderItem extends Model
{
    use HasFactory;

    protected $table = 'inv_purchase_order_items';

    protected $fillable = [
        'po_id',
        'master_barang_id',
        'jumlah_pesan',
        'jumlah_diterima',
        'harga_satuan',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function purchaseOrder()
    {
        return $this->belongsTo(InvPurchaseOrder::class, 'po_id');
    }

    public function masterBarang()
    {
        return $this->belongsTo(InvMasterBarang::class, 'master_barang_id');
    }

    // ─── Accessors ───────────────────────────────────────────

    public function getSisaAttribute(): int
    {
        return (int) $this->jumlah_pesan - (int) $this->jumlah_diterima;
    }
}
