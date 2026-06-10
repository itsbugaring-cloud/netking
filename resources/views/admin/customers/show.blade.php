@extends('layouts.app')
@section('title', 'Pelanggan: ' . $customer->name)

@section('styles')
<style>
    .customer-show-page [style*="color:#1e293b"],
    .customer-show-page [style*="color: #1e293b"] {
        color: var(--txt) !important;
    }

    .customer-show-page [style*="color:#64748b"],
    .customer-show-page [style*="color: #64748b"],
    .customer-show-page [style*="color:#94a3b8"],
    .customer-show-page [style*="color: #94a3b8"],
    .customer-show-page [style*="color:#475569"],
    .customer-show-page [style*="color: #475569"] {
        color: var(--txt-3) !important;
    }

    .customer-show-page [style*="background:#f5f5f9"],
    .customer-show-page [style*="background: #f5f5f9"] {
        background: var(--surface-2) !important;
        border-color: var(--border) !important;
        color: var(--blue) !important;
    }

    .customer-show-page [style*="border-top:1px solid #f0eff5"],
    .customer-show-page [style*="border-top:1px solid #dbdade"],
    .customer-show-page [style*="border-bottom:1px solid #f0eff5"] {
        border-color: var(--border) !important;
    }

    .customer-show-page #topology-card .card-header {
        background: color-mix(in srgb, var(--surface-2) 86%, var(--surface)) !important;
        border-bottom: 1px solid var(--border) !important;
    }

    .customer-show-page #topology-card .card-body {
        background: linear-gradient(135deg, color-mix(in srgb, var(--surface) 92%, var(--surface-2)) 0%, color-mix(in srgb, var(--surface-2) 88%, var(--surface)) 100%) !important;
    }

    .customer-show-page #topology-card [style*="color:#fff"],
    .customer-show-page #topology-card [style*="color: #fff"],
    .customer-show-page #topology-card [style*="color:#e2e8f0"],
    .customer-show-page #topology-card [style*="color: #e2e8f0"] {
        color: var(--txt) !important;
    }

    .customer-show-page #topology-card [style*="rgba(255,255,255,.12)"] {
        background: var(--surface-2) !important;
        color: var(--txt-2) !important;
        border: 1px solid var(--border) !important;
    }

    .customer-show-page #topology-card [style*="rgba(255,255,255,.08)"] {
        border-color: var(--border) !important;
    }

    .customer-show-page .avatar {
        color: #fff;
    }

    .customer-show-page .tab-content > .tab-pane > .card {
        border-top-left-radius: 0 !important;
        border-top-right-radius: 0 !important;
    }

    .customer-show-page #topology-card [style*="background:#0f172a"],
    .customer-show-page #topology-card [style*="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%)"] {
        background: color-mix(in srgb, var(--surface) 90%, #0b1220) !important;
        border-color: var(--border) !important;
    }

    .customer-show-page #topology-card [style*="rgba(59,130,246,.2)"],
    .customer-show-page #topology-card [style*="rgba(139,92,246,.2)"],
    .customer-show-page #topology-card [style*="rgba(34,197,94,.2)"],
    .customer-show-page #topology-card [style*="rgba(245,158,11,.2)"],
    .customer-show-page #topology-card [style*="rgba(239,68,68,.2)"] {
        background: var(--surface-2) !important;
    }

    .customer-show-page #topology-card code {
        background: var(--surface-2) !important;
        border: 1px solid var(--border) !important;
        color: var(--blue) !important;
    }

    .customer-show-page [style*="background: hsl("] {
        color: #fff !important;
    }
</style>
@endsection

@section('content')
@php
    $isFinance = (auth()->user()->role ?? null) === 'finance';
@endphp
<div class="ms-page customer-show-page">
<div class="ms-page-head">
    <div>
        <div class="ms-page-kicker"><i class='bx bx-user'></i> Detail Pelanggan</div>
        <h1 class="ms-page-title">{{ $customer->name }}</h1>
    </div>
    <div class="ms-page-actions">
        @unless($isFinance)
        <button type="button" class="ms-btn-secondary" onclick="copyPaymentLink()" title="Salin link bayar untuk dikirim ke pelanggan via WA">
            <i class='bx bx-copy'></i> Salin Link Bayar
        </button>
        <a href="{{ route('admin.payments.manual', $customer) }}" class="ms-btn-secondary" style="color:#16a34a;border-color:#16a34a;">
            <i class='bx bx-money'></i> Tandai Bayar Manual
        </a>
        <button type="button" class="ms-btn-secondary" data-bs-toggle="modal" data-bs-target="#resetPortalPasswordModal">
            <i class='bx bx-key'></i> Reset Password Portal
        </button>
        <a href="{{ route('admin.customers.edit', $customer) }}" class="ms-btn">
            <i class='bx bx-edit'></i> Ubah
        </a>
        @endunless
        <a href="javascript:history.back()" class="ms-btn-secondary">
            <i class='bx bx-arrow-back'></i> Kembali
        </a>
    </div>
</div>

