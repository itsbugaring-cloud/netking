@extends('layouts.app')
@section('title', 'Customer: ' . $customer->name)

@section('content')
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4>{{ $customer->name }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                <li class="breadcrumb-item active">{{ $customer->name }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary btn-sm">
            <i class='bx bx-edit me-1'></i> Edit
        </a>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-sm" style="background:#f5f5f9; color:#1e293b;">
            <i class='bx bx-arrow-back me-1'></i> Back
        </a>
    </div>
</div>

{{-- ─── Live Connection Topology ─────────────────────────────────────── --}}
<div class="card mb-3" id="topology-card">
    <div class="card-header d-flex align-items-center justify-content-between py-2" style="background:#0f172a; border-radius:8px 8px 0 0;">
        <div class="d-flex align-items-center gap-2">
            <span style="font-size:0.6rem;width:8px;height:8px;border-radius:50%;display:inline-block;" id="topo-dot"></span>
            <span style="color:#fff; font-weight:600; font-size:.875rem;">Koneksi Aktif</span>
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
            <span class="ms-2" style="color:#94a3b8; font-size:.8rem;">Loading topology...</span>
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
                    <div style="font-size:.6rem;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Last Seen</div>
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
                <div style="font-size:0.875rem; color:#64748b; margin-bottom:0.75rem;">{{ $customer->phone ?? 'No phone' }}</div>
                @if($customer->status === 'active')
                <span class="badge-status badge-active">Active</span>
                @elseif($customer->status === 'pending')
                <span class="badge-status badge-pending">Pending</span>
                @else
                <span class="badge-status badge-inactive">{{ ucfirst($customer->status) }}</span>
                @endif
            </div>
            <div class="card-body" style="border-top:1px solid #f0eff5; padding-top:1rem;">
                @php
                $fields = [
                ['PPPoE User', "<code style='background:#f5f5f9;padding:2px 8px;border-radius:4px;font-size:0.8125rem;color:#2563eb;'>{$customer->pppoe_user}</code> <button class='btn btn-sm btn-clipboard p-0 ms-1' data-clipboard-text='{$customer->pppoe_user}' title='Copy' style='border:none;background:none;color:#94a3b8;cursor:pointer;'><i class='bx bx-copy'></i></button>"],
                ['Package', $customer->package->name ?? 'N/A'],
                ['Monthly Price', 'Rp ' . number_format($customer->package_price ?? 0, 0, ',', '.')],
                ['Area', $customer->area->name ?? 'N/A'],
                ['Partner', $customer->partner->name ?? 'Direct'],
                ['ODP', $customer->odp ? $customer->odp->name . ' (Port ' . ($customer->odp_port ?? '?') . ')' : 'N/A'],
                ['ONT SN', $customer->ont_sn ? "<code style='background:#f5f5f9;padding:2px 8px;border-radius:4px;font-size:0.8125rem;color:#2563eb;'>{$customer->ont_sn}</code>" : 'N/A'],
                ['Remote IP', "<code style='background:#f5f5f9;padding:2px 8px;border-radius:4px;font-size:0.8125rem;color:#2563eb;'>" . ($customer->remote_ip ?? 'Dynamic') . "</code> <button class='btn btn-sm btn-clipboard p-0 ms-1' data-clipboard-text='" . ($customer->remote_ip ?? ' Dynamic') . "' title='Copy' style='border:none;background:none;color:#94a3b8;cursor:pointer;'><i class='bx bx-copy'></i></button>" ],
                    ['Address', $customer->address ?? '-'],
                    ['Joined', $customer->created_at->format('d M Y')],
                    ];
                    @endphp
                    @foreach($fields as $field)
                    <div class="d-flex justify-content-between mb-2 pb-2" style="border-bottom:1px solid #f0eff5;">
                        <span style="font-size:0.75rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">{{ $field[0] }}</span>
                        <span style="font-size:0.875rem; color:#1e293b;">{!! $field[1] !!}</span>
                    </div>
                    @endforeach
            </div>
            <div class="card-footer" style="border-top:1px solid #dbdade; padding:1rem 1.5rem;">
                <form action="{{ route('admin.customers.toggle-status', $customer) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn w-100 {{ $customer->status === 'active' ? 'btn-warning' : 'btn-success' }}">
                        @if($customer->status === 'active')
                        <i class='bx bx-pause-circle me-1'></i> Suspend Customer
                        @else
                        <i class='bx bx-play-circle me-1'></i> Activate Customer
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Right Column: Tabbed Content --}}
    <div class="col-lg-8">

        {{-- Tab Navigation --}}
        <ul class="nav nav-tabs mb-0" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-invoices" type="button" role="tab">
                    <i class='bx bx-file me-1'></i>Invoices
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-equipment" type="button" role="tab">
                    <i class='bx bx-microchip me-1'></i>Equipment
                    <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:.65rem;">{{ $customer->devices->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-history" type="button" role="tab">
                    <i class='bx bx-history me-1'></i>History
                </button>
            </li>
            @if($customer->error_message)
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-errors" type="button" role="tab">
                    <i class='bx bx-error-circle me-1' style="color:#ff3d00;"></i>Errors
                </button>
            </li>
            @endif
        </ul>

        {{-- Tab Content --}}
        <div class="tab-content">
            {{-- Invoices Tab --}}
            <div class="tab-pane fade show active" id="tab-invoices" role="tabpanel">
                <div class="card" style="border-top-left-radius:0;border-top-right-radius:0;">
                    <div class="table-responsive">
                        <table class="table" id="customer-invoices-table">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Amount</th>
                                    <th>Period</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->invoices()->latest()->take(10)->get() as $invoice)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.invoices.show', $invoice) }}" style="color:#2563eb; font-size:0.8125rem; font-family:monospace;">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                    </td>
                                    <td style="font-weight:600; color:#1e293b;">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                                    <td style="font-size:0.8125rem; color:#64748b;">{{ \Carbon\Carbon::parse($invoice->billing_month)->format('M Y') }}</td>
                                    <td style="font-size:0.8125rem; color:#64748b;">{{ $invoice->due_date?->format('d M Y') }}</td>
                                    <td>
                                        @if($invoice->status === 'paid')
                                        <span class="badge-status badge-paid">Paid</span>
                                        @elseif($invoice->status === 'unpaid' && $invoice->due_date?->isPast())
                                        <span class="badge-status badge-overdue">Overdue</span>
                                        @else
                                        <span class="badge-status badge-unpaid">Unpaid</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm" style="background:rgba(3,195,236,0.12); color:#03c3ec; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:6px; padding:0;">
                                            <i class='bx bx-show' style="font-size:0.875rem;"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4" style="color:#64748b; font-size:0.875rem;">
                                        No invoices yet
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
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
                            <i class='bx bx-plus me-1'></i>Tambah
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Brand / Model</th>
                                    <th>Serial Number</th>
                                    <th>Status</th>
                                    <th>Assigned</th>
                                    <th style="width:80px;">Actions</th>
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
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm px-1 py-0" style="background:rgba(37,99,235,.1);color:#2563eb;border-radius:4px;" title="Edit"
                                                onclick="editDevice({{ json_encode($device) }})">
                                                <i class='bx bx-edit-alt' style="font-size:.8rem;"></i>
                                            </button>
                                            <form action="{{ route('admin.customers.devices.destroy', [$customer, $device]) }}" method="POST"
                                                onsubmit="return confirm('Hapus perangkat ini?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm px-1 py-0" style="background:rgba(239,68,68,.1);color:#ef4444;border-radius:4px;" title="Delete">
                                                    <i class='bx bx-trash' style="font-size:.8rem;"></i>
                                                </button>
                                            </form>
                                        </div>
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
                            <i class='bx bx-history me-1'></i>Activity & Connection History
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        @php
                            $logs = \App\Models\ActivityLog::where('model_type', 'App\\Models\\Customer')
                                ->where('model_id', $customer->id)
                                ->with('user')
                                ->orderBy('created_at', 'desc')
                                ->limit(30)
                                ->get();

                            $invoiceLogs = $customer->invoices()->whereNotNull('paid_at')->orderBy('paid_at', 'desc')->take(10)->get();
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
                                        'provisioned' => ['bx-check-shield', 'var(--green)'],
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
                                        · by {{ $log->user->name }}
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

                            {{-- Invoice Payment History --}}
                            @foreach($invoiceLogs as $inv)
                            <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.625rem 1.25rem;border-bottom:1px solid var(--border);">
                                <div style="width:28px;height:28px;border-radius:6px;background:rgba(34,197,94,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
                                    <i class='bx bx-money' style="font-size:.85rem;color:var(--green);"></i>
                                </div>
                                <div style="flex:1;">
                                    <div style="font-size:.8rem;font-weight:500;">Invoice {{ $inv->invoice_number }} paid — Rp {{ number_format($inv->amount, 0, ',', '.') }}</div>
                                    <div style="font-size:.7rem;color:var(--text-muted);">{{ $inv->paid_at->diffForHumans() }} · {{ \Carbon\Carbon::parse($inv->billing_month)->format('M Y') }}</div>
                                </div>
                            </div>
                            @endforeach

                            @if($logs->isEmpty() && $invoiceLogs->isEmpty())
                            <div class="text-center py-4" style="color:var(--text-muted);font-size:.875rem;">
                                <i class='bx bx-history' style="font-size:2rem;opacity:.3;display:block;margin-bottom:6px;"></i>
                                No activity history yet
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
                        <h5 class="card-title" style="color:#ff3d00;"><i class='bx bx-error-circle me-2'></i>Provisioning Error</h5>
                    </div>
                    <div class="card-body">
                        <pre style="color:#ff3d00; background:#fff5f5; padding:1rem; border-radius:0.375rem; font-size:0.8125rem;">{{ $customer->error_message }}</pre>
                        @if($customer->status === 'failed')
                        <form action="{{ route('admin.customers.retry-provision', $customer) }}" method="POST" class="mt-2">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class='bx bx-refresh me-1'></i> Retry Provisioning
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
                                <option value="active">Active</option>
                                <option value="returned">Returned</option>
                                <option value="damaged">Damaged</option>
                                <option value="lost">Lost</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Brand</label>
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
                                <option value="active">Active</option>
                                <option value="returned">Returned</option>
                                <option value="damaged">Damaged</option>
                                <option value="lost">Lost</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Brand</label>
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
                    <button type="submit" class="btn btn-primary"><i class='bx bx-check me-1'></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                document.getElementById('topo-router-status').textContent = isOnline ? '● Connected' : '○ Offline';
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

    $(function() {
        loadTopology();
        setInterval(loadTopology, 30000);
    });
</script>
@endsection