@extends('layouts.app')
@section('title', 'Queue Sync Report')

@section('content')
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-sync'></i> Bandwidth Control</div>
            <h1 class="ms-page-title">Queue Sync Report — {{ $area->name }}</h1>
        </div>
        <div class="ms-page-actions">
            <a href="{{ route('admin.queues.index', ['area_id' => $area->id]) }}" class="ms-btn-secondary">
                <i class='bx bx-arrow-back'></i> Back to Queues
            </a>
        </div>
    </div>

    {{-- Stats Overview --}}
    <div class="ms-stat-grid">
        <div class="ms-stat-card" style="--stat-accent:var(--blue);--stat-bg:color-mix(in srgb,var(--blue) 8%,var(--surface));">
            <div class="ms-stat-icon"><i class='bx bx-list-check' style="font-size:1.3rem;"></i></div>
            <div>
                <div class="ms-stat-label">Router Queues</div>
                <div class="ms-stat-value">{{ $routerQueues->count() }}</div>
            </div>
        </div>
        <div class="ms-stat-card" style="--stat-accent:var(--green);--stat-bg:color-mix(in srgb,var(--green) 8%,var(--surface));">
            <div class="ms-stat-icon"><i class='bx bx-user-check' style="font-size:1.3rem;"></i></div>
            <div>
                <div class="ms-stat-label">Customers (w/ IP+Package)</div>
                <div class="ms-stat-value">{{ $customers->count() }}</div>
            </div>
        </div>
        <div class="ms-stat-card" style="--stat-accent:var(--orange,#f97316);--stat-bg:color-mix(in srgb,var(--orange,#f97316) 8%,var(--surface));">
            <div class="ms-stat-icon"><i class='bx bx-ghost' style="font-size:1.3rem;"></i></div>
            <div>
                <div class="ms-stat-label">Orphaned Queues</div>
                <div class="ms-stat-value" style="color:var(--orange,#f97316);">{{ $orphaned->count() }}</div>
                <div class="ms-stat-meta">On router, no customer</div>
            </div>
        </div>
        <div class="ms-stat-card" style="--stat-accent:var(--red,#ef4444);--stat-bg:color-mix(in srgb,var(--red,#ef4444) 8%,var(--surface));">
            <div class="ms-stat-icon"><i class='bx bx-error' style="font-size:1.3rem;"></i></div>
            <div>
                <div class="ms-stat-label">Missing Queues</div>
                <div class="ms-stat-value" style="color:var(--red,#ef4444);">{{ $missing->count() }}</div>
                <div class="ms-stat-meta">Customer exists, no queue</div>
            </div>
        </div>
    </div>

    {{-- Orphaned Queues --}}
    @if($orphaned->count() > 0)
    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title" style="color:var(--orange,#f97316);">
                    <i class='bx bx-ghost'></i> Orphaned Queues ({{ $orphaned->count() }})
                </h5>
                <div class="ms-panel-subtitle">Queue ada di router tapi customer tidak ditemukan di database</div>
            </div>
        </div>
        <div class="ms-panel-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="font-size:.75rem;">Queue Name</th>
                            <th style="font-size:.75rem;">Target</th>
                            <th style="font-size:.75rem;">Max-Limit</th>
                            <th style="font-size:.75rem;">Comment</th>
                            <th style="font-size:.75rem;" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orphaned as $q)
                        <tr>
                            <td style="font-size:.8125rem;"><strong>{{ $q['name'] ?? '-' }}</strong></td>
                            <td><code style="font-size:.75rem;">{{ $q['target'] ?? '-' }}</code></td>
                            <td style="font-size:.78rem;font-family:monospace;">{{ $q['max-limit'] ?? '-' }}</td>
                            <td style="font-size:.78rem;color:var(--txt-3);">{{ $q['comment'] ?? '-' }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.queues.destroy') }}" class="d-inline"
                                    onsubmit="return confirm('Delete orphaned queue {{ $q['name'] ?? '' }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="area_id" value="{{ $area->id }}">
                                    <input type="hidden" name="queue_id" value="{{ $q['.id'] ?? '' }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class='bx bx-trash'></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Missing Queues --}}
    @if($missing->count() > 0)
    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title" style="color:var(--red,#ef4444);">
                    <i class='bx bx-error'></i> Missing Queues ({{ $missing->count() }})
                </h5>
                <div class="ms-panel-subtitle">Customer punya IP dan paket tapi belum ada queue di router</div>
            </div>
        </div>
        <div class="ms-panel-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="font-size:.75rem;">Customer</th>
                            <th style="font-size:.75rem;">PPPoE User</th>
                            <th style="font-size:.75rem;">Remote IP</th>
                            <th style="font-size:.75rem;">Package</th>
                            <th style="font-size:.75rem;">Expected Speed</th>
                            <th style="font-size:.75rem;" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($missing as $customer)
                        <tr>
                            <td style="font-size:.8125rem;">
                                <a href="{{ route('admin.customers.show', $customer->id) }}" style="color:var(--blue);text-decoration:none;">
                                    {{ $customer->name }}
                                </a>
                            </td>
                            <td><code style="font-size:.75rem;">{{ $customer->pppoe_user }}</code></td>
                            <td><code style="font-size:.75rem;">{{ $customer->remote_ip }}</code></td>
                            <td style="font-size:.78rem;">{{ $customer->package->name ?? '-' }}</td>
                            <td style="font-size:.78rem;font-family:monospace;">
                                {{ $customer->package->speed_up ?? '?' }}M/{{ $customer->package->speed_down ?? '?' }}M
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.queues.store') }}" class="d-inline"
                                    onsubmit="return confirm('Create queue nk-{{ $customer->id }} for {{ $customer->name }}?')">
                                    @csrf
                                    <input type="hidden" name="area_id" value="{{ $area->id }}">
                                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                                    <input type="hidden" name="upload_speed" value="{{ $customer->package->speed_up ?? 5 }}">
                                    <input type="hidden" name="download_speed" value="{{ $customer->package->speed_down ?? 10 }}">
                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                        <i class='bx bx-plus'></i> Create Queue
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- All Good --}}
    @if($orphaned->count() === 0 && $missing->count() === 0)
    <div class="ms-panel">
        <div class="ms-panel-body text-center" style="padding:3rem;color:var(--green);">
            <i class='bx bx-check-circle' style="font-size:3rem;display:block;margin-bottom:.75rem;"></i>
            <h5 class="fw-bold mb-1">All Synced!</h5>
            <p class="mb-0" style="color:var(--txt-3);font-size:.875rem;">Semua customer yang memenuhi syarat sudah memiliki queue yang sesuai di router.</p>
        </div>
    </div>
    @endif
</div>
@endsection
