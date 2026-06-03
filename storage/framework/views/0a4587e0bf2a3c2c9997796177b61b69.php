

<?php $__env->startSection('title', 'Cek Sinyal ONT'); ?>

<?php $__env->startSection('styles'); ?>
<style>
/* ── Summary Bar ─────────────────────────────────────── */
.sig-summary {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}
.sig-stat {
  flex: 1;
  min-width: 110px;
  background: var(--card-bg, #fff);
  border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 10px;
  padding: 14px 16px;
  text-align: center;
}
.sig-stat-num {
  font-size: 1.75rem;
  font-weight: 700;
  line-height: 1;
  margin-bottom: 4px;
}
.sig-stat-label {
  font-size: .72rem;
  text-transform: uppercase;
  letter-spacing: .05em;
  color: var(--text-muted, #6b7280);
}
.sig-stat.stat-total   .sig-stat-num { color: var(--text, #111); }
.sig-stat.stat-good    .sig-stat-num { color: #16a34a; }
.sig-stat.stat-weak    .sig-stat-num { color: #d97706; }
.sig-stat.stat-crit    .sig-stat-num { color: #dc2626; }
.sig-stat.stat-nodata  .sig-stat-num { color: #6b7280; }

/* ── Global Filter ───────────────────────────────────── */
.sig-filter-bar {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 18px;
}
.sig-filter-bar input {
  flex: 1;
  min-width: 200px;
  padding: 7px 12px;
  border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 8px;
  font-size: .875rem;
  background: var(--input-bg, #fff);
  color: var(--text, #111);
}
.sig-filter-bar input:focus { outline: none; border-color: var(--primary, #6366f1); box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
.sig-filter-pills { display: flex; gap: 6px; flex-wrap: wrap; }
.sig-pill {
  padding: 5px 12px;
  border-radius: 20px;
  border: 1px solid var(--border-color, #e5e7eb);
  font-size: .75rem;
  font-weight: 600;
  cursor: pointer;
  background: var(--card-bg, #fff);
  color: var(--text-muted, #6b7280);
  transition: all .15s;
}
.sig-pill.active, .sig-pill:hover { background: var(--primary, #6366f1); color: #fff; border-color: var(--primary, #6366f1); }
.sig-pill.pill-good.active  { background: #16a34a; border-color: #16a34a; }
.sig-pill.pill-weak.active  { background: #d97706; border-color: #d97706; }
.sig-pill.pill-crit.active  { background: #dc2626; border-color: #dc2626; }
.sig-pill.pill-nodata.active{ background: #6b7280; border-color: #6b7280; }

/* ── OLT Stack Card ──────────────────────────────────── */
.olt-stack {
  margin-bottom: 16px;
  border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 12px;
  overflow: hidden;
  background: var(--card-bg, #fff);
}
.olt-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 18px;
  cursor: pointer;
  user-select: none;
  border-bottom: 1px solid var(--border-color, #e5e7eb);
  background: var(--card-bg, #fff);
  transition: background .12s;
}
.olt-header:hover { background: var(--hover-bg, #f9fafb); }
.olt-dot {
  width: 10px; height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}
.olt-dot.online  { background: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.18); }
.olt-dot.offline { background: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,.18); }
.olt-dot.unknown { background: #6b7280; }
.olt-name { font-weight: 700; font-size: .9rem; }
.olt-meta { font-size: .75rem; color: var(--text-muted, #6b7280); margin-top: 1px; }
.olt-badges { display: flex; gap: 6px; margin-left: auto; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
.olt-badge {
  font-size: .68rem; font-weight: 700;
  padding: 2px 8px; border-radius: 10px;
  white-space: nowrap;
}
.badge-good   { background: #dcfce7; color: #15803d; }
.badge-weak   { background: #fef3c7; color: #92400e; }
.badge-crit   { background: #fee2e2; color: #991b1b; }
.badge-nodata { background: #f3f4f6; color: #6b7280; }
.badge-total  { background: var(--muted-bg, #f3f4f6); color: var(--text-muted, #6b7280); }
.olt-caret {
  color: var(--text-muted, #6b7280);
  font-size: 1.1rem;
  transition: transform .2s;
  margin-left: 8px;
  flex-shrink: 0;
}
.olt-stack.collapsed .olt-caret { transform: rotate(-90deg); }

/* ── ONT Table inside card ───────────────────────────── */
.olt-body { overflow: hidden; }
.olt-stack.collapsed .olt-body { display: none; }
.ont-table {
  width: 100%;
  border-collapse: collapse;
  font-size: .82rem;
}
.ont-table th {
  padding: 8px 14px;
  text-align: left;
  font-size: .7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .05em;
  color: var(--text-muted, #6b7280);
  background: var(--table-head-bg, #f9fafb);
  border-bottom: 1px solid var(--border-color, #e5e7eb);
  white-space: nowrap;
}
.ont-row {
  border-bottom: 1px solid var(--border-color-faint, #f3f4f6);
  transition: background .12s;
  cursor: pointer;
}
.ont-row:last-of-type { border-bottom: none; }
.ont-row:hover { background: var(--hover-bg, #f9fafb); }
.ont-row.filtered-out { display: none; }
.ont-row td { padding: 10px 14px; vertical-align: middle; }

/* Signal badge pill */
.sig-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: .78rem;
  font-weight: 700;
  white-space: nowrap;
}
.sig-badge.good       { background: #dcfce7; color: #15803d; }
.sig-badge.fair       { background: #d1fae5; color: #065f46; }
.sig-badge.weak       { background: #fef3c7; color: #92400e; }
.sig-badge.critical   { background: #fee2e2; color: #991b1b; }
.sig-badge.too_strong { background: #ede9fe; color: #6d28d9; }
.sig-badge.unknown    { background: #f3f4f6; color: #6b7280; }
.sig-badge.loading    { background: #e0f2fe; color: #0369a1; }

/* Mini signal bar */
.sig-bar-wrap { width: 60px; height: 6px; background: #e5e7eb; border-radius: 3px; display: inline-block; vertical-align: middle; }
.sig-bar-fill { height: 100%; border-radius: 3px; transition: width .4s; }

/* Status dot */
.ont-status-dot {
  display: inline-block;
  width: 7px; height: 7px;
  border-radius: 50%;
  margin-right: 5px;
}
.ont-status-dot.online  { background: #16a34a; }
.ont-status-dot.offline { background: #dc2626; }
.ont-status-dot.unknown { background: #9ca3af; }

/* Live check button */
.btn-live {
  padding: 4px 10px;
  font-size: .72rem;
  border-radius: 6px;
  border: 1px solid var(--primary, #6366f1);
  color: var(--primary, #6366f1);
  background: transparent;
  cursor: pointer;
  white-space: nowrap;
  transition: all .15s;
  line-height: 1.4;
}
.btn-live:hover { background: var(--primary, #6366f1); color: #fff; }
.btn-live:disabled { opacity: .5; cursor: not-allowed; }

/* No ONT empty state */
.ont-empty {
  padding: 28px;
  text-align: center;
  color: var(--text-muted, #6b7280);
  font-size: .82rem;
}

/* Toggle / Refresh buttons */
.btn-toggle-all {
  font-size: .75rem;
  padding: 5px 12px;
  border-radius: 8px;
  border: 1px solid var(--border-color, #e5e7eb);
  background: var(--card-bg, #fff);
  color: var(--text-muted, #6b7280);
  cursor: pointer;
  transition: all .15s;
}
.btn-toggle-all:hover { background: var(--hover-bg, #f3f4f6); }
.btn-refresh-all {
  font-size: .75rem;
  padding: 5px 14px;
  border-radius: 8px;
  border: none;
  background: var(--primary, #6366f1);
  color: #fff;
  cursor: pointer;
  transition: opacity .15s;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}
.btn-refresh-all:hover { opacity: .85; }

/* ONT detail row */
.ont-detail-row { display: none; background: var(--detail-bg, #f8fafc); }
.ont-detail-row.open { display: table-row; }
.ont-detail-cell { padding: 12px 18px; border-bottom: 1px solid var(--border-color, #e5e7eb); }
.ont-detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 8px;
}
.ont-detail-item {
  background: var(--card-bg, #fff);
  border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 8px;
  padding: 8px 12px;
}
.ont-detail-item .lbl { font-size: .68rem; color: var(--text-muted, #6b7280); text-transform: uppercase; letter-spacing: .04em; }
.ont-detail-item .val { font-size: .82rem; font-weight: 600; margin-top: 2px; word-break: break-all; }

/* Live toast */
#live-toast {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 9999;
  background: #1e293b;
  color: #fff;
  padding: 10px 18px;
  border-radius: 10px;
  font-size: .82rem;
  max-width: 320px;
  opacity: 0;
  transform: translateY(10px);
  pointer-events: none;
  transition: opacity .25s, transform .25s;
}
#live-toast.show { opacity: 1; transform: translateY(0); }

/* Page header */
.sig-page-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.sig-page-header h1 { font-size: 1.25rem; font-weight: 700; margin: 0 0 4px; }
.sig-page-header p  { margin: 0; font-size: .82rem; color: var(--text-muted, #6b7280); }
.sig-header-actions { display: flex; gap: 8px; align-items: center; margin-left: auto; flex-wrap: wrap; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="sig-page-header">
  <div>
    <h1><i class='bx bx-wifi-2' style="color:var(--primary,#6366f1);"></i> Cek Sinyal ONT</h1>
    <p>Data Rx Power dari database &bull; Klik <strong>Cek Live</strong> untuk refresh real-time via ACS</p>
  </div>
  <div class="sig-header-actions">
    <button class="btn-toggle-all" onclick="toggleAll(true)"><i class='bx bx-collapse'></i> Tutup Semua</button>
    <button class="btn-toggle-all" onclick="toggleAll(false)"><i class='bx bx-expand'></i> Buka Semua</button>
    <button class="btn-refresh-all" id="btn-refresh-all" onclick="refreshAllVisible()">
      <i class='bx bx-refresh'></i> Refresh Semua
    </button>
  </div>
</div>


<div class="sig-summary">
  <div class="sig-stat stat-total">
    <div class="sig-stat-num"><?php echo e($totalOnts); ?></div>
    <div class="sig-stat-label">Total ONT</div>
  </div>
  <div class="sig-stat stat-good">
    <div class="sig-stat-num"><?php echo e($goodCount); ?></div>
    <div class="sig-stat-label">Sinyal Baik</div>
  </div>
  <div class="sig-stat stat-weak">
    <div class="sig-stat-num"><?php echo e($weakCount); ?></div>
    <div class="sig-stat-label">Lemah</div>
  </div>
  <div class="sig-stat stat-crit">
    <div class="sig-stat-num"><?php echo e($critCount); ?></div>
    <div class="sig-stat-label">Kritis</div>
  </div>
  <div class="sig-stat stat-nodata">
    <div class="sig-stat-num"><?php echo e($noDataCount); ?></div>
    <div class="sig-stat-label">No Data</div>
  </div>
</div>


<div class="sig-filter-bar">
  <input type="text" id="sig-search" placeholder="🔍  Cari pelanggan, PPPoE, serial ONT... (tekan / untuk fokus)" oninput="applyFilters()" autocomplete="off">
  <div class="sig-filter-pills">
    <span class="sig-pill active"      data-filter="all"    onclick="setFilter('all')">Semua</span>
    <span class="sig-pill pill-good"   data-filter="good"   onclick="setFilter('good')">Sinyal Baik</span>
    <span class="sig-pill pill-weak"   data-filter="weak"   onclick="setFilter('weak')">Lemah</span>
    <span class="sig-pill pill-crit"   data-filter="crit"   onclick="setFilter('crit')">Kritis</span>
    <span class="sig-pill pill-nodata" data-filter="nodata" onclick="setFilter('nodata')">No Data</span>
  </div>
</div>


<?php $__empty_1 = true; $__currentLoopData = $olts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $olt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<?php
  $oltOnts   = $olt->onts;
  $oltTotal  = $oltOnts->count();
  $oltGood   = $oltOnts->filter(fn($o) => $o->rx_power !== null && $o->rx_power >= -25)->count();
  $oltWeak   = $oltOnts->filter(fn($o) => $o->rx_power !== null && $o->rx_power < -25 && $o->rx_power >= -30)->count();
  $oltCrit   = $oltOnts->filter(fn($o) => $o->rx_power !== null && $o->rx_power < -30)->count();
  $oltNoData = $oltOnts->filter(fn($o) => $o->rx_power === null)->count();
  $oltStatus = $olt->status ?? 'unknown';
?>

<div class="olt-stack collapsed" id="olt-stack-<?php echo e($olt->id); ?>">
  
  <div class="olt-header" onclick="toggleOlt(<?php echo e($olt->id); ?>)">
    <span class="olt-dot <?php echo e($oltStatus); ?>"></span>
    <div style="min-width:0;">
      <div class="olt-name"><?php echo e($olt->name); ?></div>
      <div class="olt-meta">
        <?php echo e(trim($olt->brand.' '.$olt->model) ?: 'OLT'); ?>

        <?php if($olt->ip_address): ?> &bull; <?php echo e($olt->ip_address); ?> <?php endif; ?>
        <?php if($olt->area): ?> &bull; <?php echo e($olt->area->name); ?> <?php endif; ?>
      </div>
    </div>
    <div class="olt-badges">
      <?php if($oltGood): ?>   <span class="olt-badge badge-good">✓ <?php echo e($oltGood); ?> Baik</span>   <?php endif; ?>
      <?php if($oltWeak): ?>   <span class="olt-badge badge-weak">⚠ <?php echo e($oltWeak); ?> Lemah</span>  <?php endif; ?>
      <?php if($oltCrit): ?>   <span class="olt-badge badge-crit">✕ <?php echo e($oltCrit); ?> Kritis</span> <?php endif; ?>
      <?php if($oltNoData): ?> <span class="olt-badge badge-nodata">— <?php echo e($oltNoData); ?> No Data</span> <?php endif; ?>
      <span class="olt-badge badge-total"><?php echo e($oltTotal); ?> ONT</span>
    </div>
    <i class='bx bx-chevron-down olt-caret'></i>
  </div>

  
  <div class="olt-body">
    <?php if($oltTotal === 0): ?>
      <div class="ont-empty"><i class='bx bx-info-circle'></i> Tidak ada ONT terdaftar di OLT ini.</div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table class="ont-table">
      <thead>
        <tr>
          <th style="width:32px;">#</th>
          <th>Pelanggan</th>
          <th>PPPoE</th>
          <th>Serial ONT</th>
          <th>Port</th>
          <th>Status</th>
          <th>Rx Power</th>
          <th>Kualitas</th>
          <th style="width:70px;text-align:center;">Live</th>
        </tr>
      </thead>
      <tbody>
      <?php $__currentLoopData = $oltOnts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $ont): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $rxPower   = $ont->rx_power;
        $quality   = 'unknown';
        if ($rxPower !== null) {
          if ($rxPower > -10)      $quality = 'too_strong';
          elseif ($rxPower >= -25) $quality = 'good';
          elseif ($rxPower >= -27) $quality = 'fair';
          elseif ($rxPower >= -30) $quality = 'weak';
          else                     $quality = 'critical';
        }
        $qLabel = ['too_strong'=>'Terlalu Kuat','good'=>'Baik','fair'=>'Cukup','weak'=>'Lemah','critical'=>'Kritis','unknown'=>'No Data'];
        $barColors = ['good'=>'#16a34a','fair'=>'#16a34a','too_strong'=>'#7c3aed','weak'=>'#d97706','critical'=>'#dc2626','unknown'=>'#d1d5db'];
        $barPct    = $rxPower !== null ? max(0, min(100, (($rxPower + 35) / 25) * 100)) : 0;
        $barColor  = $barColors[$quality];
        $custName  = $ont->customer?->name      ?? '—';
        $pppoeUser = $ont->customer?->pppoe_user ?? '—';
        $custId    = $ont->customer_id;
        $serial    = $ont->serial_number ?? '—';
        $portStr   = $ont->pon_port ?? ($ont->olt_port_index ? 'Port '.$ont->olt_port_index : '—');
        $ontStatus = $ont->status ?? 'unknown';
        $fqClass   = match(true) {
          $rxPower !== null && $rxPower >= -25 => 'fq-good',
          $rxPower !== null && $rxPower >= -30 => 'fq-weak',
          $rxPower !== null                    => 'fq-crit',
          default                              => 'fq-nodata',
        };
      ?>

      <tr class="ont-row <?php echo e($fqClass); ?>"
          data-ont-id="<?php echo e($ont->id); ?>"
          data-customer-id="<?php echo e($custId); ?>"
          data-cust-name="<?php echo e(strtolower($custName)); ?>"
          data-pppoe="<?php echo e(strtolower($pppoeUser)); ?>"
          data-serial="<?php echo e(strtolower($serial)); ?>"
          data-quality="<?php echo e($quality); ?>"
          onclick="toggleOntDetail(<?php echo e($ont->id); ?>)">
        <td style="color:var(--text-muted,#6b7280);font-size:.75rem;text-align:center;"><?php echo e($idx+1); ?></td>
        <td>
          <div style="font-weight:600;white-space:nowrap;"><?php echo e($custName); ?></div>
          <?php if($ont->area): ?><div style="font-size:.7rem;color:var(--text-muted,#6b7280);"><?php echo e($ont->area->name); ?></div><?php endif; ?>
        </td>
        <td><span style="font-family:monospace;font-size:.8rem;"><?php echo e($pppoeUser); ?></span></td>
        <td><span style="font-family:monospace;font-size:.78rem;color:var(--text-muted,#6b7280);"><?php echo e($serial); ?></span></td>
        <td style="font-size:.78rem;white-space:nowrap;"><?php echo e($portStr); ?></td>
        <td style="white-space:nowrap;">
          <span class="ont-status-dot <?php echo e($ontStatus); ?>"></span>
          <span style="font-size:.78rem;text-transform:capitalize;"><?php echo e($ontStatus); ?></span>
        </td>
        <td onclick="event.stopPropagation()">
          <div style="display:flex;align-items:center;gap:8px;white-space:nowrap;" id="rx-cell-<?php echo e($ont->id); ?>">
            <?php if($rxPower !== null): ?>
              <span style="font-family:monospace;font-weight:700;font-size:.85rem;"><?php echo e(number_format($rxPower,2)); ?> dBm</span>
              <div class="sig-bar-wrap"><div class="sig-bar-fill" style="width:<?php echo e($barPct); ?>%;background:<?php echo e($barColor); ?>;"></div></div>
            <?php else: ?>
              <span style="color:var(--text-muted,#6b7280);font-size:.78rem;">—</span>
            <?php endif; ?>
          </div>
        </td>
        <td onclick="event.stopPropagation()">
          <span class="sig-badge <?php echo e($quality); ?>" id="qbadge-<?php echo e($ont->id); ?>"><?php echo e($qLabel[$quality]); ?></span>
        </td>
        <td style="text-align:center;" onclick="event.stopPropagation()">
          <?php if($custId): ?>
            <button class="btn-live" id="btn-live-<?php echo e($ont->id); ?>"
                    onclick="checkLive(<?php echo e($custId); ?>, <?php echo e($ont->id); ?>, this)">
              <i class='bx bx-refresh'></i>
            </button>
          <?php else: ?>
            <span style="color:var(--text-muted,#6b7280);font-size:.7rem;">—</span>
          <?php endif; ?>
        </td>
      </tr>

      
      <tr class="ont-detail-row" id="detail-row-<?php echo e($ont->id); ?>">
        <td colspan="9" class="ont-detail-cell">
          <div class="ont-detail-grid">
            <div class="ont-detail-item">
              <div class="lbl">Deskripsi / Model</div>
              <div class="val"><?php echo e($ont->description ?: ($ont->equipment_id ?: '—')); ?></div>
            </div>
            <div class="ont-detail-item">
              <div class="lbl">Firmware</div>
              <div class="val"><?php echo e($ont->firmware_version ?: '—'); ?></div>
            </div>
            <div class="ont-detail-item">
              <div class="lbl">Tx Power</div>
              <div class="val"><?php echo e($ont->tx_power !== null ? number_format($ont->tx_power,2).' dBm' : '—'); ?></div>
            </div>
            <div class="ont-detail-item">
              <div class="lbl">Jarak</div>
              <div class="val"><?php echo e($ont->distance ? number_format($ont->distance,0).' m' : '—'); ?></div>
            </div>
            <div class="ont-detail-item">
              <div class="lbl">Last Sync</div>
              <div class="val"><?php echo e($ont->last_synced_at ? $ont->last_synced_at->diffForHumans() : '—'); ?></div>
            </div>
          </div>
          
          <div id="live-detail-<?php echo e($ont->id); ?>" style="margin-top:10px;display:none;"></div>
        </td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<div style="text-align:center;padding:60px 20px;color:var(--text-muted,#6b7280);">
  <i class='bx bx-server' style="font-size:2.5rem;display:block;margin-bottom:10px;"></i>
  Belum ada OLT yang terdaftar.
  <br><a href="<?php echo e(route('admin.olts.index')); ?>" style="margin-top:12px;display:inline-block;">Tambah OLT →</a>
</div>
<?php endif; ?>


<div style="margin-top:30px;border:1px solid var(--border-color,#e5e7eb);border-radius:10px;overflow:hidden;background:var(--card-bg,#fff);">
  <div style="padding:12px 16px;background:var(--table-head-bg,#f9fafb);border-bottom:1px solid var(--border-color,#e5e7eb);font-weight:700;font-size:.82rem;">
    <i class='bx bx-info-circle'></i> Referensi Ambang Sinyal GPON
  </div>
  <div style="display:flex;flex-wrap:wrap;">
    <?php $__currentLoopData = [
      ['> -10 dBm',     'too_strong', 'Terlalu Kuat', 'Periksa attenuator / terlalu dekat OLT'],
      ['-10 ~ -25 dBm', 'good',       'Baik',         'Sinyal normal, performa optimal'],
      ['-25 ~ -27 dBm', 'fair',       'Cukup',        'Masih aman, pantau secara berkala'],
      ['-27 ~ -30 dBm', 'weak',       'Lemah',        'Periksa sambungan / splice / konektor'],
      ['< -30 dBm',     'critical',   'Kritis',       'Segera periksa kabel & loss fiber'],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ref): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="flex:1;min-width:180px;padding:10px 16px;border-right:1px solid var(--border-color,#e5e7eb);">
      <span class="sig-badge <?php echo e($ref[1]); ?>" style="font-size:.7rem;"><?php echo e($ref[2]); ?></span>
      <div style="font-size:.78rem;font-weight:600;margin:4px 0 2px;font-family:monospace;"><?php echo e($ref[0]); ?></div>
      <div style="font-size:.72rem;color:var(--text-muted,#6b7280);"><?php echo e($ref[3]); ?></div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>


<div id="live-toast"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
/* ── Accordion ───────────────────────────────────────── */
function toggleOlt(id) {
  document.getElementById('olt-stack-' + id).classList.toggle('collapsed');
}
function toggleAll(collapse) {
  document.querySelectorAll('.olt-stack').forEach(c => {
    c.classList.toggle('collapsed', collapse);
  });
}

/* ── Detail row ──────────────────────────────────────── */
function toggleOntDetail(ontId) {
  const row = document.getElementById('detail-row-' + ontId);
  if (row) row.classList.toggle('open');
}

/* ── Filters ─────────────────────────────────────────── */
let activeFilter = 'all';

function setFilter(f) {
  activeFilter = f;
  document.querySelectorAll('.sig-pill').forEach(p =>
    p.classList.toggle('active', p.dataset.filter === f)
  );
  applyFilters();
}

function applyFilters() {
  const q = (document.getElementById('sig-search').value || '').toLowerCase().trim();

  document.querySelectorAll('.olt-stack').forEach(stack => {
    const rows = stack.querySelectorAll('.ont-row');
    let visible = 0;

    rows.forEach(row => {
      const quality = row.dataset.quality || 'unknown';
      const name    = row.dataset.custName  || '';
      const pppoe   = row.dataset.pppoe     || '';
      const serial  = row.dataset.serial    || '';

      let passQ = true;
      if      (activeFilter === 'good')   passQ = ['good','fair','too_strong'].includes(quality);
      else if (activeFilter === 'weak')   passQ = quality === 'weak';
      else if (activeFilter === 'crit')   passQ = quality === 'critical';
      else if (activeFilter === 'nodata') passQ = quality === 'unknown';

      let passS = true;
      if (q.length >= 2) passS = name.includes(q) || pppoe.includes(q) || serial.includes(q);

      const show = passQ && passS;
      row.classList.toggle('filtered-out', !show);

      const dr = document.getElementById('detail-row-' + row.dataset.ontId);
      if (dr && !show) dr.classList.remove('open');

      if (show) visible++;
    });

    // Auto-expand when searching
    if (q.length >= 2 && visible > 0) stack.classList.remove('collapsed');

    // Hide entire OLT card if zero ONTs match
    const noOnt = stack.querySelector('.ont-empty');
    stack.style.display = (visible > 0 || noOnt) ? '' : 'none';
  });
}

/* ── Live Check (single) ─────────────────────────────── */
async function checkLive(customerId, ontId, btn) {
  btn.disabled = true;
  btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i>';

  const qBadge = document.getElementById('qbadge-' + ontId);
  if (qBadge) { qBadge.className = 'sig-badge loading'; qBadge.textContent = 'Checking…'; }

  try {
    const res  = await fetch(`<?php echo e(url('/admin/signal/check')); ?>/${customerId}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    });
    const json = await res.json();

    if (json.success && json.data) {
      updateOntRow(ontId, json.data, json.source);
      showToast(`✓ ${json.data.customer_name ?? 'ONT'}: ${json.data.rx_power_str ?? '—'}`);
    } else {
      if (qBadge) { qBadge.className = 'sig-badge unknown'; qBadge.textContent = 'Gagal'; }
      showToast('⚠ ' + (json.message || 'Gagal ambil data.'), 'warn');
    }
  } catch(e) {
    if (qBadge) { qBadge.className = 'sig-badge unknown'; qBadge.textContent = 'Error'; }
    showToast('✕ Error: ' + e.message, 'error');
  }

  btn.disabled = false;
  btn.innerHTML = '<i class="bx bx-refresh"></i>';
}

function updateOntRow(ontId, d, source) {
  const rx = d.rx_power;
  const q  = d.quality || 'unknown';
  const qLabel = {too_strong:'Terlalu Kuat',good:'Baik',fair:'Cukup',weak:'Lemah',critical:'Kritis',unknown:'No Data'};
  const barCol = {good:'#16a34a',fair:'#16a34a',too_strong:'#7c3aed',weak:'#d97706',critical:'#dc2626',unknown:'#d1d5db'};
  const barPct = rx !== null ? Math.max(0, Math.min(100, ((rx + 35) / 25) * 100)) : 0;

  // Rx cell
  const cell = document.getElementById('rx-cell-' + ontId);
  if (cell) {
    if (rx !== null) {
      cell.innerHTML = `<span style="font-family:monospace;font-weight:700;font-size:.85rem;">${rx.toFixed(2)} dBm</span>
        <div class="sig-bar-wrap"><div class="sig-bar-fill" style="width:${barPct}%;background:${barCol[q]||'#d1d5db'};"></div></div>`;
    } else {
      cell.innerHTML = `<span style="color:var(--text-muted,#6b7280);font-size:.78rem;">—</span>`;
    }
  }

  // Badge
  const badge = document.getElementById('qbadge-' + ontId);
  if (badge) { badge.className = 'sig-badge ' + q; badge.textContent = qLabel[q] || q; }

  // Row filter class
  const row = document.querySelector(`.ont-row[data-ont-id="${ontId}"]`);
  if (row) {
    row.dataset.quality = q;
    row.className = row.className.replace(/fq-\w+/g,
      rx !== null && rx >= -25 ? 'fq-good' : rx !== null && rx >= -30 ? 'fq-weak' : rx !== null ? 'fq-crit' : 'fq-nodata'
    );
  }

  // Live detail block inside detail row
  const ld = document.getElementById('live-detail-' + ontId);
  if (ld) {
    const srcBadge = source === 'acs_live'
      ? '<span style="display:inline-block;padding:2px 8px;border-radius:10px;background:#e0f2fe;color:#0369a1;font-size:.72rem;font-weight:700;">● ACS LIVE</span>'
      : '<span style="display:inline-block;padding:2px 8px;border-radius:10px;background:#f3f4f6;color:#6b7280;font-size:.72rem;">● Database</span>';

    // ACS connection status — only show if it's 'online', skip 'offline'
    // (ACS offline = TR-069 not connected, bukan berarti ONT mati di OLT)
    const acsStatusOnline = d.status && d.status.toLowerCase() === 'online';
    const acsStatusItem = acsStatusOnline
      ? `<div class="ont-detail-item"><div class="lbl">Status ACS</div><div class="val" style="color:#16a34a;">● Online</div></div>`
      : (d.status ? `<div class="ont-detail-item"><div class="lbl">Status ACS</div><div class="val" style="color:#9ca3af;font-size:.78rem;">Tidak terhubung ke ACS<br><span style="font-size:.68rem;color:#d1d5db;">(bukan berarti ONT mati)</span></div></div>` : '');

    const items = [
      d.model  ? `<div class="ont-detail-item"><div class="lbl">Model</div><div class="val">${d.model}</div></div>` : '',
      d.brand  ? `<div class="ont-detail-item"><div class="lbl">Brand</div><div class="val">${d.brand}</div></div>` : '',
      d.uptime ? `<div class="ont-detail-item"><div class="lbl">Uptime ACS</div><div class="val">${d.uptime}</div></div>` : '',
      d.wan_ip ? `<div class="ont-detail-item"><div class="lbl">WAN IP</div><div class="val">${d.wan_ip}</div></div>` : '',
      d.ssid   ? `<div class="ont-detail-item"><div class="lbl">SSID Wi-Fi</div><div class="val">${d.ssid}</div></div>` : '',
      acsStatusItem,
    ].join('');

    ld.style.display = 'block';
    ld.innerHTML = `<div style="font-size:.72rem;margin-bottom:8px;">${srcBadge} &nbsp;Diperbarui sekarang</div>
      <div class="ont-detail-grid">${items}</div>`;

    // Auto-open the detail row
    const dr = document.getElementById('detail-row-' + ontId);
    if (dr) dr.classList.add('open');
  }
}

/* ── Refresh All Visible ─────────────────────────────── */
async function refreshAllVisible() {
  const btn = document.getElementById('btn-refresh-all');
  btn.disabled = true;
  btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Refreshing…';

  const rows = [...document.querySelectorAll('.ont-row:not(.filtered-out)')];
  let count = 0;
  for (const row of rows) {
    const custId = row.dataset.customerId;
    const ontId  = row.dataset.ontId;
    if (!custId || custId === 'null' || custId === '') continue;
    const b = document.getElementById('btn-live-' + ontId);
    if (!b) continue;
    await checkLive(parseInt(custId), parseInt(ontId), b);
    count++;
    await new Promise(r => setTimeout(r, 250));
  }

  btn.disabled = false;
  btn.innerHTML = '<i class="bx bx-refresh"></i> Refresh Semua';
  showToast(`✓ Selesai refresh ${count} ONT`);
}

/* ── Toast ───────────────────────────────────────────── */
let toastTimer;
function showToast(msg, type) {
  const t = document.getElementById('live-toast');
  clearTimeout(toastTimer);
  t.textContent = msg;
  t.style.background = type === 'error' ? '#dc2626' : type === 'warn' ? '#b45309' : '#1e293b';
  t.classList.add('show');
  toastTimer = setTimeout(() => t.classList.remove('show'), 3500);
}

/* ── Keyboard shortcut ───────────────────────────────── */
document.addEventListener('keydown', e => {
  if (e.key === '/' && !['INPUT','TEXTAREA'].includes(document.activeElement.tagName)) {
    e.preventDefault();
    document.getElementById('sig-search').focus();
  }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/redaman/signal.blade.php ENDPATH**/ ?>