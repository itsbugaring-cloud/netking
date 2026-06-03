
<?php $__env->startSection('title', 'NMS Live Traffic'); ?>

<?php $__env->startSection('content'); ?>

<!-- ── Offline Router Alert Banner ─────────────────────────────────────────── -->
<div id="offlineAlertBanner" style="display:none;position:sticky;top:0;z-index:1050;
     background:rgba(239,68,68,.95);color:#fff;padding:.55rem 1rem;border-radius:8px;
     margin-bottom:.75rem;font-size:.83rem;box-shadow:0 4px 12px rgba(239,68,68,.35);
     backdrop-filter:blur(4px);">
    <div class="d-flex align-items-center gap-2">
        <i class='bx bx-error-circle' style="font-size:1.2rem;flex-shrink:0;"></i>
        <div>
            <strong>Router Offline!</strong>
            <span id="offlineAlertList" style="margin-left:.4rem;opacity:.9;"></span>
        </div>
        <button onclick="document.getElementById('offlineAlertBanner').style.display='none'"
                style="margin-left:auto;background:transparent;border:none;color:#fff;font-size:1.1rem;cursor:pointer;line-height:1;">
            <i class='bx bx-x'></i>
        </button>
    </div>
</div>

<div class="page-header mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            <i class='bx bx-pulse me-2' style="color:var(--orange);"></i>Live Traffic Monitor
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo e(route('admin.nms.dashboard')); ?>">NMS</a></li>
                <li class="breadcrumb-item active">Live Traffic</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div style="font-size:.8rem;color:var(--text-muted);">
            Update: <span id="lastUpdate" style="color:var(--orange);font-weight:600;">—</span>
        </div>
        <div style="font-size:.8rem;color:var(--text-muted);">
            Refresh: <span id="countdownBadge" style="font-weight:600;color:var(--text-muted);">—</span>s
        </div>
        <button class="btn btn-sm" id="refreshBtn"
                style="background:var(--orange);color:#fff;font-size:.78rem;padding:.25rem .75rem;">
            <i class='bx bx-refresh me-1'></i>Refresh Now
        </button>
    </div>
</div>

<!-- Summary Row -->
<div class="stat-grid mb-4" id="summaryRow">
    <div class="stat-card">
        <div>
            <div class="stat-label">Router Online</div>
            <div class="stat-value" id="sumOnline" style="color:var(--green);">—</div>
            <div class="stat-change neutral" id="sumTotal">dari — area</div>
        </div>
        <div class="stat-icon si-green"><i class='bx bx-server'></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Sessions PPPoE</div>
            <div class="stat-value" id="sumSessions" style="color:var(--orange);">—</div>
            <div class="stat-change neutral">Active connections</div>
        </div>
        <div class="stat-icon si-orange"><i class='bx bx-user-check'></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Download</div>
            <div class="stat-value" id="sumRx" style="color:var(--blue,#3b82f6);">—</div>
            <div class="stat-change neutral">Mbps ↓ ke pelanggan</div>
        </div>
        <div class="stat-icon si-blue"><i class='bx bx-arrow-from-top'></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Upload</div>
            <div class="stat-value" id="sumTx" style="color:var(--purple,#8b5cf6);">—</div>
            <div class="stat-change neutral">Mbps ↑ dari pelanggan</div>
        </div>
        <div class="stat-icon" style="background:rgba(139,92,246,.12);color:var(--purple,#8b5cf6);font-size:1.6rem;width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;">
            <i class='bx bx-arrow-to-top'></i>
        </div>
    </div>
</div>

