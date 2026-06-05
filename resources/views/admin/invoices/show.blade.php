@extends('layouts.app')
@section('title', 'Invoice #' . $invoice->invoice_number)

@section('styles')
<style>
    .invoice-show-page {
        --invoice-accent: var(--blue);
        --invoice-accent-soft: color-mix(in srgb, var(--invoice-accent) 12%, var(--surface));
        --invoice-success-soft: color-mix(in srgb, var(--nk-success) 12%, var(--surface));
        --invoice-warning-soft: color-mix(in srgb, var(--nk-warning) 12%, var(--surface));
        --invoice-danger-soft: color-mix(in srgb, var(--nk-danger) 12%, var(--surface));
    }

    .invoice-show-page .table > :not(caption) > * > * {
        background: transparent;
    }

    .invoice-show-page code {
        background: var(--surface-2);
        border: 1px solid var(--border);
        color: var(--blue);
    }

    .invoice-show-page [style*="color:#1e293b"],
    .invoice-show-page [style*="color: #1e293b"] {
        color: var(--txt) !important;
    }

    .invoice-show-page [style*="color:#64748b"],
    .invoice-show-page [style*="color: #64748b"] {
        color: var(--txt-3) !important;
    }

    .invoice-show-page [style*="background:#f5f5f5"],
    .invoice-show-page [style*="background: #f5f5f5"] {
        background: var(--surface-2) !important;
        color: var(--txt-3) !important;
    }

    .invoice-show-page [style*="background:#e8faf0"],
    .invoice-show-page [style*="background: #e8faf0"] {
        background: color-mix(in srgb, var(--nk-success) 12%, var(--surface)) !important;
        color: color-mix(in srgb, var(--nk-success) 72%, var(--txt)) !important;
    }

    .invoice-show-page [style*="background:#fde7e7"],
    .invoice-show-page [style*="background: #fde7e7"] {
        background: color-mix(in srgb, var(--nk-danger) 12%, var(--surface)) !important;
        color: color-mix(in srgb, var(--nk-danger) 72%, var(--txt)) !important;
    }

    .invoice-show-page [style*="background:#fef4e4"],
    .invoice-show-page [style*="background: #fef4e4"] {
        background: color-mix(in srgb, var(--nk-warning) 12%, var(--surface)) !important;
        color: color-mix(in srgb, var(--nk-warning) 78%, var(--txt)) !important;
    }

    .invoice-show-page [style*="border-bottom:1px solid #f0eff5"],
    .invoice-show-page [style*="border-top:1px solid #f0eff5"] {
        border-color: var(--border) !important;
    }

    .invoice-show-page [style*="background:#f8fbff"],
    .invoice-show-page [style*="background:linear-gradient(180deg,#f8fbff,#eff6ff)"],
    .invoice-show-page [style*="background: linear-gradient(180deg,#f8fbff,#eff6ff)"] {
        background: color-mix(in srgb, var(--nk-info) 8%, var(--surface)) !important;
        border-color: color-mix(in srgb, var(--nk-info) 22%, var(--border)) !important;
    }

    .invoice-show-page [style*="background:#0066b3"] {
        background: color-mix(in srgb, var(--nk-info) 76%, #003b73) !important;
    }

    .invoice-show-page [style*="background:#f26522"] {
        background: color-mix(in srgb, var(--nk-warning) 72%, #9a3412) !important;
    }

    .invoice-show-page [style*="background:#f5f5f9"],
    .invoice-show-page [style*="background: #f5f5f9"] {
        background: var(--surface-2) !important;
        border-color: var(--border) !important;
        color: var(--blue) !important;
    }

    .invoice-show-page [style*="color:#2563eb"],
    .invoice-show-page [style*="color: #2563eb"] {
        color: var(--blue) !important;
    }

    .invoice-show-page [style*="color:#1aae6f"],
    .invoice-show-page [style*="color: #1aae6f"] {
        color: color-mix(in srgb, var(--nk-success) 78%, var(--txt)) !important;
    }

    .invoice-show-page [style*="color:#e74c3c"],
    .invoice-show-page [style*="color: #e74c3c"] {
        color: color-mix(in srgb, var(--nk-danger) 76%, var(--txt)) !important;
    }

    .invoice-show-page [style*="color:#f39c12"],
    .invoice-show-page [style*="color: #f39c12"] {
        color: color-mix(in srgb, var(--nk-warning) 82%, var(--txt)) !important;
    }

    .invoice-show-page [style*="color:#fff"],
    .invoice-show-page [style*="color: #fff"] {
        color: #fff !important;
    }

    .invoice-show-page [style*="background:rgba(37,99,235,0.06)"] {
        background: color-mix(in srgb, var(--nk-info) 10%, var(--surface)) !important;
        border: 1px solid color-mix(in srgb, var(--nk-info) 18%, var(--border));
    }

    .invoice-show-page [style*="border-top:1px solid rgba(37,99,235,0.1)"] {
        border-top-color: color-mix(in srgb, var(--nk-info) 18%, var(--border)) !important;
    }

    .invoice-show-page .ms-panel .ms-panel-head[style*="background:transparent"] {
        background: transparent !important;
    }

    .invoice-show-page .ms-panel .ms-panel-title i,
    .invoice-show-page .bx-credit-card {
        color: var(--blue);
    }
</style>
@endsection

@section('content')
<div class="ms-page invoice-show-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-receipt'></i> Penagihan</div>
            <h1 class="ms-page-title">Invoice {{ $invoice->invoice_number }}</h1>
        </div>
        <div class="ms-page-actions">
            <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="ms-btn-secondary" target="_blank">
                <i class='bx bx-download'></i> PDF
            </a>
            <a href="{{ route('admin.invoices.index') }}" class="ms-btn-secondary">
                <i class='bx bx-arrow-back'></i> Kembali
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="ms-panel">
                <div class="ms-panel-body">
                    <div class="d-flex justify-content-between align-items-start mb-4 pb-4" style="border-bottom:1px solid #f0eff5;">
                        <div>
                            <div style="width:42px;height:42px;border-radius:10px;background:#2563eb;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.25rem;margin-bottom:0.75rem;">
                                <i class='bx bx-wifi'></i>
                            </div>
                            <h3 style="font-size:1.25rem;font-weight:700;color:#1e293b;margin:0;">NETKING</h3>
                            <div style="font-size:0.8125rem;color:#64748b;">Penyedia Layanan Internet</div>
                        </div>
                        <div class="text-end">
                            @if($invoice->status === 'paid')
                            <span style="background:#e8faf0;color:#1aae6f;font-size:1rem;font-weight:700;padding:0.5rem 1.25rem;border-radius:8px;letter-spacing:1px;">LUNAS</span>
                            @elseif($invoice->status === 'cancelled')
                            <span style="background:#f5f5f5;color:#64748b;font-size:1rem;font-weight:700;padding:0.5rem 1.25rem;border-radius:8px;letter-spacing:1px;">DIBATALKAN</span>
                            @elseif($invoice->due_date?->isPast())
                            <span style="background:#fde7e7;color:#e74c3c;font-size:1rem;font-weight:700;padding:0.5rem 1.25rem;border-radius:8px;letter-spacing:1px;">JATUH TEMPO</span>
                            @else
                            <span style="background:#fef4e4;color:#f39c12;font-size:1rem;font-weight:700;padding:0.5rem 1.25rem;border-radius:8px;letter-spacing:1px;">BELUM LUNAS</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div style="font-size:0.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:0.5rem;">Tagihan Kepada</div>
                            <div style="font-weight:600;color:#1e293b;">{{ $invoice->customer->name ?? 'N/A' }}</div>
                            <div style="font-size:0.8125rem;color:#64748b;">{{ $invoice->customer->phone ?? '' }}</div>
                            <div style="font-size:0.8125rem;color:#64748b;">{{ $invoice->customer->address ?? '' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div style="font-size:0.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:0.5rem;">Detail Tagihan</div>
                            <div style="font-size:0.875rem;color:#1e293b;">
                                <div><strong>Nomor:</strong> {{ $invoice->invoice_number }}</div>
                                <div><strong>Diterbitkan:</strong> {{ $invoice->created_at->format('d M Y') }}</div>
                                <div><strong>Jatuh Tempo:</strong> {{ $invoice->due_date?->format('d M Y') }}</div>
                                @if($invoice->paid_at)
                                <div style="color:#1aae6f;"><strong>Dibayar:</strong> {{ $invoice->paid_at->format('d M Y, H:i') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <table class="table table-flat mb-0">
                        <thead>
                            <tr>
                                <th>Keterangan</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    @php
                                        $periodLabel = ($invoice->period_month && $invoice->period_year)
                                            ? \Carbon\Carbon::createFromDate($invoice->period_year, $invoice->period_month, 1)->translatedFormat('F Y')
                                            : optional($invoice->due_date)->translatedFormat('F Y');
                                    @endphp
                                    <div style="font-weight:500;color:#1e293b;">Layanan Internet — {{ $invoice->customer->package->name ?? 'Paket Standar' }}</div>
                                    <div style="font-size:0.8125rem;color:#64748b;">Periode: {{ $periodLabel ?: '-' }}</div>
                                </td>
                                <td class="text-end" style="font-weight:600;color:#1e293b;">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                            </tr>
                            @if($invoice->is_prorated)
                            <tr>
                                <td style="font-size:.85rem;color:#64748b;">
                                    Prorata bulan pertama ({{ $invoice->billed_days ?? 0 }} / {{ $invoice->period_days ?? 0 }} hari)
                                </td>
                                <td class="text-end" style="font-size:.85rem;color:#64748b;">
                                    Dasar: Rp {{ number_format((float) ($invoice->base_amount ?? $invoice->amount), 0, ',', '.') }}
                                </td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td style="font-weight:700;font-size:1rem;color:#1e293b;">Total Tagihan</td>
                                <td class="text-end" style="font-weight:700;font-size:1.25rem;color:#2563eb;">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    @if($invoice->status !== 'paid')
                    <div class="mt-3 pt-3" style="border-top:1px solid #f0eff5;">
                        <form action="{{ route('admin.invoices.markAsPaid', $invoice) }}" method="POST">
                            @csrf
                            <input type="hidden" name="payment_method" value="{{ $invoice->payment_method ?: 'manual_transfer' }}">
                            <button type="submit" class="ms-btn">
                                <i class='bx bx-check-circle me-1'></i> Tandai Lunas
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if($invoice->payment_proof_path)
            <div class="ms-panel mb-3">
                <div class="ms-panel-head">
                    <h5 class="ms-panel-title"><i class='bx bx-receipt me-2'></i>Bukti Pembayaran</h5>
                </div>
                <div class="ms-panel-body">
                    <div class="mb-3 pb-3" style="border-bottom:1px solid #f0eff5;">
                        <div style="font-size:0.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Status Tinjauan</div>
                        <div class="mt-1">
                            @php
                                $reviewColor = match($invoice->payment_review_status) {
                                    'submitted' => '#f39c12',
                                    'reviewed' => '#1aae6f',
                                    'rejected' => '#e74c3c',
                                    default => '#64748b',
                                };
                                $reviewBg = match($invoice->payment_review_status) {
                                    'submitted' => '#fef4e4',
                                    'reviewed' => '#e8faf0',
                                    'rejected' => '#fde7e7',
                                    default => '#f5f5f5',
                                };
                            @endphp
                            <span style="display:inline-flex;align-items:center;gap:6px;background:{{ $reviewBg }};color:{{ $reviewColor }};padding:6px 10px;border-radius:999px;font-size:0.75rem;font-weight:700;text-transform:uppercase;">
                                {{ $invoice->payment_review_status }}
                            </span>
                        </div>
                    </div>
                    <div class="mb-3 pb-3" style="border-bottom:1px solid #f0eff5;">
                        <div style="font-size:0.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Dikirimkan</div>
                        <div class="mt-1" style="font-size:0.875rem;color:#1e293b;">
                            {{ optional($invoice->payment_proof_submitted_at)->format('d M Y, H:i') ?? '—' }}
                        </div>
                    </div>
                    @if($invoice->payment_proof_notes)
                    <div class="mb-3 pb-3" style="border-bottom:1px solid #f0eff5;">
                        <div style="font-size:0.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Catatan Pelanggan</div>
                        <div class="mt-1" style="font-size:0.875rem;color:#1e293b;">{{ $invoice->payment_proof_notes }}</div>
                    </div>
                    @endif
                    <div>
                        <a href="{{ $invoice->payment_proof_url }}" class="ms-btn-secondary" target="_blank" rel="noopener">
                            <i class='bx bx-show'></i> Lihat Bukti
                        </a>
                    </div>
                </div>
            </div>
            @endif

            @if($invoice->status !== 'paid')
            <div class="ms-panel" style="border-color:#bfdbfe;background:linear-gradient(180deg,#f8fbff,#eff6ff);">
                <div class="ms-panel-head" style="background:transparent;border-bottom-color:rgba(37,99,235,0.15);">
                    <h5 class="ms-panel-title" style="color:#2563eb;"><i class='bx bx-credit-card me-2'></i>Transfer Pembayaran</h5>
                </div>
                <div class="ms-panel-body">
                    @if(!empty($paymentSettings['qris']))
                    <div style="margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid rgba(37,99,235,0.1);">
                        <div style="font-size:0.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.5rem;">QRIS</div>
                        <div style="font-size:0.95rem;font-weight:700;color:#1e293b;margin-bottom:0.75rem;">{{ $paymentSettings['qris']['label'] ?? 'QRIS NETKING' }}</div>
                        <a href="{{ $paymentSettings['qris']['image_url'] }}" target="_blank" rel="noopener" style="display:block;">
                            <img src="{{ $paymentSettings['qris']['image_url'] }}" alt="QRIS NETKING" style="width:100%;border-radius:12px;border:1px solid rgba(37,99,235,0.14);">
                        </a>
                        @if(!empty($paymentSettings['qris']['notes']))
                        <div style="margin-top:0.625rem;font-size:0.75rem;color:#2563eb;font-weight:500;">
                            <i class='bx bx-info-circle me-1'></i>{{ $paymentSettings['qris']['notes'] }}
                        </div>
                        @endif
                    </div>
                    @endif
                    @foreach($paymentSettings['accounts'] ?? [] as $account)
                    <div @if(!$loop->first) style="border-top:1px solid rgba(37,99,235,0.1);padding-top:0.75rem;margin-top:0.25rem;" @endif>
                        <div style="font-size:0.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.5rem;">Rekening {{ $account['bank_name'] }}</div>
                        <div style="display:flex;align-items:center;gap:0.75rem;">
                            <div style="width:36px;height:36px;border-radius:8px;background:{{ strtoupper($account['bank_name']) === 'BRI' ? '#0066b3' : '#f26522' }};display:flex;align-items:center;justify-content:center;">
                                <span style="color:#fff;font-weight:700;font-size:0.625rem;">{{ strtoupper($account['bank_name']) }}</span>
                            </div>
                            <div>
                                <div style="font-size:1rem;font-weight:700;color:#1e293b;letter-spacing:1px;">{{ $account['account_number'] }}</div>
                                <div style="font-size:0.75rem;color:#64748b;">a/n {{ $account['account_holder'] }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <div style="margin-top:1rem;padding:0.625rem;background:rgba(37,99,235,0.06);border-radius:8px;">
                        <div style="font-size:0.75rem;color:#2563eb;font-weight:500;">
                            <i class='bx bx-info-circle me-1'></i>{{ $paymentSettings['notes'] ?? 'Transfer atau bayar via QRIS sesuai nominal invoice, lalu upload bukti pembayaran agar admin bisa memverifikasi pembayaran Anda.' }} <strong>Rp {{ number_format($invoice->amount, 0, ',', '.') }}</strong>.
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="ms-panel">
                <div class="ms-panel-head">
                    <h5 class="ms-panel-title">Info Pembayaran</h5>
                </div>
                <div class="ms-panel-body">
                    <div class="mb-3 pb-3" style="border-bottom:1px solid #f0eff5;">
                        <div style="font-size:0.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Metode</div>
                        <div class="mt-1" style="font-size:0.875rem;color:#1e293b;">{{ $invoice->payment_method ?? '—' }}</div>
                    </div>
                    <div class="mb-3 pb-3" style="border-bottom:1px solid #f0eff5;">
                        <div style="font-size:0.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Referensi</div>
                        <div class="mt-1"><code style="background:#f5f5f9;padding:2px 8px;border-radius:4px;font-size:0.8125rem;color:#2563eb;">{{ $invoice->payment_reference ?? '—' }}</code></div>
                    </div>
                    <div class="mb-3 pb-3" style="border-bottom:1px solid #f0eff5;">
                        <div style="font-size:0.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Pelanggan</div>
                        <div class="mt-1">
                            <a href="{{ route('admin.customers.show', $invoice->customer) }}" style="color:#2563eb;text-decoration:none;font-weight:500;">{{ $invoice->customer->name ?? 'N/A' }}</a>
                        </div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Wilayah</div>
                        <div class="mt-1" style="font-size:0.875rem;color:#1e293b;">{{ $invoice->customer->area->name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
