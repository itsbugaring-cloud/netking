@extends('layouts.app')
@section('title', 'Perangkat OLT')

@section('styles')
<style>
    /* ── Kanban grid ── */
    .olt-kanban {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: .85rem;
    }

    /* ── OLT Card ── */
    .olt-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1rem;
        display: flex; flex-direction: column; gap: .6rem;
        transition: box-shadow .15s, border-color .15s;
        position: relative;
    }
    .olt-card:hover { border-color: color-mix(in srgb, var(--blue) 40%, var(--border)); box-shadow: 0 4px 16px rgba(0,0,0,.08); }
    .olt-card-link {
        position: absolute; inset: 0; border-radius: 12px; z-index: 1;
    }
    .olt-card-name {
        font-size: .875rem; font-weight: 600; color: var(--txt);
        display: flex; align-items: center; gap: .4rem;
    }
    .olt-card-meta { font-size: .75rem; color: var(--txt-3); }
    .olt-card-ip {
        font-size: .7rem; font-family: monospace;
        background: color-mix(in srgb, var(--orange) 10%, var(--surface-2));
        color: var(--orange); padding: .15rem .5rem; border-radius: 5px;
        border: 1px solid color-mix(in srgb, var(--orange) 20%, var(--border));
        display: inline-block; width: fit-content;
    }
    .olt-card-row {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: .4rem;
    }
    .olt-card-stats {
        display: flex; align-items: center; gap: .6rem;
    }
    .olt-stat { font-size: .78rem; font-weight: 600; display: flex; align-items: center; gap: .18rem; }
    .olt-card-area {
        font-size: .68rem; color: var(--txt-3);
        display: flex; align-items: center; gap: .25rem;
    }
    .olt-card-actions {
        display: flex; gap: .3rem; padding-top: .6rem;
        border-top: 1px solid var(--border);
        position: relative; z-index: 2;
    }
    .olt-card-btn {
        flex: 1; display: inline-flex; align-items: center; justify-content: center;
        height: 30px; border-radius: 7px; font-size: .78rem;
        border: 1px solid var(--border); background: transparent; color: var(--txt-3);
        cursor: pointer; text-decoration: none; transition: all .12s;
    }
    .olt-card-btn:hover { background: var(--surface-2); color: var(--txt); }
    .olt-card-btn--danger:hover {
        background: color-mix(in srgb, var(--red) 8%, var(--surface));
        color: var(--red); border-color: color-mix(in srgb, var(--red) 28%, var(--border));
    }

    /* ── Custom tooltip ── */
    [data-tip] { position: relative; }
    [data-tip]::after {
        content: attr(data-tip);
        position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%);
        background: #18181b; color: #f4f4f5;
        font-size: .7rem; font-weight: 500; white-space: nowrap;
        padding: .3rem .55rem; border-radius: 6px;
        pointer-events: none; opacity: 0; transition: opacity .15s;
        z-index: 100;
    }
    [data-tip]::before {
        content: '';
        position: absolute; bottom: calc(100% + 2px); left: 50%; transform: translateX(-50%);
        border: 4px solid transparent; border-top-color: #18181b;
        pointer-events: none; opacity: 0; transition: opacity .15s;
        z-index: 100;
    }
    [data-tip]:hover::after, [data-tip]:hover::before { opacity: 1; }
    .olt-health-bar {
        height: 4px; border-radius: 999px; background: var(--surface-2); overflow: hidden; margin-top: .2rem;
    }
    .olt-health-fill { height: 100%; background: var(--green); border-radius: 999px; }
</style>
@endsection

