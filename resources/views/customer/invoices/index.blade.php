@extends('customer.layouts.dashboard')

@section('title', 'Tagihan Saya - Portal Pelanggan')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Tagihan Saya</h2>
                <div class="text-muted mt-1">Lihat dan bayar tagihan Anda</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Tagihan #</th>
                            <th>Jumlah</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th class="w-1">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                        <tr>
                            <td class="fw-bold">{{ $invoice->invoice_number }}</td>
                            <td>Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                            <td class="text-muted">{{ $invoice->due_date->format('d M Y') }}</td>
                            <td>
                                @if($invoice->status === 'paid')
                                <span class="badge bg-success"><i class="ti ti-check me-1"></i> Lunas</span>
                                @elseif($invoice->status === 'unpaid' && $invoice->due_date->isPast())
                                <span class="badge bg-danger"><i class="ti ti-alert-triangle me-1"></i> Jatuh Tempo</span>
                                @else
                                <span class="badge bg-warning"><i class="ti ti-clock me-1"></i> Belum Lunas</span>
                                @endif
                            </td>
                            <td>
                                @if($invoice->payment_review_status === 'submitted')
                                <div class="text-muted small mb-1"><i class="ti ti-clock-hour-4 me-1"></i>Bukti bayar menunggu review</div>
                                @elseif($invoice->payment_review_status === 'rejected')
                                <div class="text-danger small mb-1"><i class="ti ti-x me-1"></i>Bukti bayar ditolak</div>
                                @endif
                                <a href="{{ route('customer.invoices.show', $invoice) }}" class="btn btn-sm btn-primary">
                                    <i class="ti ti-eye icon"></i> Lihat
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="empty">
                                    <div class="empty-icon"><i class="ti ti-file-invoice" style="font-size: 3rem;"></i></div>
                                    <p class="empty-title">Belum ada tagihan</p>
                                    <p class="empty-subtitle text-muted">Tagihan akan muncul di sini setelah dibuat</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($invoices, 'links'))
            <div class="card-footer d-flex align-items-center">
                {{ $invoices->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