{{-- ─── PPPoE Pending Enable Warning ───────────────────────────────────── --}}
@if($customer->pppoe_pending_enable)
<div class="alert mb-3" style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
    <div style="display:flex;align-items:center;gap:10px;">
        <i class='bx bx-error' style="font-size:1.4rem;color:#f97316;flex-shrink:0;"></i>
        <div>
            <div style="font-weight:700;color:#9a3412;font-size:.875rem;">PPPoE Belum Diaktifkan di Router</div>
            <div style="font-size:.8rem;color:#c2410c;margin-top:2px;">
                Pembayaran sudah dikonfirmasi tapi PPPoE <strong>{{ $customer->pppoe_user }}</strong> gagal diaktifkan otomatis karena MikroTik tidak reachable saat konfirmasi pembayaran.
                @if($customer->error_message)
                    <br>Alasan: {{ $customer->error_message }}
                @endif
            </div>
        </div>
    </div>
    @unless($isFinance)
    <form action="{{ route('admin.customers.enable-pppoe', $customer) }}" method="POST" style="flex-shrink:0;">
        @csrf
        <button type="submit" class="btn btn-sm btn-warning" style="border-radius:8px;font-weight:700;white-space:nowrap;"
            onclick="return confirm('Aktifkan PPPoE {{ $customer->pppoe_user }} sekarang?')">
            <i class='bx bx-wifi me-1'></i> Aktifkan PPPoE Sekarang
        </button>
    </form>
    @endunless
</div>
@endif

