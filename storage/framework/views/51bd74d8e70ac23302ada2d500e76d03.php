<?php $__env->startSection('title', 'Detail Telegram Request'); ?>

<?php $__env->startPush('styles'); ?>
<style>
  .tg-show-page .tg-status {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .25rem .6rem;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: var(--surface);
    font-size: .78rem;
    font-weight: 600;
    text-transform: lowercase;
  }
  .tg-show-page .tg-status::before {
    content: '';
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: #94a3b8;
  }
  .tg-show-page .tg-status.st-online::before { background:#16a34a; }
  .tg-show-page .tg-status.st-diterima::before { background:#2563eb; }
  .tg-show-page .tg-status.st-menunggu_push_olt::before { background:#f59e0b; }
  .tg-show-page .tg-status.st-menunggu_pppoe_up::before { background:#f97316; }
  .tg-show-page .tg-status.st-rejected::before { background:#ef4444; }
  .tg-show-page .tg-status.st-failed_mikrotik::before { background:#dc2626; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page tg-show-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bxl-telegram'></i> Automasi Bot</div>
      <h1 class="ms-page-title">Detail Request</h1>
      <div class="mt-1 d-flex align-items-center gap-2">
        <code><?php echo e($ref); ?></code>
        <span class="tg-status st-<?php echo e(data_get($payload, 'status', '-')); ?>"><?php echo e(data_get($payload, 'status', '-')); ?></span>
      </div>
    </div>
    <div class="ms-page-actions">
      <a href="<?php echo e(route('admin.telegram.requests.index')); ?>" class="ms-btn-secondary"><i class='bx bx-arrow-back'></i> Kembali</a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-xl-7">
      <div class="ms-panel">
        <div class="ms-panel-head"><span class="ms-panel-title">Data Draft</span></div>
        <div class="ms-panel-body">
          <table class="table table-flat mb-0">
            <tbody>
              <tr><th style="width:32%;">Area</th><td><?php echo e(data_get($payload, 'draft.area_name', '-')); ?></td></tr>
              <tr><th>Nama</th><td><?php echo e(data_get($payload, 'draft.nama', '-')); ?></td></tr>
              <tr><th>No HP</th><td><?php echo e(data_get($payload, 'draft.no_hp', '-')); ?></td></tr>
              <tr><th>SN ONT</th><td><?php echo e(data_get($payload, 'draft.sn_ont', '-')); ?></td></tr>
              <tr><th>PPPoE User</th><td><code><?php echo e(data_get($payload, 'draft.pppoe_user', '-')); ?></code></td></tr>
              <tr><th>Paket</th><td><?php echo e(data_get($payload, 'draft.paket_kode', '-')); ?> (<?php echo e(data_get($payload, 'draft.mikrotik_profile', '-')); ?>)</td></tr>
              <tr><th>Harga</th><td>Rp <?php echo e(number_format((float) data_get($payload, 'draft.harga', 0), 0, ',', '.')); ?></td></tr>
              <tr><th>Tanggal Pasang</th><td><?php echo e(data_get($payload, 'draft.tanggal_pasang', '-')); ?></td></tr>
              <tr><th>Customer ID</th><td><?php echo e(data_get($payload, 'customer_id', '-')); ?></td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="ms-panel mt-3">
        <div class="ms-panel-head"><span class="ms-panel-title">Aksi Website</span></div>
        <div class="ms-panel-body d-flex flex-wrap gap-2">
          <form method="POST" action="<?php echo e(route('admin.telegram.requests.approve', $ref)); ?>"><?php echo csrf_field(); ?> <button class="ms-btn" type="submit">Approve</button></form>
          <form method="POST" action="<?php echo e(route('admin.telegram.requests.create-customer', $ref)); ?>"><?php echo csrf_field(); ?> <button class="ms-btn-secondary" type="submit">Buat Customer + Link ONT</button></form>
          <form method="POST" action="<?php echo e(route('admin.telegram.requests.push-mikrotik', $ref)); ?>"><?php echo csrf_field(); ?> <button class="ms-btn-secondary" type="submit">Push MikroTik</button></form>
          <form method="POST" action="<?php echo e(route('admin.telegram.requests.mark-online', $ref)); ?>"><?php echo csrf_field(); ?> <button class="ms-btn-secondary" type="submit">Mark Online</button></form>
          <form method="POST" action="<?php echo e(route('admin.telegram.requests.reject', $ref)); ?>"><?php echo csrf_field(); ?> <button class="ms-btn-ghost" type="submit">Reject</button></form>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-5">
      <div class="ms-panel">
        <div class="ms-panel-head"><span class="ms-panel-title">Dibuat Oleh (Bot)</span></div>
        <div class="ms-panel-body">
          <div><strong>Nama:</strong> <?php echo e(data_get($payload, 'from.name', '-')); ?></div>
          <div><strong>Username:</strong> <?php echo e('@' . (data_get($payload, 'from.username', '-') ?: '-')); ?></div>
          <div><strong>Telegram ID:</strong> <?php echo e(data_get($payload, 'from.id', '-')); ?></div>
          <div><strong>Chat ID:</strong> <?php echo e(data_get($payload, 'chat_id', '-')); ?></div>
          <div><strong>Waktu Submit:</strong> <?php echo e(data_get($payload, 'submitted_at', '-')); ?></div>
        </div>
      </div>

      <div class="ms-panel mt-3">
        <div class="ms-panel-head"><span class="ms-panel-title">History Konfig</span></div>
        <div class="ms-panel-body">
          <?php $__empty_1 = true; $__currentLoopData = (array) data_get($payload, 'history', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="border rounded p-2 mb-2">
              <div><strong><?php echo e($h['status'] ?? '-'); ?></strong></div>
              <div><?php echo e($h['note'] ?? '-'); ?></div>
              <small class="text-muted"><?php echo e($h['at'] ?? '-'); ?> • <?php echo e($h['by'] ?? '-'); ?></small>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-muted">Belum ada history.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/telegram/requests/show.blade.php ENDPATH**/ ?>