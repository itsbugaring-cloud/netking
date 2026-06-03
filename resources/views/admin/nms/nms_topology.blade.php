@extends('layouts.app')
@section('title', 'NMS Topology')

@section('content')
<div class="page-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1"><i class='bx bx-network-chart me-2' style="color:var(--orange);"></i>Network Topology</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.nms.dashboard') }}">NMS</a></li>
                <li class="breadcrumb-item active">Topology</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span id="topoStatus" style="font-size:.75rem;color:var(--text-muted);">Memuat...</span>
        <button class="btn btn-sm" onclick="loadTopology()" style="background:rgba(249,115,22,.12);color:var(--orange);border:1px solid var(--orange);font-size:.75rem;">
            <i class='bx bx-refresh me-1'></i>Refresh
        </button>
        <button class="btn btn-sm" onclick="network && network.fit()" style="background:var(--bg-card-alt);border:1px solid var(--border-color);font-size:.75rem;">
            <i class='bx bx-fullscreen me-1'></i>Fit
        </button>
    </div>
</div>

<div class="row g-3">
    <!-- Legend & Stats -->
    <div class="col-12">
        <div class="card">
            <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center gap-3">
                <span style="font-size:.75rem;font-weight:600;color:var(--text-muted);">LEGEND:</span>
                <span class="d-flex align-items-center gap-1" style="font-size:.75rem;">
                    <span style="width:14px;height:14px;border-radius:50%;background:#6366f1;display:inline-block;"></span> VPS Hub
                </span>
                <span class="d-flex align-items-center gap-1" style="font-size:.75rem;">
                    <span style="width:14px;height:14px;border-radius:50%;background:var(--orange);display:inline-block;"></span> Router MikroTik
                </span>
                <span class="d-flex align-items-center gap-1" style="font-size:.75rem;">
                    <span style="width:14px;height:14px;border-radius:50%;background:var(--green);display:inline-block;"></span> LLDP Neighbor
                </span>
                <span class="ms-auto" style="font-size:.75rem;color:var(--text-muted);" id="topoStats"></span>
            </div>
        </div>
    </div>

    <!-- Topology Canvas -->
    <div class="col-lg-9">
        <div class="card">
            <div id="topologyCanvas" style="height:580px;width:100%;border-radius:8px;"></div>
            <div id="topologyLoader" class="d-flex align-items-center justify-content-center" style="height:580px;width:100%;position:absolute;top:0;left:0;border-radius:8px;background:var(--bg-card);">
                <div class="text-center">
                    <div class="spinner-border" style="color:var(--orange);width:2rem;height:2rem;" role="status"></div>
                    <p class="mt-3 mb-0" style="font-size:.85rem;color:var(--text-muted);">Mengambil data LLDP dari semua router...</p>
                    <p class="mt-1 mb-0" style="font-size:.75rem;color:var(--text-muted);">Mungkin butuh 10–30 detik</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Panel -->
    <div class="col-lg-3">
        <!-- Node detail -->
        <div class="card mb-3">
            <div class="card-header">
                <span class="card-title" style="font-size:.8rem;"><i class='bx bx-info-circle me-1' style="color:var(--orange);"></i>Detail Node</span>
            </div>
            <div class="card-body" id="nodeDetail">
                <div class="text-center py-3" style="color:var(--text-muted);font-size:.8rem;">
                    Klik node untuk detail
                </div>
            </div>
        </div>

        <!-- Router list -->
        <div class="card">
            <div class="card-header">
                <span class="card-title" style="font-size:.8rem;"><i class='bx bx-list-ul me-1' style="color:var(--orange);"></i>Router List</span>
            </div>
            <div class="card-body p-0">
                <div id="routerList" style="max-height:280px;overflow-y:auto;">
                    @foreach($areas as $area)
                    <div class="router-list-item" onclick="focusNode('area_{{ $area->id }}')"
                         style="padding:.5rem .75rem;border-bottom:1px solid var(--border-color);cursor:pointer;font-size:.78rem;transition:background .15s;"
                         onmouseover="this.style.background='var(--bg-card-alt)'" onmouseout="this.style.background=''">
                        <div style="font-weight:600;">{{ $area->name }}</div>
                        <div style="color:var(--text-muted);font-size:.7rem;">{{ $area->router_ip }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- vis.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vis-network@9.1.9/dist/dist/vis-network.min.css">
<script src="https://cdn.jsdelivr.net/npm/vis-network@9.1.9/dist/dist/vis-network.min.js"></script>

<style>
#topologyCanvas { position: relative; }
.vis-network:focus { outline: none; }
</style>

<script>
let network = null;
let allNodes = null, allEdges = null;

const groupColors = {
    vps:      { background: '#6366f1', border: '#4f46e5', font: { color: '#fff' } },
    router:   { background: 'var(--orange)', border: '#ea580c', font: { color: '#fff' } },
    neighbor: { background: 'var(--green)',  border: '#16a34a', font: { color: '#fff' } },
};

async function loadTopology() {
    document.getElementById('topologyLoader').style.display = 'flex';
    document.getElementById('topoStatus').textContent = 'Memuat...';

    try {
        const res  = await fetch('{{ route("admin.nms.topology.data") }}');
        const data = await res.json();

        const nodes = new vis.DataSet(data.nodes.map(n => ({
            id:    n.id,
            label: n.label,
            title: n.title || n.label,
            group: n.group,
            color: groupColors[n.group] || groupColors.neighbor,
            font:  { color: '#fff', size: 11, bold: true },
            shape: n.group === 'vps' ? 'diamond' : (n.group === 'router' ? 'box' : 'ellipse'),
            size:  n.group === 'vps' ? 28 : (n.group === 'router' ? 22 : 18),
            shadow: true,
            _data: n,
        })));

        const edges = new vis.DataSet(data.edges.map(e => ({
            from:  e.from,
            to:    e.to,
            title: e.title || '',
            color: { color: e.from === 'vps' ? '#6366f1' : (e.to?.startsWith('nbr_') ? 'var(--green)' : '#94a3b8'),
                     highlight: 'var(--orange)', opacity: 0.8 },
            width: e.from === 'vps' ? 2 : 1.5,
            dashes: e.to?.startsWith('nbr_'),
            smooth: { type: 'curvedCW', roundness: 0.1 },
        })));

        allNodes = nodes; allEdges = edges;

        const container = document.getElementById('topologyCanvas');
        const options = {
            layout:  { improvedLayout: true },
            physics: {
                enabled: true,
                solver:  'forceAtlas2Based',
                forceAtlas2Based: { gravitationalConstant: -80, centralGravity: 0.008, springLength: 140, springConstant: 0.04 },
                stabilization: { iterations: 150 },
            },
            interaction: { hover: true, tooltipDelay: 100, zoomView: true, dragView: true },
            nodes: { borderWidth: 2, shadow: { enabled: true, size: 6, x: 2, y: 2 } },
            edges: { arrows: { to: { enabled: false } } },
        };

        if (network) network.destroy();
        network = new vis.Network(container, { nodes, edges }, options);

        network.on('click', params => {
            if (params.nodes.length > 0) {
                const node = nodes.get(params.nodes[0]);
                showNodeDetail(node);
            } else {
                document.getElementById('nodeDetail').innerHTML = '<div class="text-center py-3" style="color:var(--text-muted);font-size:.8rem;">Klik node untuk detail</div>';
            }
        });

        network.on('stabilized', () => {
            document.getElementById('topologyLoader').style.display = 'none';
        });

        const routerCount   = data.nodes.filter(n => n.group === 'router').length;
        const neighborCount = data.nodes.filter(n => n.group === 'neighbor').length;
        document.getElementById('topoStats').textContent = `${routerCount} router · ${neighborCount} neighbor · ${data.edges.length} link`;
        document.getElementById('topoStatus').textContent = 'Diperbarui ' + new Date().toLocaleTimeString('id-ID');

    } catch(err) {
        document.getElementById('topologyLoader').style.display = 'none';
        document.getElementById('topoStatus').textContent = 'Gagal memuat';
        console.error(err);
    }
}

function showNodeDetail(node) {
    if (!node) return;
    const d = node._data || {};
    const groupLabel = { vps: 'VPS Hub', router: 'MikroTik Router', neighbor: 'LLDP Neighbor' }[node.group] || node.group;
    const groupColor = { vps: '#6366f1', router: 'var(--orange)', neighbor: 'var(--green)' }[node.group] || 'var(--text-muted)';

    document.getElementById('nodeDetail').innerHTML = `
        <div class="mb-2">
            <span class="badge" style="background:${groupColor}20;color:${groupColor};font-size:.7rem;">${groupLabel}</span>
        </div>
        <div style="font-weight:700;font-size:.9rem;margin-bottom:.5rem;">${node.label.replace('\n', ' ')}</div>
        ${d.ip ? `<div style="font-size:.75rem;color:var(--text-muted);">IP: <code>${d.ip}</code></div>` : ''}
        ${d.title ? `<div style="font-size:.72rem;color:var(--text-muted);margin-top:.4rem;white-space:pre-wrap;">${d.title}</div>` : ''}
        ${node.group === 'router' ? `<a href="{{ route('admin.nms.diagnostics') }}?area=${d.id?.replace('area_','')}" class="btn btn-sm mt-2 w-100" style="background:rgba(249,115,22,.12);color:var(--orange);border:1px solid var(--orange);font-size:.72rem;"><i class='bx bx-terminal me-1'></i>Diagnostics</a>` : ''}
    `;
}

function focusNode(nodeId) {
    if (!network) return;
    network.focus(nodeId, { scale: 1.5, animation: { duration: 600, easingFunction: 'easeInOutQuad' } });
    network.selectNodes([nodeId]);
    if (allNodes) showNodeDetail(allNodes.get(nodeId));
}

// Load on page ready
loadTopology();

// Auto-refresh 5 menit
setInterval(loadTopology, 5 * 60 * 1000);
</script>
@endsection