{{-- ─── Live Connection Topology ─────────────────────────────────────── --}}
<div class="card mb-3" id="topology-card">
    <div class="card-header d-flex align-items-center justify-content-between py-2" style="background:#0f172a; border-radius:8px 8px 0 0;">
        <div class="d-flex align-items-center gap-2">
            <span style="font-size:0.6rem;width:8px;height:8px;border-radius:50%;display:inline-block;" id="topo-dot"></span>
            <span style="color:#fff; font-weight:600; font-size:.875rem;">Koneksi Langsung</span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm px-2 py-0" style="background:rgba(255,255,255,.12);color:#94a3b8;font-size:.75rem;" onclick="loadTopology()">
                <i class='bx bx-refresh'></i> Live
            </button>
        </div>
    </div>
    <div class="card-body p-0" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); border-radius:0 0 8px 8px; position:relative; overflow:hidden;">
        {{-- Loading --}}
        <div id="topo-loading" class="text-center py-5">
            <div class="spinner-border spinner-border-sm text-info"></div>
            <span class="ms-2" style="color:#94a3b8; font-size:.8rem;">Memuat topologi...</span>
        </div>

        {{-- Topology Visual --}}
        <div id="topo-content" style="display:none; padding:1.5rem;">
            <div class="d-flex align-items-center justify-content-center gap-0 flex-wrap" style="min-height:120px;">
                {{-- Router Node --}}
                <div class="text-center" style="min-width:80px;">
                    <div style="width:48px;height:48px;border-radius:10px;background:rgba(59,130,246,.2);border:2px solid #3b82f6;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;">
                        <i class='bx bx-wifi' style="font-size:1.25rem;color:#60a5fa;"></i>
                    </div>
                    <div style="font-size:.7rem;font-weight:700;color:#e2e8f0;">Router</div>
                    <div id="topo-router-status" style="font-size:.6rem;color:#94a3b8;">—</div>
                </div>

                {{-- Line 1: Router → ONT --}}
                <div class="d-flex flex-column align-items-center" style="min-width:50px;">
                    <div style="font-size:.6rem;color:#94a3b8;margin-bottom:1px;" id="topo-signal">—</div>
                    <div style="height:2px;width:40px;background:linear-gradient(90deg,#3b82f6,#8b5cf6);border-radius:1px;position:relative;">
                        <div style="position:absolute;top:-3px;right:-3px;width:6px;height:6px;border-radius:50%;background:#8b5cf6;animation:pulse 2s infinite;"></div>
                    </div>
                    <div style="font-size:.55rem;color:#64748b;margin-top:1px;">Signal</div>
                </div>

                {{-- ONT Node --}}
                <div class="text-center" style="min-width:80px;">
                    <div style="width:48px;height:48px;border-radius:10px;background:rgba(139,92,246,.2);border:2px solid #8b5cf6;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;">
                        <i class='bx bx-chip' style="font-size:1.25rem;color:#a78bfa;"></i>
                    </div>
                    <div style="font-size:.7rem;font-weight:700;color:#e2e8f0;" id="topo-ont-name">ONT</div>
                    <div id="topo-ont-status" style="font-size:.6rem;color:#94a3b8;">—</div>
                </div>

                {{-- Line 2: ONT → ODP --}}
                <div class="d-flex flex-column align-items-center" style="min-width:50px;">
                    <div style="font-size:.6rem;color:#94a3b8;margin-bottom:1px;">FO</div>
                    <div style="height:2px;width:40px;background:linear-gradient(90deg,#8b5cf6,#22c55e);border-radius:1px;position:relative;">
                        <div style="position:absolute;top:-3px;right:-3px;width:6px;height:6px;border-radius:50%;background:#22c55e;animation:pulse 2s infinite;"></div>
                    </div>
                    <div style="font-size:.55rem;color:#64748b;margin-top:1px;">Fiber</div>
                </div>

                {{-- ODP Node --}}
                <div class="text-center" style="min-width:80px;">
                    <div style="width:48px;height:48px;border-radius:10px;background:rgba(34,197,94,.2);border:2px solid #22c55e;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;">
                        <i class='bx bx-git-branch' style="font-size:1.25rem;color:#4ade80;"></i>
                    </div>
                    <div style="font-size:.7rem;font-weight:700;color:#e2e8f0;" id="topo-odp-name">ODP</div>
                    <div id="topo-odp-ports" style="font-size:.6rem;color:#94a3b8;">—</div>
                </div>

                {{-- Line 3: ODP → OLT --}}
                <div class="d-flex flex-column align-items-center" style="min-width:50px;">
                    <div style="font-size:.6rem;color:#94a3b8;margin-bottom:1px;">Uplink</div>
                    <div style="height:2px;width:40px;background:linear-gradient(90deg,#22c55e,#f59e0b);border-radius:1px;position:relative;">
                        <div style="position:absolute;top:-3px;right:-3px;width:6px;height:6px;border-radius:50%;background:#f59e0b;animation:pulse 2s infinite;"></div>
                    </div>
                    <div style="font-size:.55rem;color:#64748b;margin-top:1px;">PON</div>
                </div>

                {{-- OLT Node --}}
                <div class="text-center" style="min-width:80px;">
                    <div style="width:48px;height:48px;border-radius:10px;background:rgba(245,158,11,.2);border:2px solid #f59e0b;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;">
                        <i class='bx bx-server' style="font-size:1.25rem;color:#fbbf24;"></i>
                    </div>
                    <div style="font-size:.7rem;font-weight:700;color:#e2e8f0;" id="topo-olt-name">OLT</div>
                    <div id="topo-olt-info" style="font-size:.6rem;color:#94a3b8;">—</div>
                </div>

                {{-- Line 4: OLT → Server --}}
                <div class="d-flex flex-column align-items-center" style="min-width:50px;">
                    <div style="font-size:.6rem;color:#94a3b8;margin-bottom:1px;" id="topo-wan-ip">—</div>
                    <div style="height:2px;width:40px;background:linear-gradient(90deg,#f59e0b,#ef4444);border-radius:1px;position:relative;">
                        <div style="position:absolute;top:-3px;right:-3px;width:6px;height:6px;border-radius:50%;background:#ef4444;animation:pulse 2s infinite;"></div>
                    </div>
                    <div style="font-size:.55rem;color:#64748b;margin-top:1px;">WAN</div>
                </div>

                {{-- Server Node --}}
                <div class="text-center" style="min-width:80px;">
                    <div style="width:48px;height:48px;border-radius:10px;background:rgba(239,68,68,.2);border:2px solid #ef4444;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;">
                        <i class='bx bx-cloud' style="font-size:1.25rem;color:#f87171;"></i>
                    </div>
                    <div style="font-size:.7rem;font-weight:700;color:#e2e8f0;">Server</div>
                    <div id="topo-server-info" style="font-size:.6rem;color:#94a3b8;">Uptime: 100%</div>
                </div>
            </div>

            {{-- Detail Row --}}
            <div class="d-flex flex-wrap gap-3 justify-content-center mt-3 pt-3" style="border-top:1px solid rgba(255,255,255,.08);">
                <div class="text-center px-2">
                    <div style="font-size:.6rem;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Uptime</div>
                    <div style="font-size:.75rem;font-weight:600;color:#e2e8f0;" id="topo-uptime">—</div>
                </div>
                <div class="text-center px-2">
                    <div style="font-size:.6rem;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">SSID</div>
                    <div style="font-size:.75rem;font-weight:600;color:#e2e8f0;" id="topo-ssid">—</div>
                </div>
                <div class="text-center px-2">
                    <div style="font-size:.6rem;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Firmware</div>
                    <div style="font-size:.75rem;font-weight:600;color:#e2e8f0;" id="topo-firmware">—</div>
                </div>
                <div class="text-center px-2">
                    <div style="font-size:.6rem;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">ONT Model</div>
                    <div style="font-size:.75rem;font-weight:600;color:#e2e8f0;" id="topo-model">—</div>
                </div>
                <div class="text-center px-2">
                    <div style="font-size:.6rem;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">OLT IP</div>
                    <div style="font-size:.75rem;font-weight:600;color:#e2e8f0;" id="topo-olt-ip">—</div>
                </div>
                <div class="text-center px-2">
                    <div style="font-size:.6rem;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Terakhir Dilihat</div>
                    <div style="font-size:.75rem;font-weight:600;color:#e2e8f0;" id="topo-last-seen">—</div>
                </div>
            </div>
        </div>

        {{-- No ACS --}}
        <div id="topo-nodata" style="display:none; padding:2rem; text-align:center;">
            <i class='bx bx-signal-4' style="font-size:2rem;color:#475569;"></i>
            <div style="color:#94a3b8;font-size:.8rem;margin-top:8px;">
                ACS tidak tersedia atau ONT SN belum diset
            </div>
        </div>
    </div>
