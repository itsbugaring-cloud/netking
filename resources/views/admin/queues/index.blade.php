@extends('layouts.app')
@section('title', 'Simple Queue Management')

@section('styles')
<style>
    .queue-table th { font-size: .75rem; text-transform: uppercase; color: var(--txt-3); font-weight: 600; }
    .queue-table td { font-size: .8125rem; vertical-align: middle; }
    .queue-speed { font-family: monospace; font-size: .75rem; }
    .queue-speed-up { color: var(--blue); }
    .queue-speed-down { color: var(--green); }
    .queue-badge { display: inline-flex; align-items: center; gap: .2rem; font-size: .7rem; padding: .15rem .5rem; border-radius: 999px; font-weight: 600; }
    .queue-badge-enabled { background: color-mix(in srgb, var(--green) 12%, var(--surface)); color: var(--green); border: 1px solid color-mix(in srgb, var(--green) 25%, var(--border)); }
    .queue-badge-disabled { background: color-mix(in srgb, var(--red) 12%, var(--surface)); color: var(--red); border: 1px solid color-mix(in srgb, var(--red) 25%, var(--border)); }
    .queue-customer-link { font-size: .78rem; color: var(--blue); text-decoration: none; }
    .queue-customer-link:hover { text-decoration: underline; }
    .router-kanban { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: .75rem; }
    .router-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: .9rem 1rem; display: flex; flex-direction: column; gap: .45rem; cursor: pointer; text-decoration: none; transition: box-shadow .15s, border-color .15s; }
    .router-card:hover { border-color: color-mix(in srgb, var(--blue) 45%, var(--border)); box-shadow: 0 4px 16px rgba(0,0,0,.08); text-decoration: none; }
    .router-card--active { border-color: var(--blue) !important; background: color-mix(in srgb, var(--blue) 6%, var(--surface)); box-shadow: 0 0 0 3px color-mix(in srgb, var(--blue) 18%, transparent); }
    .router-card-name { font-size: .875rem; font-weight: 700; color: var(--txt); display: flex; align-items: center; gap: .4rem; }
    .router-card-ip { font-size: .7rem; font-family: monospace; background: color-mix(in srgb, var(--orange) 10%, var(--surface-2)); color: var(--orange); padding: .12rem .45rem; border-radius: 5px; border: 1px solid color-mix(in srgb, var(--orange) 20%, var(--border)); display: inline-block; width: fit-content; }
    .router-card-active-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--blue); flex-shrink: 0; }
</style>
@endsection

