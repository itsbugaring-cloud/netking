
<?php $__env->startSection('title', 'Kalender Penagihan'); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-calendar'></i> Jadwal Penagihan</div>
      <h1 class="ms-page-title">Kalender Penagihan</h1>
    </div>
    <div class="ms-page-actions">
      <a href="<?php echo e(route('admin.invoices.index')); ?>" class="ms-btn-secondary">
        <i class='bx bx-list-ul'></i> Daftar Invoice
      </a>
    </div>
  </div>

  <div class="ms-panel">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-calendar-event me-2' style="color:#2563eb;"></i>Ringkasan Jatuh Tempo</h5>
      <div class="d-flex gap-2 flex-wrap">
        <span class="badge-status badge-active">Lunas</span>
        <span class="badge-status badge-danger">Jatuh Tempo</span>
        <span class="badge-status badge-pending">Tertunda</span>
      </div>
    </div>
    <div class="ms-panel-body">
      <div id="billing-calendar"></div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('billing-calendar');
    if (!calendarEl || typeof FullCalendar === 'undefined') return;

    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,dayGridWeek,listWeek'
      },
      height: 'auto',
      events: '<?php echo e(route("admin.billing.calendar")); ?>',
      eventClick: function(info) {
        if (info.event.url) {
          info.jsEvent.preventDefault();
          window.location.href = info.event.url;
        }
      },
      eventDidMount: function(info) {
        if (typeof bootstrap !== 'undefined') {
          new bootstrap.Tooltip(info.el, {
            title: info.event.title,
            placement: 'top',
            trigger: 'hover',
            container: 'body'
          });
        }
      },
      dayMaxEvents: 3,
      moreLinkContent: function(args) {
        return '+' + args.num + ' lainnya';
      },
      themeSystem: 'standard'
    });

    calendar.render();
  });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/billing/calendar.blade.php ENDPATH**/ ?>