@section('content')
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-server'></i> Inventaris Fiber</div>
            <h1 class="ms-page-title">Perangkat OLT</h1>
        </div>
        <div class="ms-page-actions">
            <span id="olt-ar-chip" style="display:inline-flex;align-items:center;gap:.35rem;font-size:.78rem;padding:.28rem .75rem;border-radius:999px;background:color-mix(in srgb,var(--orange) 12%,var(--surface));color:var(--orange);border:1px solid color-mix(in srgb,var(--orange) 25%,var(--border));cursor:pointer;" onclick="oltArToggle()" title="Klik untuk matikan auto-refresh">
                <i class='bx bx-time-five'></i>
                <span id="olt-ar-label">Refresh dalam 30d</span>
            </span>
            <a href="{{ route('admin.olts.monitor') }}" class="ms-btn-secondary">
                <i class='bx bx-broadcast'></i> Pantau
            </a>
            <a href="{{ route('admin.olts.create') }}" class="ms-btn">
                <i class='bx bx-plus'></i> Tambah OLT
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success mb-3" style="border-radius:.5rem;">
        <i class='bx bx-check-circle me-2'></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger mb-3" style="border-radius:.5rem;">
        <i class='bx bx-error-circle me-2'></i>{{ session('error') }}
    </div>
    @endif

    @php
        $totalOnts  = $olts->sum(fn($o) => $o->onts->count());
        $onlineOnts = $olts->sum(fn($o) => $o->onts->where('status','online')->count());
        $offlineOnts= $totalOnts - $onlineOnts;
        $onlinePct  = $totalOnts > 0 ? round($onlineOnts / $totalOnts * 100) : 0;
    @endphp

    {{-- Stats --}}
    <div class="ms-stat-grid">
        <div class="ms-stat-card" style="--stat-accent:var(--nk-info);--stat-bg:color-mix(in srgb,var(--nk-info) 10%,var(--surface));">
            <div class="ms-stat-icon"><i class='bx bx-server' style="font-size:1.3rem;"></i></div>
            <div>
                <div class="ms-stat-label">Total OLT</div>
                <div class="ms-stat-value">{{ $olts->count() }}</div>
                <div class="ms-stat-meta">Kepala jaringan fiber</div>
            </div>
        </div>
        <div class="ms-stat-card" style="--stat-accent:var(--nk-success);--stat-bg:color-mix(in srgb,var(--nk-success) 10%,var(--surface));">
            <div class="ms-stat-icon"><i class='bx bx-chip' style="font-size:1.3rem;"></i></div>
            <div>
                <div class="ms-stat-label">Total ONT</div>
                <div class="ms-stat-value">{{ $totalOnts }}</div>
                <div class="ms-stat-meta">{{ $onlinePct }}% online</div>
            </div>
            <div class="ms-auto" style="width:72px;">
                <div class="olt-health-bar"><div class="olt-health-fill" style="width:{{ $onlinePct }}%;"></div></div>
            </div>
        </div>
        <div class="ms-stat-card" style="--stat-accent:var(--nk-success);--stat-bg:color-mix(in srgb,var(--nk-success) 10%,var(--surface));">
            <div class="ms-stat-icon"><i class='bx bx-signal-5' style="font-size:1.3rem;"></i></div>
            <div>
                <div class="ms-stat-label">ONT Online</div>
                <div class="ms-stat-value" style="color:var(--green);">{{ $onlineOnts }}</div>
                <div class="ms-stat-meta">{{ $offlineOnts }} offline</div>
            </div>
        </div>
    </div>

    {{-- Kanban Grid --}}
    <div class="olt-kanban mt-3">
        @forelse($olts as $olt)
        @php
            $oltTotal   = $olt->onts->count();
            $oltOnline  = $olt->onts->where('status','online')->count();
            $oltOffline = $oltTotal - $oltOnline;
            $oltPct     = $oltTotal > 0 ? round($oltOnline / $oltTotal * 100) : 0;
            $proto      = strtolower($olt->preferred_protocol);
        @endphp
        <div class="olt-card">
            {{-- Invisible full-card link --}}
            <a href="{{ route('admin.olts.show', $olt) }}" class="olt-card-link" aria-label="{{ $olt->name }}"></a>

            <div class="olt-card-name">
                <i class='bx bx-server' style="color:var(--orange);font-size:1rem;flex-shrink:0;"></i>
                {{ $olt->name }}
            </div>

            <div class="olt-card-meta">{{ $olt->brand }} {{ $olt->model }}</div>

            <div class="olt-card-ip">{{ $olt->ip_address }}</div>

            <div class="olt-card-row">
                <span class="nk-badge nk-badge-sm {{ $proto === 'snmp' ? 'nk-badge-purple' : ($proto === 'ssh' ? 'nk-badge-cyan' : 'nk-badge-blue') }}">
                    {{ $olt->preferred_protocol }}
                </span>
                <div class="olt-card-stats">
                    <span class="olt-stat" style="color:var(--green);"><i class='bx bx-wifi' style="font-size:.85rem;"></i>{{ $oltOnline }}</span>
                    <span class="olt-stat" style="color:var(--red);"><i class='bx bx-wifi-off' style="font-size:.85rem;"></i>{{ $oltOffline }}</span>
                    <span class="olt-stat" style="color:var(--txt-3);">/ {{ $oltTotal }}</span>
                </div>
            </div>

            <div>
                <div class="olt-health-bar"><div class="olt-health-fill" style="width:{{ $oltPct }}%;"></div></div>
            </div>

            <div class="olt-card-area">
                <i class='bx bx-map-pin' style="font-size:.8rem;"></i>
                {{ $olt->area->name ?? '—' }}
            </div>

            <div class="olt-card-actions">
                <a href="{{ route('admin.olts.show', $olt) }}" class="olt-card-btn" data-tip="Lihat ONT">
                    <i class='bx bx-chip'></i>
                </a>
                <form action="{{ route('admin.olts.sync', $olt) }}" method="POST" style="flex:1;">
                    @csrf
                    <button type="submit" class="olt-card-btn" style="width:100%;" data-tip="Sinkronisasi ONT">
                        <i class='bx bx-refresh'></i>
                    </button>
                </form>
                <a href="{{ route('admin.olts.edit', $olt) }}" class="olt-card-btn" data-tip="Edit OLT">
                    <i class='bx bx-edit-alt'></i>
                </a>
                <form action="{{ route('admin.olts.destroy', $olt) }}" method="POST" style="flex:1;"
                    data-confirm="Hapus OLT {{ $olt->name }} beserta semua data ONT?">
                    @csrf @method('DELETE')
                    <button type="submit" class="olt-card-btn olt-card-btn--danger" style="width:100%;" data-tip="Hapus OLT">
                        <i class='bx bx-trash'></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--txt-3);">
            <i class='bx bx-server' style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
            <p class="mb-1 fw-semibold">Belum ada perangkat OLT</p>
            <a href="{{ route('admin.olts.create') }}" class="ms-btn mt-2">
                <i class='bx bx-plus'></i> Tambah OLT pertama Anda
            </a>
        </div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        $('form[data-confirm]').on('submit', function(e) {
            if (!confirm($(this).data('confirm'))) e.preventDefault();
        });
    });

    // ── Auto-refresh OLT index ─────────────────────────────────────────────
    var oltArSecs     = 30;
    var oltArCurrent  = oltArSecs;
    var oltArActive   = true;
    var oltArTimer    = null;

    function oltArTick() {
        oltArCurrent--;
        var label = document.getElementById('olt-ar-label');
        var chip  = document.getElementById('olt-ar-chip');
        if (label) label.textContent = 'Refresh dalam ' + oltArCurrent + 'd';
        if (oltArCurrent <= 0) {
            clearInterval(oltArTimer);
            location.reload();
        }
    }

    window.oltArToggle = function() {
        var label = document.getElementById('olt-ar-label');
        var chip  = document.getElementById('olt-ar-chip');
        if (oltArActive) {
            clearInterval(oltArTimer);
            oltArTimer  = null;
            oltArActive = false;
            oltArCurrent = oltArSecs;
            if (label) label.textContent = 'Auto-refresh: OFF';
            if (chip)  chip.style.background = 'var(--surface)';
            if (chip)  chip.style.color = 'var(--txt-3)';
            if (chip)  chip.style.borderColor = 'var(--border)';
        } else {
            oltArActive  = true;
            oltArCurrent = oltArSecs;
            oltArTimer   = setInterval(oltArTick, 1000);
            if (chip)  chip.style.background = '';
            if (chip)  chip.style.color = 'var(--orange)';
            if (chip)  chip.style.borderColor = '';
            oltArTick();
        }
    };

    // Start immediately
    document.addEventListener('DOMContentLoaded', function() {
        oltArTimer = setInterval(oltArTick, 1000);
        oltArTick();
    });
</script>
@endsection
