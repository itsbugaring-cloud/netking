
<?php $__env->startSection('title', 'Unit Perangkat (SN)'); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page nk-list-page inv-units-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-chip'></i> Inventaris</div>
      <h1 class="ms-page-title">Unit Perangkat (SN)</h1>
    </div>
    <div class="ms-page-actions">
      <a href="<?php echo e(route('admin.inventory.units.create')); ?>" class="ms-btn"><i class='bx bx-plus'></i> Tambah Unit</a>
    </div>
  </div>

  <?php if(session('success')): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo e(session('success')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>
  <?php if(session('error')): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo e(session('error')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <div class="ms-stat-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 1rem;">
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-primary"><i class='bx bx-chip'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Total Unit</div>
        <div class="ms-stat-value"><?php echo e(number_format($total_unit ?? 0)); ?></div>
        <div class="ms-stat-meta">semua status</div>
      </div>
    </div>
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-success"><i class='bx bx-buildings'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Di Gudang</div>
        <div class="ms-stat-value"><?php echo e(number_format($total_gudang ?? 0)); ?></div>
        <div class="ms-stat-meta">siap pakai</div>
      </div>
    </div>
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-info"><i class='bx bx-wifi'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Terpasang</div>
        <div class="ms-stat-value"><?php echo e(number_format($total_terpasang ?? 0)); ?></div>
        <div class="ms-stat-meta">di lokasi pelanggan</div>
      </div>
    </div>
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-danger"><i class='bx bx-error-circle'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Rusak</div>
        <div class="ms-stat-value"><?php echo e(number_format($total_rusak ?? 0)); ?></div>
        <div class="ms-stat-meta">perlu tindak lanjut</div>
      </div>
    </div>
  </div>

  <div class="ms-panel">
    <div class="ms-panel-head">
      <div>
        <h5 class="ms-panel-title">Daftar Unit</h5>
        <div class="ms-panel-subtitle">Unit perangkat dengan serial number</div>
      </div>
      <div class="ms-toolbar-right">
        <span class="ms-chip"><i class='bx bx-data'></i> <?php echo e($units->total()); ?> unit</span>
      </div>
    </div>

    <div class="ms-toolbar">
      <div class="ms-toolbar-left">
        <form method="GET" action="<?php echo e(route('admin.inventory.units.index')); ?>" class="ms-filter-form">
          <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                 placeholder="Cari SN / MAC..." class="form-control form-control-sm">
          <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <?php $__currentLoopData = $status_options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($val); ?>" <?php echo e(request('status') === $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <select name="lokasi_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Lokasi</option>
            <?php $__currentLoopData = $lokasi_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($loc->id); ?>" <?php echo e(request('lokasi_id') == $loc->id ? 'selected' : ''); ?>>
                <?php echo e($loc->nama_lokasi); ?>

              </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <button type="submit" class="ms-btn-secondary ms-btn-sm"><i class='bx bx-search'></i></button>
          <?php if(request()->anyFilled(['search','status','lokasi_id'])): ?>
          <a href="<?php echo e(route('admin.inventory.units.index')); ?>" class="ms-btn-ghost ms-btn-sm">Reset Filter</a>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th>Serial Number</th>
              <th>MAC Address</th>
              <th>Barang</th>
              <th>Status</th>
              <th>Lokasi</th>
              <th>Penanggung Jawab</th>
              <th style="width:110px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td><code><?php echo e($unit->serial_number); ?></code></td>
              <td>
                <?php if($unit->mac_address): ?>
                  <code><?php echo e($unit->mac_address); ?></code>
                <?php else: ?>
                  <span style="color:var(--txt-3)">-</span>
                <?php endif; ?>
              </td>
              <td>
                <div><?php echo e($unit->masterBarang->merek ?? '-'); ?> <?php echo e($unit->masterBarang->tipe ?? ''); ?></div>
                <div style="font-size:.78rem;color:var(--txt-3)"><?php echo e($unit->masterBarang->kategori->nama ?? ''); ?></div>
              </td>
              <td>
                <?php $st = $unit->status ?? ''; ?>
                <?php if($st === 'gudang'): ?>
                  <span class="badge-status badge-active">Gudang</span>
                <?php elseif($st === 'terpasang'): ?>
                  <span class="badge-status badge-active" style="background:color-mix(in srgb,var(--blue) 12%,var(--surface));color:var(--blue);border-color:color-mix(in srgb,var(--blue) 25%,var(--border));">Terpasang</span>
                <?php elseif($st === 'dibawa_teknisi'): ?>
                  <span class="badge-status badge-pending">Teknisi</span>
                <?php elseif($st === 'rusak'): ?>
                  <span class="badge-status badge-inactive">Rusak</span>
                <?php elseif($st === 'rma'): ?>
                  <span class="badge-status badge-inactive">RMA</span>
                <?php elseif($st === 'terjual'): ?>
                  <span class="badge-status badge-inactive">Terjual</span>
                <?php elseif($st === 'hilang'): ?>
                  <span class="badge-status badge-inactive">Hilang</span>
                <?php else: ?>
                  <span class="badge-status badge-inactive"><?php echo e($st); ?></span>
                <?php endif; ?>
              </td>
              <td><?php echo e($unit->lokasi->nama_lokasi ?? '-'); ?></td>
              <td><?php echo e($unit->penanggung_jawab ?? '-'); ?></td>
              <td>
                <div class="d-flex gap-1">
                  <a href="<?php echo e(route('admin.inventory.units.show', $unit)); ?>" class="nk-action-btn view" title="Detail">
                    <i class='bx bx-show'></i>
                  </a>
                  <a href="<?php echo e(route('admin.inventory.units.edit', $unit)); ?>" class="nk-action-btn edit" title="Edit">
                    <i class='bx bx-edit'></i>
                  </a>
                  <form action="<?php echo e(route('admin.inventory.units.destroy', $unit)); ?>" method="POST" class="m-0"
                        onsubmit="return confirm('Hapus unit ini?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="nk-action-btn delete" title="Hapus">
                      <i class='bx bx-trash'></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="7">
              <div class="empty-state">
                <div class="empty-state-icon"><i class='bx bx-chip'></i></div>
                <div class="empty-state-title">Belum ada unit</div>
                <div class="empty-state-desc">Mulai tambahkan unit perangkat pertama</div>
              </div>
            </td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if($units->hasPages()): ?>
    <div class="ms-panel-footer"><?php echo e($units->withQueryString()->links()); ?></div>
    <?php endif; ?>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/inventory/units/index.blade.php ENDPATH**/ ?>