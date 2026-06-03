
<?php $__env->startSection('title', 'Komisi'); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page nk-list-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-dollar-circle'></i> Manajemen Komisi</div>
      <h1 class="ms-page-title">Komisi Mitra</h1>
    </div>
    <div class="ms-page-actions">
      <a href="<?php echo e(route('admin.commissions.export', request()->query())); ?>" class="ms-btn-secondary">
        <i class='bx bx-download'></i> Export CSV
      </a>
    </div>
  </div>

  <div class="stat-grid mb-4">
    <div class="stat-card">
      <div>
        <div class="stat-label">Tertunda</div>
        <div class="stat-value" style="font-size:1.1rem;">Rp <?php echo e(number_format($stats['total_pending'] ?? 0, 0, ',', '.')); ?></div>
        <div class="stat-change" style="color:var(--orange,#f59e0b);">Menunggu pembayaran pelanggan</div>
      </div>
      <div class="stat-icon" style="background:color-mix(in srgb,var(--orange,#f59e0b) 10%,var(--surface));color:var(--orange,#f59e0b);"><i class='bx bx-time'></i></div>
    </div>
    <div class="stat-card">
      <div>
        <div class="stat-label">Dikonfirmasi</div>
        <div class="stat-value" style="font-size:1.1rem;">Rp <?php echo e(number_format($stats['total_unpaid'] ?? 0, 0, ',', '.')); ?></div>
        <div class="stat-change neutral">Siap dicairkan ke mitra</div>
      </div>
      <div class="stat-icon si-blue"><i class='bx bx-wallet'></i></div>
    </div>
    <div class="stat-card">
      <div>
        <div class="stat-label">Sudah Dibayar</div>
        <div class="stat-value" style="font-size:1.1rem;">Rp <?php echo e(number_format($stats['total_paid'] ?? 0, 0, ',', '.')); ?></div>
        <div class="stat-change up">Pencairan selesai</div>
      </div>
      <div class="stat-icon si-green"><i class='bx bx-check-circle'></i></div>
    </div>
  </div>

  
  <div class="ms-panel mb-3">
    <div class="ms-panel-body" style="padding:14px 18px;">
      <form method="GET" action="<?php echo e(route('admin.commissions.index')); ?>" class="d-flex flex-wrap gap-2 align-items-end">
        <div>
          <label style="font-size:.75rem;font-weight:600;color:var(--txt-3);display:block;margin-bottom:4px;">Mitra</label>
          <select name="partner_id" class="nk-length-select" style="min-width:160px;" onchange="this.form.submit()">
            <option value="">Semua Mitra</option>
            <?php $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($p->id); ?>" <?php echo e(request('partner_id')==$p->id?'selected':''); ?>><?php echo e($p->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div>
          <label style="font-size:.75rem;font-weight:600;color:var(--txt-3);display:block;margin-bottom:4px;">Bulan</label>
          <select name="month" class="nk-length-select" style="min-width:100px;" onchange="this.form.submit()">
            <option value="">Semua</option>
            <?php $__currentLoopData = ['1'=>'Jan','2'=>'Feb','3'=>'Mar','4'=>'Apr','5'=>'Mei','6'=>'Jun','7'=>'Jul','8'=>'Agu','9'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num=>$name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($num); ?>" <?php echo e(request('month')==$num?'selected':''); ?>><?php echo e($name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div>
          <label style="font-size:.75rem;font-weight:600;color:var(--txt-3);display:block;margin-bottom:4px;">Tahun</label>
          <select name="year" class="nk-length-select" style="min-width:90px;" onchange="this.form.submit()">
            <option value="">Semua</option>
            <?php $__currentLoopData = range(now()->year, 2024); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($y); ?>" <?php echo e(request('year')==$y?'selected':''); ?>><?php echo e($y); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div>
          <label style="font-size:.75rem;font-weight:600;color:var(--txt-3);display:block;margin-bottom:4px;">Status</label>
          <select name="status" class="nk-length-select" style="min-width:140px;" onchange="this.form.submit()">
            <option value="">Semua</option>
            <option value="pending" <?php echo e(request('status')=='pending'?'selected':''); ?>>Tertunda</option>
            <option value="unpaid" <?php echo e(request('status')=='unpaid'?'selected':''); ?>>Dikonfirmasi</option>
            <option value="paid" <?php echo e(request('status')=='paid'?'selected':''); ?>>Sudah Dibayar</option>
          </select>
        </div>
        <div style="display:flex;gap:6px;align-items:flex-end;">
          <button type="submit" class="ms-btn-secondary" style="height:34px;"><i class='bx bx-filter-alt'></i> Filter</button>
          <?php if(request()->hasAny(['partner_id','month','year','status'])): ?>
          <a href="<?php echo e(route('admin.commissions.index')); ?>" class="ms-btn-secondary" style="height:34px;color:var(--red);">
            <i class='bx bx-x'></i> Reset
          </a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  
  <?php if($partnerSummary->isNotEmpty()): ?>
  <div class="ms-panel mb-3">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-bar-chart me-2'></i>Ringkasan per Mitra</h5>
    </div>
    <div class="table-responsive">
      <table class="table table-flat mb-0">
        <thead>
          <tr>
            <th>Mitra</th>
            <th class="text-end">Jml Transaksi</th>
            <th class="text-end">Siap Dicairkan</th>
            <th class="text-end">Sudah Dibayar</th>
            <th class="text-end">Total</th>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $partnerSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ps): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td style="font-weight:600;"><?php echo e($ps->user->name ?? '-'); ?></td>
            <td class="text-end"><?php echo e($ps->cnt); ?></td>
            <td class="text-end" style="color:var(--blue);font-weight:600;">Rp <?php echo e(number_format($ps->unpaid_total,0,',','.')); ?></td>
            <td class="text-end" style="color:var(--green);font-weight:600;">Rp <?php echo e(number_format($ps->paid_total,0,',','.')); ?></td>
            <td class="text-end" style="font-weight:700;">Rp <?php echo e(number_format($ps->total,0,',','.')); ?></td>
          </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <div class="ms-panel">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-dollar-circle me-2'></i>Riwayat Komisi</h5>
    </div>
    <div class="ms-table-shell">
      <div class="nk-table-controls">
        <div class="nk-search-wrap nk-table-search-trigger">
          <i class='bx bx-search'></i>
          <input type="text" id="comm-search" class="nk-search-input" placeholder="Cari komisi...">
        </div>
        <div class="d-flex align-items-center gap-2">
          <span style="font-size:.78rem;color:var(--txt-3);">Tampilkan</span>
          <select id="comm-length" class="nk-length-select">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-flat mb-0" id="commissions-table" style="min-width:1320px;">
          <thead>
            <tr>
              <th style="min-width:180px;">Mitra</th>
              <th style="min-width:180px;">Pelanggan</th>
              <th style="min-width:130px;">Periode</th>
              <th style="min-width:130px;">Paket</th>
              <th style="min-width:140px;">Komisi</th>
              <th style="min-width:120px;">Status</th>
              <th style="min-width:220px;">Catatan</th>
              <th style="min-width:120px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $__currentLoopData = $commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $commission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $monthNames = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']; ?>
            <tr>
              <td style="font-weight:600;"><?php echo e($commission->user->name ?? '-'); ?></td>
              <td><?php echo e($commission->customer->name ?? '-'); ?></td>
              <td>
                <div style="font-weight:600;"><?php echo e($monthNames[$commission->month] ?? '-'); ?> <?php echo e($commission->year); ?></div>
                <?php if($commission->invoice): ?>
                <div style="font-size:.72rem;color:var(--txt-3);"><?php echo e($commission->invoice->invoice_number); ?></div>
                <?php endif; ?>
              </td>
              <td>Rp <?php echo e(number_format($commission->customer->package_price ?? 0, 0, ',', '.')); ?></td>
              <td style="font-weight:700;color:var(--green);">Rp <?php echo e(number_format($commission->amount, 0, ',', '.')); ?></td>
              <td>
                <?php if($commission->status === 'paid'): ?>
                <span class="badge-status badge-paid">Lunas</span>
                <?php elseif($commission->status === 'unpaid'): ?>
                <span class="badge-status badge-active">Dikonfirmasi</span>
                <?php else: ?>
                <span class="badge-status badge-pending">Tertunda</span>
                <?php endif; ?>
              </td>
              <td style="font-size:.75rem;color:var(--txt-3);">
                <?php if($commission->status === 'pending'): ?>
                <i class='bx bx-time' style="color:var(--orange,#f59e0b);"></i> Invoice pelanggan belum dibayar
                <?php elseif($commission->status === 'unpaid'): ?>
                <i class='bx bx-check' style="color:var(--blue);"></i> Siap untuk dicairkan
                <?php elseif($commission->status === 'paid'): ?>
                <div><i class='bx bx-check-double' style="color:var(--green);"></i> Dibayar <?php echo e($commission->paid_at?->format('d M Y')); ?></div>
                <?php if($commission->payment_method): ?>
                <div>Via: <?php echo e($commission->payment_method); ?></div>
                <?php endif; ?>
                <?php if($commission->payment_proof): ?>
                <a href="<?php echo e(Storage::url($commission->payment_proof)); ?>" target="_blank" style="color:var(--blue);font-size:.72rem;"><i class='bx bx-file'></i> Lihat bukti</a>
                <?php endif; ?>
                <?php endif; ?>
              </td>
              <td>
                <?php if($commission->status === 'unpaid'): ?>
                <button type="button" class="nk-action-btn pay" title="Cairkan Komisi"
                  data-id="<?php echo e($commission->id); ?>"
                  data-name="<?php echo e($commission->user->name ?? '-'); ?>"
                  data-amount="Rp <?php echo e(number_format($commission->amount, 0, ',', '.')); ?>"
                  data-url="<?php echo e(route('admin.commissions.pay-single', $commission)); ?>"
                  onclick="openPayModal(this)">
                  <i class='bx bx-money'></i>
                </button>
                <?php elseif($commission->status === 'paid'): ?>
                <span style="font-size:.72rem;color:var(--green);font-weight:600;"><i class='bx bx-check-circle'></i> Selesai</span>
                <?php else: ?>
                <span style="font-size:.72rem;color:var(--txt-3);">—</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>


