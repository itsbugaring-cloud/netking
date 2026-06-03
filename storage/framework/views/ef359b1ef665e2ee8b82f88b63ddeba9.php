
<?php $__env->startSection('title', 'Laporan Data Pelanggan'); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page nk-list-page">
  <div class="ms-page-head">
    <div>
      <h1 class="ms-page-title">Laporan Data Pelanggan</h1>
    </div>
    <div class="ms-page-actions">
      <a href="<?php echo e(route('admin.reports.export-billing', request()->query())); ?>" class="btn btn-sm btn-success" style="border-radius:8px;font-weight:600;">
        <i class='bx bx-download me-1'></i> Export CSV
      </a>
    </div>
  </div>

  
  <div class="stat-grid mb-4">
    <div class="stat-card">
      <div><div class="stat-label">Total Pelanggan</div><div class="stat-value"><?php echo e(number_format($stats['total'])); ?></div></div>
      <div class="stat-icon si-blue"><i class='bx bx-group'></i></div>
    </div>
    <div class="stat-card">
      <div><div class="stat-label">Aktif</div><div class="stat-value"><?php echo e(number_format($stats['active'])); ?></div></div>
      <div class="stat-icon si-green"><i class='bx bx-wifi'></i></div>
    </div>
    <div class="stat-card">
      <div><div class="stat-label">Diisolir</div><div class="stat-value"><?php echo e(number_format($stats['suspended'])); ?></div></div>
      <div class="stat-icon" style="background:color-mix(in srgb,var(--red,#ef4444) 10%,var(--surface));color:var(--red,#ef4444);"><i class='bx bx-block'></i></div>
    </div>
    <div class="stat-card">
      <div><div class="stat-label">Ada Tunggakan</div><div class="stat-value"><?php echo e(number_format($stats['unpaid_customers'])); ?></div></div>
      <div class="stat-icon" style="background:color-mix(in srgb,var(--orange,#f59e0b) 10%,var(--surface));color:var(--orange,#f59e0b);"><i class='bx bx-receipt'></i></div>
    </div>
  </div>

  
  <div class="ms-panel mb-3">
    <div class="ms-panel-body" style="padding:14px 18px;">
      <form method="GET" action="<?php echo e(route('admin.reports.billing')); ?>" class="d-flex flex-wrap gap-2 align-items-end">
        <div>
          <label style="font-size:.75rem;font-weight:600;color:var(--txt-3);display:block;margin-bottom:4px;">Area</label>
          <select name="area_id" class="form-select form-select-sm" style="min-width:150px;border-radius:8px;">
            <option value="">Semua Area</option>
            <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($area->id); ?>" <?php echo e(request('area_id')==$area->id?'selected':''); ?>><?php echo e($area->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div>
          <label style="font-size:.75rem;font-weight:600;color:var(--txt-3);display:block;margin-bottom:4px;">Mitra/Teknisi</label>
          <select name="partner_id" class="form-select form-select-sm" style="min-width:160px;border-radius:8px;">
            <option value="">Semua Mitra</option>
            <?php $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($p->id); ?>" <?php echo e(request('partner_id')==$p->id?'selected':''); ?>><?php echo e($p->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div>
          <label style="font-size:.75rem;font-weight:600;color:var(--txt-3);display:block;margin-bottom:4px;">Status Pelanggan</label>
          <select name="status" class="form-select form-select-sm" style="min-width:150px;border-radius:8px;">
            <option value="">Semua Status</option>
            <option value="active" <?php echo e(request('status')=='active'?'selected':''); ?>>Aktif</option>
            <option value="suspended" <?php echo e(request('status')=='suspended'?'selected':''); ?>>Diisolir</option>
            <option value="provisioning" <?php echo e(request('status')=='provisioning'?'selected':''); ?>>Dalam Proses</option>
            <option value="failed" <?php echo e(request('status')=='failed'?'selected':''); ?>>Gagal</option>
          </select>
        </div>
        <div>
          <label style="font-size:.75rem;font-weight:600;color:var(--txt-3);display:block;margin-bottom:4px;">Status Tagihan</label>
          <select name="invoice_status" class="form-select form-select-sm" style="min-width:150px;border-radius:8px;">
            <option value="">Semua</option>
            <option value="unpaid" <?php echo e(request('invoice_status')=='unpaid'?'selected':''); ?>>Ada Tunggakan</option>
            <option value="paid" <?php echo e(request('invoice_status')=='paid'?'selected':''); ?>>Sudah Bayar</option>
          </select>
        </div>
        <div style="display:flex;gap:6px;align-items:flex-end;">
          <button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px;height:31px;">
            <i class='bx bx-filter-alt'></i> Filter
          </button>
          <?php if(request()->hasAny(['area_id','partner_id','status','invoice_status'])): ?>
          <a href="<?php echo e(route('admin.reports.billing')); ?>" class="btn btn-sm ms-btn-secondary" style="border-radius:8px;height:31px;">Reset</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  
  <div class="ms-panel">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-group me-2'></i>Data Pelanggan (<?php echo e($customers->total()); ?> total)</h5>
    </div>
    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama</th>
              <th>Area</th>
              <th>Mitra/Teknisi</th>
              <th>Paket</th>
              <th>Status</th>
              <th>Total Bayar</th>
              <th>Tunggakan</th>
            </tr>
          </thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td style="color:var(--txt-3);font-size:.8rem;"><?php echo e($customers->firstItem() + $i); ?></td>
              <td>
                <a href="<?php echo e(route('admin.customers.show', $c)); ?>" style="font-weight:600;color:var(--txt);text-decoration:none;">
                  <?php echo e($c->name); ?>

                </a>
                <div style="font-size:.72rem;color:var(--txt-3);"><?php echo e($c->phone ?? '-'); ?></div>
              </td>
              <td style="font-size:.8125rem;"><?php echo e($c->area->name ?? '-'); ?></td>
              <td style="font-size:.8125rem;"><?php echo e($c->partner->name ?? '-'); ?></td>
              <td style="font-size:.8125rem;"><?php echo e($c->package->name ?? '-'); ?></td>
              <td>
                <?php
                  $sm = ['active'=>['Aktif','badge-active'],'suspended'=>['Diisolir','badge-inactive'],'provisioning'=>['Dalam Proses','badge-pending'],'failed'=>['Gagal','badge-danger'],'pending'=>['Pending','badge-pending']];
                  [$sl,$sc] = $sm[$c->status] ?? [ucfirst($c->status),'badge-inactive'];
                ?>
                <span class="badge-status <?php echo e($sc); ?>"><?php echo e($sl); ?></span>
              </td>
              <td style="font-weight:600;color:var(--green);font-size:.8125rem;">
                Rp <?php echo e(number_format($c->paid_total ?? 0, 0, ',', '.')); ?>

              </td>
              <td>
                <?php if(($c->unpaid_total ?? 0) > 0): ?>
                  <span style="font-weight:700;color:var(--red);font-size:.8125rem;">
                    Rp <?php echo e(number_format($c->unpaid_total, 0, ',', '.')); ?>

                  </span>
                <?php else: ?>
                  <span style="color:var(--txt-3);font-size:.8rem;">—</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="8" class="text-center py-4" style="color:var(--txt-3);">Tidak ada data</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="mt-3"><?php echo e($customers->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/reports/billing.blade.php ENDPATH**/ ?>