<!-- Area Cards Grid -->
<div id="areaGrid" class="row g-3">
    <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-xl-4 col-lg-6" id="card-area-<?php echo e($area->id); ?>">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <div style="font-weight:700;font-size:.9rem;"><?php echo e($area->name); ?></div>
                        <code style="font-size:.72rem;color:var(--text-muted);"><?php echo e($area->router_ip); ?></code>
                    </div>
                    <span class="area-status-badge badge" id="badge-<?php echo e($area->id); ?>"
                          style="background:#e5e7eb;color:#6b7280;font-size:.7rem;">
                        Mengecek...
                    </span>
                </div>

                <!-- Identity -->
                <div id="identity-<?php echo e($area->id); ?>" style="font-size:.75rem;color:var(--text-muted);margin-bottom:.6rem;">
                    <i class='bx bx-chip me-1'></i><span>—</span>
                </div>

                <!-- Sessions + Kill button -->
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class='bx bx-user' style="font-size:1rem;color:var(--orange);"></i>
                    <span style="font-size:.8rem;color:var(--text-muted);">Sessions:</span>
                    <span id="sessions-<?php echo e($area->id); ?>" style="font-weight:700;font-size:.9rem;">—</span>
                    <button class="btn btn-sm sessions-btn ms-auto" data-area="<?php echo e($area->id); ?>"
                            style="font-size:.68rem;padding:.15rem .5rem;border:1px solid var(--orange);color:var(--orange);background:transparent;border-radius:4px;display:none;">
                        <i class='bx bx-list-ul me-1'></i>Lihat Sesi
                    </button>
                </div>

                <!-- Traffic bars -->
                <div id="traffic-<?php echo e($area->id); ?>">
                    <!-- RX -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between" style="font-size:.72rem;margin-bottom:.2rem;">
                            <span style="color:var(--text-muted);">↓ Download</span>
                            <span id="rx-label-<?php echo e($area->id); ?>" style="font-weight:700;color:#3b82f6;">— Mbps</span>
                        </div>
                        <div style="background:#e5e7eb;border-radius:4px;height:8px;overflow:hidden;">
                            <div id="rx-bar-<?php echo e($area->id); ?>" style="height:100%;width:0%;background:linear-gradient(90deg,#3b82f6,#60a5fa);border-radius:4px;transition:width .8s ease;"></div>
                        </div>
                    </div>
                    <!-- TX -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between" style="font-size:.72rem;margin-bottom:.2rem;">
                            <span style="color:var(--text-muted);">↑ Upload</span>
                            <span id="tx-label-<?php echo e($area->id); ?>" style="font-weight:700;color:#8b5cf6;">— Mbps</span>
                        </div>
                        <div style="background:#e5e7eb;border-radius:4px;height:8px;overflow:hidden;">
                            <div id="tx-bar-<?php echo e($area->id); ?>" style="height:100%;width:0%;background:linear-gradient(90deg,#8b5cf6,#a78bfa);border-radius:4px;transition:width .8s ease;"></div>
                        </div>
                    </div>
                    <!-- Traffic History Sparkline -->
                    <div style="margin-bottom:.3rem;">
                        <div style="font-size:.65rem;color:var(--text-muted);margin-bottom:.15rem;">Histori 10 menit terakhir</div>
                        <canvas id="spark-<?php echo e($area->id); ?>" height="30" style="width:100%;display:block;border-radius:3px;"></canvas>
                    </div>
                </div>

                <!-- Interface breakdown toggle -->
                <div style="margin-top:.4rem;">
                    <button class="iface-toggle btn btn-sm" data-id="<?php echo e($area->id); ?>"
                            style="font-size:.68rem;padding:.15rem .5rem;border:1px solid #e5e7eb;color:var(--text-muted);background:transparent;border-radius:4px;width:100%;">
                        <i class='bx bx-chevron-down me-1'></i>Semua Interface
                    </button>
                    <div id="iface-breakdown-<?php echo e($area->id); ?>" style="display:none;margin-top:.4rem;max-height:200px;overflow-y:auto;"></div>
                </div>

                <!-- Offline overlay -->
                <div id="offline-<?php echo e($area->id); ?>" style="display:none;text-align:center;padding:.5rem 0;color:var(--red,#ef4444);font-size:.8rem;">
                    <i class='bx bx-wifi-off me-1'></i>Router Offline
                    <div id="offline-err-<?php echo e($area->id); ?>" style="font-size:.68rem;color:var(--text-muted);margin-top:.2rem;"></div>
                </div>
            </div>
            <!-- Footer: interface name + timestamp -->
            <div class="card-footer" style="font-size:.68rem;color:var(--text-muted);padding:.4rem .75rem;display:flex;justify-content:space-between;">
                <span id="iface-<?php echo e($area->id); ?>"><i class='bx bx-cable me-1'></i>—</span>
                <span id="ts-<?php echo e($area->id); ?>">—</span>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<!-- ── Session Panel (custom overlay, no Bootstrap Modal) ──────────────── -->