<div id="payModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.55);align-items:center;justify-content:center;">
  <div style="background:var(--surface);border-radius:16px;width:100%;max-width:480px;margin:auto;padding:28px 28px 24px;position:relative;box-shadow:0 20px 60px rgba(0,0,0,.35);">
    <button onclick="closePayModal()" style="position:absolute;top:16px;right:16px;background:none;border:none;font-size:1.3rem;color:var(--txt-3);cursor:pointer;line-height:1;"><i class='bx bx-x'></i></button>
    <div style="font-size:1rem;font-weight:700;margin-bottom:4px;color:var(--txt);">
      <i class='bx bx-money-withdraw' style="color:var(--green);"></i> Cairkan Komisi
    </div>
    <div id="payModalDesc" style="font-size:.83rem;color:var(--txt-3);margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:14px;"></div>

    <form id="payForm" method="POST" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <div style="margin-bottom:14px;">
        <label style="font-size:.78rem;font-weight:600;color:var(--txt-2);display:block;margin-bottom:6px;">Metode Pembayaran</label>
        <select name="payment_method" class="nk-length-select" style="width:100%;">
          <option value="">— Pilih metode —</option>
          <option value="Transfer Bank">Transfer Bank</option>
          <option value="OVO">OVO</option>
          <option value="GoPay">GoPay</option>
          <option value="DANA">DANA</option>
          <option value="ShopeePay">ShopeePay</option>
          <option value="Tunai">Tunai</option>
          <option value="Lainnya">Lainnya</option>
        </select>
      </div>

      <div style="margin-bottom:14px;">
        <label style="font-size:.78rem;font-weight:600;color:var(--txt-2);display:block;margin-bottom:6px;">
          Catatan <span style="font-weight:400;color:var(--txt-3);">(opsional)</span>
        </label>
        <textarea name="payment_notes" rows="2" placeholder="Nomor referensi, nama rekening, dll..."
          style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);color:var(--txt);font-size:.83rem;resize:vertical;"></textarea>
      </div>

      <div style="margin-bottom:22px;">
        <label style="font-size:.78rem;font-weight:600;color:var(--txt-2);display:block;margin-bottom:6px;">
          Bukti Pembayaran <span style="font-weight:400;color:var(--txt-3);">(opsional · JPG/PNG/PDF ≤3MB)</span>
        </label>
        <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf"
          style="width:100%;padding:6px 10px;border:1px solid var(--border);border-radius:8px;background:var(--bg);color:var(--txt);font-size:.82rem;">
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" onclick="closePayModal()" class="ms-btn-secondary">Batal</button>
        <button type="submit" class="ms-btn" style="background:var(--green,#22c55e);border-color:var(--green,#22c55e);">
          <i class='bx bx-check'></i> Cairkan Sekarang
        </button>
      </div>
    </form>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
  $(function() {
    var table = $('#commissions-table').DataTable({
      dom: '<rt><"d-flex justify-content-between align-items-center mt-3"ip>',
      pageLength: 25,
      order: [[2, 'desc']],
      language: {
        info: '_START_-_END_ dari _TOTAL_',
        infoEmpty: 'Tidak ada data',
        zeroRecords: 'Tidak ditemukan',
        paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
      },
      columnDefs: [{ orderable: false, targets: [7] }]
    });
    $('#comm-search').on('input', function() { table.search(this.value).draw(); });
    $('#comm-length').on('change', function() { table.page.len(+this.value).draw(); });
  });

  function openPayModal(btn) {
    var name   = btn.getAttribute('data-name');
    var amount = btn.getAttribute('data-amount');
    var url    = btn.getAttribute('data-url');
    document.getElementById('payModalDesc').textContent = 'Cairkan komisi ' + amount + ' untuk mitra ' + name;
    document.getElementById('payForm').action = url;
    document.getElementById('payForm').reset();
    document.getElementById('payModal').style.display = 'flex';
  }

  function closePayModal() {
    document.getElementById('payModal').style.display = 'none';
  }

  document.getElementById('payModal').addEventListener('click', function(e) {
    if (e.target === this) closePayModal();
  });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/commissions/index.blade.php ENDPATH**/ ?>