
<?php $__env->startSection('title', $olt->name . ' — ONT Inventory'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .olt-show-page {
        --olt-orange: color-mix(in srgb, var(--nk-warning) 82%, #6b2d12);
        --olt-orange-soft: color-mix(in srgb, var(--nk-warning) 12%, var(--surface));
        --olt-purple: color-mix(in srgb, var(--nk-primary) 82%, #312e81);
        --olt-purple-soft: color-mix(in srgb, var(--nk-primary) 12%, var(--surface));
        --olt-cyan-soft: color-mix(in srgb, var(--nk-info) 10%, var(--surface));
    }

    .olt-show-page [style*="color:#1e3a8a"] {
        color: color-mix(in srgb, var(--nk-info) 72%, var(--txt)) !important;
    }

    .olt-show-page [style*="color:#64748b"],
    .olt-show-page [style*="color:#94a3b8"],
    .olt-show-page [style*="color:#9ba3c0"] {
        color: var(--txt-3) !important;
    }

    .olt-show-page [style*="color:#c8d4f0"],
    .olt-show-page [style*="color:#d8c8f8"] {
        color: var(--txt-2) !important;
    }

    .olt-show-page [style*="color:#60a5fa"] {
        color: color-mix(in srgb, var(--nk-info) 78%, var(--txt)) !important;
    }

    .olt-show-page [style*="color:#c084fc"],
    .olt-show-page [style*="color:#a855f7"] {
        color: color-mix(in srgb, var(--nk-primary) 78%, var(--txt)) !important;
    }

    .olt-show-page [style*="background:rgba(37,99,235,.06)"] {
        background: var(--olt-cyan-soft) !important;
        border-color: color-mix(in srgb, var(--nk-info) 20%, var(--border)) !important;
    }

    .olt-show-page [style*="--stat-bg:#eff6ff"] {
        --stat-bg: color-mix(in srgb, var(--nk-info) 10%, var(--surface));
        --stat-accent: var(--nk-info);
    }

    .olt-show-page [style*="--stat-bg:#ecfdf3"] {
        --stat-bg: color-mix(in srgb, var(--nk-success) 10%, var(--surface));
        --stat-accent: var(--nk-success);
    }

    .olt-show-page [style*="--stat-bg:#fef2f2"] {
        --stat-bg: color-mix(in srgb, var(--nk-danger) 10%, var(--surface));
        --stat-accent: var(--nk-danger);
    }

    .olt-show-page [style*="--stat-bg:#fff7ed"] {
        --stat-bg: var(--olt-orange-soft);
        --stat-accent: var(--olt-orange);
    }

    .olt-show-page [style*="--stat-bg:#faf5ff"] {
        --stat-bg: var(--olt-purple-soft);
        --stat-accent: var(--olt-purple);
    }

    .olt-show-page [style*="--stat-bg:#f0fdfa"] {
        --stat-bg: color-mix(in srgb, var(--nk-info) 9%, var(--surface));
        --stat-accent: color-mix(in srgb, var(--nk-info) 80%, var(--txt));
    }

    .olt-show-page .ms-panel-title i[style*="color:var(--orange)"] {
        color: var(--olt-orange) !important;
    }

    .olt-show-page .input-group-text[style*="rgba(249,115,22"],
    .olt-show-page .btn[style*="rgba(249,115,22"],
    .olt-show-page .dropdown-toggle[style*="rgba(249,115,22"] {
        background: var(--olt-orange-soft) !important;
        border-color: color-mix(in srgb, var(--nk-warning) 24%, var(--border)) !important;
        color: var(--olt-orange) !important;
    }

    .olt-show-page .form-control[style*="rgba(249,115,22"],
    .olt-show-page .form-select[style*="width:86px"] {
        border-color: color-mix(in srgb, var(--nk-warning) 24%, var(--border)) !important;
        background: var(--surface) !important;
        color: var(--txt) !important;
    }

    .olt-show-page .dropdown-menu[style*="min-width:160px"] {
        background: var(--surface) !important;
        border: 1px solid var(--border) !important;
        color: var(--txt) !important;
    }

    /* code in serial number cell — plain monospace, no box */
    #ont-table code {
        background: none !important;
        border: none !important;
        color: var(--txt) !important;
        font-family: ui-monospace, 'Cascadia Code', 'Fira Code', monospace;
        font-size: .75rem !important;
        font-weight: 600;
        padding: 0 !important;
    }

    .olt-show-page .modal-content[style*="background:#1e2235"] {
        background: var(--surface) !important;
        border-color: var(--border) !important;
        color: var(--txt) !important;
    }

    /* ── Custom modal overlay ─────────────────────────────────────────── */
    #wanConfigModal,
    #profileModal {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(3px);
        z-index: 4000 !important;
    }

    #wanConfigModal.is-open,
    #profileModal.is-open {
        display: flex !important;
    }

    body.nk-modal-open {
        overflow: hidden !important;
    }

    /* ── Modal dialog / content positioning ──────────────────────────── */
    #wanConfigModal .modal-dialog,
    #wanConfigModal .modal-content,
    #wanConfigModal .modal-header,
    #wanConfigModal .modal-body,
    #wanConfigModal .modal-footer,
    #profileModal .modal-dialog,
    #profileModal .modal-content,
    #profileModal .modal-header,
    #profileModal .modal-body,
    #profileModal .modal-footer {
        position: relative;
        pointer-events: auto !important;
    }

    #wanConfigModal .modal-dialog {
        width: min(720px, calc(100vw - 24px));
        max-width: 720px;
        margin: 0;
        z-index: 4001 !important;
    }

    #profileModal .modal-dialog {
        width: min(500px, calc(100vw - 24px));
        max-width: 500px;
        margin: 0;
        z-index: 4001 !important;
    }

    #wanConfigModal .modal-content,
    #profileModal .modal-content {
        width: 100%;
        max-height: calc(100vh - 32px);
        overflow: auto;
        z-index: 4002 !important;
        border-radius: .75rem !important;
        background: var(--surface) !important;
        border: 1px solid var(--border) !important;
        box-shadow: 0 24px 64px rgba(0,0,0,.18) !important;
    }

    /* ── Modal header ────────────────────────────────────────────────── */
    #wanConfigModal .modal-header {
        background: color-mix(in srgb, var(--orange, #f97316) 7%, var(--surface)) !important;
        border-bottom: 1px solid color-mix(in srgb, var(--orange, #f97316) 22%, var(--border)) !important;
        border-radius: .75rem .75rem 0 0 !important;
        padding: 1rem 1.25rem !important;
    }

    #profileModal .modal-header {
        background: color-mix(in srgb, #a855f7 7%, var(--surface)) !important;
        border-bottom: 1px solid color-mix(in srgb, #a855f7 22%, var(--border)) !important;
        border-radius: .75rem .75rem 0 0 !important;
        padding: 1rem 1.25rem !important;
    }

    /* ── Modal footer ────────────────────────────────────────────────── */
    #wanConfigModal .modal-footer,
    #profileModal .modal-footer {
        background: var(--surface-2, var(--surface)) !important;
        border-top: 1px solid var(--border) !important;
        padding: .75rem 1.25rem !important;
        justify-content: space-between !important;
        border-radius: 0 0 .75rem .75rem !important;
    }

    /* ── Modal text colors ───────────────────────────────────────────── */
    #wanConfigModal .modal-title,
    #profileModal .modal-title {
        color: var(--txt, inherit) !important;
        font-weight: 700 !important;
    }

    #wanConfigModal small,
    #profileModal small {
        color: var(--text-muted, #6b7280) !important;
    }

    /* ── Form labels ─────────────────────────────────────────────────── */
    #wanConfigModal .form-label,
    #profileModal .form-label {
        font-size: .76rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: .3px !important;
        color: var(--txt, inherit) !important;
        margin-bottom: .35rem !important;
    }

    #wanConfigModal .form-text,
    #profileModal .form-text {
        font-size: .68rem !important;
        color: var(--text-muted, #6b7280) !important;
    }

    /* ── Form controls ───────────────────────────────────────────────── */
    #wanConfigModal .form-control,
    #wanConfigModal .form-select,
    #profileModal .form-control,
    #profileModal .form-select {
        background: var(--bg, #f7f7f8) !important;
        border: 1px solid var(--border) !important;
        color: var(--txt, inherit) !important;
        font-size: .84rem !important;
        pointer-events: auto !important;
    }

    #wanConfigModal .form-control:focus,
    #wanConfigModal .form-select:focus {
        border-color: var(--orange, #f97316) !important;
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--orange, #f97316) 18%, transparent) !important;
        outline: none !important;
    }

    #profileModal .form-control:focus,
    #profileModal .form-select:focus {
        border-color: #a855f7 !important;
        box-shadow: 0 0 0 3px rgba(168,85,247,.15) !important;
        outline: none !important;
    }

    #wanConfigModal button,
    #wanConfigModal input,
    #wanConfigModal select,
    #profileModal button,
    #profileModal input,
    #profileModal select {
        pointer-events: auto !important;
    }

    /* ── Info box inside modals ─────────────────────────────────────── */
    .nk-modal-info {
        background: color-mix(in srgb, #2563eb 7%, var(--surface));
        border: 1px solid color-mix(in srgb, #2563eb 20%, var(--border));
        border-radius: .5rem;
        padding: .75rem;
        font-size: .78rem;
        line-height: 1.6;
        margin-bottom: .85rem;
    }

    .nk-modal-info-warn {
        background: color-mix(in srgb, var(--orange, #f97316) 7%, var(--surface));
        border: 1px solid color-mix(in srgb, var(--orange, #f97316) 22%, var(--border));
    }

    .nk-modal-info-purple {
        background: color-mix(in srgb, #a855f7 7%, var(--surface));
        border: 1px solid color-mix(in srgb, #a855f7 22%, var(--border));
    }

    .nk-modal-info-green {
        background: color-mix(in srgb, #16a34a 7%, var(--surface));
        border: 1px solid color-mix(in srgb, #16a34a 22%, var(--border));
    }

    /* ── Section dividers ───────────────────────────────────────────── */
    .nk-modal-section {
        border-top: 1px solid var(--border) !important;
        padding-top: .85rem !important;
        margin-top: .85rem !important;
    }

    .nk-modal-section-title {
        font-size: .67rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: .6px !important;
        color: var(--text-muted, #6b7280) !important;
        margin-bottom: .65rem !important;
    }

    /* ── Password toggle button ─────────────────────────────────────── */
    .nk-pass-toggle {
        background: var(--bg, #f7f7f8) !important;
        border: 1px solid var(--border) !important;
        border-left: 0 !important;
        color: var(--text-muted, #6b7280) !important;
        cursor: pointer !important;
        padding: 0 .55rem !important;
        border-radius: 0 .375rem .375rem 0 !important;
        pointer-events: auto !important;
    }

    .nk-pass-toggle:hover { color: var(--txt, inherit) !important; }

    .olt-show-page .modal-body [style*="background:#1a2540"] {
        background: color-mix(in srgb, var(--nk-info) 9%, var(--surface)) !important;
        border-color: color-mix(in srgb, var(--nk-info) 24%, var(--border)) !important;
    }

    .olt-show-page .modal-body [style*="background:#2a1845"] {
        background: color-mix(in srgb, var(--nk-primary) 10%, var(--surface)) !important;
        border-color: color-mix(in srgb, var(--nk-primary) 24%, var(--border)) !important;
    }

    .olt-show-page .modal-body .form-control[style*="background:#252840"],
    .olt-show-page .modal-body .form-select[style*="background:#252840"] {
        background: var(--surface-2) !important;
        border-color: var(--border) !important;
        color: var(--txt) !important;
    }

    .olt-show-page .modal-footer[style*="background:#1e2235"] {
        background: var(--surface) !important;
        border-top-color: var(--border) !important;
    }

    .olt-show-page .modal-footer .btn[style*="background:var(--orange)"] {
        background: var(--olt-orange) !important;
        color: #fff !important;
    }

    .olt-show-page .modal-footer .btn[style*="background:#a855f7"] {
        background: var(--olt-purple) !important;
        color: #fff !important;
    }

    .olt-show-page .ms-table-shell .table > :not(caption) > * > * {
        background: transparent;
    }

    /* Table: override workspace-shell min-width, use fixed layout */
    #ont-table { table-layout: fixed !important; width: 1035px !important; min-width: 0 !important; }
    #ont-table th:nth-child(1),  #ont-table td:nth-child(1)  { width: 42px  !important; min-width: 42px  !important; max-width: 42px  !important; }
    #ont-table th:nth-child(2),  #ont-table td:nth-child(2)  { width: 145px !important; min-width: 145px !important; max-width: 145px !important; }
    #ont-table th:nth-child(3),  #ont-table td:nth-child(3)  { width: 110px !important; min-width: 110px !important; max-width: 110px !important; }
    #ont-table th:nth-child(4),  #ont-table td:nth-child(4)  { width: 95px  !important; min-width: 95px  !important; max-width: 95px  !important; }
    #ont-table th:nth-child(5),  #ont-table td:nth-child(5)  { width: 78px  !important; min-width: 78px  !important; max-width: 78px  !important; }
    #ont-table th:nth-child(6),  #ont-table td:nth-child(6)  { width: 100px !important; min-width: 100px !important; max-width: 100px !important; }
    #ont-table th:nth-child(7),  #ont-table td:nth-child(7)  { width: 100px !important; min-width: 100px !important; max-width: 100px !important; }
    #ont-table th:nth-child(8),  #ont-table td:nth-child(8)  { width: 85px  !important; min-width: 85px  !important; max-width: 85px  !important; }
    #ont-table th:nth-child(9),  #ont-table td:nth-child(9)  { width: 200px !important; min-width: 200px !important; max-width: 200px !important; }
    #ont-table th:nth-child(10), #ont-table td:nth-child(10) { width: 210px !important; min-width: 210px !important; max-width: 210px !important; }
    #ont-table th, #ont-table td { white-space: nowrap !important; overflow: hidden; text-overflow: ellipsis; }
    #ont-table td { padding: .45rem .75rem !important; }
    #ont-table th { padding: .5rem .75rem !important; }
    #ont-table td:nth-child(9) { white-space: normal !important; }

    /* Filter tabs — shadcn plain tab style */
    .ont-filter-tabs {
        display: inline-flex; align-items: center; gap: 1px;
    }
    .ont-filter-tab {
        display: inline-flex; align-items: center;
        padding: .25rem .75rem; font-size: .78rem; font-weight: 500;
        border-radius: 6px; border: none;
        background: transparent; color: var(--txt-3);
        cursor: pointer; transition: color .12s, background .12s;
        white-space: nowrap; line-height: 1.5;
    }
    .ont-filter-tab:hover { color: var(--txt); background: var(--surface-2); }
    .ont-filter-tab.active { color: var(--txt); font-weight: 600; background: var(--surface-2); }

    /* Fix Action column buttons border-radius */
    #ont-table .btn, #ont-table .btn-sm { border-radius: 6px !important; }
    #ont-table .dropdown-menu { border-radius: 8px !important; border: 1px solid var(--border) !important; }

    /* Customer link cell — native select */
    .ont-link-select {
        width: 150px; font-size: .72rem; height: 26px;
        padding: 0 6px;
        border: 1px solid var(--border); border-radius: 6px;
        background: var(--surface); color: var(--txt-3);
        outline: none; cursor: pointer;
    }
    /* ── WAN Config Modal form section dividers ─────────────────────── */
    .nk-modal-section {
        border-top: 1px solid #2d3349;
        padding-top: .85rem;
        margin-top: .85rem;
    }
    .nk-modal-section-title {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #6b7280;
        margin-bottom: .65rem;
    }
    .nk-pass-toggle {
        background: #2d3349; border: 1px solid #3a4060; border-left: 0;
        color: #9ba3c0; cursor: pointer; padding: 0 .55rem;
        border-radius: 0 4px 4px 0;
    }
    .nk-pass-toggle:hover { color: #e2e8f0; }
    /* ── Flash notification banner ────────────────────────────────────── */
    .nk-flash {
        display: flex; align-items: center; gap: .6rem;
        padding: .65rem 1rem; border-radius: .5rem;
        margin-bottom: .75rem; font-size: .84rem; font-weight: 500;
        animation: nkFlashIn .25s ease;
    }
    @keyframes nkFlashIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:none; } }
    .nk-flash i { font-size: 1.1rem; flex-shrink: 0; }
    .nk-flash span { flex: 1; }
    .nk-flash button { background: none; border: none; cursor: pointer; padding: 0; line-height: 1; opacity: .7; }
    .nk-flash button:hover { opacity: 1; }
    .nk-flash-success { background: rgba(22,163,74,.12); border: 1px solid rgba(22,163,74,.35); color: #15803d; }
    .nk-flash-error   { background: rgba(220,38,38,.10); border: 1px solid rgba(220,38,38,.35); color: #b91c1c; }
    .nk-flash-info    { background: rgba(37,99,235,.08); border: 1px solid rgba(37,99,235,.25); color: #1e40af; }

    /* Link + Actions buttons — compact xs */
    .ont-action-btn {
        display: inline-flex; align-items: center; gap: 3px;
        padding: 2px 7px; font-size: .68rem; font-weight: 500;
        border-radius: 6px; cursor: pointer; white-space: nowrap;
        background: rgba(249,115,22,.1); color: var(--orange);
        border: 1px solid rgba(249,115,22,.25); line-height: 1.6;
    }
    .ont-action-btn:hover { background: rgba(249,115,22,.18); color: var(--orange); }
    .ont-action-btn i { font-size: .75rem; }

    .ont-row-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        justify-content: flex-end;
    }

    .ont-row-actions form {
        margin: 0;
    }

    /* Fix DataTables controls border-radius → shadcn style */
    #ont-table_wrapper input,
    #ont-table_wrapper input[type="search"],
    #ont-table_wrapper select,
    #ont-table_wrapper .dataTables_filter input,
    #ont-table_wrapper .dataTables_length select,
    #ont-table_wrapper .form-control,
    #ont-table_wrapper .form-select {
        border-radius: 6px !important;
        border: 1px solid var(--border) !important;
        background: var(--surface) !important;
        color: var(--txt) !important;
        font-size: .8125rem !important;
        padding: .3rem .65rem !important;
        outline: none !important;
        box-shadow: none !important;
        --bs-border-radius: 6px !important;
        --bs-border-radius-sm: 6px !important;
        --bs-border-radius-lg: 6px !important;
        --bs-border-radius-pill: 6px !important;
    }

    .olt-show-page .ms-table-shell {
        padding: 0 !important; margin: 0 !important;
        border: 0 !important; background: transparent !important; box-shadow: none !important;
        border-top: 1px solid var(--border) !important;
    }
    .olt-show-page .ms-table-shell .table-responsive { border: 0 !important; background: transparent !important; }
    .olt-show-page .ms-table-shell .dataTables_wrapper { padding: 0 !important; }
    /* Remove ms-panel outer box for ONT section */
    .olt-show-page .ms-panel { border: none !important; box-shadow: none !important; background: transparent !important; border-radius: 0 !important; }
    .olt-show-page .ms-panel-head { border-bottom: 1px solid var(--border) !important; border-radius: 0 !important; background: transparent !important; }
    .olt-show-page .ms-panel-body { background: transparent !important; }

    .olt-show-page [style*="color:#94a3b8"] {
        color: var(--txt-3) !important;
    }

    .olt-show-page [style*="color:#a855f7"] {
        color: var(--olt-purple) !important;
    }

    .olt-show-page [style*="background:rgba(148,163,184,.1)"] {
        background: color-mix(in srgb, var(--border) 55%, transparent) !important;
        color: var(--txt-3) !important;
        border-color: var(--border) !important;
    }

    .olt-show-page [style*="background:#252840"] {
        background: var(--surface-2) !important;
        border-color: var(--border) !important;
        color: var(--txt) !important;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page olt-show-page">
<div class="ms-page-head">
    <div>
        <div class="ms-page-kicker"><i class='bx bx-server'></i> Inventaris OLT</div>
        <h1 class="ms-page-title"><?php echo e($olt->name); ?></h1>
        <div class="ms-page-subtitle"><?php echo e($olt->brand); ?> <?php echo e($olt->model); ?> · <?php echo e($olt->ip_address); ?></div>
    </div>
    <div class="ms-page-actions">
        <form action="<?php echo e(route('admin.olts.sync', $olt)); ?>" method="POST" class="d-inline">
            <?php echo csrf_field(); ?>
            <button type="submit" class="ms-btn">
                <i class='bx bx-refresh'></i> Sinkron ONT
            </button>
        </form>
        <form action="<?php echo e(route('admin.olts.sync-now', $olt)); ?>" method="POST" class="d-inline"
              onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=\'bx bx-loader-alt bx-spin\'></i> Sedang Sync';">
            <?php echo csrf_field(); ?>
            <button type="submit" class="ms-btn-secondary" title="Jalankan sync sekarang, langsung tanpa queue worker.">
                <i class='bx bx-bolt-circle'></i> Sync Langsung
            </button>
        </form>
        <button type="button" id="nk-ar-btn" class="ms-btn-ghost" onclick="nkArToggle()" title="Auto-refresh halaman setiap 30 detik" style="color:var(--orange);">
            <i class='bx bx-time-five me-1'></i><span id="nk-ar-label">Refresh dalam 0:30</span>
        </button>
        <a href="<?php echo e(route('admin.olts.edit', $olt)); ?>" class="ms-btn-ghost">
            <i class='bx bx-edit'></i> Ubah
        </a>
    </div>
</div>

<div class="ms-inline-kpis mb-3">
    <a href="<?php echo e(route('admin.olts.index')); ?>" class="ms-kpi-chip" style="text-decoration:none;">
        <i class='bx bx-arrow-back me-1'></i> Back to OLT Devices
    </a>
</div>

<?php if(session('success')): ?>
<div class="nk-flash nk-flash-success" id="nk-flash-msg">
    <i class='bx bx-check-circle'></i>
    <span><?php echo e(session('success')); ?></span>
    <button type="button" onclick="this.parentElement.remove()"><i class='bx bx-x'></i></button>
</div>
<?php elseif(session('error')): ?>
<div class="nk-flash nk-flash-error" id="nk-flash-msg">
    <i class='bx bx-error-circle'></i>
    <span><?php echo e(session('error')); ?></span>
    <button type="button" onclick="this.parentElement.remove()"><i class='bx bx-x'></i></button>
</div>
<?php elseif(session('info')): ?>
<div class="nk-flash nk-flash-info" id="nk-flash-msg">
    <i class='bx bx-info-circle'></i>
    <span><?php echo e(session('info')); ?></span>
    <button type="button" onclick="this.parentElement.remove()"><i class='bx bx-x'></i></button>
</div>
<?php endif; ?>

<div class="alert alert-info py-2 px-3 mb-3" style="font-size:.82rem;border:1px solid rgba(37,99,235,.16);background:rgba(37,99,235,.06);color:#1e3a8a;">
    <i class='bx bx-info-circle me-1'></i>
    Flow aktif saat ini: <strong>PPPoE per area -> Customer -> Sync ONT dari OLT -> Assign ONT manual</strong>.
</div>


<?php
    $onlinePct = $stats['total'] > 0 ? round($stats['online'] / $stats['total'] * 100) : 0;
?>


<div class="ms-stat-grid mb-4">
    <div class="ms-stat-card" style="--stat-accent:#2563eb;--stat-bg:#eff6ff;">
        <div class="ms-stat-icon"><i class='bx bx-chip'></i></div>
        <div>
            <div class="ms-stat-label">Total ONTs</div>
            <div class="ms-stat-value"><?php echo e($stats['total']); ?></div>
        </div>
    </div>
    <div class="ms-stat-card" style="--stat-accent:#22c55e;--stat-bg:#ecfdf3;">
        <div class="ms-stat-icon"><i class='bx bx-check-circle'></i></div>
        <div>
            <div class="ms-stat-label">Online</div>
            <div class="ms-stat-value" style="color:var(--green);"><?php echo e($stats['online']); ?></div>
        </div>
    </div>
    <div class="ms-stat-card" style="--stat-accent:#ef4444;--stat-bg:#fef2f2;">
        <div class="ms-stat-icon"><i class='bx bx-x-circle'></i></div>
        <div>
            <div class="ms-stat-label">Offline</div>
            <div class="ms-stat-value" style="color:var(--red);"><?php echo e($stats['offline']); ?></div>
        </div>
    </div>
    <div class="ms-stat-card" style="--stat-accent:#f97316;--stat-bg:#fff7ed;">
        <div class="ms-stat-icon"><i class='bx bx-link'></i></div>
        <div>
            <div class="ms-stat-label">Linked</div>
            <div class="ms-stat-value"><?php echo e($stats['linked']); ?></div>
        </div>
    </div>
    <div class="ms-stat-card" style="--stat-accent:#a855f7;--stat-bg:#faf5ff;">
        <div class="ms-stat-icon"><i class='bx bx-unlink'></i></div>
        <div>
            <div class="ms-stat-label">Unlinked</div>
            <div class="ms-stat-value" style="color:#a855f7;"><?php echo e($stats['unlinked']); ?></div>
        </div>
    </div>
    <div class="ms-stat-card" style="--stat-accent:#0f766e;--stat-bg:#f0fdfa;">
        <div class="ms-stat-icon"><i class='bx bx-network-chart'></i></div>
        <div>
            <div class="ms-stat-label">Protocol</div>
            <div class="ms-stat-value" style="font-size:1rem;"><?php echo e(strtoupper($olt->preferred_protocol)); ?></div>
            <div class="ms-stat-meta"><?php echo e($olt->ip_address); ?></div>
        </div>
    </div>
</div>


<div class="ms-panel">
    <div class="ms-panel-head d-flex justify-content-between align-items-center">
        <span class="ms-panel-title">
            <i class='bx bx-chip me-2' style="color:var(--orange);"></i>ONT Inventory
            <span class="ms-2 ms-kpi-chip"><strong><?php echo e($stats['total']); ?></strong> devices</span>
        </span>
        <div class="d-flex align-items-center gap-2">
            <span class="d-flex align-items-center gap-1" style="font-size:.7rem;">
                <span style="width:7px;height:7px;border-radius:50%;background:var(--green);display:inline-block;animation:pulse-green 2s infinite;"></span>
                <span style="color:var(--green);"><?php echo e($stats['online']); ?> online</span>
            </span>
            <span style="font-size:.7rem;color:var(--text-muted);"><?php echo e($onts->first()?->last_synced_at?->diffForHumans() ?? 'Never synced'); ?></span>
        </div>
    </div>
    
    <div class="ms-panel-body pt-0 pb-0">
        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="d-flex gap-3 flex-wrap align-items-center">
                <div style="position:relative;display:flex;align-items:center;">
                    <i class='bx bx-search' style="position:absolute;left:.6rem;color:var(--orange);font-size:.9rem;pointer-events:none;"></i>
                    <input type="text" id="sn-search" autofocus
                        placeholder="Search by Serial Number / Name / PON..."
                        style="padding:.35rem .75rem .35rem 2rem;font-size:.8125rem;border:1px solid var(--border);border-radius:6px;background:var(--surface);color:var(--txt);outline:none;width:340px;font-family:inherit;">
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size:.76rem;color:var(--txt-3);font-weight:500;">Show</span>
                    <select id="ont-length" class="no-select2" style="width:70px;padding:.3rem .4rem;font-size:.8125rem;border:1px solid var(--border);border-radius:6px;background:var(--surface);color:var(--txt);outline:none;font-family:inherit;cursor:pointer;">
                        <option value="25">25</option>
                        <option value="50" selected>50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                    </select>
                </div>
            </div>
            <div class="ont-filter-tabs">
                <button type="button" class="ont-filter-tab ont-quick-filter active" data-filter="all">All</button>
                <button type="button" class="ont-filter-tab ont-quick-filter" data-filter="online">Online</button>
                <button type="button" class="ont-filter-tab ont-quick-filter" data-filter="offline">Offline</button>
                <button type="button" class="ont-filter-tab ont-quick-filter" data-filter="linked">Linked</button>
                <button type="button" class="ont-filter-tab ont-quick-filter" data-filter="unlinked">Unlinked</button>
            </div>
        </div>
    </div>
    <div class="ms-table-shell">
    <div class="table-responsive mt-2">
        <table class="table table-flat mb-0" id="ont-table">
            <thead>
                <tr>
                    <th style="width:38px">#</th>
                    <th style="width:140px">Serial Number</th>
                    <th style="width:110px">ONT Name</th>
                    <th style="width:90px">PON Port</th>
                    <th style="width:75px">Status</th>
                    <th style="width:95px">Rx Power</th>
                    <th style="width:95px">Tx Power</th>
                    <th style="width:80px">Distance</th>
                    <th style="width:230px">Customer</th>
                    <th style="width:80px">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $onts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $ont): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $q = $ont->signal_quality ?? 'unknown';
                    $rxColor = match($q) {
                        'excellent' => 'var(--green)',
                        'good' => 'var(--blue)',
                        'fair' => '#eab308',
                        'weak' => 'var(--red)',
                        default => 'var(--text-muted)',
                    };
                ?>
                <tr class="<?php echo e($ont->status === 'offline' ? 'ont-offline-row' : ''); ?>"
                    data-status="<?php echo e($ont->status ?? 'unknown'); ?>"
                    data-linked="<?php echo e($ont->customer_id ? '1' : '0'); ?>">
                    <td class="cell-nowrap cell-index" style="font-size:.75rem;color:var(--text-muted);"><?php echo e($loop->iteration); ?></td>
                    <td class="cell-nowrap cell-serial">
                        <code style="font-size:.75rem;font-weight:600;white-space:nowrap;display:inline-block;"><?php echo e($ont->serial_number); ?></code>
                    </td>
                    <td style="min-width:90px;font-size:.8rem;">
                        <?php echo e($ont->description ?? '—'); ?>

                    </td>
                    <td style="white-space:nowrap;">
                        <span style="font-size:.8rem;"><?php echo e($ont->pon_port ?? '—'); ?>:<?php echo e($ont->olt_port_index ?? '—'); ?></span>
                    </td>
                    <td>
                        <?php if($ont->status === 'online'): ?>
                        <span class="badge-status badge-active">
                            <i class='bx bxs-circle bx-flashing' style="font-size:.4rem;margin-right:3px;vertical-align:middle;"></i>Online
                        </span>
                        <?php elseif($ont->status === 'offline'): ?>
                        <span class="badge-status badge-inactive">Offline</span>
                        <?php else: ?>
                        <span class="badge-status" style="background:rgba(148,163,184,.1);color:#94a3b8;">Unknown</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <?php if($ont->rx_power !== null): ?>
                        <div>
                            <span style="color:<?php echo e($rxColor); ?>;font-weight:600;font-size:.8rem;"><?php echo e(number_format($ont->rx_power, 2)); ?> dBm</span>
                        </div>
                        <?php if($q !== 'unknown'): ?>
                        <div style="font-size:.6rem;color:<?php echo e($rxColor); ?>;text-transform:uppercase;font-weight:600;letter-spacing:.4px;margin-top:1px;"><?php echo e($q); ?></div>
                        <?php endif; ?>
                        <?php else: ?>
                        <span style="color:var(--text-muted);font-size:.75rem;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($ont->tx_power !== null): ?>
                        <span style="font-size:.8rem;"><?php echo e(number_format($ont->tx_power, 2)); ?> dBm</span>
                        <?php else: ?>
                        <span style="color:var(--text-muted);font-size:.75rem;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($ont->distance): ?>
                        <span style="font-size:.8rem;"><?php echo e(number_format($ont->distance)); ?>m</span>
                        <?php else: ?>
                        <span style="color:var(--text-muted);font-size:.75rem;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($ont->customer): ?>
                        <a href="<?php echo e(route('admin.customers.show', $ont->customer)); ?>" style="text-decoration:none;font-size:.8rem;font-weight:500;">
                            <i class='bx bx-user me-1' style="font-size:.7rem;"></i><?php echo e($ont->customer->name); ?>

                        </a>
                        <?php else: ?>
                        <form action="<?php echo e(route('admin.olts.link-customer', $ont)); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <div class="d-flex gap-1 align-items-center">
                                <select name="customer_id" class="ont-link-select no-select2"
                                    data-ont-id="<?php echo e($ont->id); ?>">
                                    <option value="">Link customer…</option>
                                    <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?><?php echo e($c->pppoe_user ? ' ('.$c->pppoe_user.')' : ''); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <button type="submit" class="ont-action-btn">
                                    <i class='bx bx-link'></i> Link
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="ont-row-actions">
                            <form action="<?php echo e(route('admin.olts.reboot-ont', $ont)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="ont-action-btn"
                                    onclick="return confirm('Reboot ONT <?php echo e($ont->serial_number); ?>?')">
                                    <i class='bx bx-power-off'></i> Reboot
                                </button>
                            </form>

                            <button type="button" class="ont-action-btn"
                                onclick="return nkOpenWanConfigFromTrigger(this);"
                                data-wan-url="<?php echo e(route('admin.olts.wan-config', $ont)); ?>"
                                data-ont-sn="<?php echo e($ont->serial_number); ?>"
                                data-ont-pon="<?php echo e($ont->pon_port); ?>:<?php echo e($ont->olt_port_index); ?>">
                                <i class='bx bx-network-chart'></i> WAN
                            </button>

                            <button type="button" class="ont-action-btn"
                                onclick="return nkOpenProfileFromTrigger(this);"
                                data-profile-url="<?php echo e(route('admin.olts.set-profile', $ont)); ?>"
                                data-ont-sn="<?php echo e($ont->serial_number); ?>"
                                data-ont-pon="<?php echo e($ont->pon_port); ?>:<?php echo e($ont->olt_port_index); ?>">
                                <i class='bx bx-tachometer'></i> Profile
                            </button>

                            <?php if($ont->customer_id): ?>
                            <form action="<?php echo e(route('admin.olts.link-customer', $ont)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="customer_id" value="">
                                <button type="submit" class="ont-action-btn"
                                    onclick="return confirm('Unlink customer from this ONT?')">
                                    <i class='bx bx-unlink'></i> Unlink
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>

                </tr>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="10" class="text-center py-5" style="color:var(--text-muted);">
                        <i class='bx bx-chip' style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                        <p class="fw-semibold mb-1">No ONTs found</p>
                        <p class="mb-0">Click <strong>"Sync ONTs from OLT"</strong> to fetch the inventory</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </div>
</div>
</div>


<?php $brand = strtolower($olt->brand); ?>
<div id="wanConfigModal" aria-hidden="true">
    <div class="modal-dialog">
        <form id="wanConfigForm" action="" method="POST">
            <?php echo csrf_field(); ?>
            <div class="modal-content">
                
                <div class="modal-header">
                    <div>
                        <h6 class="modal-title">
                            <i class='bx bx-network-chart me-2' style="color:var(--orange);"></i>Set WAN Config
                        </h6>
                        <small>
                            ONT <code id="wanOntSn" style="color:var(--orange);font-weight:600;"></code>
                            &nbsp;|&nbsp; PON <span id="wanOntPon" style="font-weight:600;"></span>
                            &nbsp;|&nbsp; <span style="text-transform:uppercase;font-weight:700;letter-spacing:.3px;"><?php echo e($olt->brand); ?></span>
                        </small>
                    </div>
                    <button type="button" class="btn-close" data-close-manual-modal></button>
                </div>

                
                <div class="modal-body" style="padding:1.25rem;">

                    
                    <div class="nk-modal-info nk-modal-info-warn">
                        <strong><i class='bx bx-info-circle me-1'></i>Tentang WAN Config</strong><br>
                        Konfigurasi layanan internet ONT via OLT CLI.
                        <?php if(str_contains($brand, 'tenda')): ?>
                            <strong>Tenda:</strong> perintah <code>service-port</code> + <code>gemport</code> + VLAN/mode dikirim ke OLT.
                        <?php elseif(str_contains($brand, 'cdata') || str_contains($brand, 'c-data')): ?>
                            <strong>C-Data:</strong> alur <em>T-CONT → GEM Port → Service-Port → VLAN</em> dikirim via Telnet.
                        <?php elseif(str_contains($brand, 'hsgq')): ?>
                            <strong>HSGQ G02ID:</strong> <code>wan add</code>, <code>wan vlan</code>, <code>wan mtu</code> dikirim ke context ONT.
                        <?php endif; ?>
                    </div>

                    
                    <div class="nk-modal-section-title">Koneksi WAN</div>
                    <div class="row g-3 mb-1">
                        <div class="col-sm-4">
                            <label class="form-label">WAN Index</label>
                            <select name="wan_index" class="form-select form-select-sm">
                                <?php $__currentLoopData = range(0,63); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wid): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($wid); ?>" <?php echo e($wid === 0 ? 'selected' : ''); ?>><?php echo e($wid); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="form-text">Index WAN pada ONT (default: 0)</div>
                        </div>
                        <div class="col-sm-8">
                            <label class="form-label">WAN Type <span class="text-danger">*</span></label>
                            <select name="mode" id="nkWanType" class="form-select form-select-sm" required
                                onchange="nkWanTypeChanged(this)">
                                <option value="pppoe">PPPoE — Route (auth ke PPPoE server)</option>
                                <option value="bridge">Bridge / IPoE — DHCP langsung dari WAN</option>
                                <option value="static">Static IP — IP tetap (router mode)</option>
                            </select>
                            <div class="form-text">PPPoE = auth user/pass &nbsp;·&nbsp; Bridge = DHCP otomatis &nbsp;·&nbsp; Static = IP manual</div>
                        </div>
                    </div>

                    
                    <div class="nk-modal-section">
                        <div class="nk-modal-section-title">Pengaturan VLAN</div>
                        <div class="row g-3 mb-1">
                            <div class="col-sm-3">
                                <label class="form-label">VLAN Mode</label>
                                <select name="vlan_mode" id="nkVlanMode" class="form-select form-select-sm"
                                    onchange="nkVlanModeChanged(this)">
                                    <option value="tagged">Tagged</option>
                                    <option value="untagged">Untagged</option>
                                </select>
                                <div class="form-text">Tagged = pakai VLAN ID</div>
                            </div>
                            <div class="col-sm-5" id="nkVlanIdWrap">
                                <label class="form-label">VLAN ID</label>
                                <input type="number" name="vlan_id" class="form-control form-control-sm"
                                    placeholder="1–4094" min="1" max="4094">
                                <div class="form-text">1–4094 sesuai VLAN di OLT/switch</div>
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">VLAN Priority (PCP)</label>
                                <select name="vlan_priority" class="form-select form-select-sm">
                                    <?php $__currentLoopData = range(0,7); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($p); ?>" <?php echo e($p === 0 ? 'selected' : ''); ?>>
                                        <?php echo e($p); ?><?php echo e($p === 0 ? ' — Best Effort' : ($p === 7 ? ' — Tertinggi' : '')); ?>

                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <div class="form-text">802.1p QoS (0–7)</div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="nk-modal-section" id="nkPppoeSection">
                        <div class="nk-modal-section-title">
                            PPPoE Authentication
                            <span style="font-weight:400;text-transform:none;letter-spacing:0;">&nbsp;— opsional, jika ONT dial sendiri</span>
                        </div>
                        <div class="row g-3 mb-1">
                            <div class="col-sm-6">
                                <label class="form-label">Username</label>
                                <input type="text" name="pppoe_username" class="form-control form-control-sm"
                                    placeholder="e.g. user@isp.net" autocomplete="off">
                                <div class="form-text">Kosongkan jika PPPoE dihandle MikroTik</div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Password</label>
                                <div class="input-group input-group-sm">
                                    <input type="password" name="pppoe_password" id="nkPppoePass"
                                        class="form-control form-control-sm"
                                        placeholder="••••••••" autocomplete="new-password"
                                        style="border-right:0;">
                                    <button type="button" class="nk-pass-toggle" onclick="nkTogglePppoePass()" title="Tampilkan/sembunyikan">
                                        <i class='bx bx-show' id="nkPppoePassIcon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="nk-modal-section">
                        <div class="nk-modal-section-title">MTU &amp; Port GPON</div>
                        <div class="row g-3 mb-1">
                            <div class="col-sm-3">
                                <label class="form-label">MTU Size</label>
                                <input type="number" name="mtu" id="nkMtuInput" class="form-control form-control-sm"
                                    value="1500" min="1492" max="9216">
                                <div class="form-text">PPPoE = 1492 &nbsp;·&nbsp; Bridge = 1500</div>
                            </div>
                            <?php if(str_contains($brand, 'cdata') || str_contains($brand, 'c-data')): ?>
                            <div class="col-sm-3">
                                <label class="form-label">Service Port ID</label>
                                <input type="number" name="service_port" class="form-control form-control-sm"
                                    placeholder="Auto" min="1" max="4094">
                                <div class="form-text">ID service-port (kosong = auto)</div>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label">GEM Port</label>
                                <input type="number" name="gem_port" class="form-control form-control-sm"
                                    value="1" min="1" max="8">
                                <div class="form-text">GPON GEM channel (default: 1)</div>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label">T-CONT Slot</label>
                                <input type="number" name="tcont_slot" class="form-control form-control-sm"
                                    value="1" min="1" max="8">
                                <div class="form-text">Bandwidth slot (default: 1)</div>
                            </div>
                            <?php elseif(str_contains($brand, 'tenda')): ?>
                            <div class="col-sm-4">
                                <label class="form-label">GEM Port</label>
                                <input type="number" name="gem_port" class="form-control form-control-sm"
                                    value="1" min="1" max="8">
                                <div class="form-text">GPON GEM port index (default: 1)</div>
                            </div>
                            <div class="col-sm-5">
                                <label class="form-label">
                                    Service Port ID
                                    <span class="form-text d-inline" style="text-transform:none;letter-spacing:0;">(opsional)</span>
                                </label>
                                <input type="number" name="service_port" class="form-control form-control-sm"
                                    placeholder="Auto" min="1" max="4094">
                                <div class="form-text">Nomor service-port di OLT (auto jika kosong)</div>
                            </div>
                            <?php else: ?>
                            <div class="col-sm-9">
                                <div class="nk-modal-info nk-modal-info-green mb-0" style="margin-top:.25rem;">
                                    <i class='bx bx-info-circle me-1'></i>
                                    HSGQ: WAN Index + VLAN + MTU + PPPoE auth dikirim ke ONT context.
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <?php if(!str_contains($brand, 'hsgq')): ?>
                    <div class="nk-modal-section">
                        <div class="nk-modal-section-title">
                            Profil Bandwidth DBA
                            <span style="font-weight:400;text-transform:none;letter-spacing:0;">&nbsp;— opsional</span>
                        </div>
                        <input type="text" name="profile" class="form-control form-control-sm"
                            placeholder="e.g. best-effort, 10M-profile">
                        <div class="form-text">Nama DBA profile di OLT untuk kontrol bandwidth upstream. Kosongkan jika belum ada.</div>
                    </div>
                    <?php endif; ?>

                </div>

                
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-close-manual-modal>
                        <i class='bx bx-x me-1'></i>Batal
                    </button>
                    <button type="submit" class="btn btn-sm" style="background:var(--orange);color:#fff;font-weight:600;padding:.35rem 1.1rem;">
                        <i class='bx bx-send me-1'></i>Push WAN Config ke OLT
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>


<div id="profileModal" aria-hidden="true">
    <div class="modal-dialog">
        <form id="profileForm" action="" method="POST">
            <?php echo csrf_field(); ?>
            <div class="modal-content">
                
                <div class="modal-header">
                    <div>
                        <h6 class="modal-title">
                            <i class='bx bx-tachometer me-2' style="color:#a855f7;"></i>Ganti DBA / Bandwidth Profile
                        </h6>
                        <small>
                            ONT <code id="profileOntSn" style="color:#a855f7;font-weight:600;"></code>
                            &nbsp;|&nbsp; PON <span id="profileOntPon" style="font-weight:600;"></span>
                        </small>
                    </div>
                    <button type="button" class="btn-close" data-close-manual-modal></button>
                </div>

                
                <div class="modal-body" style="padding:1.25rem;">

                    
                    <div class="nk-modal-info nk-modal-info-purple mb-3">
                        <strong><i class='bx bx-info-circle me-1'></i>Apa itu DBA Profile?</strong><br>
                        <strong>DBA = Dynamic Bandwidth Allocation</strong> — profile yang mengatur <strong>kecepatan upload</strong> (upstream) customer.
                        Gunakan untuk <strong>upgrade/downgrade paket</strong> tanpa reprovision WAN dari awal.<br><br>
                        ⚠️ Profile harus <strong>sudah dibuat di OLT</strong> (via web OLT atau CLI), baru bisa di-bind di sini.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Nama DBA Profile <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="profile_name" class="form-control form-control-sm"
                            placeholder="e.g. best-effort, 10M-up, bandwidth-10M" required>
                        <div class="form-text">
                            Nama harus sama persis dengan yang ada di OLT
                            <?php if(str_contains($brand,'cdata') || str_contains($brand,'c-data')): ?>
                            (cek: <code>show dba-profile all</code>)
                            <?php elseif(str_contains($brand,'hsgq')): ?>
                            (cek: <code>show gpon dba-profile all</code>)
                            <?php else: ?>
                            (cek via menu Profile di web OLT)
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if(str_contains($brand,'cdata') || str_contains($brand,'c-data') || str_contains($brand,'hsgq')): ?>
                    <div class="nk-modal-section">
                        <div class="nk-modal-section-title">Pengaturan T-CONT</div>
                        <div class="mb-1">
                            <label class="form-label">T-CONT Slot</label>
                            <input type="number" name="tcont_slot" class="form-control form-control-sm"
                                value="1" min="1" max="8">
                            <div class="form-text">Index T-CONT yang di-rebind (default: 1)</div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>

                
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-close-manual-modal>
                        <i class='bx bx-x me-1'></i>Batal
                    </button>
                    <button type="submit" class="btn btn-sm" style="background:#a855f7;color:#fff;font-weight:600;padding:.35rem 1.1rem;">
                        <i class='bx bx-send me-1'></i>Terapkan Profile ke ONT
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<style>
    .stat-card { transition: transform .2s ease, box-shadow .2s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.15); }
    .stat-label { font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:2px; }
    .stat-value { font-size:1.4rem;font-weight:700;line-height:1.2; }
    .ont-offline-row { opacity: .45; }
    .ont-offline-row:hover { opacity: 1; }
    @keyframes pulse-green { 0%,100% { opacity:1; } 50% { opacity:.4; } }
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
<script>
    $(function() {
        var currentQuickFilter = 'all';
        if ($('#ont-table tbody tr td[colspan]').length === 0) {
            var dt = $('#ont-table').DataTable({
                autoWidth: false,
                dom: '<"d-flex justify-content-between align-items-center mb-3"f<"text-muted small"l>><rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                pageLength: 50,
                order: [[4, 'asc'], [3, 'asc']],
                language: {
                    search: '', searchPlaceholder: 'Search SN / PON...',
                    lengthMenu: 'Show _MENU_', info: '_START_-_END_ of _TOTAL_',
                    paginate: { previous: '‹ Sebelumnya', next: 'Selanjutnya ›' }
                },
                columnDefs: [
                    { orderable: false, targets: [9] },
                    { className: 'dt-nowrap', targets: [0,3,4,5,6,7] }
                ]
            });

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'ont-table') return true;
                if (currentQuickFilter === 'all') return true;

                var row = dt.row(dataIndex).node();
                if (!row) return true;

                var $row = $(row);
                var status = String($row.data('status') || '');
                var linked = String($row.data('linked') || '0');

                if (currentQuickFilter === 'online') return status === 'online';
                if (currentQuickFilter === 'offline') return status === 'offline';
                if (currentQuickFilter === 'linked') return linked === '1';
                if (currentQuickFilter === 'unlinked') return linked === '0';
                return true;
            });

            $('#sn-search').on('keyup input', function() {
                dt.search(this.value).draw();
            });

            $('#ont-length').on('change', function() {
                dt.page.len(parseInt(this.value, 10)).draw();
            });

            $('.ont-quick-filter').on('click', function() {
                currentQuickFilter = $(this).data('filter');
                $('.ont-quick-filter').removeClass('active');
                $(this).addClass('active');
                dt.draw();
            });
        }


        // ── Dropdown fix: use Popper strategy:fixed so overflow containers don't clip ──
        document.querySelectorAll('#ont-table .dropdown-toggle').forEach(function(el) {
            new bootstrap.Dropdown(el, {
                popperConfig: { strategy: 'fixed' }
            });
        });

        function populateWanModal(btn) {
            var modalEl = document.getElementById('wanConfigModal');
            if (!modalEl || !btn) return;
            document.getElementById('wanConfigForm').action   = btn.dataset.wanUrl || '';
            document.getElementById('wanOntSn').textContent   = btn.dataset.ontSn || '';
            document.getElementById('wanOntPon').textContent  = btn.dataset.ontPon || '';
            // Reset all fields to defaults
            modalEl.querySelectorAll('input[type=number]').forEach(function(i){ i.value = i.defaultValue || ''; });
            modalEl.querySelectorAll('input[type=text], input[type=password]').forEach(function(i){ i.value = ''; });
            modalEl.querySelectorAll('select').forEach(function(s){ s.selectedIndex = 0; });
            // Re-apply conditional UI state
            var wanTypeEl = document.getElementById('nkWanType');
            var vlanModeEl = document.getElementById('nkVlanMode');
            if (wanTypeEl) nkWanTypeChanged(wanTypeEl);
            if (vlanModeEl) nkVlanModeChanged(vlanModeEl);
        }

        function populateProfileModal(btn) {
            var modalEl = document.getElementById('profileModal');
            if (!modalEl || !btn) return;
            document.getElementById('profileForm').action       = btn.dataset.profileUrl || '';
            document.getElementById('profileOntSn').textContent = btn.dataset.ontSn || '';
            document.getElementById('profileOntPon').textContent = btn.dataset.ontPon || '';
            var profileField = modalEl.querySelector('input[name=profile_name]');
            if (profileField) profileField.value = '';
        }

        function nkCleanupModalState() {
            document.querySelectorAll('.modal-backdrop').forEach(function(el) { el.remove(); });
            document.body.classList.remove('modal-open');
            document.body.classList.remove('nk-modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
        }

        function nkOpenManualModal(modalEl) {
            if (!modalEl) return;
            nkCleanupModalState();
            modalEl.classList.add('is-open');
            modalEl.setAttribute('aria-hidden', 'false');
            document.body.classList.add('nk-modal-open');
        }

        function nkCloseManualModal(modalEl) {
            if (!modalEl) return;
            modalEl.classList.remove('is-open');
            modalEl.setAttribute('aria-hidden', 'true');
            nkCleanupModalState();
        }

        // ── WAN Config modal dynamic field helpers ────────────────────────────
        window.nkWanTypeChanged = function(sel) {
            var type = sel.value;
            var pppoeSection = document.getElementById('nkPppoeSection');
            var mtuInput     = document.getElementById('nkMtuInput');
            if (pppoeSection) pppoeSection.style.display = (type === 'pppoe') ? '' : 'none';
            if (mtuInput && !mtuInput._userEdited) {
                mtuInput.value = (type === 'pppoe') ? '1492' : '1500';
            }
        };
        window.nkVlanModeChanged = function(sel) {
            var wrap = document.getElementById('nkVlanIdWrap');
            if (!wrap) return;
            var isUntagged = sel.value === 'untagged';
            wrap.style.display = isUntagged ? 'none' : '';
            if (isUntagged) {
                var vlanIn = wrap.querySelector('input[name=vlan_id]');
                if (vlanIn) vlanIn.value = '';
            }
        };
        window.nkTogglePppoePass = function() {
            var input = document.getElementById('nkPppoePass');
            var icon  = document.getElementById('nkPppoePassIcon');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) { icon.classList.remove('bx-show'); icon.classList.add('bx-hide'); }
            } else {
                input.type = 'password';
                if (icon) { icon.classList.remove('bx-hide'); icon.classList.add('bx-show'); }
            }
        };

        window.nkOpenWanConfigFromTrigger = function(btn) {
            if (!btn) return false;
            var wanDropdownToggle = btn.closest('.dropdown')?.querySelector('.dropdown-toggle');
            var wanDropdown = wanDropdownToggle ? bootstrap.Dropdown.getInstance(wanDropdownToggle) : null;
            if (wanDropdown) wanDropdown.hide();
            nkCleanupModalState();
            populateWanModal(btn);
            setTimeout(function() {
                nkOpenManualModal(document.getElementById('wanConfigModal'));
            }, 30);
            return false;
        };

        window.nkOpenProfileFromTrigger = function(btn) {
            if (!btn) return false;
            var profileDropdownToggle = btn.closest('.dropdown')?.querySelector('.dropdown-toggle');
            var profileDropdown = profileDropdownToggle ? bootstrap.Dropdown.getInstance(profileDropdownToggle) : null;
            if (profileDropdown) profileDropdown.hide();
            nkCleanupModalState();
            populateProfileModal(btn);
            setTimeout(function() {
                nkOpenManualModal(document.getElementById('profileModal'));
            }, 30);
            return false;
        };

        ['wanConfigModal', 'profileModal'].forEach(function(id) {
            var modalEl = document.getElementById(id);
            if (!modalEl) return;
            modalEl.addEventListener('click', function(e) {
                if (e.target === modalEl) {
                    nkCloseManualModal(modalEl);
                }
            });
            modalEl.querySelectorAll('[data-close-manual-modal]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    nkCloseManualModal(modalEl);
                });
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            ['wanConfigModal', 'profileModal'].forEach(function(id) {
                var modalEl = document.getElementById(id);
                if (modalEl && modalEl.classList.contains('is-open')) {
                    nkCloseManualModal(modalEl);
                }
            });
        });

        // Auto-dismiss flash message after 6s
        var flashMsg = document.getElementById('nk-flash-msg');
        if (flashMsg) {
            setTimeout(function() {
                flashMsg.style.transition = 'opacity .4s';
                flashMsg.style.opacity = '0';
                setTimeout(function() { flashMsg.remove(); }, 420);
            }, 6000);
        }

        // Auto-dismiss sync toast after 5s
        var toast = document.getElementById('sync-toast');
        if (toast) {
            setTimeout(function() {
                toast.classList.add('sync-toast-dismiss');
                setTimeout(function() { toast.remove(); }, 450);
            }, 5000);
        }
    });

    // ── Auto-refresh ─────────────────────────────────────────────────────────
    var nkArInterval  = null;
    var nkArSeconds   = 30; // 30 detik
    var nkArCountdown = nkArSeconds;
    var nkArActive    = false;

    // Auto-start ON by default
    document.addEventListener('DOMContentLoaded', function() {
        nkArToggle();
    });

    function nkArTick() {
        nkArCountdown--;
        var m = Math.floor(nkArCountdown / 60);
        var s = nkArCountdown % 60;
        var label = document.getElementById('nk-ar-label');
        var btn   = document.getElementById('nk-ar-btn');
        if (label) label.textContent = 'Refresh dalam ' + m + ':' + (s < 10 ? '0' : '') + s;
        if (btn)   btn.style.color   = 'var(--orange)';
        if (nkArCountdown <= 0) {
            window.location.reload();
        }
    }

    window.nkArToggle = function() {
        var label = document.getElementById('nk-ar-label');
        var btn   = document.getElementById('nk-ar-btn');
        if (nkArActive) {
            clearInterval(nkArInterval);
            nkArInterval  = null;
            nkArActive    = false;
            nkArCountdown = nkArSeconds;
            if (label) label.textContent = 'Auto-refresh: OFF';
            if (btn)   btn.style.color   = '';
        } else {
            nkArActive    = true;
            nkArCountdown = nkArSeconds;
            nkArInterval  = setInterval(nkArTick, 1000);
            nkArTick();
        }
    };
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/olts/show.blade.php ENDPATH**/ ?>