<div id="sessOverlay" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.45);"
     onclick="if(event.target===this) closeSessPanel()"></div>
<div id="sessPanel" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
     z-index:10000;width:min(96vw,1000px);max-height:85vh;background:var(--bg-card,#fff);
     border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.25);display:none;flex-direction:column;overflow:hidden;">
    <!-- Header -->
    <div style="display:flex;align-items:center;padding:.75rem 1rem;border-bottom:1px solid var(--border,#e5e7eb);gap:.5rem;flex-shrink:0;">
        <i class='bx bx-user-check' style="color:var(--orange);font-size:1.1rem;"></i>
        <span style="font-weight:600;font-size:.9rem;">Sesi Aktif —</span>
        <span id="sessModalAreaName" style="font-weight:600;font-size:.9rem;color:var(--orange);">—</span>
        <button id="sessRefreshBtn" onclick="refreshSessionsModal()" title="Refresh"
                style="margin-left:auto;background:none;border:1px solid var(--border,#e5e7eb);border-radius:6px;
                       padding:.2rem .5rem;font-size:.8rem;cursor:pointer;color:var(--text-muted,#6b7280);">
            <i class='bx bx-refresh'></i>
        </button>
        <button onclick="closeSessPanel()"
                style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--text-muted,#6b7280);
                       line-height:1;padding:.1rem .3rem;border-radius:4px;">&times;</button>
    </div>
    <!-- Note bar -->
    <div id="sessNoteBar" style="display:none;background:#fff8e7;border-bottom:1px solid #f0c040;
         padding:.35rem .75rem;font-size:.72rem;color:#7a5c00;flex-shrink:0;">
        <i class='bx bx-info-circle me-1'></i>
        Kolom <strong>Total DL/UL</strong> menampilkan 0 jika PPPoE accounting belum aktif di router.
        Aktifkan di <em>PPP → Profiles → Accounting</em>.
    </div>
    <!-- Body -->
    <div id="sessModalBody" style="overflow-y:auto;padding:.75rem 1rem;flex:1;">
        <div style="text-align:center;padding:2rem 0;color:var(--text-muted,#6b7280);">
            <i class='bx bx-loader-alt bx-spin' style="font-size:1.5rem;"></i>
            <div style="margin-top:.5rem;font-size:.83rem;">Memuat sesi...</div>
        </div>
    </div>
    <!-- Footer -->
    <div style="display:flex;align-items:center;padding:.5rem .75rem;border-top:1px solid var(--border,#e5e7eb);
         font-size:.75rem;color:var(--text-muted,#6b7280);flex-shrink:0;">
        <span id="sessModalCount">—</span>
        <button onclick="closeSessPanel()"
                style="margin-left:auto;background:var(--orange,#f5650d);color:#fff;border:none;
                       border-radius:6px;padding:.3rem .85rem;font-size:.75rem;cursor:pointer;">Tutup</button>
    </div>
</div>

<style>
#summaryRow .stat-grid { margin-bottom: 0; }
.si-blue  { background:rgba(59,130,246,.12); color:#3b82f6; font-size:1.6rem; width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; }
.sessions-btn:hover { background:rgba(var(--orange-rgb,245,101,13),.08) !important; }
</style>

<script>
const LIVE_DATA_URL   = '<?php echo e(route("admin.nms.live-traffic.data")); ?>';
const SESSIONS_URL    = '/admin/nms/live-traffic/'; // + areaId + '/sessions'
const SESSION_KILL_URL = '<?php echo e(route("admin.nms.session-kill")); ?>';
const CSRF_TOKEN      = document.querySelector('meta[name="csrf-token"]')?.content || '';
const REFRESH_INTERVAL = 30;

let countdown      = REFRESH_INTERVAL;
let countdownTimer = null;
let maxObservedMbps = 50;

// Traffic history per area (last 10 readings)
const trafficHistory = {};

// ── Format helpers ─────────────────────────────────────────────────────────
function mbpsLabel(mbps) {
    if (mbps === null || mbps === undefined) return '<span style="color:#aaa;font-size:.7rem;">Mengukur...</span>';
    if (mbps >= 1000) return (mbps / 1000).toFixed(1) + ' Gbps';
    if (mbps >= 0.1)  return mbps.toFixed(2) + ' Mbps';
    if (mbps > 0)     return (mbps * 1000).toFixed(0) + ' Kbps';
    return '0 Mbps';
}

function barPct(mbps) {
    if (!mbps || mbps <= 0) return 0;
    if (mbps > maxObservedMbps) maxObservedMbps = Math.ceil(mbps * 1.3);
    return Math.min(100, Math.round((mbps / maxObservedMbps) * 100));
}

function fmtBytes(b) {
    if (b >= 1073741824) return (b / 1073741824).toFixed(1) + ' GB';
    if (b >= 1048576)    return (b / 1048576).toFixed(1) + ' MB';
    if (b >= 1024)       return (b / 1024).toFixed(0) + ' KB';
    return b + ' B';
}

// ── Sparkline renderer ─────────────────────────────────────────────────────
function drawSparkline(canvasId, histArr) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx    = canvas.getContext('2d');
    const W      = canvas.offsetWidth || 200;
    const H      = 30;
    canvas.width  = W;
    canvas.height = H;
    ctx.clearRect(0, 0, W, H);

    if (histArr.length < 2) {
        ctx.fillStyle = '#e5e7eb';
        ctx.fillRect(0, H / 2 - 1, W, 2);
        return;
    }

    const maxRx = Math.max(...histArr.map(h => h.rx), 1);
    const maxTx = Math.max(...histArr.map(h => h.tx), 1);
    const yMax  = Math.max(maxRx, maxTx, 1);

    const stepX = W / (histArr.length - 1);

    function drawLine(data, color, alpha) {
        ctx.beginPath();
        ctx.strokeStyle = color;
        ctx.lineWidth   = 1.5;
        ctx.globalAlpha = alpha;
        data.forEach((v, i) => {
            const x = i * stepX;
            const y = H - Math.round((v / yMax) * (H - 2)) - 1;
            i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
        });
        ctx.stroke();
        ctx.globalAlpha = 1;
    }

    drawLine(histArr.map(h => h.rx), '#3b82f6', 0.85);
    drawLine(histArr.map(h => h.tx), '#8b5cf6', 0.85);
}

// ── Offline Alert Banner ────────────────────────────────────────────────────
function checkOfflineAlert(rows) {
    const offlineAreas = rows.filter(r => !r.online).map(r => r.area_name);
    const banner       = document.getElementById('offlineAlertBanner');
    const listEl       = document.getElementById('offlineAlertList');

    if (offlineAreas.length > 0) {
        listEl.textContent  = offlineAreas.join(', ');
        banner.style.display = 'block';
    } else {
        banner.style.display = 'none';
    }
}

// ── Fetch & render ────────────────────────────────────────────────────────
async function fetchData() {
    try {
        const res  = await fetch(LIVE_DATA_URL);
        const json = await res.json();
        renderData(json);
        document.getElementById('lastUpdate').textContent = json.updated_at || '—';
        checkOfflineAlert(json.data || []);
    } catch (e) {
        console.error('Live traffic error:', e);
    }
}

function renderData(json) {
    const rows = json.data || [];

    // Summary
    let online = 0, sessions = 0, totalRx = 0, totalTx = 0, hasSpeeds = false;
    rows.forEach(r => {
        if (!r.online) return;
        online++;
        sessions += r.sessions || 0;
        if (r.rx_mbps !== null) { totalRx += r.rx_mbps; hasSpeeds = true; }
        if (r.tx_mbps !== null) { totalTx += r.tx_mbps; }
    });

    document.getElementById('sumOnline').textContent   = online;
    document.getElementById('sumTotal').textContent    = 'dari ' + rows.length + ' area';
    document.getElementById('sumSessions').textContent = sessions;
    document.getElementById('sumRx').textContent       = hasSpeeds ? mbpsLabel(totalRx).replace(/<[^>]+>/g,'') : 'Mengukur...';
    document.getElementById('sumTx').textContent       = hasSpeeds ? mbpsLabel(totalTx).replace(/<[^>]+>/g,'') : 'Mengukur...';

    // Area cards
    rows.forEach(row => {
        const id  = row.area_id;
        const get = s => document.getElementById(s + '-' + id);

        const badge       = get('badge');
        const identEl     = get('identity');
        const sessEl      = get('sessions');
        const offlineEl   = get('offline');
        const trafficEl   = get('traffic');
        const ifaceEl     = get('iface');
        const tsEl        = get('ts');
        const rxLabel     = get('rx-label');
        const txLabel     = get('tx-label');
        const rxBar       = get('rx-bar');
        const txBar       = get('tx-bar');
        const sessBtnEl   = document.querySelector('.sessions-btn[data-area="' + id + '"]');

        if (!badge) return;

        if (!row.online) {
            badge.textContent   = 'OFFLINE';
            badge.style.cssText = 'background:rgba(239,68,68,.15);color:#ef4444;font-size:.7rem;';
            if (identEl)   identEl.querySelector('span').textContent = '—';
            if (sessEl)    sessEl.textContent = '—';
            if (sessBtnEl) sessBtnEl.style.display = 'none';
            if (trafficEl) trafficEl.style.display = 'none';
            if (offlineEl) {
                offlineEl.style.display = 'block';
                const errEl = document.getElementById('offline-err-' + id);
                if (errEl) errEl.textContent = (row.error || '').substring(0, 80);
            }
            if (tsEl) tsEl.textContent = json.updated_at || '—';
            return;
        }

        // ── ONLINE ─────────────────────────────────────────────────────────
        badge.textContent   = 'ONLINE';
        badge.style.cssText = 'background:rgba(34,197,94,.15);color:#22c55e;font-size:.7rem;';
        if (offlineEl)  offlineEl.style.display  = 'none';
        if (trafficEl)  trafficEl.style.display  = 'block';
        if (identEl)    identEl.querySelector('span').textContent = row.identity || row.area_name;
        if (sessEl)     sessEl.textContent  = row.sessions || 0;
        if (sessBtnEl)  sessBtnEl.style.display = row.sessions > 0 ? 'inline-flex' : 'none';
        if (ifaceEl)    ifaceEl.innerHTML   = '<i class=\'bx bx-cable me-1\'></i>' + (row.iface || '—');
        if (tsEl)       tsEl.textContent    = json.updated_at || '—';

        const rxMbps = row.rx_mbps;
        const txMbps = row.tx_mbps;

        if (rxLabel) rxLabel.innerHTML = mbpsLabel(rxMbps);
        if (txLabel) txLabel.innerHTML = mbpsLabel(txMbps);
        if (rxBar)   rxBar.style.width = barPct(rxMbps) + '%';
        if (txBar)   txBar.style.width = barPct(txMbps) + '%';

        // ── Traffic history ──────────────────────────────────────────────
        if (!trafficHistory[id]) trafficHistory[id] = [];
        trafficHistory[id].push({ rx: rxMbps || 0, tx: txMbps || 0, ts: Date.now() });
        if (trafficHistory[id].length > 10) trafficHistory[id].shift();
        drawSparkline('spark-' + id, trafficHistory[id]);

        // ── Interface breakdown ──────────────────────────────────────────
        const bdEl = document.getElementById('iface-breakdown-' + id);
        if (bdEl && row.interfaces && row.interfaces.length > 0) {
            const maxIf = Math.max(...row.interfaces.map(i => i.rx_mbps + i.tx_mbps), 1);
            bdEl.innerHTML = row.interfaces.map(ifc => {
                const rxW = Math.min(100, Math.round(ifc.rx_mbps / maxIf * 100));
                const txW = Math.min(100, Math.round(ifc.tx_mbps / maxIf * 100));
                return '<div style="margin-bottom:.35rem;padding:.3rem .4rem;background:var(--bg-card-alt,#f8f9fa);border-radius:4px;">'
                     + '<div style="display:flex;justify-content:space-between;font-size:.68rem;font-weight:600;margin-bottom:.2rem;">'
                     + '<span>' + ifc.name + '</span>'
                     + '<span style="color:var(--text-muted);font-size:.62rem;">' + ifc.type + '</span>'
                     + '</div>'
                     + '<div style="display:flex;gap:.3rem;align-items:center;margin-bottom:.1rem;">'
                     + '<span style="font-size:.62rem;color:#3b82f6;width:10px;">↓</span>'
                     + '<div style="flex:1;background:#e5e7eb;border-radius:3px;height:5px;">'
                     + '<div style="width:' + rxW + '%;height:100%;background:#3b82f6;border-radius:3px;"></div>'
                     + '</div>'
                     + '<span style="font-size:.65rem;color:#3b82f6;min-width:48px;text-align:right;">' + ifc.rx_mbps.toFixed(2) + ' Mbps</span>'
                     + '</div>'
                     + '<div style="display:flex;gap:.3rem;align-items:center;">'
                     + '<span style="font-size:.62rem;color:#8b5cf6;width:10px;">↑</span>'
                     + '<div style="flex:1;background:#e5e7eb;border-radius:3px;height:5px;">'
                     + '<div style="width:' + txW + '%;height:100%;background:#8b5cf6;border-radius:3px;"></div>'
                     + '</div>'
                     + '<span style="font-size:.65rem;color:#8b5cf6;min-width:48px;text-align:right;">' + ifc.tx_mbps.toFixed(2) + ' Mbps</span>'
                     + '</div>'
                     + '</div>';
            }).join('');
        }
    });
}

// ── Interface breakdown toggle ─────────────────────────────────────────────
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.iface-toggle');
    if (!btn) return;
    const id  = btn.dataset.id;
    const bd  = document.getElementById('iface-breakdown-' + id);
    if (!bd)  return;
    const open = bd.style.display !== 'none';
    bd.style.display = open ? 'none' : 'block';
    btn.innerHTML = open
        ? '<i class=\'bx bx-chevron-down me-1\'></i>Semua Interface'
        : '<i class=\'bx bx-chevron-up me-1\'></i>Sembunyikan';
});