</div>

<div class="row g-3">

    {{-- Profile Card --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center py-4">
                <div class="avatar avatar-lg mb-3 mx-auto" style="width:72px; height:72px; font-size:1.75rem; background: hsl({{ crc32($customer->name) % 360 }}, 55%, 60%);">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <h5 style="font-weight:700; color:#1e293b; margin-bottom:0.25rem;">{{ $customer->name }}</h5>
                <div style="font-size:0.875rem; color:#64748b; margin-bottom:0.75rem;">{{ $customer->phone ?? 'Tidak ada telepon' }}</div>
                @php
                  $statusMap = ['active'=>['Aktif','badge-active'],'suspended'=>['Diisolir','badge-inactive'],'provisioning'=>['Dalam Proses','badge-pending'],'failed'=>['Gagal','badge-danger'],'pending'=>['Pending','badge-pending']];
                  [$slabel,$sclass] = $statusMap[$customer->status] ?? [ucfirst($customer->status),'badge-inactive'];
                @endphp
                <span class="badge-status {{ $sclass }}">{{ $slabel }}</span>
            </div>
            <div class="card-body" style="border-top:1px solid #f0eff5; padding-top:1rem;">
                @php
                $billingStartLabel = optional($customer->billing_start_date)->format('d M Y')
                  ?? optional($customer->created_at)->format('d M Y')
                  ?? '-';
                $fields = [
                ['ID Pelanggan', "<code style='background:#f5f5f9;padding:2px 8px;border-radius:4px;font-size:0.8125rem;color:#2563eb;'>" . ($customer->customer_code ?? '-') . "</code> <button class='btn btn-sm btn-clipboard p-0 ms-1' data-clipboard-text='" . ($customer->customer_code ?? '') . "' title='Salin' style='border:none;background:none;color:#94a3b8;cursor:pointer;'><i class='bx bx-copy'></i></button>"],
                ['PPPoE User', "<code style='background:#f5f5f9;padding:2px 8px;border-radius:4px;font-size:0.8125rem;color:#2563eb;'>{$customer->pppoe_user}</code> <button class='btn btn-sm btn-clipboard p-0 ms-1' data-clipboard-text='{$customer->pppoe_user}' title='Salin' style='border:none;background:none;color:#94a3b8;cursor:pointer;'><i class='bx bx-copy'></i></button>"],
                ['Paket', $customer->package->name ?? 'N/A'],
                ['Harga Bulanan', 'Rp ' . number_format($customer->package_price ?? 0, 0, ',', '.')],
                ['Mulai Tagihan', $billingStartLabel],
                ['Jatuh Tempo', 'Tgl ' . ($customer->billing_due_day ?? config('billing.invoice_due_day', 20))],
                ['Area', $customer->area->name ?? 'N/A'],
                ['ODP', $customer->odp ? $customer->odp->name . ' (Port ' . ($customer->odp_port ?? '?') . ')' : 'N/A'],
                ['ONT SN', $customer->ont_sn ? "<code style='background:#f5f5f9;padding:2px 8px;border-radius:4px;font-size:0.8125rem;color:#2563eb;'>{$customer->ont_sn}</code>" : 'N/A'],
                ['Remote IP', "<code style='background:#f5f5f9;padding:2px 8px;border-radius:4px;font-size:0.8125rem;color:#2563eb;'>" . ($customer->remote_ip ?? 'Dinamis') . "</code> <button class='btn btn-sm btn-clipboard p-0 ms-1' data-clipboard-text='" . ($customer->remote_ip ?? ' Dinamis') . "' title='Salin' style='border:none;background:none;color:#94a3b8;cursor:pointer;'><i class='bx bx-copy'></i></button>" ],
                    ['Alamat', $customer->address ?? '-'],
                    ['Bergabung', $customer->created_at->format('d M Y')],
                    ];
                    @endphp
                    @foreach($fields as $field)
                    <div class="d-flex justify-content-between mb-2 pb-2" style="border-bottom:1px solid #f0eff5;">
                        <span style="font-size:0.75rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">{{ $field[0] }}</span>
                        <span style="font-size:0.875rem; color:#1e293b;">{!! $field[1] !!}</span>
                    </div>
                    @endforeach
            </div>
            @unless($isFinance)
            <div class="card-footer" style="border-top:1px solid #dbdade; padding:1rem 1.5rem;">
                <form action="{{ route('admin.customers.toggle-status', $customer) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn w-100 {{ $customer->status === 'active' ? 'btn-warning' : 'btn-success' }}">
                        @if($customer->status === 'active')
                        <i class='bx bx-pause-circle me-1'></i> Tangguhkan Pelanggan
                        @else
                        <i class='bx bx-play-circle me-1'></i> Aktifkan Pelanggan
                        @endif
                    </button>
                </form>
            </div>
            @endunless
        </div>
    </div>

    {{-- Right Column: Tabbed Content --}}
    <div class="col-lg-8">

        {{-- Tab Navigation --}}
        <ul class="nav nav-tabs mb-0" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-payments" type="button" role="tab">
                    <i class='bx bx-money me-1'></i>Pembayaran
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-equipment" type="button" role="tab">
                    <i class='bx bx-microchip me-1'></i>Perangkat
                    <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:.65rem;">{{ $customer->devices->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-history" type="button" role="tab">
                    <i class='bx bx-history me-1'></i>Riwayat
                </button>
            </li>
            @if($customer->error_message)
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-errors" type="button" role="tab">
                    <i class='bx bx-error-circle me-1' style="color:#ff3d00;"></i>Kesalahan
                </button>
            </li>
            @endif
        </ul>

        {{-- Tab Content --}}
        <div class="tab-content">
            {{-- Payments Tab --}}
            <div class="tab-pane fade show active" id="tab-payments" role="tabpanel">
                @php
                    $recentPayments = $customer->payments()->latest()->take(10)->get();
                    $hasManualPayments = $recentPayments->contains(fn($payment) => $payment->created_by_user_id && !$payment->bukti_path);
                @endphp
                <div class="card" style="border-top-left-radius:0;border-top-right-radius:0;">
                    <div class="table-responsive">
                        @if($hasManualPayments)
                        <form id="bulk-delete-manual-payments-form" action="{{ route('admin.payments.bulk-destroy') }}" method="POST" onsubmit="return confirm('Hapus semua pembayaran manual yang dipilih?');" class="d-none">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                        </form>
                        <div class="d-flex justify-content-end align-items-center gap-2 px-3 pt-3">
                            <label class="d-flex align-items-center gap-2 mb-0" style="font-size:.78rem;color:#64748b;">
                                <input type="checkbox" id="select-all-manual-payments">
                                Pilih semua manual
                            </label>
                            <button type="submit" form="bulk-delete-manual-payments-form" class="btn btn-sm btn-outline-danger" style="font-size:.75rem;">
                                Hapus yang dipilih
                            </button>
                        </div>
                        @endif
                        <table class="table table-flat mb-0" id="customer-payments-table">
                            <thead>
                                <tr>
                                    <th style="width:44px;">#</th>
                                    <th>Periode</th>
                                    <th>Jumlah</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th style="width:88px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayments as $payment)
                                @php
                                    $isManualPayment = $payment->created_by_user_id && !$payment->bukti_path;
                                @endphp
                                <tr>
                                    <td>
                                        @if($isManualPayment)
                                        <input type="checkbox" name="payment_ids[]" value="{{ $payment->id }}" form="bulk-delete-manual-payments-form" class="manual-payment-checkbox" style="width:16px;height:16px;">
                                        @else
                                        <span style="color:#94a3b8;font-size:.8rem;">—</span>
                                        @endif
                                    </td>
                                    <td style="font-size:0.8125rem; color:#64748b;">
                                        {{ \Carbon\Carbon::createFromDate($payment->periode_tahun, $payment->periode_bulan, 1)->translatedFormat('M Y') }}
                                    </td>
                                    <td style="font-weight:600; color:#1e293b;">Rp {{ number_format($payment->jumlah, 0, ',', '.') }}</td>
                                    <td style="font-size:0.8125rem; color:#64748b;">{{ ucfirst($payment->metode) }}</td>
                                    <td>
                                        @if($payment->status === 'approved')
                                        <span class="badge-status badge-paid">Disetujui</span>
                                        @elseif($payment->status === 'rejected')
                                        <span class="badge-status badge-overdue">Ditolak</span>
                                        @else
                                        <span class="badge-status badge-unpaid">Pending</span>
                                        @endif
                                    </td>
                                    <td style="font-size:0.8125rem; color:#64748b;">{{ $payment->created_at->format('d M Y') }}</td>
                                    <td>
                                        @if($isManualPayment)
                                        <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST" onsubmit="return confirm('Hapus pembayaran manual ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size:.72rem;padding:.2rem .5rem;">
                                                Hapus
                                            </button>
                                        </form>
                                        @else
                                        <span style="color:#94a3b8;font-size:.8rem;">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4" style="color:#64748b; font-size:0.875rem;">
                                        Belum ada pembayaran
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Equipment Tab --}}
            <div class="tab-pane fade" id="tab-equipment" role="tabpanel">
                <div class="card" style="border-top-left-radius:0;border-top-right-radius:0;">
                    <div class="card-header d-flex align-items-center justify-content-between py-2">
                        <h6 class="mb-0" style="font-size:.8125rem; font-weight:600; color:#1e293b;">
                            <i class='bx bx-microchip me-1'></i>Inventory Perangkat
                        </h6>
                        @unless($isFinance)
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
                            <i class='bx bx-plus me-1'></i>Tambah
                        </button>
                        @endunless
                    </div>
                    <div class="table-responsive">
                        <table class="table table-flat mb-0">
                            <thead>
                                <tr>
                                    <th>Tipe</th>
                                    <th>Merek / Model</th>
                                    <th>Serial Number</th>
                                    <th>Status</th>
                                    <th>Dipasang</th>
                                    <th style="width:80px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->devices as $device)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary" style="font-size:.7rem;">{{ $device->type_label }}</span>
                                    </td>
                                    <td style="font-size:.8125rem; color:#1e293b;">
                                        {{ $device->brand ?? '—' }}
                                        @if($device->model)
                                        <span style="color:#64748b;">{{ $device->model }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($device->serial_number)
                                        <code style="background:#f5f5f9;padding:2px 6px;border-radius:4px;font-size:.75rem;color:#2563eb;">{{ $device->serial_number }}</code>
                                        @else
                                        <span style="color:#94a3b8;font-size:.8rem;">—</span>
                                        @endif
                                    </td>
                                    <td>{!! $device->status_badge !!}</td>
                                    <td style="font-size:.8rem;color:#64748b;">{{ $device->assigned_at?->format('d M Y') ?? '—' }}</td>
                                    <td>
                                        @unless($isFinance)
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm px-1 py-0" style="background:rgba(37,99,235,.1);color:#2563eb;border-radius:4px;" title="Ubah"
                                                onclick="editDevice({{ json_encode($device) }})">
                                                <i class='bx bx-edit-alt' style="font-size:.8rem;"></i>
                                            </button>
                                            <form action="{{ route('admin.customers.devices.destroy', [$customer, $device]) }}" method="POST"
                                                onsubmit="return confirm('Hapus perangkat ini?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm px-1 py-0" style="background:rgba(239,68,68,.1);color:#ef4444;border-radius:4px;" title="Hapus">
                                                    <i class='bx bx-trash' style="font-size:.8rem;"></i>
                                                </button>
                                            </form>
                                        </div>
                                        @else
                                        <span style="color:#94a3b8;font-size:.8rem;">Lihat saja</span>
                                        @endunless
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4" style="color:#64748b;font-size:.875rem;">
                                        <i class='bx bx-package' style="font-size:2rem;color:#cbd5e1;display:block;margin-bottom:6px;"></i>
                                        Belum ada perangkat. Klik <strong>Tambah</strong> untuk menambahkan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Connection History Tab --}}
            <div class="tab-pane fade" id="tab-history" role="tabpanel">
                <div class="card" style="border-top-left-radius:0;border-top-right-radius:0;">
                    <div class="card-header py-2">
                        <h6 class="mb-0" style="font-size:.8125rem; font-weight:600;">
                            <i class='bx bx-history me-1'></i>Aktivitas & Riwayat Koneksi
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        @php
                            try {
                                $logs = \App\Models\ActivityLog::where('subject_type', 'App\\Models\\Customer')
                                    ->where('subject_id', $customer->id)
                                    ->with('user')
                                    ->orderBy('created_at', 'desc')
                                    ->limit(30)
                                    ->get();
                            } catch (\Exception $e) {
                                // Fallback: table might not have subject_type/subject_id columns yet
                                try {
                                    $logs = \App\Models\ActivityLog::where('customer_id', $customer->id)
                                        ->orderBy('created_at', 'desc')
                                        ->limit(30)
                                        ->get();
                                } catch (\Exception $e2) {
                                    $logs = collect();
                                }
                            }

                            $paymentLogs = $customer->payments()->where('status', 'approved')->orderBy('approved_at', 'desc')->take(10)->get();
                        @endphp
                        <div style="max-height:400px;overflow-y:auto;">
                            @forelse($logs as $log)
                            <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.625rem 1.25rem;border-bottom:1px solid var(--border);">
                                @php
                                    $iconMap = [
                                        'created' => ['bx-plus-circle', 'var(--green)'],
                                        'updated' => ['bx-edit', 'var(--blue)'],
                                        'status_changed' => ['bx-transfer', 'var(--orange)'],
                                        'deleted' => ['bx-trash', 'var(--red)'],
'provisioned' => ['bx-check-circle', 'var(--green)'],
                                        'suspended' => ['bx-pause-circle', 'var(--red)'],
                                        'activated' => ['bx-play-circle', 'var(--green)'],
                                    ];
                                    $icon = $iconMap[$log->action] ?? ['bx-circle', '#94a3b8'];
                                @endphp
                                <div style="width:28px;height:28px;border-radius:6px;background:{{ $icon[1] }}15;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
                                    <i class='bx {{ $icon[0] }}' style="font-size:.85rem;color:{{ $icon[1] }};"></i>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:.8rem;font-weight:500;">{{ $log->description }}</div>
                                    <div style="font-size:.7rem;color:var(--text-muted);">
                                        {{ $log->created_at->diffForHumans() }}
                                        @if($log->user)
                                        · oleh {{ $log->user->name }}
                                        @endif
                                        @if($log->changes)
                                        @foreach($log->changes as $field => $change)
                                        <span class="d-block mt-1" style="font-size:.65rem;">
                                            <strong>{{ $field }}:</strong>
                                            @if(is_array($change))
                                                {{ $change['old'] ?? '—' }} → {{ $change['new'] ?? '—' }}
                                            @else
                                                {{ $change }}
                                            @endif
                                        </span>
                                        @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            @endforelse

                            {{-- Payment History --}}
                            @foreach($paymentLogs as $pmt)
                            <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.625rem 1.25rem;border-bottom:1px solid var(--border);">
                                <div style="width:28px;height:28px;border-radius:6px;background:rgba(34,197,94,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
                                    <i class='bx bx-money' style="font-size:.85rem;color:var(--green);"></i>
                                </div>
                                <div style="flex:1;">
                                    <div style="font-size:.8rem;font-weight:500;">Pembayaran disetujui — Rp {{ number_format($pmt->jumlah, 0, ',', '.') }}</div>
                                    <div style="font-size:.7rem;color:var(--text-muted);">{{ $pmt->approved_at->diffForHumans() }} · {{ \Carbon\Carbon::createFromDate($pmt->periode_tahun, $pmt->periode_bulan, 1)->translatedFormat('M Y') }}</div>
                                </div>
                            </div>
                            @endforeach

                            @if($logs->isEmpty() && $paymentLogs->isEmpty())
                            <div class="text-center py-4" style="color:var(--text-muted);font-size:.875rem;">
                                <i class='bx bx-history' style="font-size:2rem;opacity:.3;display:block;margin-bottom:6px;"></i>
                                Belum ada riwayat aktivitas
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Errors Tab --}}
            @if($customer->error_message)
            <div class="tab-pane fade" id="tab-errors" role="tabpanel">
                <div class="card card-danger" style="border-top-left-radius:0;border-top-right-radius:0;">
                    <div class="card-header">
                        <h5 class="card-title" style="color:#ff3d00;"><i class='bx bx-error-circle me-2'></i>Kesalahan Provisioning</h5>
                    </div>
                    <div class="card-body">
                        <pre style="color:#ff3d00; background:#fff5f5; padding:1rem; border-radius:0.375rem; font-size:0.8125rem;">{{ $customer->error_message }}</pre>
                        @if($customer->status === 'failed' && ! $isFinance)
                        <form action="{{ route('admin.customers.retry-provision', $customer) }}" method="POST" class="mt-2">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class='bx bx-refresh me-1'></i> Coba Ulang Provisioning
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

