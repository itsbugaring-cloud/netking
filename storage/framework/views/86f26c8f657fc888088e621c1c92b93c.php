
<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('styles'); ?>
<style>
  .dashboard-page {
    --ops-success: color-mix(in srgb, var(--nk-success) 82%, var(--txt));
    --ops-success-soft: color-mix(in srgb, var(--nk-success) 10%, var(--surface));
    --ops-warning: color-mix(in srgb, var(--nk-warning) 86%, var(--txt));
    --ops-warning-soft: color-mix(in srgb, var(--nk-warning) 10%, var(--surface));
    --ops-danger: color-mix(in srgb, var(--nk-danger) 82%, var(--txt));
    --ops-danger-soft: color-mix(in srgb, var(--nk-danger) 10%, var(--surface));
  }

  .ops-shell {
    display: flex;
    flex-direction: column;
    gap: 18px;
  }

  .ops-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    padding: 4px 0 2px;
  }

  .ops-eyebrow {
    font-size: .7rem;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--txt-3);
    margin-bottom: 8px;
  }

  .ops-title {
    margin: 0;
    font-size: 1.7rem;
    line-height: 1.08;
    letter-spacing: -.03em;
    color: var(--txt);
    font-weight: 650;
  }

  .ops-subtitle {
    margin-top: 8px;
    font-size: .88rem;
    color: var(--txt-3);
    max-width: 760px;
  }

  .ops-head-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }

  .ops-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0 12px;
    min-height: 34px;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--txt-2);
    font-size: .78rem;
    font-weight: 500;
  }

  .ops-kpis {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
  }

  .ops-kpi {
    border: 1px solid var(--border);
    background: var(--surface);
    border-radius: 16px;
    padding: 16px 18px;
    min-height: 118px;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .ops-kpi-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
  }

  .ops-kpi-label {
    font-size: .73rem;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--txt-3);
  }

  .ops-kpi-value {
    margin-top: 6px;
    font-size: 1.72rem;
    line-height: 1;
    letter-spacing: -.04em;
    color: var(--txt);
    font-weight: 700;
  }

  .ops-kpi-meta {
    font-size: .82rem;
    color: var(--txt-2);
  }

  .ops-kpi-icon {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--surface-2);
    border: 1px solid var(--border);
    color: var(--txt-2);
    font-size: 1rem;
    flex-shrink: 0;
  }

  .ops-kpi-icon i,
  .ops-pill i,
  .ops-quick-link i {
    font-family: 'boxicons' !important;
    font-style: normal !important;
    font-weight: 400 !important;
    font-variant: normal !important;
    text-transform: none !important;
    line-height: 1 !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    font-size: 1rem !important;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }

  .ops-panel {
    border: 1px solid var(--border);
    background: var(--surface);
    border-radius: 18px;
    overflow: hidden;
  }

  .ops-panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 18px;
    border-bottom: 1px solid var(--border);
  }

  .ops-panel-title {
    margin: 0;
    font-size: .97rem;
    font-weight: 620;
    letter-spacing: -.02em;
    color: var(--txt);
  }

  .ops-panel-subtitle {
    margin-top: 4px;
    font-size: .78rem;
    color: var(--txt-3);
  }

  .ops-panel-body {
    padding: 18px;
  }

  .ops-network-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
  }

  .ops-network-card {
    padding: 15px 16px;
    border-radius: 14px;
    border: 1px solid var(--border);
    background: var(--surface-2);
    min-height: 108px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 12px;
  }

  .ops-network-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }

  .ops-network-label {
    font-size: .74rem;
    color: var(--txt-3);
    text-transform: uppercase;
    letter-spacing: .06em;
    font-weight: 600;
  }

  .ops-network-value {
    font-size: 1.34rem;
    font-weight: 700;
    letter-spacing: -.04em;
    color: var(--txt);
  }

  .ops-network-value .muted {
    color: var(--txt-3);
  }

  .ops-network-foot {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: .76rem;
    color: var(--txt-2);
  }

  .ops-dot {
    width: 7px;
    height: 7px;
    border-radius: 999px;
    display: inline-block;
    background: var(--ops-success);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--ops-success) 18%, transparent);
  }

  .ops-analytics {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
    gap: 18px;
  }

  .ops-stack {
    display: grid;
    gap: 18px;
  }

  .ops-chart {
    height: 320px;
  }

  .ops-mini-chart {
    height: 280px;
  }

  .ops-table-wrap {
    overflow-x: auto;
  }

  .ops-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 640px;
  }

  .ops-table th {
    font-size: .7rem;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--txt-3);
    padding: 0 0 10px;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
  }

  .ops-table td {
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
    font-size: .84rem;
    color: var(--txt-2);
    vertical-align: middle;
  }

  .ops-table tr:last-child td {
    border-bottom: none;
  }

  .ops-primary-text {
    color: var(--txt);
    font-weight: 560;
  }

  .ops-secondary-text {
    color: var(--txt-3);
    font-size: .76rem;
    margin-top: 4px;
  }

  .ops-status {
    display: inline-flex;
    align-items: center;
    min-height: 26px;
    padding: 0 10px;
    border-radius: 999px;
    font-size: .73rem;
    font-weight: 600;
    border: 1px solid var(--border);
    background: var(--surface-2);
    color: var(--txt-2);
  }

  .ops-status.status-active {
    color: var(--ops-success);
    border-color: color-mix(in srgb, var(--ops-success) 24%, var(--border));
    background: var(--ops-success-soft);
  }

  .ops-status.status-suspended,
  .ops-status.status-provisioning {
    color: var(--ops-warning);
    border-color: color-mix(in srgb, var(--ops-warning) 24%, var(--border));
    background: var(--ops-warning-soft);
  }

  .ops-status.status-failed,
  .ops-status.status-unpaid {
    color: var(--ops-danger);
    border-color: color-mix(in srgb, var(--ops-danger) 24%, var(--border));
    background: var(--ops-danger-soft);
  }

  .ops-list {
    display: grid;
    gap: 10px;
  }

  .ops-list-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    border: 1px solid var(--border);
    background: var(--surface-2);
    border-radius: 14px;
  }

  .ops-list-rank {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    border: 1px solid var(--border);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .76rem;
    color: var(--txt-3);
    background: var(--surface);
    flex-shrink: 0;
  }

  .ops-list-main {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    flex: 1;
  }

  .ops-list-copy {
    min-width: 0;
  }

  .ops-list-title {
    font-size: .85rem;
    font-weight: 560;
    color: var(--txt);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .ops-list-subtitle {
    margin-top: 3px;
    font-size: .75rem;
    color: var(--txt-3);
  }

  .ops-list-value {
    font-size: .86rem;
    font-weight: 650;
    color: var(--txt);
    flex-shrink: 0;
  }

  .ops-quick-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
  }

  .ops-quick-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 13px 14px;
    border: 1px solid var(--border);
    background: var(--surface-2);
    border-radius: 14px;
    color: var(--txt);
    text-decoration: none;
    transition: border-color .15s ease, transform .15s ease;
  }

  .ops-quick-link:hover {
    color: var(--txt);
    border-color: color-mix(in srgb, var(--blue) 26%, var(--border));
    transform: translateY(-1px);
  }

  .ops-quick-label {
    font-size: .84rem;
    font-weight: 560;
  }

  .ops-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 220px;
    border: 1px dashed var(--border);
    border-radius: 16px;
    background: var(--surface-2);
    color: var(--txt-3);
    font-size: .84rem;
  }

  @media (max-width: 1199.98px) {
    .ops-kpis,
    .ops-network-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .ops-analytics {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 767.98px) {
    .ops-head {
      flex-direction: column;
      align-items: stretch;
    }

    .ops-kpis,
    .ops-network-grid,
    .ops-quick-grid {
      grid-template-columns: 1fr;
    }

    .ops-panel-head,
    .ops-panel-body {
      padding: 14px;
    }

    .ops-chart,
    .ops-mini-chart {
      height: 260px;
    }
  }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
  $labels = $chartData['labels'] ?? [];
  $revenueSeries = $chartData['revenue'] ?? [];
  $growthSeries = $chartData['growth'] ?? [];
  $areaNames = $areaStats->take(6)->pluck('name')->values();
  $areaCounts = $areaStats->take(6)->pluck('customers_count')->values();
  $activeRate = ($stats['total_customers'] ?? 0) > 0 ? round((($stats['active_customers'] ?? 0) / max(1, $stats['total_customers'])) * 100) : 0;
?>

<div class="ops-shell dashboard-page">
  <div class="ops-head">
    <div>
      <div class="ops-eyebrow">Ringkasan Operasional</div>
      <h1 class="ops-title">Dashboard</h1>
    </div>
    <div class="ops-head-actions">
      <span class="ops-pill"><i class='bx bx-time'></i> Auto-refresh 30 dtk</span>
      <button class="ms-btn-secondary" type="button" onclick="location.reload()">
        <i class='bx bx-refresh'></i>
        Refresh
      </button>
    </div>
  </div>

  <div class="ops-kpis">
    
    <div class="ops-kpi">
      <div class="ops-kpi-top">
        <div>
          <div class="ops-kpi-label">Total Pelanggan</div>
          <div class="ops-kpi-value"><?php echo e(number_format($stats['total_customers'] ?? 0)); ?></div>
        </div>
        <div class="ops-kpi-icon" style="background:rgba(91,99,211,.1);border-color:rgba(91,99,211,.2);color:#5b63d3;">
          <i class='bx bx-user'></i>
        </div>
      </div>
      <div class="ops-kpi-meta"><?php echo e(number_format($stats['active_customers'] ?? 0)); ?> aktif di <?php echo e(number_format($stats['total_areas'] ?? 0)); ?> area</div>
    </div>

    
    <?php $rateColor = $activeRate >= 90 ? 'rgba(22,163,74,.1)' : ($activeRate >= 70 ? 'rgba(217,119,6,.1)' : 'rgba(220,38,38,.1)');
         $rateBorder = $activeRate >= 90 ? 'rgba(22,163,74,.25)' : ($activeRate >= 70 ? 'rgba(217,119,6,.25)' : 'rgba(220,38,38,.25)');
         $rateText = $activeRate >= 90 ? '#16a34a' : ($activeRate >= 70 ? '#d97706' : '#dc2626'); ?>
    <div class="ops-kpi">
      <div class="ops-kpi-top">
        <div>
          <div class="ops-kpi-label">Tingkat Aktif</div>
          <div class="ops-kpi-value"><?php echo e($activeRate); ?>%</div>
        </div>
        <div class="ops-kpi-icon" style="background:<?php echo e($rateColor); ?>;border-color:<?php echo e($rateBorder); ?>;color:<?php echo e($rateText); ?>;">
          <i class='bx bx-check-circle'></i>
        </div>
      </div>
      <div class="ops-kpi-meta"><?php echo e(number_format($stats['active_customers'] ?? 0)); ?> aktif &middot; <?php echo e(number_format($stats['suspended_customers'] ?? 0)); ?> nonaktif</div>
    </div>

    
    <?php $unpaid = $stats['unpaid_invoices_count'] ?? 0;
         $unpaidBg = $unpaid > 0 ? 'rgba(217,119,6,.1)' : 'rgba(22,163,74,.1)';
         $unpaidBorder = $unpaid > 0 ? 'rgba(217,119,6,.25)' : 'rgba(22,163,74,.25)';
         $unpaidText = $unpaid > 0 ? '#d97706' : '#16a34a'; ?>
    <div class="ops-kpi">
      <div class="ops-kpi-top">
        <div>
          <div class="ops-kpi-label">Tagihan Belum Dibayar</div>
          <div class="ops-kpi-value"><?php echo e(number_format($unpaid)); ?></div>
        </div>
        <div class="ops-kpi-icon" style="background:<?php echo e($unpaidBg); ?>;border-color:<?php echo e($unpaidBorder); ?>;color:<?php echo e($unpaidText); ?>;">
          <i class='bx bx-<?php echo e($unpaid > 0 ? "error" : "check"); ?>'></i>
        </div>
      </div>
      <div class="ops-kpi-meta">
        <?php if(($stats['overdue_invoices_count'] ?? 0) > 0): ?>
          <span style="color:#dc2626;"><?php echo e(number_format($stats['overdue_invoices_count'])); ?> jatuh tempo</span>
        <?php else: ?>
          Semua tagihan dalam antrian normal
        <?php endif; ?>
      </div>
    </div>

    
    <div class="ops-kpi">
      <div class="ops-kpi-top">
        <div>
          <div class="ops-kpi-label">MRR (Est. Bulanan)</div>
          <div class="ops-kpi-value">Rp <?php echo e(number_format($stats['mrr'] ?? 0, 0, ',', '.')); ?></div>
        </div>
        <div class="ops-kpi-icon" style="background:rgba(91,99,211,.1);border-color:rgba(91,99,211,.2);color:#5b63d3;">
          <i class='bx bx-line-chart'></i>
        </div>
      </div>
      <div class="ops-kpi-meta">Terbayar bulan ini: Rp <?php echo e(number_format($stats['monthly_revenue'] ?? 0, 0, ',', '.')); ?></div>
    </div>
  </div>

  <section class="ops-panel">
    <div class="ops-panel-head">
      <div>
        <h2 class="ops-panel-title">Status jaringan</h2>
      </div>
      <span class="ops-pill" style="color:var(--ops-success);border-color:color-mix(in srgb,var(--ops-success) 22%,var(--border));"><i class='bx bx-pulse'></i> Live Monitoring</span>
    </div>
    <div class="ops-panel-body">
      <div class="ops-network-grid">
        <div class="ops-network-card">
          <div class="ops-network-top">
            <div>
              <div class="ops-network-label">ACS ONTs</div>
              <div class="ops-network-value"><span id="live-acs-online">—</span> <span class="muted">/ <span id="live-acs-total">—</span></span></div>
            </div>
            <div class="ops-kpi-icon"><i class='bx bx-wifi'></i></div>
          </div>
          <div class="ops-network-foot"><span class="ops-dot"></span> Langsung dari Erka ACS</div>
        </div>

        <div class="ops-network-card">
          <div class="ops-network-top">
            <div>
              <div class="ops-network-label">Inventaris OLT</div>
              <div class="ops-network-value"><span id="live-ont-online">—</span> <span class="muted">/ <span id="live-ont-total">—</span></span></div>
            </div>
            <div class="ops-kpi-icon"><i class='bx bx-server'></i></div>
          </div>
          <div class="ops-network-foot"><span class="ops-dot"></span> Online / total ONT</div>
        </div>

        <div class="ops-network-card">
          <div class="ops-network-top">
            <div>
              <div class="ops-network-label">Belum Dibayar</div>
              <div class="ops-network-value"><span id="live-unpaid"><?php echo e(number_format($stats['unpaid_invoices_count'] ?? 0)); ?></span></div>
            </div>
            <div class="ops-kpi-icon"><i class='bx bx-calendar-exclamation'></i></div>
          </div>
          <div class="ops-network-foot">
            <span class="ops-dot" style="background:#fbbf24;box-shadow:0 0 0 3px rgba(251,191,36,.14);"></span>
            <span id="live-overdue-wrap" style="<?php echo e(($stats['overdue_invoices_count'] ?? 0) > 0 ? 'display:inline;' : 'display:none;'); ?>"><span id="live-overdue"><?php echo e(number_format($stats['overdue_invoices_count'] ?? 0)); ?></span> jatuh tempo</span>
            <span id="live-overdue-empty" style="<?php echo e(($stats['overdue_invoices_count'] ?? 0) > 0 ? 'display:none;' : 'display:inline;'); ?>">Antrian tindak lanjut tagihan</span>
          </div>
        </div>

        <div class="ops-network-card">
          <div class="ops-network-top">
            <div>
              <div class="ops-network-label">Pelanggan aktif</div>
              <div class="ops-network-value"><span id="live-active"><?php echo e(number_format($stats['active_customers'] ?? 0)); ?></span> <span class="muted">/ <span id="live-total-cust"><?php echo e(number_format($stats['total_customers'] ?? 0)); ?></span></span></div>
            </div>
            <div class="ops-kpi-icon"><i class='bx bx-user-check'></i></div>
          </div>
          <div class="ops-network-foot"><span class="ops-dot"></span> Jumlah layanan aktif saat ini</div>
        </div>
      </div>
    </div>
  </section>

  <div class="ops-analytics">
    <section class="ops-panel">
      <div class="ops-panel-head">
        <div>
          <h2 class="ops-panel-title">Tren pendapatan</h2>
          <div class="ops-panel-subtitle">Tagihan terbayar dalam 6 bulan terakhir.</div>
        </div>
      </div>
      <div class="ops-panel-body">
        <?php if(array_sum($revenueSeries) > 0): ?>
          <div id="revenueChart" class="ops-chart"></div>
        <?php else: ?>
          <div class="ops-empty" style="flex-direction:column;gap:8px;min-height:320px;">
            <i class='bx bx-bar-chart-alt-2' style="font-size:2rem;color:var(--txt-3);"></i>
            <div style="font-weight:600;color:var(--txt-2);">Belum ada pendapatan bulan ini</div>
            <div style="font-size:.78rem;color:var(--txt-3);">Data akan muncul setelah tagihan berstatus <em>paid</em>.</div>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <div class="ops-stack">
      <section class="ops-panel">
        <div class="ops-panel-head">
          <div>
            <h2 class="ops-panel-title">Pertumbuhan pelanggan</h2>
            <div class="ops-panel-subtitle">Pertumbuhan kumulatif pelanggan per bulan.</div>
          </div>
        </div>
        <div class="ops-panel-body">
          <div id="growthChart" class="ops-mini-chart"></div>
        </div>
      </section>

      <section class="ops-panel">
        <div class="ops-panel-head">
          <div>
            <h2 class="ops-panel-title">Distribusi area</h2>
            <div class="ops-panel-subtitle">Area teratas berdasarkan jumlah pelanggan.</div>
          </div>
        </div>
        <div class="ops-panel-body">
          <?php if($areaCounts->sum() > 0): ?>
            <div id="areaChart" class="ops-mini-chart"></div>
          <?php else: ?>
            <div class="ops-empty">Belum ada data area untuk divisualisasikan.</div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>

  <div class="ops-analytics">
    <section class="ops-panel">
      <div class="ops-panel-head">
        <div>
          <h2 class="ops-panel-title">Pelanggan terbaru</h2>
          <div class="ops-panel-subtitle">Pelanggan terbaru yang masuk ke sistem.</div>
        </div>
      </div>
      <div class="ops-panel-body">
        <div class="ops-table-wrap">
          <table class="ops-table">
            <thead>
              <tr>
                <th>Pelanggan</th>
                <th>Area</th>
                <th>Paket</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php $__empty_1 = true; $__currentLoopData = $recentCustomers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                  <td>
                    <div class="ops-primary-text"><?php echo e($customer->name); ?></div>
                    <div class="ops-secondary-text"><?php echo e($customer->pppoe_user ?: 'Tanpa PPPoE'); ?></div>
                  </td>
                  <td><?php echo e($customer->area->name ?? 'Tidak Ditetapkan'); ?></td>
                  <td><?php echo e($customer->package->name ?? 'Tanpa paket'); ?></td>
                  <td>
                    <?php
                      $statusLabel = ['active'=>'Aktif','suspended'=>'Nonaktif','provisioning'=>'Proses','failed'=>'Gagal','unpaid'=>'Belum Bayar'][$customer->status] ?? ucfirst($customer->status);
                    ?>
                    <span class="ops-status status-<?php echo e($customer->status); ?>"><?php echo e($statusLabel); ?></span>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                  <td colspan="4">
                    <div class="ops-empty" style="min-height:180px;">Belum ada pelanggan terbaru.</div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <div class="ops-stack">
      <section class="ops-panel">
        <div class="ops-panel-head">
          <div>
            <h2 class="ops-panel-title">Aksi cepat</h2>
            <div class="ops-panel-subtitle">Akses cepat ke area kerja inti.</div>
          </div>
        </div>
        <div class="ops-panel-body">
          <div class="ops-quick-grid">
            <a href="<?php echo e(route('admin.customers.index')); ?>" class="ops-quick-link">
              <span class="ops-quick-label">Pelanggan</span>
              <i class='bx bx-right-arrow-alt'></i>
            </a>
            <a href="<?php echo e(route('admin.pppoe.index')); ?>" class="ops-quick-link">
              <span class="ops-quick-label">PPPoE</span>
              <i class='bx bx-right-arrow-alt'></i>
            </a>
            <a href="<?php echo e(route('admin.olts.index')); ?>" class="ops-quick-link">
              <span class="ops-quick-label">OLT & ONT</span>
              <i class='bx bx-right-arrow-alt'></i>
            </a>
            <a href="<?php echo e(route('admin.invoices.index')); ?>" class="ops-quick-link">
              <span class="ops-quick-label">Tagihan</span>
              <i class='bx bx-right-arrow-alt'></i>
            </a>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
  (function () {
    const labels = <?php echo json_encode($labels, 15, 512) ?>;
    const revenueSeries = <?php echo json_encode($revenueSeries, 15, 512) ?>;
    const growthSeries = <?php echo json_encode($growthSeries, 15, 512) ?>;
    const areaLabels = <?php echo json_encode($areaNames, 15, 512) ?>;
    const areaSeries = <?php echo json_encode($areaCounts, 15, 512) ?>;

    function chartPalette() {
      const dark = document.documentElement.getAttribute('data-theme') === 'dark';
      return {
        dark,
        primary: dark ? '#5e6ad2' : '#5b63d3',
        primarySoft: dark ? 'rgba(94,106,210,0.2)' : 'rgba(91,99,211,0.16)',
        success: dark ? '#4ade80' : '#16a34a',
        warning: dark ? '#fbbf24' : '#d97706',
        danger: dark ? '#fb7185' : '#e11d48',
        text: dark ? '#f4f4f5' : '#18181b',
        muted: dark ? '#71717a' : '#71717a',
        border: dark ? 'rgba(255,255,255,0.08)' : '#e7e7ec',
        fill: dark ? '#111113' : '#ffffff'
      };
    }

    function initCharts() {
      if (typeof ApexCharts === 'undefined') return;
      const palette = chartPalette();

      const revenueEl = document.querySelector('#revenueChart');
      if (revenueEl) {
        revenueEl.innerHTML = '';
        new ApexCharts(revenueEl, {
          chart: {
            type: 'area',
            height: 320,
            toolbar: { show: false },
            background: 'transparent'
          },
          series: [{
            name: 'Pendapatan',
            data: revenueSeries
          }],
          xaxis: {
            categories: labels,
            labels: { style: { colors: palette.muted, fontSize: '12px' } },
            axisBorder: { show: false },
            axisTicks: { show: false }
          },
          yaxis: {
            labels: {
              style: { colors: [palette.muted], fontSize: '12px' },
              formatter: function (value) {
                if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                if (value >= 1000) return 'Rp ' + Math.round(value / 1000) + 'k';
                return 'Rp ' + value;
              }
            }
          },
          dataLabels: { enabled: false },
          stroke: {
            curve: 'smooth',
            width: 2.2
          },
          colors: [palette.primary],
          grid: {
            borderColor: palette.border,
            strokeDashArray: 4
          },
          fill: {
            type: 'gradient',
            gradient: {
              shadeIntensity: 1,
              opacityFrom: 0.22,
              opacityTo: 0.02,
              stops: [0, 100]
            }
          },
          tooltip: {
            theme: palette.dark ? 'dark' : 'light',
            y: {
              formatter: function (value) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);
              }
            }
          },
          legend: { show: false }
        }).render();
      }

      const growthEl = document.querySelector('#growthChart');
      if (growthEl) {
        growthEl.innerHTML = '';
        new ApexCharts(growthEl, {
          chart: {
            type: 'line',
            height: 280,
            toolbar: { show: false },
            background: 'transparent'
          },
          series: [{
            name: 'Pelanggan',
            data: growthSeries
          }],
          xaxis: {
            categories: labels,
            labels: { style: { colors: labels.map(() => palette.muted), fontSize: '12px' } },
            axisBorder: { show: false },
            axisTicks: { show: false }
          },
          yaxis: {
            labels: { style: { colors: [palette.muted], fontSize: '12px' } }
          },
          stroke: {
            curve: 'smooth',
            width: 2.2
          },
          markers: {
            size: 3,
            strokeWidth: 0,
            colors: [palette.primary]
          },
          colors: [palette.primary],
          dataLabels: { enabled: false },
          grid: {
            borderColor: palette.border,
            strokeDashArray: 4
          },
          tooltip: {
            theme: palette.dark ? 'dark' : 'light'
          },
          legend: { show: false }
        }).render();
      }

      const areaEl = document.querySelector('#areaChart');
      if (areaEl && areaSeries.length) {
        areaEl.innerHTML = '';
        new ApexCharts(areaEl, {
          chart: {
            type: 'donut',
            height: 280,
            background: 'transparent'
          },
          series: areaSeries,
          labels: areaLabels,
          colors: [
            palette.primary,
            '#7c83e6',
            '#8b92eb',
            '#9da3ef',
            '#b0b5f4',
            '#c4c8f7'
          ],
          stroke: {
            width: 0
          },
          legend: {
            position: 'bottom',
            fontSize: '12px',
            labels: { colors: palette.muted }
          },
          dataLabels: {
            enabled: false
          },
          plotOptions: {
            pie: {
              donut: {
                size: '72%',
                labels: {
                  show: true,
                  value: {
                    color: palette.text
                  },
                  total: {
                    show: true,
                    label: 'Total',
                    color: palette.muted
                  }
                }
              }
            }
          },
          tooltip: {
            theme: palette.dark ? 'dark' : 'light'
          }
        }).render();
      }
    }

    function fetchLiveStatus() {
      fetch(<?php echo json_encode(route('admin.api.dashboard-live'), 15, 512) ?>)
        .then(function (response) { return response.json(); })
        .then(function (data) {
          if (data.acs_online !== undefined) {
            document.getElementById('live-acs-online').textContent = data.acs_online;
            document.getElementById('live-acs-total').textContent = data.acs_total;
          }
          if (data.ont_online !== undefined) {
            document.getElementById('live-ont-online').textContent = data.ont_online;
            document.getElementById('live-ont-total').textContent = data.ont_total;
          }
          if (data.unpaid_invoices !== undefined) {
            document.getElementById('live-unpaid').textContent = data.unpaid_invoices;
          }
          if (data.overdue_invoices !== undefined) {
            const wrap = document.getElementById('live-overdue-wrap');
            const empty = document.getElementById('live-overdue-empty');
            document.getElementById('live-overdue').textContent = data.overdue_invoices;
            if (data.overdue_invoices > 0) {
              wrap.style.display = 'inline';
              empty.style.display = 'none';
            } else {
              wrap.style.display = 'none';
              empty.style.display = 'inline';
            }
          }
          if (data.active_customers !== undefined) {
            document.getElementById('live-active').textContent = data.active_customers;
            document.getElementById('live-total-cust').textContent = data.total_customers;
          }
        })
        .catch(function () {});
    }

    document.addEventListener('DOMContentLoaded', function () {
      initCharts();
      fetchLiveStatus();
      setInterval(fetchLiveStatus, 30000);
    });

    window.addEventListener('storage', function (event) {
      if (event.key === 'nk_theme') {
        initCharts();
      }
    });
  })();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>