// ── Sessions Modal ─────────────────────────────────────────────────────────
let activeSessionAreaId = null;

document.addEventListener('click', function(e) {
    const btn = e.target.closest('.sessions-btn');
    if (!btn) return;
    const areaId = btn.dataset.area;
    openSessionsModal(areaId);
});

async function fetchAndRenderSessions(areaId) {
    const refreshBtn = document.getElementById('sessRefreshBtn');
    if (refreshBtn) { refreshBtn.disabled = true; refreshBtn.innerHTML = '<i class=\'bx bx-loader-alt bx-spin\'></i>'; }
    document.getElementById('sessModalBody').innerHTML =
        '<div class="text-center py-4 text-muted"><i class=\'bx bx-loader-alt bx-spin\' style="font-size:1.5rem;"></i>'
        + '<div style="margin-top:.5rem;font-size:.83rem;">Memuat sesi...</div></div>';
    document.getElementById('sessModalCount').textContent = '—';
    document.getElementById('sessNoteBar').style.display = 'none';

    try {
        const res  = await fetch(SESSIONS_URL + areaId + '/sessions');
        const json = await res.json();
        if (!json.success || !json.data) {
            document.getElementById('sessModalBody').innerHTML =
                '<div class="text-center py-3 text-danger"><i class=\'bx bx-error-circle me-1\'></i>' + (json.error || 'Gagal memuat sesi') + '</div>';
            return;
        }
        renderSessions(json.data, areaId);
        document.getElementById('sessModalCount').textContent = json.data.length + ' sesi aktif';
    } catch (err) {
        document.getElementById('sessModalBody').innerHTML =
            '<div class="text-center py-3 text-danger"><i class=\'bx bx-error-circle me-1\'></i>Error: ' + err.message + '</div>';
    } finally {
        if (refreshBtn) { refreshBtn.disabled = false; refreshBtn.innerHTML = '<i class=\'bx bx-refresh\'></i>'; }
    }
}