</div>

{{-- Add Device Modal --}}
@unless($isFinance)
<div class="modal fade" id="resetPortalPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.customers.reset-portal-password', $customer) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class='bx bx-key me-1'></i>Reset Password Portal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        Kosongkan password jika ingin generate password acak otomatis. Customer akan diminta login ulang di aplikasi.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password portal baru</label>
                        <input type="text" name="portal_password" class="form-control" placeholder="Kosongkan untuk generate otomatis" autocomplete="off">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Konfirmasi password portal baru</label>
                        <input type="text" name="portal_password_confirmation" class="form-control" placeholder="Ulangi jika isi manual" autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class='bx bx-check me-1'></i>Reset Password Portal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addDeviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.customers.devices.store', $customer) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class='bx bx-plus-circle me-1'></i>Tambah Perangkat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="ont">ONT</option>
                                <option value="router">Router</option>
                                <option value="cable">Kabel FO</option>
                                <option value="adapter">Adapter</option>
                                <option value="splitter">Splitter</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="active">Aktif</option>
                                <option value="returned">Dikembalikan</option>
                                <option value="damaged">Rusak</option>
                                <option value="lost">Hilang</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Merek</label>
                            <input type="text" name="brand" class="form-control" placeholder="Tenda, TP-Link...">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control" placeholder="HG6245D...">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Serial Number</label>
                            <input type="text" name="serial_number" class="form-control" placeholder="SN perangkat...">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Tanggal Dipasang</label>
                            <input type="date" name="assigned_at" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Tanggal Dikembalikan</label>
                            <input type="date" name="returned_at" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class='bx bx-check me-1'></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Device Modal --}}