@section('content')
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-tachometer'></i> Bandwidth Control</div>
            <h1 class="ms-page-title">Simple Queue</h1>
        </div>
        @if($selectedArea)
        <div class="ms-page-actions">
            <a href="{{ route('admin.queues.sync', ['area_id' => $selectedArea->id]) }}" class="ms-btn-secondary">
                <i class='bx bx-sync'></i> Sync Check
            </a>
            <a href="{{ route('admin.queues.create', ['area_id' => $selectedArea->id]) }}" class="ms-btn">
                <i class='bx bx-plus'></i> Create Queue
            </a>
        </div>
        @endif
    </div>

    {{-- Router / Area Selector --}}
    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title">Pilih Router / Area</h5>
                <div class="ms-panel-subtitle">Pilih area untuk melihat simple queue pada router</div>
            </div>
        </div>
        <div class="ms-panel-body">
            <div class="router-kanban">
                @foreach($areas as $area)
                @php $isActive = $selectedArea?->id == $area->id; @endphp
                <a href="{{ route('admin.queues.index', ['area_id' => $area->id]) }}"
                   class="router-card {{ $isActive ? 'router-card--active' : '' }}">
                    <div class="router-card-name">
                        @if($isActive)
                            <div class="router-card-active-dot"></div>
                        @else
                            <i class='bx bx-router' style="color:var(--txt-3);font-size:.95rem;flex-shrink:0;"></i>
                        @endif
                        {{ $area->name }}
                    </div>
                    <div class="router-card-ip">{{ $area->router_ip }}</div>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Error --}}
    @if($error)
    <div class="ms-panel" style="border-color:color-mix(in srgb,var(--red) 25%,var(--border));background:color-mix(in srgb,var(--red) 6%,var(--surface));">
        <div class="ms-panel-body d-flex align-items-start gap-2" style="color:var(--red);">
            <i class='bx bx-error-circle mt-1' style="font-size:1.1rem;"></i>
            <div>
                <strong>Tidak Dapat Terhubung ke Router</strong><br>
                {{ $error }}<br>
                <small>Pastikan router MikroTik aktif, API port 8728 terbuka, dan kredensial benar.</small>
            </div>
        </div>
    </div>
    @endif

    {{-- Queue Table --}}
    @if($selectedArea && !$error)
    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title">Simple Queues — {{ $selectedArea->name }}</h5>
                <div class="ms-panel-subtitle">{{ count($queues) }} queue ditemukan</div>
            </div>
        </div>
        <div class="ms-panel-body p-0">
            @if(count($queues) === 0)
            <div style="text-align:center;padding:3rem;color:var(--txt-3);">
                <i class='bx bx-list-ul' style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                <p class="mb-0 fw-semibold">Tidak ada simple queue pada router ini</p>
                <p class="mb-0 mt-1" style="font-size:.8rem;">Klik "Create Queue" untuk menambahkan queue baru.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover queue-table mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Target</th>
                            <th>Customer</th>
                            <th>Max-Limit</th>
                            <th>Burst</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($queues as $q)
                        @php
                            $isDisabled = ($q['disabled'] ?? 'false') === 'true';
                            $customer = $q['_customer'] ?? null;
                            $maxLimit = $q['max-limit'] ?? '-';
                            $burstLimit = $q['burst-limit'] ?? '';
                            $burstThreshold = $q['burst-threshold'] ?? '';
                            $burstTime = $q['burst-time'] ?? '';

                            // Parse speeds for edit modal
                            $upSpeed = 0; $downSpeed = 0;
                            if (preg_match('/(\d+)[Mm]\/(\d+)[Mm]/', $maxLimit, $sm)) {
                                $upSpeed = (int)$sm[1];
                                $downSpeed = (int)$sm[2];
                            }
                        @endphp
                        <tr>
                            <td>
                                <strong style="font-size:.8125rem;">{{ $q['name'] ?? '-' }}</strong>
                                @if($q['comment'] ?? '')
                                <br><small style="color:var(--txt-3);">{{ $q['comment'] }}</small>
                                @endif
                            </td>
                            <td><code style="font-size:.75rem;">{{ $q['target'] ?? '-' }}</code></td>
                            <td>
                                @if($customer)
                                <a href="{{ route('admin.customers.show', $customer->id) }}" class="queue-customer-link">
                                    {{ $customer->name }}
                                </a>
                                @else
                                <span style="color:var(--txt-3);font-size:.78rem;">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="queue-speed">
                                    <span class="queue-speed-up">↑ {{ explode('/', $maxLimit)[0] ?? '-' }}</span>
                                    /
                                    <span class="queue-speed-down">↓ {{ explode('/', $maxLimit)[1] ?? '-' }}</span>
                                </span>
                            </td>
                            <td>
                                @if($burstLimit)
                                <span class="queue-speed" style="font-size:.7rem;">{{ $burstLimit }}</span>
                                @else
                                <span style="color:var(--txt-3);font-size:.72rem;">—</span>
                                @endif
                            </td>
                            <td>
                                @if($isDisabled)
                                <span class="queue-badge queue-badge-disabled"><i class='bx bx-pause-circle'></i> Disabled</span>
                                @else
                                <span class="queue-badge queue-badge-enabled"><i class='bx bx-check-circle'></i> Active</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                    onclick="openEditModal('{{ $q['.id'] ?? '' }}', '{{ $q['name'] ?? '' }}', {{ $upSpeed }}, {{ $downSpeed }})"
                                    title="Edit Speed">
                                    <i class='bx bx-edit-alt'></i>
                                </button>
                                <form method="POST" action="{{ route('admin.queues.destroy') }}" class="d-inline"
                                    onsubmit="return confirm('Delete queue {{ $q['name'] ?? '' }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="area_id" value="{{ $selectedArea->id }}">
                                    <input type="hidden" name="queue_id" value="{{ $q['.id'] ?? '' }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Edit Modal --}}
@if($selectedArea)
<div class="modal fade" id="editQueueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.queues.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="area_id" value="{{ $selectedArea->id }}">
            <input type="hidden" name="queue_id" id="edit_queue_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Queue: <span id="edit_queue_name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Upload Speed (Mbps)</label>
                            <input type="number" name="upload_speed" id="edit_upload_speed" class="form-control" min="1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Download Speed (Mbps)</label>
                            <input type="number" name="download_speed" id="edit_download_speed" class="form-control" min="1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Burst Upload (Mbps)</label>
                            <input type="number" name="burst_upload" id="edit_burst_upload" class="form-control" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Burst Download (Mbps)</label>
                            <input type="number" name="burst_download" id="edit_burst_download" class="form-control" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Burst Threshold Up (Mbps)</label>
                            <input type="number" name="burst_threshold_up" id="edit_burst_threshold_up" class="form-control" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Burst Threshold Down (Mbps)</label>
                            <input type="number" name="burst_threshold_down" id="edit_burst_threshold_down" class="form-control" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> Update Queue</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
function openEditModal(queueId, queueName, uploadSpeed, downloadSpeed) {
    document.getElementById('edit_queue_id').value = queueId;
    document.getElementById('edit_queue_name').textContent = queueName;
    document.getElementById('edit_upload_speed').value = uploadSpeed;
    document.getElementById('edit_download_speed').value = downloadSpeed;
    document.getElementById('edit_burst_upload').value = '';
    document.getElementById('edit_burst_download').value = '';
    document.getElementById('edit_burst_threshold_up').value = '';
    document.getElementById('edit_burst_threshold_down').value = '';
    var modal = new bootstrap.Modal(document.getElementById('editQueueModal'));
    modal.show();
}
</script>
@endsection