function refreshSessionsModal() {
    if (activeSessionAreaId) fetchAndRenderSessions(activeSessionAreaId);
}

function closeSessPanel() {
    document.getElementById('sessOverlay').style.display = 'none';
    document.getElementById('sessPanel').style.display   = 'none';
    document.body.style.overflow = '';
}

async function openSessionsModal(areaId) {
    activeSessionAreaId = areaId;
    const cardEl  = document.getElementById('card-area-' + areaId);
    const areaName = cardEl ? cardEl.querySelector('[style*="font-weight:700"]')?.textContent?.trim() : ('Area #' + areaId);
    document.getElementById('sessModalAreaName').textContent = areaName || '—';

    // Show custom overlay + panel (no Bootstrap modal = no backdrop stacking issues)
    document.getElementById('sessOverlay').style.display = 'block';
    document.getElementById('sessPanel').style.display   = 'flex';
    document.body.style.overflow = 'hidden';

    await fetchAndRenderSessions(areaId);
}

function fmtBps(bps) {
    if (!bps || bps <= 0) return null;
    if (bps >= 1000000) return (bps / 1000000).toFixed(1) + ' Mbps';
    if (bps >= 1000)    return (bps / 1000).toFixed(0) + ' Kbps';
    return bps + ' bps';
}

function renderSessions(sessions, areaId) {
    if (!sessions.length) {
        document.getElementById('sessModalBody').innerHTML =
            '<div class="text-center py-4 text-muted"><i class=\'bx bx-info-circle me-1\'></i>Tidak ada sesi aktif</div>';
        return;
    }

    // Check if any session has byte data
    const hasBytes = sessions.some(s => (s.bytes_in || 0) > 0 || (s.bytes_out || 0) > 0);
    const hasRate  = sessions.some(s => (s.rate_in  || 0) > 0 || (s.rate_out  || 0) > 0);
    document.getElementById('sessNoteBar').style.display = (!hasBytes && !hasRate) ? 'block' : 'none';

    let html = '<div style="overflow-x:auto;">'
             + '<table class="table table-sm table-hover mb-0" style="font-size:.78rem;min-width:620px;">'
             + '<thead style="position:sticky;top:0;z-index:1;background:var(--bg-card-alt,#f8f9fa);"><tr>'
             + '<th style="white-space:nowrap;">Username</th>'
             + '<th style="white-space:nowrap;">IP Address</th>'
             + '<th style="white-space:nowrap;">Uptime</th>'
             + '<th style="white-space:nowrap;">Download</th>'
             + '<th style="white-space:nowrap;">Upload</th>'
             + '<th style="white-space:nowrap;">MAC/Caller</th>'
             + '<th></th>'
             + '</tr></thead><tbody>';

    sessions.forEach(s => {
        // Show current rate if available, fallback to total bytes, fallback to '—'
        const dlRate = fmtBps(s.rate_in);
        const ulRate = fmtBps(s.rate_out);
        const dlTotal = s.bytes_in > 0 ? fmtBytes(s.bytes_in) : null;
        const ulTotal = s.bytes_out > 0 ? fmtBytes(s.bytes_out) : null;

        // If we have rate, show rate (primary) + total (secondary). If only total, show total. Else '—'
        const dlCell = dlRate
            ? dlRate + (dlTotal ? '<br><small style="color:var(--text-muted);font-size:.68rem;">' + dlTotal + '</small>' : '')
            : (dlTotal || '<span style="color:var(--text-muted);">—</span>');
        const ulCell = ulRate
            ? ulRate + (ulTotal ? '<br><small style="color:var(--text-muted);font-size:.68rem;">' + ulTotal + '</small>' : '')
            : (ulTotal || '<span style="color:var(--text-muted);">—</span>');

        const callerShort = (s.caller_id || '').substring(0, 17) || '—';

        html += '<tr>'
              + '<td><code style="font-size:.78rem;">' + (s.username || '—') + '</code>'
              + (s.service ? '<br><small style="color:var(--text-muted);font-size:.65rem;">' + s.service + '</small>' : '')
              + '</td>'
              + '<td style="white-space:nowrap;">' + (s.address || '—') + '</td>'
              + '<td style="white-space:nowrap;">' + (s.uptime || '—') + '</td>'
              + '<td style="white-space:nowrap;">' + dlCell + '</td>'
              + '<td style="white-space:nowrap;">' + ulCell + '</td>'
              + '<td style="font-size:.7rem;color:var(--text-muted);white-space:nowrap;" title="' + (s.caller_id || '') + '">' + callerShort + '</td>'
              + '<td><button class="btn btn-sm kill-btn" data-username="' + (s.username || '') + '" data-area="' + areaId + '"'
              + ' style="white-space:nowrap;font-size:.68rem;padding:.15rem .45rem;background:rgba(239,68,68,.1);color:#ef4444;border:1px solid rgba(239,68,68,.3);border-radius:4px;">'
              + '<i class=\'bx bx-power-off me-1\'></i>Kick</button></td>'
              + '</tr>';
    });

    html += '</tbody></table></div>';
    document.getElementById('sessModalBody').innerHTML = html;

    // Kill button handler
    document.querySelectorAll('.kill-btn').forEach(btn => {
        btn.addEventListener('click', () => killSession(btn));
    });
}

async function killSession(btn) {
    const username = btn.dataset.username;
    const areaId   = btn.dataset.area;
    if (!username || !confirm('Disconnect sesi: ' + username + '?')) return;

    btn.disabled  = true;
    btn.innerHTML = '<i class=\'bx bx-loader-alt bx-spin\'></i>';

    try {
        const res  = await fetch(SESSION_KILL_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body:    JSON.stringify({ area_id: areaId, username: username }),
        });
        const json = await res.json();
        if (json.success) {
            btn.closest('tr').style.opacity = '.4';
            btn.innerHTML = '<i class=\'bx bx-check\'></i> Done';
            btn.style.color = '#22c55e';
            btn.style.borderColor = '#22c55e';
        } else {
            btn.disabled  = false;
            btn.innerHTML = '<i class=\'bx bx-power-off me-1\'></i>Kick';
            alert('Gagal: ' + (json.error || 'Unknown error'));
        }
    } catch (err) {
        btn.disabled  = false;
        btn.innerHTML = '<i class=\'bx bx-power-off me-1\'></i>Kick';
        alert('Error: ' + err.message);
    }
}

// ── Countdown ─────────────────────────────────────────────────────────────
function startCountdown() {
    if (countdownTimer) clearInterval(countdownTimer);
    countdown = REFRESH_INTERVAL;
    const badge = document.getElementById('countdownBadge');
    badge.textContent = countdown;
    countdownTimer = setInterval(() => {
        countdown--;
        badge.textContent = countdown;
        if (countdown <= 0) {
            clearInterval(countdownTimer);
            fetchData().then(startCountdown);
        }
    }, 1000);
}

document.getElementById('refreshBtn').addEventListener('click', () => {
    if (countdownTimer) clearInterval(countdownTimer);
    fetchData().then(startCountdown);
});

// Boot
fetchData().then(startCountdown);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/nms/nms_live_traffic.blade.php ENDPATH**/ ?>