<div class="modal fade" id="editDeviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editDeviceForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class='bx bx-edit me-1'></i>Edit Perangkat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select name="type" id="edit-type" class="form-select" required>
                                <option value="ont">ONT</option>
                                <option value="router">Router</option>
                                <option value="cable">Kabel FO</option>
                                <option value="adapter">Adapter</option>
                                <option value="splitter">Splitter</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="edit-status" class="form-select" required>
                                <option value="active">Aktif</option>
                                <option value="returned">Dikembalikan</option>
                                <option value="damaged">Rusak</option>
                                <option value="lost">Hilang</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Merek</label>
                            <input type="text" name="brand" id="edit-brand" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" id="edit-model" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Serial Number</label>
                            <input type="text" name="serial_number" id="edit-serial" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Tanggal Dipasang</label>
                            <input type="date" name="assigned_at" id="edit-assigned" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Tanggal Dikembalikan</label>
                            <input type="date" name="returned_at" id="edit-returned" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" id="edit-notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class='bx bx-check me-1'></i>Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endunless

<style>
    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.4;
        }
    }
</style>

<script>
    function loadTopology() {
        document.getElementById('topo-loading').style.display = 'block';
        document.getElementById('topo-content').style.display = 'none';
        document.getElementById('topo-nodata').style.display = 'none';

        $.getJSON('{{ route("admin.customers.topology", $customer) }}')
            .done(function(d) {
                if (!d.acs && !d.ont) {
                    document.getElementById('topo-nodata').style.display = 'block';
                    document.getElementById('topo-dot').style.background = '#64748b';
                    return;
                }

                document.getElementById('topo-content').style.display = 'block';

                var isOnline = d.acs && d.acs.online;
                document.getElementById('topo-dot').style.background = isOnline ? '#22c55e' : '#ef4444';
                document.getElementById('topo-router-status').textContent = isOnline ? '● Terhubung' : '○ Offline';
                document.getElementById('topo-router-status').style.color = isOnline ? '#4ade80' : '#f87171';

                // ONT node
                if (d.ont) {
                    var rx = d.ont.rx_power !== null ? d.ont.rx_power + ' dBm' : '—';
                    document.getElementById('topo-signal').textContent = rx;
                    document.getElementById('topo-ont-status').textContent = d.ont.status || '—';
                    document.getElementById('topo-ont-status').style.color = d.ont.status === 'online' ? '#4ade80' : '#f87171';
                }
                if (d.acs) {
                    document.getElementById('topo-ont-name').textContent = (d.acs.manufacturer || '') + ' ' + (d.acs.model || '');
                }

                // ODP node
                if (d.odp) {
                    document.getElementById('topo-odp-name').textContent = d.odp.name;
                    document.getElementById('topo-odp-ports').textContent = '● ' + d.odp.ports;
                }

                // OLT node
                if (d.olt) {
                    document.getElementById('topo-olt-name').textContent = d.olt.name || 'OLT';
                    document.getElementById('topo-olt-info').textContent = d.olt.uptime || '—';
                    document.getElementById('topo-olt-ip').textContent = d.olt.ip || '—';
                }

                // ACS details
                if (d.acs) {
                    document.getElementById('topo-wan-ip').textContent = d.acs.wan_ip || '—';
                    document.getElementById('topo-uptime').textContent = formatUptime(d.acs.uptime);
                    document.getElementById('topo-ssid').textContent = d.acs.ssid || '—';
                    document.getElementById('topo-firmware').textContent = d.acs.firmware || '—';
                    document.getElementById('topo-model').textContent = (d.acs.manufacturer || '') + ' ' + (d.acs.model || '');
                    document.getElementById('topo-last-seen').textContent = d.acs.last_seen || '—';
                    document.getElementById('topo-server-info').textContent = d.acs.online ? 'Online' : d.acs.last_seen;
                    document.getElementById('topo-server-info').style.color = d.acs.online ? '#4ade80' : '#f87171';
                }
            })
            .fail(function() {
                document.getElementById('topo-nodata').style.display = 'block';
                document.getElementById('topo-dot').style.background = '#64748b';
            })
            .always(function() {
                document.getElementById('topo-loading').style.display = 'none';
            });
    }

    function formatUptime(seconds) {
        if (!seconds) return '—';
        var d = Math.floor(seconds / 86400);
        var h = Math.floor((seconds % 86400) / 3600);
        var m = Math.floor((seconds % 3600) / 60);
        if (d > 0) return d + 'd ' + h + 'h';
        if (h > 0) return h + 'h ' + m + 'm';
        return m + 'm';
    }

    function editDevice(device) {
        var form = document.getElementById('editDeviceForm');
        form.action = '{{ route("admin.customers.devices.store", $customer) }}'.replace('/devices', '/devices/' + device.id);

        document.getElementById('edit-type').value = device.type;
        document.getElementById('edit-status').value = device.status;
        document.getElementById('edit-brand').value = device.brand || '';
        document.getElementById('edit-model').value = device.model || '';
        document.getElementById('edit-serial').value = device.serial_number || '';
        document.getElementById('edit-assigned').value = device.assigned_at ? device.assigned_at.substring(0, 10) : '';
        document.getElementById('edit-returned').value = device.returned_at ? device.returned_at.substring(0, 10) : '';
        document.getElementById('edit-notes').value = device.notes || '';

        new bootstrap.Modal(document.getElementById('editDeviceModal')).show();
    }

    function copyPaymentLink() {
        var url = '{{ url("/bayar?customer_code=" . $customer->customer_code) }}';
        navigator.clipboard.writeText(url).then(function() {
            toastr.success('Link bayar disalin: ' + url);
        }).catch(function() {
            prompt('Salin link ini:', url);
        });
    }

    $(function() {
        loadTopology();
        setInterval(loadTopology, 30000);

        var selectAllManual = document.getElementById('select-all-manual-payments');
        if (selectAllManual) {
            selectAllManual.addEventListener('change', function() {
                document.querySelectorAll('.manual-payment-checkbox').forEach(function(cb) {
                    cb.checked = selectAllManual.checked;
                });
            });
        }
    });
</script>
</div>
@endsection
