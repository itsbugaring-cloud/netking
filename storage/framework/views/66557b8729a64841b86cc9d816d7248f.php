
<?php $__env->startSection('title', 'Gateway WhatsApp'); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bxl-whatsapp'></i> Gateway Pesan</div>
      <h1 class="ms-page-title">Gateway WhatsApp</h1>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="ms-panel h-100">
        <div class="ms-panel-body d-flex align-items-center gap-3">
          <div style="width:44px;height:44px;background:<?php echo e($connected ? '#f0fdf4' : '#fef2f2'); ?>;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class='bx <?php echo e($connected ? "bxl-whatsapp" : "bx-wifi-off"); ?>' style="font-size:1.35rem;color:<?php echo e($connected ? '#22c55e' : '#ef4444'); ?>;"></i>
          </div>
          <div>
            <div style="font-weight:700;color:<?php echo e($connected ? '#166534' : '#991b1b'); ?>;"><?php echo e($connected ? 'Terhubung' : 'Tidak Terhubung'); ?></div>
            <div style="font-size:.8rem;color:#64748b;"><?php echo e($configured ? 'Fonnte API key terkonfigurasi' : 'Tidak ada API key yang dikonfigurasi'); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-xl-6">
      <div class="ms-panel">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-key me-2' style="color:#2563eb;"></i>Konfigurasi Fonnte API</h5>
        </div>
        <form method="POST" action="<?php echo e(route('admin.whatsapp.config')); ?>">
          <?php echo csrf_field(); ?>
          <div class="ms-panel-body">
            <div class="mb-3">
              <label class="form-label">Fonnte API Key <span class="text-danger">*</span></label>
              <input type="text" name="fonnte_api_key" class="form-control font-monospace" placeholder="Tempel token Fonnte Anda di sini" value="<?php echo e(config('services.fonnte.api_key') ? '●●●●●●●●●●●●●●●●' : ''); ?>">
              <div class="form-text">Dapatkan API key Anda dari <a href="https://fonnte.com" target="_blank">fonnte.com</a>.</div>
            </div>
          </div>
          <div class="ms-panel-foot d-flex justify-content-end">
            <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan API Key</button>
          </div>
        </form>
      </div>

      <div class="ms-panel mt-3">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-send me-2' style="color:#2563eb;"></i>Kirim Uji Pesan</h5>
        </div>
        <form method="POST" action="<?php echo e(route('admin.whatsapp.test-send')); ?>">
          <?php echo csrf_field(); ?>
          <div class="ms-panel-body">
            <div class="mb-3">
              <label class="form-label">Nomor Telepon</label>
              <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx" required>
            </div>
            <div class="mb-0">
              <label class="form-label">Pesan</label>
              <textarea name="message" class="form-control" rows="3" required placeholder="Halo ini test dari NETKING..."></textarea>
            </div>
          </div>
          <div class="ms-panel-foot d-flex justify-content-end">
            <button type="submit" class="ms-btn" <?php echo e(!$configured ? 'disabled' : ''); ?>>
              <i class='bx bx-send'></i> Kirim Uji
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="col-xl-6">
      <div class="ms-panel">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-user me-2' style="color:#2563eb;"></i>Kirim ke Pelanggan</h5>
        </div>
        <form method="POST" action="<?php echo e(route('admin.whatsapp.send-customer')); ?>">
          <?php echo csrf_field(); ?>
          <div class="ms-panel-body">
            <div class="mb-3">
              <label class="form-label">Pilih Pelanggan</label>
              <select name="customer_id" class="form-select" required>
                <option value="">Pilih Pelanggan</option>
                <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?> (<?php echo e($c->phone); ?>)</option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
            <div class="mb-0">
              <label class="form-label">Pesan</label>
              <textarea name="message" class="form-control" rows="5" required placeholder="Ketik pesan WA di sini..."></textarea>
            </div>
          </div>
          <div class="ms-panel-foot d-flex justify-content-end">
            <button type="submit" class="ms-btn" <?php echo e(!$configured ? 'disabled' : ''); ?>>
              <i class='bxl-whatsapp'></i> Kirim Pesan
            </button>
          </div>
        </form>
      </div>

      <div class="ms-panel mt-3">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-list-ul me-2' style="color:#2563eb;"></i>Template Otomatis</h5>
        </div>
        <div class="ms-panel-body">
          <ul class="mb-0" style="font-size:.86rem;line-height:1.9;padding-left:1rem;">
            <li><strong>Selamat Datang</strong> dikirim saat pelanggan menjadi aktif</li>
            <li><strong>Pengingat Invoice</strong> dikirim pada H-3, H-1, dan H+1</li>
            <li><strong>Konfirmasi Pembayaran</strong> dikirim setelah pembayaran</li>
            <li><strong>Pemberitahuan Suspensi</strong> dikirim saat layanan ditangguhkan</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/whatsapp/index.blade.php ENDPATH**/ ?>