<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'amount',
        'base_amount',
        'billed_days',
        'period_days',
        'period_month',
        'period_year',
        'is_prorated',
        'status',
        'payment_method',
        'payment_url',
        'payment_reference',
        'payment_proof_path',
        'payment_proof_original_name',
        'payment_proof_notes',
        'payment_proof_submitted_at',
        'payment_review_status',
        'payment_reviewed_at',
        'payment_reject_reason',
        'due_date',
        'paid_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'base_amount' => 'decimal:2',
        'is_prorated' => 'boolean',
        'payment_proof_submitted_at' => 'datetime',
        'payment_reviewed_at' => 'datetime',
    ];

    protected $appends = [
        'payment_proof_url',
    ];

    /**
     * Generate unique invoice number
     * Format: INV/NK/YYYYMM/001
     */
    public static function generateInvoiceNumber(): string
    {
        return \Illuminate\Support\Facades\DB::transaction(function () {
            $prefix = 'INV/NK/' . now()->format('Ym') . '/';

            $lastInvoice = self::where('invoice_number', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('invoice_number', 'desc')
                ->first();

            if ($lastInvoice) {
                $lastNumber = (int) substr($lastInvoice->invoice_number, -3);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Customer relationship
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(string $paymentMethod, ?string $paymentReference = null): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($paymentMethod, $paymentReference) {
            $this->update([
                'status' => 'paid',
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference,
                'payment_review_status' => $this->payment_proof_path ? 'reviewed' : $this->payment_review_status,
                'payment_reviewed_at' => $this->payment_proof_path ? now() : $this->payment_reviewed_at,
                'paid_at' => now(),
            ]);

            // Auto-activate customer if they were suspended
            if ($this->customer->status === 'suspended') {
                $this->customer->loadMissing('area');

                $pppoeEnabled  = false;
                $pppoeError    = null;

                try {
                    $mikrotik = \App\Services\MikroTikService::forArea($this->customer->area);
                    if ($mikrotik->isConnected()) {
                        $mikrotik->toggleSecret($this->customer->pppoe_user, true);
                        $pppoeEnabled = true;
                    } else {
                        $pppoeError = 'MikroTik tidak dapat terhubung';
                    }
                } catch (\Exception $e) {
                    $pppoeError = $e->getMessage();
                    \Illuminate\Support\Facades\Log::warning(
                        "MikroTik re-enable failed for {$this->customer->pppoe_user}: {$pppoeError}"
                    );
                }

                $this->customer->update([
                    'status'                => 'active',
                    'error_message'         => $pppoeEnabled ? null : "PPPoE belum diaktifkan: {$pppoeError}",
                    'pppoe_pending_enable'  => !$pppoeEnabled,
                ]);

                ActivityLog::log(
                    'activate',
                    "Customer {$this->customer->name} diaktifkan setelah bayar invoice {$this->invoice_number}"
                        . ($pppoeEnabled ? '' : " — PPPoE pending (MikroTik: {$pppoeError})"),
                    $this->customer
                );
            }
        });
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'unpaid' && $this->due_date->isPast();
    }

    /**
     * Scope: Unpaid invoices
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    /**
     * Scope: Overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->unpaid()->where('due_date', '<', now());
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        if (!$this->payment_proof_path) {
            return null;
        }

        return Storage::disk('public')->url($this->payment_proof_path);
    }
}
