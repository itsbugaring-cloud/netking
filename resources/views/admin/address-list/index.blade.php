@extends('layouts.app')

@section('title', 'Address List — Isolir')

@section('content')
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-shield-quarter'></i> Firewall</div>
            <h1 class="ms-page-title">Address List — Isolir</h1>
        </div>
    </div>

    {{-- ── Area Selector ── --}}
    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title">Pilih Router / Area</h5>
                <div class="ms-panel-subtitle">Pilih area untuk melihat dan mengelola address-list isolir</div>
            </div>
        </div>
        <div class="ms-panel-body">
            <form method="GET" action="{{ route('admin.address-list.index') }}" class="d-flex align-items-end gap-3 flex-wrap">
                <div style="min-width:240px;">
                    <label class="form-label fw-semibold" style="font-size:.8rem;">Area / Router</label>
                    <select name="area_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">— Pilih Area —</option>
                        @foreach($areas as $area)
                        <option value="{{ $area->id }}" {{ ($selectedArea?->id == $area->id) ? 'selected' : '' }}>
                            {{ $area->name }} ({{ $area->router_ip }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if($error)
    <div class="ms-panel" style="border-color:color-mix(in srgb,var(--red) 25%,var(--border));background:color-mix(in srgb,var(--red) 6%,var(--surface));">
        <div class="ms-panel-body d-flex align-items-start gap-2" style="color:var(--red);">
            <i class='bx bx-error-circle mt-1' style="font-size:1.1rem;"></i>
            <div>
                <strong>Tidak Dapat Terhubung ke Router</strong><br>
                {{ $error }}<br>
                <small style="color:color-mix(in srgb,var(--red) 80%,var(--txt));">Pastikan router MikroTik aktif, API port 8728 terbuka, dan kredensial benar.</small>
            </div>
        </div>
    </div>
    @endif

    @if($selectedArea && !$error)

    {{-- ── Action Buttons ── --}}
    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title">Aksi</h5>
                <div class="ms-panel-subtitle">{{ $selectedArea->name }} — {{ count($entries) }} entri di list "{{ $listName }}"</div>
            </div>
            <div class="ms-toolbar-right d-flex gap-2 flex-wrap">
                {{-- Sync Status --}}
                <form method="POST" action="{{ route('admin.address-list.sync') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="area_id" value="{{ $selectedArea->id }}">
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class='bx bx-refresh'></i> Sync Status
                    </button>
                </form>
                {{-- Bulk Isolir --}}
                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#bulkIsolirModal">
                    <i class='bx bx-bolt-circle'></i> Bulk Isolir Overdue
                </button>
                {{-- Add Single --}}
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#isolateModal">
                    <i class='bx bx-plus'></i> Isolir Customer
                </button>
            </div>
        </div>
    </div>

    {{-- ── Address List Table ── --}}
    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title">Daftar Address-List "{{ $listName }}"</h5>
            </div>
        </div>
        <div class="ms-panel-body p-0">
            @if(count($entries) === 0)
            <div style="text-align:center;padding:3rem;color:var(--txt-3);">
                <i class='bx bx-list-ul' style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                <p class="mb-0 fw-semibold">Address-list kosong</p>
                <small>Belum ada IP yang di-isolir di router ini.</small>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="table-layout:auto;">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>IP Address</th>
                            <th>Customer</th>
                            <th>Timeout</th>
                            <th>Comment</th>
                            <th>Creation Time</th>
                            <th style="width:100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $i => $entry)
                        @php $cust = $entry['_customer'] ?? null; @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <code style="font-size:.8rem;">{{ $entry['address'] ?? '-' }}</code>
                            </td>
                            <td>
                                @if($cust)
                                <a href="{{ route('admin.customers.show', $cust->id) }}" style="font-weight:600;font-size:.85rem;">
                                    {{ $cust->name }}
                                </a>
                                <div style="font-size:.72rem;color:var(--txt-3);">{{ $cust->pppoe_user }}</div>
                                @else
                                <span style="color:var(--txt-3);font-size:.8rem;">—</span>
                                @endif
                            </td>
                            <td style="font-size:.8rem;">{{ $entry['timeout'] ?? '—' }}</td>
                            <td style="font-size:.8rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;">{{ $entry['comment'] ?? '—' }}</td>
                            <td style="font-size:.8rem;">{{ $entry['creation-time'] ?? '—' }}</td>
                            <td>
                                @if($cust)
                                <form method="POST" action="{{ route('admin.address-list.deisolate') }}" class="d-inline"
                                    onsubmit="return confirm('De-isolir {{ $cust->name }}?');">
                                    @csrf
                                    <input type="hidden" name="customer_id" value="{{ $cust->id }}">
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="De-isolir">
                                        <i class='bx bx-check'></i>
                                    </button>
                                </form>
                                @else
                                <span style="color:var(--txt-3);font-size:.75rem;">Manual</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Modal: Isolate Single Customer ── --}}
    <div class="modal fade" id="isolateModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.address-list.isolate') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Isolir Customer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Customer</label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">— Pilih Customer —</option>
                                @foreach(\App\Models\Customer::where('area_id', $selectedArea->id)->whereNotNull('remote_ip')->where('remote_ip','!=','')->where('is_isolated', false)->orderBy('name')->get() as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} — {{ $c->remote_ip }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Timeout <small class="text-muted">(opsional, contoh: 7d, 12h)</small></label>
                            <input type="text" name="timeout" class="form-control" placeholder="Kosongkan = permanen" maxlength="10">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class='bx bx-shield-quarter'></i> Isolir
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Modal: Bulk Isolir ── --}}
    <div class="modal fade" id="bulkIsolirModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.address-list.bulk-isolate') }}">
                @csrf
                <input type="hidden" name="area_id" value="{{ $selectedArea->id }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bulk Isolir — Customer Overdue</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning" style="font-size:.85rem;">
                            <i class='bx bx-info-circle'></i>
                            Ini akan menambahkan semua customer <strong>{{ $selectedArea->name }}</strong> yang memiliki tagihan belum dibayar
                            melewati grace period ({{ config('netking.isolir_grace_days', 7) }} hari) ke address-list "{{ $listName }}".
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Timeout <small class="text-muted">(opsional)</small></label>
                            <input type="text" name="timeout" class="form-control" placeholder="Kosongkan = permanen" maxlength="10">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class='bx bx-bolt-circle'></i> Jalankan Bulk Isolir
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @endif
</div>
@endsection
