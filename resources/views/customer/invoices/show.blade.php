@extends('customer.layouts.dashboard')

@section('title', 'Invoice #' . $invoice->invoice_number)

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Tagihan</div>
                <h2 class="page-title">Invoice #{{ $invoice->invoice_number }}</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('customer.invoices.index') }}" class="btn">
                    <i class="ti ti-arrow-left icon"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-lg-8">
                <div class="card" id="invoice-card">
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-6">
                                <h3 class="mb-1"><i class="ti ti-network me-2"></i>NETKING</h3>
                                <p class="text-muted mb-0">Invoice {{ $invoice->invoice_number }}</p>
                            </div>
                            <div class="col-6 text-end">
                                @if($invoice->status === 'paid')
                                <span class="badge bg-success fs-4 px-3 py-2">LUNAS</span>
                                @elseif($invoice->status === 'unpaid' && $invoice->due_date->isPast())
                                <span class="badge bg-danger fs-4 px-3 py-2">JATUH TEMPO</span>
                                @else
                                <span class="badge bg-warning fs-4 px-3 py-2">BELUM LUNAS</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h4 class="subheader">Tagihan Kepada</h4>
                                <p class="mb-1 fw-bold">{{ $invoice->customer->name }}</p>
                                <p class="text-muted mb-0">{{ $invoice->customer->phone ?? '' }}</p>
                                <p class="text-muted mb-0">{{ $invoice->customer->address ?? '' }}</p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h4 class="subheader">Detail Tagihan</h4>
                                <p class="mb-1">Diterbitkan: {{ $invoice->created_at->format('d M Y') }}</p>
                                <p class="mb-1">Jatuh Tempo: {{ $invoice->due_date->format('d M Y') }}</p>
                                @if($invoice->paid_at)
                                <p class="mb-0 text-success fw-bold">Dibayar: {{ $invoice->paid_at->format('d M Y H:i') }}</p>
                                @endif
                            </div>
                        </div>

                        <table class="table table-transparent table-responsive">
                            <thead>
                                <tr>
                                    <th>Keterangan</th>
                                    <th class="text-end">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <p class="mb-1 fw-bold">Layanan Internet - {{ $invoice->customer->package->name ?? 'Standar' }}</p>
                                        <p class="text-muted mb-0">Langganan bulanan</p>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="fw-bold text-uppercase">Total Tagihan</td>
                                    <td class="fw-bold text-end fs-3">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pembayaran</h3>
                    </div>
                    <div class="card-body">
                        @if($invoice->status === 'paid')
                        <div class="alert alert-success">
                            <i class="ti ti-circle-check me-2"></i>
                            Tagihan ini telah dibayar. Terima kasih!
                        </div>
                        <div class="datagrid">
                            <div class="datagrid-item">
                                <div class="datagrid-title">Metode Pembayaran</div>
                                <div class="datagrid-content">{{ $invoice->payment_method ?? 'Direct' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Referensi</div>
                                <div class="datagrid-content"><code>{{ $invoice->payment_reference ?? '-' }}</code></div>
                            </div>
                        </div>
                        @else
                        @if($invoice->payment_url)
                        <a href="{{ $invoice->payment_url }}" class="btn btn-primary w-100 mb-3" target="_blank">
                            <i class="ti ti-credit-card icon"></i> Bayar Sekarang
                        </a>
                        @endif
                        @if(!empty($paymentSettings['qris']))
                        <div class="mb-3">
                            <div class="text-uppercase text-muted small fw-bold mb-2">QRIS</div>
                            <div class="border rounded-4 p-3 bg-light">
                                <div class="fw-bold mb-2">{{ $paymentSettings['qris']['label'] ?? 'QRIS NETKING' }}</div>
                                <a href="{{ $paymentSettings['qris']['image_url'] }}" target="_blank" rel="noopener">
                                    <img src="{{ $paymentSettings['qris']['image_url'] }}" alt="QRIS NETKING" class="img-fluid rounded-3 border">
                                </a>
                                @if(!empty($paymentSettings['qris']['notes']))
                                <div class="text-muted small mt-2">{{ $paymentSettings['qris']['notes'] }}</div>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if(!empty($paymentSettings['accounts']) && count($paymentSettings['accounts']))
                        <div class="mb-3">
                            <div class="text-uppercase text-muted small fw-bold mb-2">Rekening Transfer</div>
                            <div class="d-flex flex-column gap-2">
                                @foreach($paymentSettings['accounts'] as $account)
                                <div class="border rounded-4 p-3 bg-light">
                                    <div class="fw-bold">{{ $account['bank_name'] ?? '-' }}</div>
                                    <div class="fs-4 fw-bold">{{ $account['account_number'] ?? '-' }}</div>
                                    <div class="text-muted small">a.n. {{ $account['account_holder'] ?? '-' }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="alert alert-info mb-0">
                            <i class="ti ti-info-circle me-2"></i>
                            {{ $paymentSettings['notes'] ?? 'Transfer atau bayar via QRIS sesuai nominal invoice, lalu upload bukti pembayaran agar admin bisa memverifikasi pembayaran Anda.' }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
