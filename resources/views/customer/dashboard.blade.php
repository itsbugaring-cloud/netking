@extends('customer.layouts.dashboard')

@section('title', 'Dasbor - Portal Pelanggan')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Welcome, {{ $customer->name }} 👋</h2>
                <div class="text-muted mt-1">Berikut ringkasan akun Anda</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-deck row-cards">

            <!-- Package Info Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-package me-2"></i>Paket Anda</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <span class="avatar avatar-lg bg-blue-lt me-3">
                                <i class="ti ti-wifi" style="font-size: 1.5rem;"></i>
                            </span>
                            <div>
                                <h2 class="mb-0">{{ $stats['package'] }}</h2>
                                <div class="text-muted">Paket internet saat ini</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <div class="subheader mb-1">Status</div>
                                    @if($stats['status'] === 'active')
                                    <span class="badge bg-success"><i class="ti ti-check me-1"></i> Aktif</span>
                                    @else
                                    <span class="badge bg-danger"><i class="ti ti-x me-1"></i> {{ ucfirst($stats['status']) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <div class="subheader mb-1">Koneksi</div>
                                    @if($stats['is_online'])
                                    <span class="badge bg-success"><i class="ti ti-circle-filled me-1" style="font-size: 8px;"></i> Online</span>
                                    @else
                                    <span class="badge bg-secondary"><i class="ti ti-circle-filled me-1" style="font-size: 8px;"></i> Offline</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="datagrid mt-2">
                            <div class="datagrid-item">
                                <div class="datagrid-title">IP Address</div>
                                <div class="datagrid-content"><code>{{ $stats['ip_address'] }}</code></div>
                            </div>
                            @if($stats['is_online'])
                            <div class="datagrid-item">
                                <div class="datagrid-title">Waktu Aktif</div>
                                <div class="datagrid-content">{{ $stats['uptime'] }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing Info Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-cash me-2"></i>Tagihan</h3>
                    </div>
                    <div class="card-body">
                        @if($stats['unpaid_count'] > 0)
                        <div class="alert alert-warning">
                            <div class="d-flex">
                                <div><i class="ti ti-alert-triangle me-2"></i></div>
                                <div>
                                    <h4 class="alert-title">Pembayaran Diperlukan</h4>
                                    <div>Anda memiliki {{ $stats['unpaid_count'] }} tagihan belum lunas dengan total
                                        <strong>Rp {{ number_format($stats['unpaid_amount'], 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-success">
                            <div class="d-flex">
                                <div><i class="ti ti-circle-check me-2"></i></div>
                                <div>Semua tagihan telah lunas! Anda tidak memiliki tunggakan.</div>
                            </div>
                        </div>
                        @endif

                        @if($stats['latest_invoice'])
                        <div class="mt-3">
                            <div class="subheader mb-2">Tagihan Terbaru</div>
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="avatar bg-{{ $stats['latest_invoice']->status === 'paid' ? 'green' : 'yellow' }}-lt">
                                                <i class="ti ti-file-invoice"></i>
                                            </span>
                                        </div>
                                        <div class="col">
                                            <div class="fw-bold">#{{ $stats['latest_invoice']->invoice_number }}</div>
                                            <div class="text-muted">Rp {{ number_format($stats['latest_invoice']->amount, 0, ',', '.') }} · Jatuh Tempo {{ $stats['latest_invoice']->due_date->format('d M Y') }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ route('customer.invoices.show', $stats['latest_invoice']) }}" class="btn btn-primary btn-sm">Lihat</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-bolt me-2"></i>Akses Cepat</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <a href="{{ route('customer.invoices.index') }}" class="btn btn-outline-primary w-100 py-3">
                                    <i class="ti ti-file-invoice icon mb-1"></i>
                                    <div>Lihat Tagihan</div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="{{ route('customer.profile.index') }}" class="btn btn-outline-primary w-100 py-3">
                                    <i class="ti ti-user icon mb-1"></i>
                                    <div>Profil Saya</div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection