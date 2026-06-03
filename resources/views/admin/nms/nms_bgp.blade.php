@extends('layouts.app')
@section('title', 'NMS BGP Monitor')

@section('content')
<div class="page-header mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            <i class='bx bx-git-branch me-2' style="color:var(--orange);"></i>BGP Monitor
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.nms.dashboard') }}">NMS</a></li>
                <li class="breadcrumb-item active">BGP Monitor</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span id="lastUpdate" style="font-size:.8rem;color:var(--text-muted);">Memuat...</span>
        <button class="btn btn-sm" id="refreshBtn"
                style="background:var(--orange);color:#fff;font-size:.78rem;padding:.25rem .75rem;">
            <i class='bx bx-refresh me-1'></i>Refresh
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="stat-grid mb-4">
    <div class="stat-card">
        <div>
            <div class="stat-label">Router dengan BGP</div>
            <div class="stat-value" id="sumRouters" style="color:var(--orange);">—</div>
            <div class="stat-change neutral">Router aktif</div>
        </div>
        <div class="stat-icon si-orange"><i class='bx bx-server'></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Total BGP Peers</div>
            <div class="stat-value" id="sumPeers" style="color:#3b82f6;">—</div>
            <div class="stat-change neutral">Semua router</div>
        </div>
        <div class="stat-icon si-blue"><i class='bx bx-git-branch'></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Peers Established</div>
            <div class="stat-value" id="sumEstablished" style="color:var(--green);">—</div>
            <div class="stat-change neutral">Session aktif</div>
        </div>
        <div class="stat-icon si-green"><i class='bx bx-check-circle'></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Peers Down</div>
            <div class="stat-value" id="sumDown" style="color:var(--red,#ef4444);">—</div>
            <div class="stat-change neutral">Tidak established</div>
        </div>
        <div class="stat-icon" style="background:rgba(239,68,68,.12);color:#ef4444;font-size:1.6rem;width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;">
            <i class='bx bx-error-circle'></i>
        </div>
    </div>
</div>

<!-- Loading -->
<div id="loadingState" class="text-center py-5 text-muted">
    <i class='bx bx-loader-alt bx-spin' style="font-size:2rem;"></i>
    <div style="margin-top:.5rem;">Mengambil status BGP dari semua router...</div>
</div>

<!-- Area Results -->
<div id="areaGrid" class="row g-3" style="display:none;"></div>

<style>
.bgp-peer-row { border-bottom: 1px solid var(--border-color, #e5e7eb); padding: .4rem 0; }
.bgp-peer-row:last-child { border-bottom: none; }
.peer-state-established { color: #22c55e; font-weight: 700; }
.peer-state-other { color: #ef4444; }
.si-blue { background:rgba(59,130,246,.12); color:#3b82f6; font-size:1.6rem; width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; }
</style>

<script>
const BGP_DATA_URL = '{{ route("admin.nms.bgp.data") }}';

async function fetchData() {
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('areaGrid').style.display     = 'none';

    try {
        const res  = await fetch(BGP_DATA_URL);
        const json = await res.json();
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('areaGrid').style.display     = '';
        document.getElementById('lastUpdate').textContent     = 'Update: ' + (json.updated_at || '—');
        renderData(json.data || []);
    } catch (e) {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('areaGrid').innerHTML =
            '<div class="col-12"><div class="alert alert-danger"><i class=\'bx bx-error-circle me-2\'></i>Gagal memuat: ' + e.message + '</div></div>';
        document.getElementById('areaGrid').style.display = '';
    }
}

function isEstablished(state) {
    if (!state) return false;
    const s = String(state).toLowerCase();
    return s === 'established' || s === 'true' || s === '1';
}

function renderData(rows) {
    const grid = document.getElementById('areaGrid');
    grid.innerHTML = '';

    let sumRouters = 0, sumPeers = 0, sumEstablished = 0, sumDown = 0;

    rows.forEach(row => {
        if (!row.online) return;
        if (!row.peers || !row.peers.length) return;

        sumRouters++;
        sumPeers       += row.peers.length;
        sumEstablished += row.peers.filter(p => isEstablished(p.state)).length;
        sumDown        += row.peers.filter(p => !isEstablished(p.state) && !p.disabled).length;

        let peersHtml = '';

        row.peers.forEach(p => {
            const established = isEstablished(p.state);
            const stateLabel  = established ? 'Established' : (p.disabled ? 'Disabled' : String(p.state || 'Unknown'));
            const stateClass  = established ? 'peer-state-established' : 'peer-state-other';
            const stateIcon   = established ? 'bx-check-circle' : 'bx-x-circle';
            const stateColor  = established ? '#22c55e' : (p.disabled ? '#9ca3af' : '#ef4444');

            peersHtml += '<div class="bgp-peer-row">'
                + '<div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;">'
                + '<div style="flex:1;min-width:0;">'
                + '<div style="font-weight:600;font-size:.8rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + (p.name || '—') + '</div>'
                + '<div style="font-size:.68rem;color:var(--text-muted);">'
                + 'Remote: <code style="font-size:.68rem;">' + (p.remote_address || '—') + '</code>'
                + (p.remote_as !== '—' ? ' &nbsp;AS ' + p.remote_as : '')
                + '</div>'
                + '</div>'
                + '<div style="text-align:right;flex-shrink:0;">'
                + '<div><i class=\'bx ' + stateIcon + ' me-1\' style="color:' + stateColor + ';"></i>'
                + '<span class="' + stateClass + '" style="font-size:.75rem;color:' + stateColor + ';">' + stateLabel + '</span></div>'
                + '<div style="font-size:.65rem;color:var(--text-muted);">'
                + (established && p.uptime !== '—' ? 'Up: ' + p.uptime : '')
                + (p.prefix_count > 0 ? ' &nbsp;Prefixes: ' + p.prefix_count : '')
                + '</div>'
                + '</div>'
                + '</div>'
                + '</div>';
        });

        const allOk        = row.peers.every(p => isEstablished(p.state) || p.disabled);
        const hasProblem   = row.peers.some(p => !isEstablished(p.state) && !p.disabled);
        const headerColor  = hasProblem ? '#ef4444' : '#22c55e';

        const cardHtml = '<div class="col-xl-4 col-lg-6">'
            + '<div class="card h-100">'
            + '<div class="card-body p-3">'
            + '<div class="d-flex align-items-start justify-content-between mb-2">'
            + '<div>'
            + '<div style="font-weight:700;font-size:.9rem;">' + row.area_name + '</div>'
            + '<code style="font-size:.72rem;color:var(--text-muted);">' + row.router_ip + '</code>'
            + '</div>'
            + '<div style="text-align:right;">'
            + '<span class="badge" style="background:rgba(34,197,94,.15);color:#22c55e;font-size:.7rem;">ONLINE</span>'
            + '<div style="font-size:.68rem;color:' + headerColor + ';margin-top:.15rem;">'
            + row.peers.filter(p => isEstablished(p.state)).length + '/' + row.peers.length + ' peers OK'
            + '</div>'
            + '</div>'
            + '</div>'
            + peersHtml
            + '</div>'
            + '</div>'
            + '</div>';

        grid.insertAdjacentHTML('beforeend', cardHtml);
    });

    // Offline / No-BGP routers
    const noBgpRows = rows.filter(r => !r.online || !r.peers || !r.peers.length);
    if (noBgpRows.length > 0) {
        let noBgpHtml = '<div class="col-12"><div class="card"><div class="card-body p-3">'
            + '<div style="font-weight:600;margin-bottom:.5rem;font-size:.85rem;color:var(--text-muted);">'
            + '<i class=\'bx bx-info-circle me-1\'></i>Router tanpa BGP / Offline</div>'
            + '<div class="row g-2">';

        noBgpRows.forEach(row => {
            const offline = !row.online;
            const color   = offline ? '#ef4444' : '#9ca3af';
            const label   = offline ? 'OFFLINE' : 'No BGP';
            noBgpHtml += '<div class="col-auto">'
                + '<span style="font-size:.75rem;padding:.25rem .55rem;border-radius:4px;background:rgba(0,0,0,.05);color:' + color + ';">'
                + '<i class=\'bx ' + (offline ? 'bx-wifi-off' : 'bx-minus-circle') + ' me-1\'></i>'
                + row.area_name + ' <span style="font-size:.65rem;opacity:.7;">(' + label + ')</span>'
                + '</span></div>';
        });

        noBgpHtml += '</div></div></div></div>';
        grid.insertAdjacentHTML('beforeend', noBgpHtml);
    }

    document.getElementById('sumRouters').textContent    = sumRouters;
    document.getElementById('sumPeers').textContent      = sumPeers;
    document.getElementById('sumEstablished').textContent = sumEstablished;
    document.getElementById('sumDown').textContent       = sumDown;
}

document.getElementById('refreshBtn').addEventListener('click', fetchData);

// Auto-refresh every 30s
setInterval(fetchData, 30000);
fetchData();
</script>
@endsection
