@extends('layouts.app')
@section('title', 'Invoice #' . $invoice->invoice_number)

@section('content')
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4>Invoice {{ $invoice->invoice_number }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}">Invoices</a></li>
                <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-sm" target="_blank" style="background:rgba(105,108,255,0.12); color:#2563eb; font-weight:600;">
            <i class='bx bx-download me-1'></i> PDF
        </a>
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-sm" style="background:#f5f5f9; color:#1e293b;">
            <i class='bx bx-arrow-back me-1'></i> Back
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <!-- Invoice Header -->
                <div class="d-flex justify-content-between align-items-start mb-4 pb-4" style="border-bottom:1px solid #f0eff5;">
                    <div>
                        <div style="width:42px; height:42px; border-radius:10px; background:#2563eb; display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.25rem; margin-bottom:0.75rem;">
                            <i class='bx bx-wifi'></i>
                        </div>
                        <h3 style="font-size:1.25rem; font-weight:700; color:#1e293b; margin:0;">NETKING</h3>
                        <div style="font-size:0.8125rem; color:#64748b;">Internet Service Provider</div>
                    </div>
                    <div class="text-end">
                        @if($invoice->status === 'paid')
                        <span style="background:#e8faf0; color:#1aae6f; font-size:1rem; font-weight:700; padding:0.5rem 1.25rem; border-radius:8px; letter-spacing:1px;">PAID</span>
                        @elseif($invoice->status === 'cancelled')
                        <span style="background:#f5f5f5; color:#64748b; font-size:1rem; font-weight:700; padding:0.5rem 1.25rem; border-radius:8px; letter-spacing:1px;">CANCELLED</span>
                        @elseif($invoice->due_date?->isPast())
                        <span style="background:#fde7e7; color:#e74c3c; font-size:1rem; font-weight:700; padding:0.5rem 1.25rem; border-radius:8px; letter-spacing:1px;">OVERDUE</span>
                        @else
                        <span style="background:#fef4e4; color:#f39c12; font-size:1rem; font-weight:700; padding:0.5rem 1.25rem; border-radius:8px; letter-spacing:1px;">UNPAID</span>
                        @endif
                    </div>
                </div>

                <!-- Bill To / Invoice Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div style="font-size:0.75rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:0.5rem;">Bill To</div>
                        <div style="font-weight:600; color:#1e293b;">{{ $invoice->customer->name ?? 'N/A' }}</div>
                        <div style="font-size:0.8125rem; color:#64748b;">{{ $invoice->customer->phone ?? '' }}</div>
                        <div style="font-size:0.8125rem; color:#64748b;">{{ $invoice->customer->address ?? '' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div style="font-size:0.75rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:0.5rem;">Invoice Details</div>
                        <div style="font-size:0.875rem; color:#1e293b;">
                            <div><strong>Number:</strong> {{ $invoice->invoice_number }}</div>
                            <div><strong>Issued:</strong> {{ $invoice->created_at->format('d M Y') }}</div>
                            <div><strong>Due:</strong> {{ $invoice->due_date?->format('d M Y') }}</div>
                            @if($invoice->paid_at)
                            <div style="color:#1aae6f;"><strong>Paid:</strong> {{ $invoice->paid_at->format('d M Y, H:i') }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div style="font-weight:500; color:#1e293b;">Internet Service — {{ $invoice->customer->package->name ?? 'Standard Package' }}</div>
                                <div style="font-size:0.8125rem; color:#64748b;">Period: {{ \Carbon\Carbon::parse($invoice->billing_month)->format('F Y') }}</div>
                            </td>
                            <td class="text-end" style="font-weight:600; color:#1e293b;">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="font-weight:700; font-size:1rem; color:#1e293b;">Total</td>
                            <td class="text-end" style="font-weight:700; font-size:1.25rem; color:#2563eb;">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>

                @if($invoice->status !== 'paid')
                <div class="mt-3 pt-3" style="border-top:1px solid #f0eff5;">
                    <form action="{{ route('admin.invoices.markAsPaid', $invoice) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-success">
                            <i class='bx bx-check-circle me-1'></i> Mark as Paid
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @if($invoice->status !== 'paid')
        <div class="card" style="border: 2px solid #2563eb; background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);">
            <div class="card-header" style="background:transparent; border-bottom:1px solid rgba(37,99,235,0.15);">
                <h5 class="card-title" style="color:#2563eb;"><i class='bx bx-credit-card me-2'></i>Transfer Pembayaran</h5>
            </div>
            <div class="card-body">
                <div style="font-size:0.75rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.5rem;">Rekening BRI</div>
                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.75rem;">
                    <div style="width:36px; height:36px; border-radius:8px; background:#0066b3; display:flex; align-items:center; justify-content:center;">
                        <span style="color:#fff; font-weight:700; font-size:0.625rem;">BRI</span>
                    </div>
                    <div>
                        <div style="font-size:1rem; font-weight:700; color:#1e293b; letter-spacing:1px;">159601000592564</div>
                        <div style="font-size:0.75rem; color:#64748b;">a/n Deni Firmansyah</div>
                    </div>
                </div>
                <div style="border-top:1px solid rgba(37,99,235,0.1); padding-top:0.75rem; margin-top:0.25rem;">
                    <div style="font-size:0.75rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.5rem;">Rekening BNI</div>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <div style="width:36px; height:36px; border-radius:8px; background:#f26522; display:flex; align-items:center; justify-content:center;">
                            <span style="color:#fff; font-weight:700; font-size:0.625rem;">BNI</span>
                        </div>
                        <div>
                            <div style="font-size:1rem; font-weight:700; color:#1e293b; letter-spacing:1px;">0320906963</div>
                            <div style="font-size:0.75rem; color:#64748b;">a/n Deni Firmansyah</div>
                        </div>
                    </div>
                </div>
                <div style="margin-top:1rem; padding:0.625rem; background:rgba(37,99,235,0.06); border-radius:8px;">
                    <div style="font-size:0.75rem; color:#2563eb; font-weight:500;">
                        <i class='bx bx-info-circle me-1'></i>Transfer sebesar <strong>Rp {{ number_format($invoice->amount, 0, ',', '.') }}</strong> dan konfirmasi ke admin.
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Payment Info</h5>
            </div>
            <div class="card-body">
                <div class="mb-3 pb-3" style="border-bottom:1px solid #f0eff5;">
                    <div style="font-size:0.75rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Method</div>
                    <div class="mt-1" style="font-size:0.875rem; color:#1e293b;">{{ $invoice->payment_method ?? '—' }}</div>
                </div>
                <div class="mb-3 pb-3" style="border-bottom:1px solid #f0eff5;">
                    <div style="font-size:0.75rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Reference</div>
                    <div class="mt-1"><code style="background:#f5f5f9; padding:2px 8px; border-radius:4px; font-size:0.8125rem; color:#2563eb;">{{ $invoice->payment_reference ?? '—' }}</code></div>
                </div>
                <div class="mb-3 pb-3" style="border-bottom:1px solid #f0eff5;">
                    <div style="font-size:0.75rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Customer</div>
                    <div class="mt-1">
                        <a href="{{ route('admin.customers.show', $invoice->customer) }}" style="color:#2563eb; text-decoration:none; font-weight:500;">{{ $invoice->customer->name ?? 'N/A' }}</a>
                    </div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Area</div>
                    <div class="mt-1" style="font-size:0.875rem; color:#1e293b;">{{ $invoice->customer->area->name ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection