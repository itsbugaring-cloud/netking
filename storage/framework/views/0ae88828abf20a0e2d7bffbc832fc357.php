
<?php $__env->startSection('title', 'NMS Diagnostics'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1"><i class='bx bx-terminal me-2' style="color:var(--orange);"></i>Network Diagnostics</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo e(route('admin.nms.dashboard')); ?>">NMS</a></li>
                <li class="breadcrumb-item active">Diagnostics</li>
            </ol>
        </nav>
    </div>
    <div>
        <span style="font-size:.75rem;color:var(--text-muted);">Dijalankan dari router MikroTik masing-masing area via WireGuard VPN</span>
    </div>
</div>

<div class="row g-4">
    <!-- Form Panel -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <span class="card-title"><i class='bx bx-cog me-2' style="color:var(--orange);"></i>Konfigurasi</span>
            </div>
            <div class="card-body">
                <form id="diagForm">
                    <?php echo csrf_field(); ?>
                    <!-- Router/Area -->
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.8rem;">Router (Area)</label>
                        <select class="form-select form-select-sm" id="areaSelect" name="area_id" required>
                            <option value="">-- Pilih Router --</option>
                            <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($area->id); ?>" data-ip="<?php echo e($area->router_ip); ?>">
                                <?php echo e($area->name); ?> (<?php echo e($area->router_ip); ?>)
                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div id="routerBadge" class="mt-1" style="font-size:.72rem;display:none;">
                            <span class="badge" style="background:rgba(249,115,22,.15);color:var(--orange);">
                                <i class='bx bx-server me-1'></i><span id="routerIpLabel"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Tool -->
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.8rem;">Tool</label>
                        <div class="d-flex gap-2">
                            <div class="tool-btn active" data-tool="ping" onclick="selectTool(this)">
                                <i class='bx bx-radio-circle-marked'></i> Ping
                            </div>
                            <div class="tool-btn" data-tool="traceroute" onclick="selectTool(this)">
                                <i class='bx bx-git-branch'></i> Traceroute
                            </div>
                        </div>
                        <input type="hidden" id="toolInput" name="tool" value="ping">
                    </div>

                    <!-- Target -->
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.8rem;">Target (IP / Hostname)</label>
                        <input type="text" class="form-control form-control-sm" id="targetInput" name="target"
                               placeholder="Contoh: 8.8.8.8 atau google.com" required>
                        <!-- Quick targets -->
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <span class="quick-target" onclick="setTarget('8.8.8.8')">8.8.8.8</span>
                            <span class="quick-target" onclick="setTarget('1.1.1.1')">1.1.1.1</span>
                            <span class="quick-target" onclick="setTarget('google.com')">google.com</span>
                            <span class="quick-target" onclick="setTarget('103.127.137.24')">VPS</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-sm w-100" id="runBtn"
                            style="background:var(--orange);color:#fff;font-weight:600;">
                        <i class='bx bx-play-circle me-1'></i>Jalankan
                    </button>
                </form>

                <!-- History -->
                <div id="historyPanel" class="mt-4" style="display:none;">
                    <div style="font-size:.75rem;font-weight:600;color:var(--text-muted);margin-bottom:.5rem;">RIWAYAT</div>
                    <div id="historyList"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Result Panel — single div, content injected by JS -->
    <div class="col-lg-8">
        <div id="resultPanel" style="min-height:420px;">
            <!-- IDLE -->
            <div class="card" style="min-height:420px;display:flex;align-items:center;justify-content:center;">
                <div class="text-center" style="color:var(--text-muted);">
                    <i class='bx bx-terminal' style="font-size:3rem;opacity:.3;"></i>
                    <p class="mt-2 mb-0" style="font-size:.85rem;">Pilih router dan target, lalu klik <strong>Jalankan</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.tool-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .35rem .75rem; border-radius: 6px; cursor: pointer;
    font-size: .8rem; font-weight: 600; transition: all .2s;
    background: var(--bg-card-alt); color: var(--text-muted);
    border: 1.5px solid var(--border-color);
}
.tool-btn.active {
    background: rgba(249,115,22,.12); color: var(--orange);
    border-color: var(--orange);
}
.quick-target {
    display: inline-block; padding: .2rem .5rem; border-radius: 4px;
    font-size: .72rem; background: var(--bg-card-alt);
    border: 1px solid var(--border-color); cursor: pointer;
    color: var(--text-muted); transition: all .15s;
}
.quick-target:hover { background: rgba(249,115,22,.1); color: var(--orange); border-color: var(--orange); }
.history-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: .3rem .5rem; border-radius: 5px; cursor: pointer;
    font-size: .75rem; margin-bottom: .25rem;
    background: var(--bg-card-alt); border: 1px solid var(--border-color);
    transition: all .15s;
}
.history-item:hover { border-color: var(--orange); }
.history-item .hist-badge { font-size: .65rem; padding: .15rem .4rem; border-radius: 4px; }
.hop-ok { color: var(--green); font-weight: 600; }
.hop-timeout { color: var(--text-muted); }
.ping-bar { display: inline-block; height: 12px; min-width: 3px; border-radius: 2px; vertical-align: middle; }
</style>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const diagHistory = []; // renamed from 'history' to avoid browser global conflict

// Area select → show IP badge
document.getElementById('areaSelect').addEventListener('change', function() {
    const ip = this.options[this.selectedIndex]?.dataset?.ip;
    const badge = document.getElementById('routerBadge');
    if (ip) { document.getElementById('routerIpLabel').textContent = ip; badge.style.display = 'block'; }
    else     { badge.style.display = 'none'; }
});

function selectTool(el) {
    document.querySelectorAll('.tool-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('toolInput').value = el.dataset.tool;
}

function setTarget(val) {
    document.getElementById('targetInput').value = val;
    document.getElementById('targetInput').focus();
}

// ── Single panel injector — no more show/hide multiple divs ──────────────
function setPanel(html) {
    document.getElementById('resultPanel').innerHTML = html;
}

function showLoading(tool) {
    setPanel(`
        <div class="card" style="min-height:420px;display:flex;align-items:center;justify-content:center;">
            <div class="text-center">
                <div class="spinner-border" style="color:var(--orange);width:2.5rem;height:2.5rem;" role="status"></div>
                <p class="mt-3 mb-0" style="font-size:.9rem;color:var(--text-muted);">Menjalankan ${tool}...</p>
            </div>
        </div>`);
}

function showError(msg, detail) {
    setPanel(`
        <div class="card" style="min-height:420px;display:flex;align-items:center;justify-content:center;">
            <div class="text-center">
                <i class='bx bx-error-circle' style="font-size:3rem;color:var(--red);"></i>
                <p class="mt-2 mb-0 fw-semibold" style="color:var(--red);">${msg}</p>
                <p class="mt-1 mb-0" style="font-size:.8rem;color:var(--text-muted);">${detail || ''}</p>
            </div>
        </div>`);
}

function showPing(data) {
    const lossColor = data.loss === 0 ? 'var(--green)' : (data.loss === 100 ? 'var(--red)' : 'var(--orange)');
    const badgeHtml = data.loss === 0
        ? `<span class="badge" style="background:rgba(34,197,94,.15);color:var(--green);">✓ Reachable</span>`
        : (data.loss === 100
            ? `<span class="badge" style="background:rgba(239,68,68,.15);color:var(--red);">✗ Unreachable</span>`
            : `<span class="badge" style="background:rgba(249,115,22,.15);color:var(--orange);">⚠ ${data.loss}% Loss</span>`);

    const maxTime = Math.max(...(data.results || []).map(r => r.time || 0), 1);
    const rows = (data.results || []).map((r, i) => {
        const isOk = r.status === 'ok';
        const barW = isOk ? Math.max(6, Math.round((r.time / maxTime) * 110)) : 0;
        const barColor = isOk ? (r.time < 50 ? 'var(--green)' : r.time < 150 ? 'var(--orange)' : 'var(--red)') : '#ddd';
        return `<tr>
            <td style="font-size:.75rem;">${i + 1}</td>
            <td style="font-size:.75rem;">${isOk
                ? '<span style="color:var(--green);font-weight:600;">✓ OK</span>'
                : '<span style="color:var(--red);">✗ timeout</span>'}</td>
            <td style="font-size:.75rem;font-weight:600;">${isOk ? r.time + ' ms' : '—'}</td>
            <td style="font-size:.75rem;color:var(--text-muted);">${r.ttl || '—'}</td>
            <td><span style="display:inline-block;height:10px;width:${barW}px;min-width:3px;border-radius:2px;background:${barColor};vertical-align:middle;"></span></td>
        </tr>`;
    }).join('');

    setPanel(`
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title">
                    <i class='bx bx-radio-circle-marked me-2' style="color:var(--orange);"></i>
                    Hasil Ping — <strong style="color:var(--orange);">${data.target}</strong>
                </span>
                ${badgeHtml}
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4 text-center">
                    <div class="col-3">
                        <div class="p-2 rounded" style="background:var(--bg-card-alt,#f8f9fa);">
                            <div style="font-size:1.4rem;font-weight:700;color:${lossColor};">${data.loss}%</div>
                            <div style="font-size:.7rem;color:var(--text-muted);">PACKET LOSS</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded" style="background:var(--bg-card-alt,#f8f9fa);">
                            <div style="font-size:1.4rem;font-weight:700;color:var(--green,#22c55e);">${data.min}</div>
                            <div style="font-size:.7rem;color:var(--text-muted);">MIN (ms)</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded" style="background:var(--bg-card-alt,#f8f9fa);">
                            <div style="font-size:1.4rem;font-weight:700;color:var(--orange,#f97316);">${data.avg}</div>
                            <div style="font-size:.7rem;color:var(--text-muted);">AVG (ms)</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded" style="background:var(--bg-card-alt,#f8f9fa);">
                            <div style="font-size:1.4rem;font-weight:700;color:var(--red,#ef4444);">${data.max}</div>
                            <div style="font-size:.7rem;color:var(--text-muted);">MAX (ms)</div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr>
                            <th style="font-size:.72rem;">#</th>
                            <th style="font-size:.72rem;">Status</th>
                            <th style="font-size:.72rem;">Time (ms)</th>
                            <th style="font-size:.72rem;">TTL</th>
                            <th style="font-size:.72rem;">Visual</th>
                        </tr></thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
                <div class="mt-3" style="font-size:.72rem;color:var(--text-muted);">
                    Via router: <code>${data.router_ip || data.router}</code> &bull; ${data.executed_at || ''}
                </div>
            </div>
        </div>`);
}

function showTraceroute(data) {
    const rows = (data.hops || []).map(h => {
        const isTimeout = !h.address || h.address === '*';
        return `<tr>
            <td style="font-size:.75rem;font-weight:700;color:var(--orange);">${h.hop}</td>
            <td style="font-size:.75rem;${isTimeout ? 'color:var(--text-muted)' : 'font-weight:600;'}">
                ${isTimeout ? '*' : '<code style="font-size:.72rem;">' + h.address + '</code>'}
            </td>
            <td style="font-size:.75rem;">${h.loss || '—'}</td>
            <td style="font-size:.75rem;">${h.time1 !== null ? h.time1 + ' ms' : '—'}</td>
            <td style="font-size:.75rem;">${h.time2 !== null ? h.time2 + ' ms' : '—'}</td>
            <td style="font-size:.75rem;">${h.time3 !== null ? h.time3 + ' ms' : '—'}</td>
            <td style="font-size:.72rem;">${h.status ? '<span style="color:var(--green);">' + h.status + '</span>' : ''}</td>
        </tr>`;
    }).join('');

    setPanel(`
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title">
                    <i class='bx bx-git-branch me-2' style="color:var(--orange);"></i>
                    Traceroute — <strong style="color:var(--orange);">${data.target}</strong>
                </span>
                <span class="badge" style="background:rgba(249,115,22,.15);color:var(--orange);">${(data.hops||[]).length} hops</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr>
                            <th style="font-size:.72rem;width:40px;">Hop</th>
                            <th style="font-size:.72rem;">Address</th>
                            <th style="font-size:.72rem;">Loss</th>
                            <th style="font-size:.72rem;">Time 1</th>
                            <th style="font-size:.72rem;">Time 2</th>
                            <th style="font-size:.72rem;">Time 3</th>
                            <th style="font-size:.72rem;">Status</th>
                        </tr></thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
                <div class="p-3" style="font-size:.72rem;color:var(--text-muted);">
                    Via router: <code>${data.router_ip || data.router}</code> &bull; ${data.executed_at || ''}
                </div>
            </div>
        </div>`);
}

// ── Form submit ───────────────────────────────────────────────────────────
document.getElementById('diagForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const areaId = document.getElementById('areaSelect').value;
    const tool   = document.getElementById('toolInput').value;
    const target = document.getElementById('targetInput').value.trim();
    if (!areaId || !target) return;

    showLoading(tool);
    document.getElementById('runBtn').disabled = true;

    try {
        const res  = await fetch('<?php echo e(route("admin.nms.diagnostics.run")); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ area_id: areaId, tool, target })
        });
        const data = await res.json();

        addDiagHistory(tool, target, data.area_name, data.success);

        if (!data.success) {
            showError('Gagal menjalankan ' + tool, data.error || 'Unknown error');
            return;
        }

        if (tool === 'ping') showPing(data);
        else                 showTraceroute(data);

    } catch(err) {
        showError('Request error', err.message);
    } finally {
        document.getElementById('runBtn').disabled = false;
    }
});

function addDiagHistory(tool, target, areaName, success) {
    diagHistory.unshift({ tool, target, areaName, success, time: new Date().toLocaleTimeString('id-ID') });
    if (diagHistory.length > 8) diagHistory.pop();

    const list = document.getElementById('historyList');
    list.innerHTML = diagHistory.map((h, i) => `
        <div class="history-item" onclick="rerunDiag(${i})">
            <div>
                <span class="hist-badge" style="background:rgba(249,115,22,.12);color:var(--orange);">${h.tool}</span>
                <span style="font-weight:600;margin-left:.4rem;">${h.target}</span>
                <div style="font-size:.68rem;color:var(--text-muted);">${h.areaName || ''} &bull; ${h.time}</div>
            </div>
            <span style="color:${h.success ? 'var(--green)' : 'var(--red)'};font-size:.85rem;">${h.success ? '✓' : '✗'}</span>
        </div>
    `).join('');
    document.getElementById('historyPanel').style.display = 'block';
}

function rerunDiag(idx) {
    const h = diagHistory[idx];
    if (!h) return;
    document.getElementById('targetInput').value = h.target;
    document.getElementById('toolInput').value = h.tool;
    document.querySelectorAll('.tool-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.tool === h.tool);
    });
    document.getElementById('diagForm').dispatchEvent(new Event('submit'));
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/nms/nms_diagnostics.blade.php ENDPATH**/ ?>