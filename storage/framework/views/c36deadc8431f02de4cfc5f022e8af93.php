<?php $__env->startSection('title', 'Telegram Request'); ?>

<?php $__env->startPush('styles'); ?>
<style>
  .tg-req-page .ms-table-shell { padding: 0; }
  .tg-req-page .table th,
  .tg-req-page .table td { vertical-align: middle; }
  .tg-req-status {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .2rem .55rem;
    border-radius: 999px;
    border: 1px solid var(--border);
    font-size: .73rem;
    font-weight: 600;
    line-height: 1;
    color: var(--txt);
    background: var(--surface);
    text-transform: lowercase;
  }
  .tg-req-status::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: #94a3b8;
  }
  .tg-req-status.st-online::before { background: #16a34a; }
  .tg-req-status.st-diterima::before { background: #2563eb; }
  .tg-req-status.st-menunggu_push_olt::before { background: #f59e0b; }
  .tg-req-status.st-menunggu_pppoe_up::before { background: #f97316; }
  .tg-req-status.st-rejected::before { background: #ef4444; }
  .tg-req-status.st-failed_mikrotik::before { background: #dc2626; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page tg-req-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bxl-telegram'></i> Automasi Bot</div>
      <h1 class="ms-page-title">Telegram Request</h1>
    </div>
  </div>

  <div class="ms-panel">
    <div class="ms-panel-head">
      <span class="ms-panel-title">
        <i class='bx bx-list-ul me-2' style="color:var(--blue);"></i>Antrian Request
        <span class="ms-2 ms-kpi-chip"><strong><?php echo e($total); ?></strong> total</span>
      </span>
    </div>

    <div class="ms-panel-body">
      <form class="row g-2 mb-3" method="GET" action="<?php echo e(route('admin.telegram.requests.index')); ?>">
        <div class="col-lg-5">
          <div class="nk-search-wrap">
            <i class='bx bx-search'></i>
            <input type="text" name="q" class="nk-search-input" value="<?php echo e($q); ?>" placeholder="">
          </div>
        </div>
        <div class="col-lg-3">
          <select name="status" class="form-select form-select-sm">
            <option value="">Semua status</option>
            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($s); ?>" <?php if($status === $s): echo 'selected'; endif; ?>><?php echo e($s); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="col-lg-2">
          <button class="ms-btn w-100" type="submit"><i class='bx bx-filter-alt'></i> Filter</button>
        </div>
        <div class="col-lg-2">
          <a class="ms-btn-secondary w-100 d-inline-flex justify-content-center" href="<?php echo e(route('admin.telegram.requests.index')); ?>">Reset</a>
        </div>
      </form>

      <div class="ms-table-shell">
        <div class="table-responsive">
          <table class="table table-flat mb-0">
            <thead>
              <tr>
                <th>Ref</th>
                <th>Status</th>
                <th>Area</th>
                <th>Pelanggan</th>
                <th>PPPoE</th>
                <th>Dibuat Oleh</th>
                <th>Waktu</th>
                <th style="width:100px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><code><?php echo e($it['ref']); ?></code></td>
                <td>
                  <span class="tg-req-status st-<?php echo e($it['status']); ?>"><?php echo e($it['status']); ?></span>
                </td>
                <td><?php echo e($it['area_name'] ?: '-'); ?></td>
                <td><?php echo e($it['customer_name'] ?: '-'); ?></td>
                <td><code><?php echo e($it['pppoe_user'] ?: '-'); ?></code></td>
                <td>
                  <div><?php echo e($it['from_name'] ?: '-'); ?></div>
                  <small class="text-muted"><?php echo e('@' . ($it['from_username'] ?: '-')); ?> • <?php echo e($it['chat_id'] ?: '-'); ?></small>
                </td>
                <td><?php echo e($it['submitted_at'] ?: '-'); ?></td>
                <td>
                  <a href="<?php echo e(route('admin.telegram.requests.show', $it['ref'])); ?>" class="ms-btn-secondary" style="padding:.35rem .65rem;font-size:.77rem;">Detail</a>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr><td colspan="8" class="text-center text-muted py-4">Belum ada request.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small">Halaman <?php echo e($page); ?> dari <?php echo e($lastPage); ?></div>
        <div class="d-flex gap-2">
          <?php if($page > 1): ?>
            <a class="ms-btn-secondary" href="<?php echo e(route('admin.telegram.requests.index', ['q' => $q, 'status' => $status, 'page' => $page - 1])); ?>">Prev</a>
          <?php endif; ?>
          <?php if($page < $lastPage): ?>
            <a class="ms-btn-secondary" href="<?php echo e(route('admin.telegram.requests.index', ['q' => $q, 'status' => $status, 'page' => $page + 1])); ?>">Next</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/telegram/requests/index.blade.php ENDPATH**/ ?>