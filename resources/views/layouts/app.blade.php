<!doctype html>
<html lang="en">
<script>
  (function() {
    try { localStorage.removeItem('nk_theme'); localStorage.removeItem('nk_sb_collapsed'); } catch (e) {}
    document.documentElement.setAttribute('data-theme', 'light');
    document.documentElement.removeAttribute('data-sidebar');
  })();
</script>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','Dasbor')</title>
  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

  <!-- DataTables -->
  <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <!-- Flatpickr -->
  <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css" rel="stylesheet">
  <!-- Dropzone -->
  <link href="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/dropzone.min.css" rel="stylesheet">
  <!-- Toastr -->
  <link href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css" rel="stylesheet">
  <!-- Animate.css -->
  <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
  <!-- NProgress -->
  <link href="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.css" rel="stylesheet">
  <!-- Quill Rich Text Editor -->
  <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
  <!-- FullCalendar -->
  <link href="https://unpkg.com/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
  <style>
    /* NProgress override */
    #nprogress .bar {
      background: #f97316;
      height: 3px;
    }

    #nprogress .peg {
      box-shadow: 0 0 10px #f97316, 0 0 5px #f97316;
    }

    /* DataTables overrides */
    .dataTables_wrapper .dataTables_filter input {
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 5px 12px;
      font-size: 0.8125rem;
      font-family: 'Inter', sans-serif;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
      border-color: #f97316;
      outline: none;
      box-shadow: 0 0 0 3px rgba(249, 115, 22, .12);
    }



    .dataTables_wrapper .dataTables_info {
      font-size: 0.8125rem;
      color: #64748b;
    }

    .dt-buttons .btn {
      border-radius: 8px !important;
      font-size: 0.8125rem !important;
      font-weight: 500 !important;
    }

    /* Toastr overrides — Modern tech / NOC alert panel */
    #toast-container {
      z-index: 20000 !important;
      pointer-events: none;
    }

    #toast-container > div,
    #toast-container > div * {
      pointer-events: auto;
    }

    .toast-top-right {
      top: 78px;
      right: 1rem;
    }

    #toast-container > div {
      width: min(360px, calc(100vw - 24px));
      border-radius: 14px;
      box-shadow: 0 14px 30px rgba(15, 23, 42, 0.14);
      font-family: 'Inter', sans-serif;
      font-size: .84rem;
      font-weight: 500;
      line-height: 1.35;
      padding: 12px 14px 12px 16px !important;
      opacity: 1 !important;
      border: 1px solid #e2e8f0;
      background-color: #ffffff !important;
      background: linear-gradient(180deg, #ffffff, #f8fafc) !important;
      color: #0f172a !important;
      display: block;
      position: relative;
      overflow: hidden;
    }

    #toast-container > div::before {
      content: '';
      position: absolute;
      inset: 0 auto 0 0;
      width: 3px;
      border-radius: 14px 0 0 14px;
      background: var(--toast-accent, #38bdf8);
      box-shadow: 0 0 12px var(--toast-accent, #38bdf8);
    }

    #toast-container > .toast {
      padding-left: 14px !important;
      background-image: none !important;
      opacity: 1 !important;
    }

    #toast-container .toast-title {
      font-size: .66rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .1em;
      margin: 0 1.4rem .18rem 0;
      display: block;
      color: #64748b !important;
    }

    #toast-container .toast-message {
      margin: 0;
      padding-right: 1.25rem;
      font-size: .84rem;
      font-weight: 600;
      line-height: 1.42;
      word-break: break-word;
      color: #0f172a !important;
    }

    #toast-container .toast-success {
      --toast-accent: #22c55e;
      border-color: #bbf7d0;
      background-color: #f0fdf4 !important;
      background: linear-gradient(180deg, #f7fff9, #f0fdf4) !important;
    }

    #toast-container .toast-error {
      --toast-accent: #f87171;
      border-color: #fecaca;
      background-color: #fef2f2 !important;
      background: linear-gradient(180deg, #fff8f8, #fef2f2) !important;
    }

    #toast-container .toast-warning {
      --toast-accent: #f59e0b;
      border-color: #fde68a;
      background-color: #fffbeb !important;
      background: linear-gradient(180deg, #fffdf5, #fffbeb) !important;
    }

    #toast-container .toast-info {
      --toast-accent: #38bdf8;
      border-color: #bfdbfe;
      background-color: #eff6ff !important;
      background: linear-gradient(180deg, #f8fbff, #eff6ff) !important;
    }

    #toast-container .toast-success,
    #toast-container .toast-error,
    #toast-container .toast-warning,
    #toast-container .toast-info {
      color: #0f172a !important;
    }

    #toast-container .toast-close-button {
      color: #64748b;
      opacity: 1;
      text-shadow: none;
      font-weight: 400;
      font-size: 1rem;
      top: 8px;
      right: 8px;
      width: 20px;
      height: 20px;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(148, 163, 184, 0.12);
    }
    #toast-container .toast-close-button:hover {
      background: rgba(148, 163, 184, 0.2);
      color: #0f172a;
    }
    
    /* Progress bar */
    #toast-container .toast-progress {
      background: linear-gradient(90deg, var(--toast-accent, #38bdf8), rgba(255,255,255,0.55));
      opacity: 1;
      bottom: 0;
      height: 3px;
    }

    #toast-container .toast:before {
      display: none !important;
    }

    [data-theme="dark"] #toast-container > div {
      border-color: rgba(71, 85, 105, 0.5);
      background-color: #111827 !important;
      background: linear-gradient(180deg, #0f172a, #111827) !important;
      color: #e2e8f0 !important;
      box-shadow: 0 18px 36px rgba(0, 0, 0, 0.35);
      opacity: 1 !important;
    }

    [data-theme="dark"] #toast-container .toast-title {
      color: rgba(226, 232, 240, 0.64) !important;
    }

    [data-theme="dark"] #toast-container .toast-message {
      color: #f8fafc !important;
    }

    [data-theme="dark"] #toast-container .toast-success {
      border-color: rgba(34, 197, 94, 0.25);
      background-color: #0d1f17 !important;
      background: linear-gradient(180deg, #0f172a, #0d1f17) !important;
    }

    [data-theme="dark"] #toast-container .toast-error {
      border-color: rgba(248, 113, 113, 0.25);
      background-color: #221315 !important;
      background: linear-gradient(180deg, #0f172a, #221315) !important;
    }

    [data-theme="dark"] #toast-container .toast-warning {
      border-color: rgba(245, 158, 11, 0.25);
      background-color: #231a0f !important;
      background: linear-gradient(180deg, #0f172a, #231a0f) !important;
    }

    [data-theme="dark"] #toast-container .toast-info {
      border-color: rgba(56, 189, 248, 0.25);
      background-color: #0f1b26 !important;
      background: linear-gradient(180deg, #0f172a, #0f1b26) !important;
    }

    [data-theme="dark"] #toast-container .toast-close-button {
      color: rgba(226, 232, 240, 0.72);
      background: rgba(255,255,255,0.06);
    }

    [data-theme="dark"] #toast-container .toast-close-button:hover {
      color: #fff;
      background: rgba(255,255,255,0.12);
    }

    @media (max-width: 576px) {
      .toast-top-right {
        top: 66px;
        right: .75rem;
        left: .75rem;
      }
      #toast-container > div {
        width: auto;
        padding: 11px 13px 11px 15px !important;
      }
    }

    /* DataTables full table overrides — prevent conflicts with our .table styles */
    .dataTables_wrapper {
      color: var(--txt);
    }

    .dataTables_wrapper table.dataTable {
      border-collapse: collapse !important;
    }

    .dataTables_wrapper table.dataTable thead th {
      border-bottom: 1px solid var(--border) !important;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      color: var(--txt-2);
      letter-spacing: 0.04em;
      padding: 0.75rem 1rem;
      background: var(--surface);
      white-space: nowrap;
    }

    .dataTables_wrapper table.dataTable thead th.sorting::before,
    .dataTables_wrapper table.dataTable thead th.sorting::after,
    .dataTables_wrapper table.dataTable thead th.sorting_asc::before,
    .dataTables_wrapper table.dataTable thead th.sorting_asc::after,
    .dataTables_wrapper table.dataTable thead th.sorting_desc::before,
    .dataTables_wrapper table.dataTable thead th.sorting_desc::after {
      font-size: 0.6rem;
      opacity: 0.5;
    }

    .dataTables_wrapper table.dataTable tbody td {
      padding: 0.75rem 1rem;
      border-bottom: 1px solid var(--border);
      font-size: 0.875rem;
      vertical-align: middle;
      color: var(--txt);
    }

    .dataTables_wrapper table.dataTable tbody tr:hover td {
      background-color: var(--hover-bg) !important;
      color: var(--txt) !important;
    }

    .dataTables_wrapper table.dataTable tbody tr:last-child td {
      border-bottom: none;
    }



    /* Flatpickr input override */
    .flatpickr-input {
      font-family: 'Inter', sans-serif;
    }

    /* SweetAlert2 font override */
    .swal2-popup {
      font-family: 'Inter', sans-serif !important;
      border-radius: 16px !important;
    }

    .swal2-title {
      font-size: 1.125rem !important;
      font-weight: 600 !important;
    }

    .swal2-content,
    .swal2-html-container {
      font-size: 0.875rem !important;
      color: var(--txt-2) !important;
    }

    /* ── Badge styles consolidated below in BADGES section ── */

    /* ── Phase D: Card Variants ── */
    .card-stat {
      border-left: 3px solid var(--blue);
    }

    .card-info {
      border-left: 3px solid var(--cyan, #06b6d4);
    }

    .card-danger {
      border-left: 3px solid var(--red, #ef4444);
    }

    .card-warning {
      border-left: 3px solid var(--orange, #f59e0b);
    }

    .card-success {
      border-left: 3px solid var(--green, #10b981);
    }

    /* ── Phase D: Breadcrumb Polish ── */
    .breadcrumb {
      font-size: 0.8125rem;
      background: none;
      padding: 0;
    }

    .breadcrumb-item a {
      color: var(--blue);
      text-decoration: none;
      transition: opacity .15s;
    }

    .breadcrumb-item a:hover {
      opacity: .7;
    }

    .breadcrumb-item.active {
      color: var(--txt-3, #94a3b8);
    }

    .breadcrumb-item+.breadcrumb-item::before {
      content: '›';
      color: var(--txt-3, #94a3b8);
    }

    /* ── Phase D: Modal Animations ── */
    .modal .modal-content {
      border: none;
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, .12);
    }

    .modal .modal-header {
      border-bottom: 1px solid var(--border, #f0eff5);
      padding: 1rem 1.5rem;
    }

    .modal .modal-title {
      font-weight: 700;
      font-size: 1rem;
    }

    .modal .modal-footer {
      border-top: 1px solid var(--border, #f0eff5);
      padding: 0.75rem 1.5rem;
    }

    .modal.fade .modal-dialog {
      transform: scale(0.95) translateY(-10px);
      transition: transform .2s ease;
    }

    .modal.show .modal-dialog {
      transform: scale(1) translateY(0);
    }

    .swal2-popup.swal2-toast.nk-toast-popup {
      padding: 0 !important;
      background: #fff !important;
    }

    .swal2-popup.swal2-toast.nk-toast-popup .swal2-html-container {
      margin: 0 !important;
      padding: 0 !important;
      font-size: inherit !important;
      line-height: inherit !important;
      color: inherit !important;
    }

    .nk-toast-shell {
      padding: 12px 14px 12px 16px;
      text-align: left;
    }

    .nk-toast-title {
      font-size: .68rem;
      font-weight: 700;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: #64748b;
      margin-bottom: .25rem;
    }

    .nk-toast-message {
      font-size: .95rem;
      line-height: 1.38;
      font-weight: 600;
      color: #0f172a;
    }

    [data-theme="dark"] .swal2-popup.swal2-toast.nk-toast-popup {
      background: #0f172a !important;
      border-color: rgba(71, 85, 105, .5) !important;
      color: #f8fafc !important;
    }

    [data-theme="dark"] .nk-toast-title {
      color: rgba(226, 232, 240, .66);
    }

    [data-theme="dark"] .nk-toast-message {
      color: #f8fafc;
    }

    /* ── Phase D: Nav-tabs Theme ── */
    .nav-tabs {
      border-bottom: 2px solid var(--border, #f0eff5);
    }

    .nav-tabs .nav-link {
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--txt-2, #64748b);
      border: none;
      padding: 0.625rem 1rem;
      border-bottom: 2px solid transparent;
      margin-bottom: -2px;
      transition: color .15s, border-color .15s;
    }

    .nav-tabs .nav-link:hover {
      color: var(--blue);
      border-bottom-color: var(--blue-md, #dbeafe);
    }

    .nav-tabs .nav-link.active {
      color: var(--blue);
      border-bottom-color: var(--blue);
      background: transparent;
    }

    .tab-content>.tab-pane {
      padding-top: 1rem;
    }

    /* ── Phase D: FullCalendar Theme ── */
    .fc {
      font-family: 'Inter', sans-serif;
    }

    .fc .fc-toolbar-title {
      font-size: 1.125rem;
      font-weight: 700;
      color: var(--txt, #1e293b);
    }

    .fc .fc-button {
      font-size: 0.8125rem;
      font-weight: 500;
      border-radius: 6px;
      padding: 4px 12px;
    }

    .fc .fc-button-primary {
      background: var(--blue);
      border-color: var(--blue);
    }

    .fc .fc-button-primary:hover {
      background: var(--blue-dk, #1d4ed8);
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active {
      background: var(--blue-dk, #1d4ed8);
      border-color: var(--blue-dk, #1d4ed8);
    }

    .fc .fc-daygrid-day-number {
      font-size: 0.8125rem;
      color: var(--txt-2, #64748b);
    }

    .fc .fc-event {
      border-radius: 4px;
      font-size: 0.75rem;
      padding: 1px 4px;
      border: none;
    }

    .fc .fc-col-header-cell-cushion {
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    /* ApexCharts tooltip z-index */
    .apexcharts-tooltip {
      z-index: 9999 !important;
    }

    /* ── Text Overflow Prevention ── */
    .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }

    .table {
      table-layout: fixed;
      width: 100%;
    }

    .table th,
    .table td {
      overflow: hidden;
      text-overflow: ellipsis;
      word-break: break-word;
      vertical-align: middle;
      font-size: .8125rem;
      padding: .5rem .625rem;
    }

    

    .table td:last-child,
    .table th:last-child {
      overflow: visible;
      white-space: nowrap;
      width: auto;
    }

    .table .cell-nowrap {
      overflow: visible;
      text-overflow: clip;
      white-space: nowrap;
      word-break: normal;
    }

    .table .cell-index {
      width: 120px;
      min-width: 120px;
      max-width: 120px;
      text-align: center;
      font-variant-numeric: tabular-nums;
    }

    .table .cell-serial {
      min-width: 200px;
    }

    .card-body {
      overflow: hidden;
    }

    .card-title {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    /* ── DataTable Controls Polish ── */
    .dataTables_wrapper .dataTables_filter input {
      border: 1px solid var(--border, #dde1e7);
      border-radius: 6px;
      padding: 5px 12px;
      font-size: .8125rem;
      outline: none;
      min-width: 200px;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
      border-color: var(--blue);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
    }

    .dataTables_wrapper .dataTables_length select {
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 4px 8px;
      font-size: .8125rem;
      background-color: var(--surface) !important;
      color: var(--txt) !important;
    }

    .dataTables_wrapper .dataTables_info {
      font-size: .8125rem;
      color: var(--txt-3, #94a3b8);
    }

    /* Fix Pagination alignment */
    .dataTables_wrapper .d-flex.justify-content-between.align-items-center.mt-3 {
      gap: 1rem;
      padding: 1rem 1.5rem;
      margin-top: 0 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button,
    .pagination .page-link {
      border-radius: 8px !important;
      border: 1px solid var(--border);
      color: var(--txt-2);
      font-size: .875rem;
      font-weight: 600;
      padding: .45rem .7rem;
      min-width: 38px;
      min-height: 38px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #fff;
      transition: all .12s;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.active,
    .pagination .page-item.active .page-link {
      background-color: #2563eb !important;
      color: #fff !important;
      border-color: #2563eb !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current),
    .pagination .page-link:hover:not(.active) {
      background-color: var(--hover-bg) !important;
      color: var(--txt) !important;
    }
    
    .pagination .page-item.disabled .page-link {
      opacity: 0.5;
      background-color: transparent !important;
    }


    /* =====================================================
       NETKING — DESKA THEME (Orange / shadcn aesthetic)
       ===================================================== */
    :root {
      --blue: #1e293b;
      --blue-dk: #0f172a;
      --blue-lt: rgba(30, 41, 59, 0.1);
      --blue-md: rgba(30, 41, 59, 0.16);
      --blue-glow: rgba(30, 41, 59, 0.24);

      --green: #22c55e;
      --red: #ef4444;
      --orange: #f97316;
      --cyan: #06b6d4;

      --bg: #f7f7f8;
      --surface: #ffffff;
      --surface-2: #fcfcfd;
      --border: #e7e7ec;
      --bd-dk: #d4d4dc;

      --txt: #18181b;
      --txt-2: #52525b;
      --txt-3: #71717a;

      --sb-w: 260px;
      --r: 8px;
      --r-sm: 6px;

      --shadow-xs: none;
      --shadow-sm: 0 1px 2px rgba(16, 24, 40, 0.04);
      --shadow-md: 0 8px 24px rgba(16, 24, 40, 0.06);
      --hover-bg: #f3f4f6;
      --shadow-lg: 0 14px 36px rgba(16, 24, 40, 0.08);
    }

    /* ======= DARK MODE — Complete ======= */
    [data-theme="dark"] {
      --blue: #334155;
      --blue-dk: #1e293b;
      --blue-lt: rgba(51, 65, 85, 0.16);
      --blue-md: rgba(51, 65, 85, 0.22);
      --blue-glow: rgba(51, 65, 85, 0.32);
      --hover-bg: rgba(255, 255, 255, 0.05);
      --bg: #09090b;
      --surface: #09090b;
      --surface-2: #111113;
      --border: rgba(255, 255, 255, .08);
      --bd-dk: rgba(255, 255, 255, .1);
      --txt: #f4f4f5;
      --txt-2: #a1a1aa;
      --txt-3: #71717a;
      --shadow-xs: none;
      --shadow-sm: 0 0 0 1px rgba(255,255,255,0.03);
      --shadow-md: 0 0 0 1px rgba(255,255,255,0.04), 0 18px 40px rgba(0,0,0,0.32);
      --shadow-lg: 0 0 0 1px rgba(255,255,255,0.05), 0 24px 60px rgba(0,0,0,0.4);
      color-scheme: dark;

      /* Force Bootstrap 5 CSS variables to dark */
      --bs-body-bg: #0a0a0a;
      --bs-body-color: #fafafa;
      --bs-table-bg: transparent;
      --bs-table-color: #fafafa;
      --bs-table-striped-bg: rgba(255,255,255,.02);
      --bs-table-striped-color: #fafafa;
      --bs-table-hover-bg: rgba(255,255,255,.05);
      --bs-table-hover-color: #fafafa;
      --bs-table-active-bg: rgba(249,115,22,.1);
      --bs-table-border-color: rgba(255,255,255,.08);
      --bs-border-color: rgba(255,255,255,.08);
      --bs-tertiary-bg: #1a1a1a;
      --bs-secondary-bg: #1a1a1a;
    }

    /* -- Body & Global -- */
    [data-theme="dark"] body { background: var(--bg); color: var(--txt); }

    /* -- Sidebar -- */
    [data-theme="dark"] .sidebar { background: var(--bg); border-color: var(--border); }

    /* -- Topbar -- */
    [data-theme="dark"] .topbar { background: var(--bg); border-color: var(--border); box-shadow: none; }
    [data-theme="dark"] .notif-dot { border-color: var(--bg); }

    /* -- Cards & Stat Cards -- */
    [data-theme="dark"] .card { background: var(--surface); border-color: var(--border); }
    [data-theme="dark"] .stat-card { background: var(--surface); border-color: var(--border); }

    /* -- Panels: same bg as page shell in dark mode (no card elevation) -- */
    [data-theme="dark"] .ms-panel { background: var(--bg) !important; }
    [data-theme="dark"] .ms-table-shell { background: var(--bg) !important; }
    [data-theme="dark"] .workspace-shell .main .ms-table-shell { background: var(--bg) !important; }

    /* -- Stat Icons (dark backgrounds) -- */
    [data-theme="dark"] .si-blue  { background: rgba(249,115,22,.12); }
    [data-theme="dark"] .si-green { background: rgba(34,197,94,.12); }
    [data-theme="dark"] .si-orange { background: rgba(245,158,11,.12); }
    [data-theme="dark"] .si-cyan  { background: rgba(6,182,212,.12); }
    [data-theme="dark"] .si-red   { background: rgba(239,68,68,.12); }

    /* -- Tables -- */
    [data-theme="dark"] .table { color: var(--txt); }
    [data-theme="dark"] .table thead th { background: var(--surface); color: #e5e5e5 !important; border-color: var(--border) !important; font-weight: 600; }
    [data-theme="dark"] .table tbody td { border-color: var(--border) !important; color: var(--txt) !important; }

    /* NUCLEAR: Override ALL hardcoded inline colors inside dark mode tables, cards, forms */
    /* EXCLUDES: badge-status, badge, btn elements */
    [data-theme="dark"] .table tbody td div:not(.badge):not(.badge-status):not([class*="badge-"]),
    [data-theme="dark"] .table tbody td span:not(.badge):not(.badge-status):not([class*="badge-"]),
    [data-theme="dark"] .table tbody td p,
    [data-theme="dark"] .table tbody td small,
    [data-theme="dark"] .table tbody td label,
    [data-theme="dark"] .table tbody td a:not(.btn),
    [data-theme="dark"] .table tbody td code,
    [data-theme="dark"] .card-body div:not(.badge):not(.badge-status):not([class*="badge-"]),
    [data-theme="dark"] .card-body span:not(.badge):not(.badge-status):not([class*="badge-"]),
    [data-theme="dark"] .card-body p,
    [data-theme="dark"] .card-body label,
    [data-theme="dark"] .card-body small {
        color: var(--txt) !important;
    }
    [data-theme="dark"] .table tbody td [style*="color:#64748b"],
    [data-theme="dark"] .table tbody td [style*="color: #64748b"],
    [data-theme="dark"] .table tbody td [style*="color:#94a3b8"],
    [data-theme="dark"] .table tbody td [style*="color: #94a3b8"],
    [data-theme="dark"] .card-body [style*="color:#64748b"],
    [data-theme="dark"] .card-body [style*="color: #64748b"],
    [data-theme="dark"] .card-body [style*="color:#94a3b8"],
    [data-theme="dark"] .card-body [style*="color: #94a3b8"] {
        color: var(--txt-2) !important;
    }
    [data-theme="dark"] .table tbody td code,
    [data-theme="dark"] code {
        background: rgba(255,255,255,0.08) !important;
        color: var(--blue) !important;
    }
    /* Dark mode badge styles — explicit colors that won't be overridden */
    [data-theme="dark"] .badge-active {
        background: rgba(16,185,129,.15) !important;
        color: #34d399 !important;
        border-color: rgba(16,185,129,.3) !important;
    }
    [data-theme="dark"] .badge-inactive {
        background: rgba(148,163,184,.1) !important;
        color: #94a3b8 !important;
        border-color: rgba(148,163,184,.2) !important;
    }
    [data-theme="dark"] .badge-pending {
        background: rgba(249,115,22,.15) !important;
        color: #fb923c !important;
        border-color: rgba(249,115,22,.3) !important;
    }
    [data-theme="dark"] .badge-suspended {
        background: rgba(249,115,22,.15) !important;
        color: #fb923c !important;
        border-color: rgba(249,115,22,.3) !important;
    }
    [data-theme="dark"] .badge-failed {
        background: rgba(239,68,68,.15) !important;
        color: #f87171 !important;
        border-color: rgba(239,68,68,.3) !important;
    }
    [data-theme="dark"] .badge-paid {
        background: rgba(16,185,129,.15) !important;
        color: #34d399 !important;
        border-color: rgba(16,185,129,.3) !important;
    }
    [data-theme="dark"] .badge-unpaid,
    [data-theme="dark"] .badge-overdue {
        background: rgba(239,68,68,.15) !important;
        color: #f87171 !important;
        border-color: rgba(239,68,68,.3) !important;
    }
    [data-theme="dark"] .badge-provisioning {
        background: rgba(6,182,212,.15) !important;
        color: #22d3ee !important;
        border-color: rgba(6,182,212,.3) !important;
    }
    [data-theme="dark"] .badge {
        color: inherit;
    }
    [data-theme="dark"] .table tbody tr { background: transparent; }
    [data-theme="dark"] .table,
    [data-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>*,
    [data-theme="dark"] .table.dataTable tbody tr.odd>* { 
        background-color: rgba(255,255,255,.015) !important; 
        color: var(--txt) !important; 
        box-shadow: inset 0 0 0 9999px transparent !important; 
    }
    
    [data-theme="dark"] .table-striped>tbody>tr:nth-of-type(even)>*,
    [data-theme="dark"] .table.dataTable tbody tr.even>* { 
        background-color: transparent !important; 
        color: var(--txt) !important; 
        box-shadow: inset 0 0 0 9999px transparent !important; 
    }

    [data-theme="dark"] .table tbody tr:hover td,
    [data-theme="dark"] .table-hover>tbody>tr:hover>*,
    [data-theme="dark"] table.dataTable.hover>tbody>tr:hover>*,
    [data-theme="dark"] table.dataTable.display>tbody>tr:hover>* { 
        background-color: rgba(255,255,255,0.05) !important; 
        color: var(--txt) !important; 
        box-shadow: inset 0 0 0 9999px rgba(255,255,255,0.05) !important; 
    }

    /* -- Forms -- */
    [data-theme="dark"] .form-control,
    [data-theme="dark"] .form-select,
    [data-theme="dark"] textarea.form-control { background: var(--bg); border-color: var(--border); color: var(--txt); }
    
    [data-theme="dark"] .form-control:disabled,
    [data-theme="dark"] .form-control[readonly] {
        background-color: rgba(255,255,255,0.03) !important;
        opacity: 0.7;
        color: var(--txt-2);
        cursor: not-allowed;
    }
    
    [data-theme="dark"] .form-control:focus,
    [data-theme="dark"] .form-select:focus { background: var(--bg); border-color: var(--blue); box-shadow: 0 0 0 3px rgba(249,115,22,.1); color: var(--txt); }
    [data-theme="dark"] .form-control::placeholder { color: var(--txt-3); }
    [data-theme="dark"] .form-label { color: var(--txt-2); }
    [data-theme="dark"] .input-group-text { background: var(--surface); border-color: var(--border); color: var(--txt-2); }

    /* -- Select2 -- */
    [data-theme="dark"] .select2-container--bootstrap-5 .select2-selection { background: var(--bg); border-color: var(--border); color: var(--txt); }
    [data-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered { color: var(--txt); }
    [data-theme="dark"] .select2-dropdown { background: var(--surface); border-color: var(--border); }
    [data-theme="dark"] .select2-search__field { background: var(--bg) !important; color: var(--txt) !important; border-color: var(--border) !important; }
    [data-theme="dark"] .select2-results__option { color: var(--txt); }
    [data-theme="dark"] .select2-results__option--highlighted { background: rgba(249,115,22,.15) !important; color: var(--blue) !important; }

    /* -- DataTables -- */
    [data-theme="dark"] .dataTables_wrapper .dataTables_filter input { background: var(--bg); color: var(--txt); border-color: var(--border); }
    [data-theme="dark"] .dataTables_wrapper .dataTables_info { color: var(--txt-3); }

    /* -- Dropdowns -- */
    [data-theme="dark"] .dropdown-menu { background: var(--surface); border-color: var(--border); color: var(--txt); }
    [data-theme="dark"] .dropdown-item { color: var(--txt-2); }
    [data-theme="dark"] .dropdown-item:hover { background: rgba(249,115,22,.1); color: var(--blue); }
    [data-theme="dark"] .dropdown-divider { border-color: var(--border); }

    /* -- Modals -- */
    [data-theme="dark"] .modal-content { background: var(--surface); color: var(--txt); border-color: var(--border); }
    [data-theme="dark"] .modal-header { border-color: var(--border); }
    [data-theme="dark"] .modal-footer { border-color: var(--border); }
    [data-theme="dark"] .btn-close { filter: invert(1); }

    /* -- Buttons -- */
    [data-theme="dark"] .btn-outline-primary { color: var(--blue); border-color: var(--blue); }
    [data-theme="dark"] .btn-outline-secondary { color: var(--txt-2); border-color: var(--border); }
    [data-theme="dark"] .btn-secondary { background: #262626; border-color: var(--border); color: var(--txt); }

    /* -- Badges (keep colors but adapt backgrounds) -- */
    [data-theme="dark"] .badge-status { border: none; }

    /* -- Bootstrap utilities -- */
    [data-theme="dark"] .bg-white { background: var(--surface) !important; }
    [data-theme="dark"] .bg-light { background: var(--bg) !important; }
    [data-theme="dark"] .text-dark { color: var(--txt) !important; }
    [data-theme="dark"] .text-muted { color: var(--txt-3) !important; }
    [data-theme="dark"] .border { border-color: var(--border) !important; }
    [data-theme="dark"] .border-bottom { border-color: var(--border) !important; }
    [data-theme="dark"] .list-group-item { background: var(--surface); border-color: var(--border); color: var(--txt); }

    /* -- SweetAlert2 -- */
    [data-theme="dark"] .swal2-popup { background: var(--surface) !important; color: var(--txt) !important; }
    [data-theme="dark"] .swal2-title { color: var(--txt) !important; }
    [data-theme="dark"] .swal2-html-container { color: var(--txt-2) !important; }
    [data-theme="dark"] .swal2-input { background: var(--bg) !important; color: var(--txt) !important; border-color: var(--border) !important; }

    /* -- Skeleton/Empty State -- */
    [data-theme="dark"] .empty-state-icon { background: #262626; }
    [data-theme="dark"] .skeleton,
    [data-theme="dark"] .skeleton-cell,
    [data-theme="dark"] .skeleton-circle { background: linear-gradient(90deg, #262626 25%, #404040 37%, #262626 63%); background-size: 800px 100%; }

    /* -- FullCalendar -- */
    [data-theme="dark"] .fc { color: var(--txt); }
    [data-theme="dark"] .fc .fc-daygrid-day { background: var(--surface); }
    [data-theme="dark"] .fc td, [data-theme="dark"] .fc th { border-color: var(--border); }

    /* -- Toastr -- */
    [data-theme="dark"] #toast-container>div { box-shadow: var(--shadow-md); }

    /* -- Scrollbar -- */
    [data-theme="dark"] ::-webkit-scrollbar { width: 6px; height: 6px; }
    [data-theme="dark"] ::-webkit-scrollbar-track { background: var(--bg); }
    [data-theme="dark"] ::-webkit-scrollbar-thumb { background: #333; border-radius: 3px; }

    /* ======= SIDEBAR COLLAPSE (shadcn/Deska style) ======= */
    .sidebar { transition: width .2s ease; }
    .topbar { transition: margin-left .2s ease; }
    .main { transition: margin-left .2s ease; }

    .sb-collapse-btn {
      position: absolute;
      top: 18px;
      right: -14px;
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: var(--surface);
      border: 1px solid var(--border);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--txt-2);
      font-size: .875rem;
      z-index: 101;
      transition: all .2s;
      box-shadow: var(--shadow-sm); /* lighter shadow */
    }
    .sb-collapse-btn:hover { background: var(--bg); color: var(--txt); }

    /* Collapsed state */
    [data-sidebar="collapsed"] .sidebar { width: 60px; }
    [data-sidebar="collapsed"] .topbar { margin-left: 60px; }
    [data-sidebar="collapsed"] .main { margin-left: 60px; }
    [data-sidebar="collapsed"] .sb-head { justify-content: center; padding: 1.25rem .5rem 1rem; }
    [data-sidebar="collapsed"] .sb-logo-name,
    [data-sidebar="collapsed"] .sb-logo-sub { display: none; }
    [data-sidebar="collapsed"] .sb-body { padding: .5rem .375rem; }
    [data-sidebar="collapsed"] .sb-label { font-size: 0; padding: .25rem; text-align: center; }
    [data-sidebar="collapsed"] .sb-label::after { content: '—'; font-size: .5rem; color: var(--txt-3); }
    [data-sidebar="collapsed"] .sb-link { justify-content: center; padding: .5rem; }
    [data-sidebar="collapsed"] .sb-link span { display: none; }
    [data-sidebar="collapsed"] .sb-icon { font-size: 1.125rem; width: auto; }
    [data-sidebar="collapsed"] .sb-foot { justify-content: center; padding: .75rem .5rem; }
    [data-sidebar="collapsed"] .sb-user-name,
    [data-sidebar="collapsed"] .sb-user-role { display: none; }
    [data-sidebar="collapsed"] .sb-collapse-btn i { transform: rotate(180deg); }
    [data-sidebar="collapsed"] .sb-collapse-btn i { transform: rotate(180deg); }

    /* Layout footer collapsed */
    .layout-footer { transition: margin-left .2s ease; }
    [data-sidebar="collapsed"] .layout-footer { margin-left: 60px; }

    /* Tooltip for collapsed items */
    [data-sidebar="collapsed"] .sb-link { position: relative; }
    [data-sidebar="collapsed"] .sb-link:hover::after {
      content: attr(data-title);
      position: absolute;
      left: calc(100% + 8px);
      top: 50%;
      transform: translateY(-50%);
      background: var(--surface);
      border: 1px solid var(--border);
      padding: .25rem .5rem;
      border-radius: 6px;
      font-size: .75rem;
      color: var(--txt);
      white-space: nowrap;
      z-index: 200;
      box-shadow: var(--shadow-md);
      pointer-events: none;
    }

    .dark-toggle {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--surface);
      border: 1px solid var(--border);
      cursor: pointer;
      transition: all .2s;
      color: var(--txt-2);
    }
    .dark-toggle:hover {
      background: var(--bg);
      color: var(--blue);
    }

    *,
    *::before,
    *::after {
      box-sizing: border-box;
    }

    html,
    body {
      height: 100%;
      margin: 0;
    }

    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
      background: var(--bg);
      color: var(--txt);
      font-size: .875rem;
      -webkit-font-smoothing: antialiased;
    }
    
    *, input, select, textarea, button, .table, .fc, .swal2-popup {
      font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
    }

    /* ======= SIDEBAR (Light) ======= */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      width: var(--sb-w);
      background: var(--surface);
      border-right: none;
      display: flex;
      flex-direction: column;
      padding: 0;
      z-index: 100;
    }


    .sb-logo-icon {
      width: 32px;
      height: 32px;
      background: var(--blue);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 14px;
      flex-shrink: 0;
    }

    .sb-logo-name {
      font-size: .9375rem;
      font-weight: 700;
      color: var(--txt);
      letter-spacing: -.3px;
      line-height: 1.1;
    }

    .sb-logo-sub {
      font-size: .65rem;
      color: var(--txt-3);
    }

    .sb-body {
      flex: 1;
      padding: 1rem .875rem;
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: #e2e8f0 transparent;
    }

    .sb-section {
      margin-bottom: .5rem;
    }

    .sb-label {
      font-size: .6rem;
      font-weight: 600;
      color: var(--txt-3);
      text-transform: uppercase;
      letter-spacing: .8px;
      padding: .625rem .375rem .25rem;
      display: block;
    }

    .sb-link {
      display: flex;
      align-items: center;
      gap: .5rem;
      padding: .4375rem .5625rem;
      border-radius: var(--r-sm);
      color: var(--txt-2);
      text-decoration: none;
      font-size: .8375rem;
      font-weight: 400;
      transition: all .12s;
      margin-bottom: 1px;
      white-space: nowrap;
    }

    .sb-link:hover {
      background: var(--blue-lt);
      color: var(--blue);
    }

    .sb-link.active {
      background: var(--blue-lt);
      color: var(--blue);
      font-weight: 500;
    }

    .sb-icon {
      font-size: 1.05rem;
      width: 1.25rem;
      flex-shrink: 0;
      text-align: center;
    }

    .sb-foot {
      padding: 1rem 1.25rem;
      border-top: none;
      display: flex;
      align-items: center;
      gap: .5rem;
    }

    .sb-user-avatar {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: var(--blue);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: .7rem;
      flex-shrink: 0;
    }

    .sb-user-name {
      font-size: .8125rem;
      font-weight: 600;
      color: var(--txt);
      line-height: 1.2;
    }

    .sb-user-role {
      font-size: .65rem;
      color: var(--txt-3);
    }

    /* ======= TOPBAR (Light) ======= */
    .topbar {
      margin-left: var(--sb-w);
      background: var(--surface);
      border-bottom: 1px solid var(--border);
      height: 56px;
      display: flex;
      align-items: center;
      padding: 0 2.5rem;
      gap: 1rem;
      position: sticky;
      top: 0;
      z-index: 50;
      box-shadow: var(--shadow-xs);
    }

    .tb-title {
      font-size: 1rem;
      font-weight: 700;
      color: var(--txt);
    }

    .tb-spacer {
      flex: 1;
    }

    .tb-search {
      position: relative;
      display: flex;
      align-items: center;
    }

    .tb-search input {
      background: var(--bg);
      border: 1px solid var(--border);
      border-radius: var(--r-sm);
      padding: .375rem .75rem .375rem 2rem;
      font-size: .8125rem;
      color: var(--txt);
      outline: none;
      font-family: inherit;
      width: 220px;
      transition: all .15s;
    }

    .tb-search input:focus {
      border-color: var(--blue);
      background: var(--surface);
    }

    .tb-search input::placeholder {
      color: var(--txt-3);
    }

    .tb-search i {
      position: absolute;
      left: .5rem;
      color: var(--txt-3);
      font-size: .9rem;
      pointer-events: none;
    }

    .tb-btn {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: var(--bg);
      border: 1px solid var(--border);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--txt-2);
      font-size: 1rem;
      position: relative;
      transition: all .15s;
    }

    .tb-btn:hover {
      background: var(--blue-lt);
      border-color: var(--blue-md);
      color: var(--blue);
    }

    .notif-dot {
      position: absolute;
      top: 7px;
      right: 7px;
      width: 7px;
      height: 7px;
      background: #f43f5e;
      border-radius: 50%;
      border: 1.5px solid #fff;
    }

    .tb-btn.has-unread {
      background: rgba(249, 115, 22, 0.08);
      border-color: #fdba74;
      color: var(--orange);
    }

    .notif-badge {
      position: absolute;
      top: -4px;
      right: -4px;
      min-width: 18px;
      height: 18px;
      padding: 0 5px;
      border-radius: 999px;
      background: linear-gradient(135deg, #f97316, #ef4444);
      color: #fff;
      border: 2px solid #fff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: .625rem;
      font-weight: 700;
      line-height: 1;
      box-shadow: 0 6px 14px rgba(239, 68, 68, .28);
    }

    .notif-menu {
      width: min(380px, calc(100vw - 24px));
      max-height: min(520px, calc(100vh - 110px));
      overflow: hidden;
      padding: 0;
    }

    .notif-panel-header,
    .notif-panel-footer {
      padding: .875rem 1rem;
      background: var(--surface);
    }

    .notif-panel-header {
      border-bottom: 1px solid var(--border);
    }

    .notif-panel-footer {
      border-top: 1px solid var(--border);
    }

    .notif-list {
      max-height: min(380px, calc(100vh - 240px));
      overflow-y: auto;
    }

    .notif-item {
      display: flex;
      align-items: flex-start;
      gap: .75rem;
      padding: .875rem 1rem;
      text-decoration: none;
      color: var(--txt);
      transition: background .12s ease, transform .12s ease;
      border-bottom: 1px solid var(--border);
      position: relative;
    }

    .notif-item:last-child {
      border-bottom: none;
    }

    .notif-item:hover {
      background: var(--hover-bg);
      color: var(--txt);
    }

    .notif-item.unread {
      background: rgba(37, 99, 235, .05);
    }

    .notif-item.unread::before {
      content: '';
      position: absolute;
      left: 10px;
      top: 50%;
      transform: translateY(-50%);
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: var(--blue);
    }

    .notif-item-icon {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      font-size: 1rem;
      margin-left: .5rem;
    }

    .notif-item-body {
      flex: 1;
      min-width: 0;
    }

    .notif-item-title {
      font-size: .8125rem;
      font-weight: 700;
      color: var(--txt);
      line-height: 1.25;
    }

    .notif-item-message {
      margin-top: .125rem;
      font-size: .75rem;
      color: var(--txt-2);
      line-height: 1.35;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .notif-item-meta {
      margin-top: .35rem;
      font-size: .6875rem;
      color: var(--txt-3);
      display: flex;
      align-items: center;
      gap: .4rem;
    }

    .notif-empty {
      padding: 1.5rem 1rem;
      text-align: center;
      color: var(--txt-3);
    }

    .notif-empty i {
      font-size: 1.5rem;
      display: block;
      margin-bottom: .35rem;
    }

    /* ======= GLOBAL SEARCH RESULTS ======= */
    .search-results {
      position: absolute;
      top: calc(100% + 6px);
      left: 0;
      right: 0;
      min-width: 320px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 10px;
      box-shadow: var(--shadow-lg);
      z-index: 1000;
      max-height: 400px;
      overflow-y: auto;
    }

    .search-result-item {
      display: flex;
      align-items: center;
      gap: .75rem;
      padding: .625rem 1rem;
      text-decoration: none;
      color: var(--txt);
      transition: background .1s;
      border-bottom: 1px solid var(--border);
    }

    .search-result-item:last-child {
      border-bottom: none;
    }

    .search-result-item:hover {
      background: var(--blue-lt);
      color: var(--blue);
    }

    .search-result-icon {
      width: 34px;
      height: 34px;
      border-radius: 8px;
      background: var(--blue-lt);
      color: var(--blue);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      flex-shrink: 0;
    }

    .search-result-title {
      font-size: .8125rem;
      font-weight: 500;
      color: var(--txt);
    }

    .search-result-sub {
      font-size: .6875rem;
      color: var(--txt-3);
    }

    .search-empty {
      text-align: center;
      padding: 1.5rem;
      color: var(--txt-3);
      font-size: .8125rem;
    }

    .search-type-label {
      padding: .375rem 1rem;
      font-size: .625rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: var(--txt-3);
      background: var(--bg);
    }

    .tb-avatar {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: var(--blue);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: .75rem;
      cursor: pointer;
      flex-shrink: 0;
      border: 2px solid var(--blue-md);
    }

    /* ======= MAIN CONTENT ======= */
    .main {
      margin-left: var(--sb-w);
      padding: 1.5rem 2.5rem;
      min-height: calc(100vh - 56px);
    }

    .page-header,
    .page-title-box {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .page-header > div:last-child,
    .page-title-box > div:last-child {
      display: flex;
      align-items: center;
      gap: .5rem;
      flex-wrap: wrap;
      justify-content: flex-end;
    }

    /* ======= PAGE HEADER ======= */
    .page-title-box {
      margin-bottom: 1.25rem;
    }

    .page-title-box h4 {
      font-size: 1.0625rem;
      font-weight: 700;
      color: var(--txt);
      margin: 0;
    }

    .breadcrumb {
      margin: 0;
      font-size: .8125rem;
    }

    .breadcrumb-item a {
      color: var(--blue);
      text-decoration: none;
    }

    .breadcrumb-item.active {
      color: var(--txt-3);
    }

    /* ======= STAT CARDS ======= */
    .stat-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1.25rem;
      margin-bottom: 1.5rem;
    }

    .stat-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r);
      padding: 1.25rem 1.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: var(--shadow-sm);
      transition: box-shadow .2s, transform .2s;
    }

    .stat-card:hover {
      box-shadow: var(--shadow-md);
      transform: translateY(-1px);
    }

    .stat-label {
      font-size: .6875rem;
      font-weight: 700;
      color: var(--txt-3);
      text-transform: uppercase;
      letter-spacing: .5px;
    }

    .stat-value {
      font-size: 1.75rem;
      font-weight: 800;
      color: var(--txt);
      margin: .2rem 0;
      letter-spacing: -.5px;
      line-height: 1;
      font-feature-settings: "tnum";
    }

    .stat-change {
      font-size: .75rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: .1rem;
    }

    .stat-change.up {
      color: var(--green);
    }

    .stat-change.down {
      color: var(--red);
    }

    .stat-change.neutral {
      color: var(--txt-3);
    }

    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: var(--r-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.375rem;
      flex-shrink: 0;
    }

    .si-blue {
      background: var(--blue-lt);
      color: var(--blue);
    }

    .si-green {
      background: #ecfdf5;
      color: var(--green);
    }

    .si-orange {
      background: #fffbeb;
      color: var(--orange);
    }

    .si-cyan {
      background: #ecfeff;
      color: var(--cyan);
    }

    /* ======= CARDS ======= */
    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r);
      box-shadow: var(--shadow-sm);
      margin-bottom: 1.25rem;
      overflow: hidden;
    }

    .card-header {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .75rem;
      background: transparent;
    }

    .card-title {
      font-size: .9375rem;
      font-weight: 700;
      color: var(--txt);
      margin: 0;
    }

    .card-body {
      padding: 1.25rem;
    }

    .card-footer {
      background: transparent;
      border-top: 1px solid var(--border);
      padding: .875rem 1.25rem;
    }

    /* ======= TABLE ======= */
    .table {
      margin: 0;
    }

    .table thead th {
      font-size: .6875rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .4px;
      color: var(--txt-3);
      border-bottom: 1px solid var(--border);
      padding: 1rem 1.25rem;
      background: #f8fafc;
      white-space: nowrap;
    }

    .table tbody td {
      padding: 1rem 1.25rem;
      vertical-align: middle;
      border-color: var(--border);
      font-size: .875rem;
      color: var(--txt-2);
    }

    .table tbody tr {
      transition: all .15s ease;
      border-left: 3px solid transparent;
    }

    .table tbody tr:hover {
      background: var(--hover-bg, rgba(249,115,22,0.04));
      border-left-color: var(--blue);
    }

    .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }

    /* ======= DATATABLES WRAPPER PADDING ======= */
    .dataTables_wrapper {
      padding: 0 1.5rem 1rem;
    }

    .dataTables_wrapper .d-flex {
      padding: 0.75rem 0;
    }

    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
      margin: 0;
    }

    .dataTables_wrapper .dataTables_paginate {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-end;
      gap: .35rem;
    }

    .dataTables_wrapper .dataTables_filter input {
      padding: 0.5rem 1rem;
      border: 1px solid var(--border);
      border-radius: var(--r-sm);
      font-size: 0.8125rem;
      min-width: 220px;
      outline: none;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
      border-color: var(--blue);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .dataTables_wrapper .dataTables_length select {
      padding: 0.35rem 0.75rem;
      border: 1px solid var(--border);
      border-radius: var(--r-sm);
      font-size: 0.8125rem;
    }

    .dataTables_wrapper .dataTables_info {
      font-size: 0.8125rem;
      color: var(--txt-3);
      padding: 0.5rem 0;
    }

    .dataTables_wrapper .dataTables_paginate {
      padding: 0.5rem 0;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: .45rem .7rem;
      margin: 0 3px;
      min-width: 38px;
      min-height: 38px;
      font-size: .875rem;
      font-weight: 600;
      line-height: 1.1;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #fff;
      color: var(--txt-2);
      cursor: pointer;
      transition: all .15s;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
      background: #f1f5f9;
      border-color: var(--blue);
      color: var(--blue);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
      background: #2563eb !important;
      border-color: #2563eb !important;
      color: #fff !important;
    }

    /* ======= BUTTONS ======= */
    .btn {
      font-weight: 500;
      font-size: .8125rem;
      border-radius: var(--r-sm);
      transition: all .15s;
    }
    .btn-primary {
      background: var(--blue);
      border: 1px solid #0f172a;
      border-bottom-width: 3px;
      border-radius: 8px;
      transition: all .1s ease;
      font-weight: 600;
      color: #fff;
    }
    .btn-primary:hover { background: var(--blue-dk); color: #fff; }
    .btn-primary:active { border-bottom-width: 1px; transform: translateY(2px); margin-bottom: 2px; }

    .btn-secondary {
      background: var(--surface);
      border: 1px solid var(--bd-dk);
      border-bottom-width: 3px;
      border-radius: 8px;
      color: var(--txt);
      font-weight: 600;
      transition: all .1s ease;
    }
    .btn-secondary:hover { background: var(--hover-bg); color: var(--txt); }
    .btn-secondary:active { border-bottom-width: 1px; transform: translateY(2px); margin-bottom: 2px; }

    .btn-outline-primary {
      border: 1px solid var(--blue);
      border-bottom-width: 3px;
      border-radius: 8px;
      color: var(--blue);
      font-weight: 600;
      transition: all .1s ease;
      background: transparent;
    }
    .btn-outline-primary:hover { background: var(--blue-lt); color: var(--blue); }
    .btn-outline-primary:active { border-bottom-width: 1px; transform: translateY(2px); margin-bottom: 2px; }

    .btn-danger {
      background: #ef4444;
      border: 1px solid #991b1b;
      border-bottom-width: 3px;
      border-radius: 8px;
      transition: all .1s ease;
      font-weight: 600;
      color: #fff;
    }
    .btn-danger:hover { background: #dc2626; color: #fff; }
    .btn-danger:active { border-bottom-width: 1px; transform: translateY(2px); margin-bottom: 2px; }

    .btn-success {
      background: #22c55e;
      border-radius: 10px;
      color: var(--txt-2);
      font-weight: 600;
      transition: all .1s ease;
      transform-origin: bottom;
    }
    .btn-outline-secondary:hover { background: var(--hover-bg); color: var(--txt); }
    .btn-outline-secondary:active { border-bottom-width: 2px; transform: scaleY(.97); }

    .btn-sm {
      font-size: .75rem;
      padding: .3rem .7rem;
    }

    .btn-icon {
      width: 30px;
      height: 30px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: .875rem;
    }

    /* ======= FORMS ======= */
    .form-control,
    .form-select {
      border-color: var(--border);
      border-radius: var(--r-sm);
      padding: .4375rem .75rem;
      color: var(--txt);
      font-size: .875rem;
      font-family: inherit;
      background: #fff;
      transition: all .15s;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--blue);
      box-shadow: 0 0 0 3px var(--blue-glow);
    }

    .form-control::placeholder {
      color: var(--txt-3);
    }

    .form-label {
      font-size: .8125rem;
      font-weight: 500;
      color: var(--txt-2);
      margin-bottom: .35rem;
    }

    .input-group-text {
      border-color: var(--border);
      background: #f8fafc;
      color: var(--txt-3);
    }

    /* ======= SELECT2 ======= */
    .select2-container--bootstrap-5 .select2-selection {
      border-color: var(--border);
      border-radius: var(--r-sm);
      min-height: 37px;
      font-size: .875rem;
      font-family: inherit;
    }

    .select2-container--bootstrap-5.select2-container--focus .select2-selection {
      border-color: var(--blue);
      box-shadow: 0 0 0 3px var(--blue-glow);
    }

    .select2-container--bootstrap-5 .select2-dropdown {
      border-color: var(--border);
      border-radius: var(--r-sm);
      box-shadow: var(--shadow-lg);
    }

    .select2-container--bootstrap-5 .select2-results__option--highlighted:not(.select2-results__option--selected) {
      background: var(--blue-lt) !important;
      color: var(--blue) !important;
    }

    .select2-container--bootstrap-5 .select2-results__option--selected {
      background: var(--blue) !important;
      color: #fff !important;
    }

    .select2-container {
      width: 100% !important;
    }

    /* ======= PAGINATION ======= */
    .pagination {
      gap: 6px;
      flex-wrap: wrap;
      margin: 0;
    }

    .pagination .page-link {
      border-radius: 8px !important;
      border: 1px solid var(--border);
      color: var(--txt-2);
      font-size: .875rem;
      font-weight: 600;
      padding: .45rem .7rem;
      min-width: 38px;
      min-height: 38px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #fff;
      transition: all .12s;
    }

    .pagination .page-link:hover {
      background: var(--hover-bg);
      color: var(--txt);
      border-color: var(--bd-dk);
    }

    .pagination .page-item.active .page-link {
      background: #2563eb !important;
      border-color: #2563eb !important;
      color: #fff !important;
      box-shadow: none !important;
      outline: none !important;
    }

    .pagination .page-item.disabled .page-link {
      background: #f8fafc;
      color: var(--txt-3);
    }

    /* ======= BADGES (Redesigned with dot indicators) ======= */
    .badge-status {
      font-size: .6875rem;
      font-weight: 600;
      padding: .25rem .7rem .25rem .55rem;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      border: 1px solid transparent;
      letter-spacing: .2px;
    }

    .badge-status::before {
      content: '';
      width: 6px;
      height: 6px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    .badge-active {
      background: #ecfdf5;
      color: #059669;
      border-color: #a7f3d0;
    }

    .badge-active::before {
      background: #10b981;
    }

    .badge-inactive {
      background: #f8fafc;
      color: var(--txt-3);
      border-color: var(--border);
    }

    .badge-inactive::before {
      background: #94a3b8;
    }

    .badge-suspended {
      background: #fff7ed;
      color: #c2410c;
      border-color: #fed7aa;
    }

    .badge-suspended::before {
      background: #f97316;
    }

    .badge-failed {
      background: #fef2f2;
      color: #dc2626;
      border-color: #fecaca;
    }

    .badge-failed::before {
      background: #ef4444;
    }

    .badge-paid {
      background: #ecfdf5;
      color: #059669;
      border-color: #a7f3d0;
    }

    .badge-paid::before {
      background: #10b981;
    }

    .badge-unpaid {
      background: #fefce8;
      color: #ca8a04;
      border-color: #fde047;
    }

    .badge-unpaid::before {
      background: #eab308;
    }

    .badge-overdue {
      background: #fef2f2;
      color: #dc2626;
      border-color: #fecaca;
    }

    .badge-overdue::before {
      background: #ef4444;
      box-shadow: 0 0 0 2px rgba(239, 68, 68, .2);
    }

    .badge-cancelled {
      background: #f8fafc;
      color: var(--txt-3);
      border-color: var(--border);
    }

    .badge-cancelled::before {
      background: #cbd5e1;
    }

    .badge-provisioning {
      background: #eff6ff;
      color: #2563eb;
      border-color: #bfdbfe;
    }

    .badge-provisioning::before {
      background: #3b82f6;
    }

    .badge-pending,
    .badge-warning {
      background: #fefce8;
      color: #ca8a04;
      border-color: #fde047;
    }

    .badge-pending::before,
    .badge-warning::before {
      background: #eab308;
    }

    .badge-open {
      background: #ecfeff;
      color: #0891b2;
      border-color: #a5f3fc;
    }

    .badge-open::before {
      background: #06b6d4;
    }

    .badge-danger {
      background: #fef2f2;
      color: #dc2626;
      border-color: #fecaca;
    }

    .badge-danger::before {
      background: #ef4444;
    }

    .badge-success {
      background: #ecfdf5;
      color: #059669;
      border-color: #a7f3d0;
    }

    .badge-success::before {
      background: #10b981;
    }

    /* ======= LOADING SKELETON ======= */
    @keyframes shimmer {
      0% {
        background-position: -400px 0;
      }

      100% {
        background-position: 400px 0;
      }
    }

    .skeleton {
      background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 37%, #f1f5f9 63%);
      background-size: 800px 100%;
      animation: shimmer 1.5s infinite ease-in-out;
      border-radius: 4px;
    }

    .skeleton-row {
      display: flex;
      gap: 1rem;
      padding: 1rem 1.25rem;
      border-bottom: 1px solid var(--border);
    }

    .skeleton-cell {
      height: 14px;
      border-radius: 4px;
      background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 37%, #f1f5f9 63%);
      background-size: 800px 100%;
      animation: shimmer 1.5s infinite ease-in-out;
    }

    .skeleton-cell.w-sm {
      width: 40px;
    }

    .skeleton-cell.w-md {
      width: 120px;
    }

    .skeleton-cell.w-lg {
      width: 200px;
    }

    .skeleton-cell.w-xl {
      width: 280px;
    }

    .skeleton-circle {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 37%, #f1f5f9 63%);
      background-size: 800px 100%;
      animation: shimmer 1.5s infinite ease-in-out;
      flex-shrink: 0;
    }

    /* ======= EMPTY STATE ======= */
    .empty-state {
      text-align: center;
      padding: 3rem 1.5rem;
    }

    .empty-state-icon {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: #f1f5f9;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.75rem;
      color: #94a3b8;
      margin-bottom: 1rem;
    }

    .empty-state-title {
      font-size: .9375rem;
      font-weight: 600;
      color: var(--txt);
      margin-bottom: .375rem;
    }

    .empty-state-desc {
      font-size: .8125rem;
      color: var(--txt-3);
      max-width: 300px;
      margin: 0 auto .75rem;
      line-height: 1.5;
    }

    /* ======= ALERTS ======= */
    .alert {
      border-radius: var(--r);
      font-size: .875rem;
      border: none;
      border-left: 4px solid;
      padding: .75rem 1rem;
      background-image: none !important;
    }
    .alert-success { background-color: #f0fdf4 !important; border-left-color: #22c55e; color: #166534; }
    .alert-danger  { background-color: #fef2f2 !important; border-left-color: #ef4444; color: #991b1b; }
    .alert-warning { background-color: #fffbeb !important; border-left-color: #f59e0b; color: #92400e; }
    .alert-info    { background-color: #eff6ff !important; border-left-color: #3b82f6; color: #1e40af; }
    [data-theme="dark"] .alert-success { background-color: rgba(34,197,94,.08) !important; color: #4ade80; }
    [data-theme="dark"] .alert-danger  { background-color: rgba(239,68,68,.08) !important; color: #f87171; }
    [data-theme="dark"] .alert-warning { background-color: rgba(245,158,11,.08) !important; color: #fbbf24; }
    [data-theme="dark"] .alert-info    { background-color: rgba(59,130,246,.08) !important; color: #60a5fa; }
    [data-theme="dark"] .alert .btn-close { filter: invert(1); }

    /* ======= POP BUTTON (3D press) ======= */
    .btn-pop {
      display: inline-flex; align-items: center; justify-content: center;
      font-weight: 600; border-radius: 10px;
      border: 2px solid; border-bottom-width: 4px;
      transition: all .1s ease; user-select: none;
      transform-origin: bottom;
    }
    .btn-pop:active { border-bottom-width: 2px; transform: scaleY(.97); }
    .btn-pop-orange { background: var(--orange); border-color: #c2410c; color: #fff; }
    .btn-pop-orange:hover { background: #ea580c; color: #fff; }
    .btn-pop-green  { background: #22c55e; border-color: #15803d; color: #fff; }
    .btn-pop-green:hover { background: #16a34a; color: #fff; }
    .btn-pop-red    { background: #ef4444; border-color: #991b1b; color: #fff; }
    .btn-pop-red:hover { background: #dc2626; color: #fff; }
    .btn-pop-blue   { background: #3b82f6; border-color: #1e40af; color: #fff; }
    .btn-pop-blue:hover { background: #2563eb; color: #fff; }
    .btn-pop-gray   { background: var(--surface); border-color: var(--bd-dk); color: var(--txt); }
    .btn-pop-gray:hover { background: var(--hover-bg); color: var(--txt); }

    /* ======= BACK BUTTON ======= */
    .btn-back {
      display: inline-flex; align-items: center; gap: .375rem;
      font-size: .8125rem; font-weight: 500; color: var(--txt-2);
      text-decoration: none; padding: .25rem .5rem;
      border-radius: var(--r-sm); border: 1px solid var(--border);
      transition: all .15s; background: var(--surface);
    }
    .btn-back:hover { background: var(--hover-bg); color: var(--txt); border-color: var(--bd-dk); }
    .btn-back i { font-size: 1rem; }

    /* ======= MODALS ======= */
    .modal-content {
      border: none;
      border-radius: var(--r);
      box-shadow: var(--shadow-lg);
    }

    .modal-header {
      border-bottom: 1px solid var(--border);
      padding: 1rem 1.25rem;
    }

    .modal-title {
      font-size: .9375rem;
      font-weight: 600;
    }

    .modal-body {
      padding: 1.25rem;
    }

    .modal-footer {
      border-top: 1px solid var(--border);
      padding: .875rem 1.25rem;
      gap: .5rem;
    }

    /* ======= DROPDOWN ======= */
    .dropdown-menu {
      border: 1px solid var(--border);
      border-radius: var(--r-sm);
      box-shadow: var(--shadow-lg);
      padding: .25rem;
      background: #fff;
      font-size: .875rem;
    }

    .dropdown-item {
      border-radius: 5px;
      padding: .4375rem .75rem;
      color: var(--txt);
    }

    .dropdown-item:hover {
      background: var(--blue-lt);
      color: var(--blue);
    }

    .dropdown-divider {
      margin: .25rem 0;
      border-color: var(--border);
    }

    /* ======= MISC ======= */
    a {
      color: var(--blue);
    }

    hr {
      border-color: var(--border);
    }

    .text-muted {
      color: var(--txt-3) !important;
    }

    .avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: var(--blue);
      color: #fff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: .75rem;
      flex-shrink: 0;
    }

    .avatar-sm {
      width: 26px;
      height: 26px;
      font-size: .6875rem;
    }

    .sneat-icon-box {
      width: 38px;
      height: 38px;
      border-radius: var(--r-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      flex-shrink: 0;
    }

    .icon-box-primary {
      background: var(--blue-lt);
      color: var(--blue);
    }

    .icon-box-success {
      background: #ecfdf5;
      color: #059669;
    }

    .icon-box-warning {
      background: #fffbeb;
      color: #d97706;
    }

    .icon-box-danger {
      background: #fef2f2;
      color: #dc2626;
    }

    .icon-box-info {
      background: #eff6ff;
      color: #2563eb;
    }

    /* Dashboard info rows */
    .info-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: .5rem 0;
      border-bottom: 1px solid var(--border);
    }

    .info-row:last-child {
      border-bottom: none;
    }

    .info-label {
      font-size: .8125rem;
      color: var(--txt-2);
    }

    .info-value {
      font-size: .8125rem;
      font-weight: 600;
      color: var(--txt);
    }

    /* Dashboard avatar circles */
    .av {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: .7rem;
      color: #fff;
      flex-shrink: 0;
    }

    .av-blue {
      background: var(--blue);
    }

    .av-green {
      background: var(--green);
    }

    .av-orange {
      background: var(--orange);
    }

    .av-red {
      background: var(--red);
    }

    .av-cyan {
      background: var(--cyan);
    }

    /* Dashboard metric bars */
    .metric-row {
      display: flex;
      flex-direction: column;
      gap: .5rem;
    }

    .metric-item {
      display: flex;
      align-items: center;
      gap: .5rem;
    }

    .metric-label {
      font-size: .8125rem;
      color: var(--txt-2);
      min-width: 80px;
    }

    .metric-bar-wrap {
      flex: 1;
      height: 6px;
      background: var(--bg);
      border-radius: 999px;
    }

    .metric-bar {
      height: 100%;
      border-radius: 999px;
      background: var(--blue);
    }

    .metric-val {
      font-size: .8125rem;
      font-weight: 700;
      color: var(--txt);
      min-width: 28px;
      text-align: right;
    }

    /* Chart wrapper */
    .chart-wrap {
      position: relative;
    }

    /* Grid helpers */
    .grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.25rem;
    }

    /* Layout footer */
    .layout-footer {
      margin-left: var(--sb-w);
      padding: .75rem 2.5rem;
      font-size: .75rem;
      color: var(--txt-3);
      border-top: 1px solid var(--border);
      background: var(--surface);
    }

    /* ======= MOBILE ======= */
    @media (max-width: 991.98px) {
      .sidebar {
        transform: translateX(-100%);
        transition: transform .2s;
      }

      .sidebar.open {
        transform: translateX(0);
        box-shadow: 0 0 0 100vw rgba(0, 0, 0, .4);
      }

      .topbar {
        margin-left: 0;
        padding: 0 1rem;
      }

      .main {
        margin-left: 0 !important;
        padding: 1rem;
      }

      .layout-footer {
        margin-left: 0;
        padding: .75rem 1rem;
      }

      .tb-burger {
        display: flex !important;
      }

      .topbar {
        gap: .75rem;
      }

      .tb-title {
        min-width: 0;
        max-width: 42vw;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .tb-search {
        min-width: 0;
      }

      .tb-search input {
        width: 160px;
      }

      .search-results {
        left: auto;
        right: 0;
        min-width: min(88vw, 320px);
        max-width: 88vw;
      }

      .page-header,
      .page-title-box {
        align-items: flex-start !important;
      }

      .page-header > div:last-child,
      .page-title-box > div:last-child {
        width: 100%;
        justify-content: flex-start;
      }

      .page-header .btn,
      .page-title-box .btn {
        white-space: nowrap;
      }

      .card-header {
        align-items: flex-start;
      }

      .card-header > .d-flex,
      .card-header > div:last-child {
        width: 100%;
        flex-wrap: wrap;
      }

      .dataTables_wrapper {
        padding: 0 .875rem .875rem;
      }

      .dataTables_wrapper .d-flex.justify-content-between,
      .dataTables_wrapper .d-flex.justify-content-between.align-items-center,
      .dataTables_wrapper .d-flex.justify-content-between.align-items-center.mb-3,
      .dataTables_wrapper .d-flex.justify-content-between.align-items-center.mt-3 {
        flex-direction: column;
        align-items: stretch !important;
        gap: .75rem;
        padding: .75rem 0 !important;
      }

      .dataTables_wrapper .dataTables_filter {
        width: 100%;
      }

      .dataTables_wrapper .dataTables_filter label,
      .dataTables_wrapper .dataTables_length label {
        display: flex;
        align-items: center;
        gap: .5rem;
        width: 100%;
      }

      .dataTables_wrapper .dataTables_filter input,
      .dataTables_wrapper .dataTables_length select {
        width: 100% !important;
        min-width: 0;
      }

      .dataTables_wrapper .dataTables_paginate {
        justify-content: flex-start;
      }

      .dataTables_wrapper .dataTables_info {
        padding-top: 0;
      }
    }

    @media (min-width: 992px) {
      .tb-burger {
        display: none !important;
      }
    }

    @media (max-width: 767.98px) {
      .stat-grid {
        grid-template-columns: 1fr 1fr;
      }

      .grid-2 {
        grid-template-columns: 1fr !important;
      }

      /* Dashboard 3fr 2fr and all multi-col grids → stack */
      [style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
      }

      .card-header {
        flex-wrap: wrap;
        gap: .5rem;
      }

      .card-title {
        font-size: .8125rem;
        white-space: normal;
        word-break: break-word;
      }

      .page-title-box {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: .5rem;
      }

      .page-header {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: .5rem;
      }

      .page-header > div:last-child,
      .page-title-box > div:last-child {
        width: 100%;
        justify-content: flex-start;
      }

      /* Search bar shrink */
      .tb-search input {
        width: 120px;
        font-size: .75rem;
      }

      /* Nav tabs scrollable */
      .nav-tabs {
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
      }

      .nav-tabs::-webkit-scrollbar {
        display: none;
      }

      .nav-tabs .nav-link {
        white-space: nowrap;
        flex-shrink: 0;
      }

      /* Table horizontal scroll */
      .table-responsive,
      .dataTables_wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
      }

      .table {
        min-width: 600px;
      }

      .table th,
      .table td {
        padding: .75rem .875rem;
      }

      .card-header {
        padding: .875rem 1rem;
      }

      .card-body,
      .modal-body {
        padding: 1rem;
      }

      .btn,
      .btn-sm {
        font-size: .75rem;
      }

      /* Stat value no overflow */
      .stat-value {
        font-size: 1.25rem;
        word-break: break-all;
      }

      .stat-label {
        font-size: .6rem;
      }

      .stat-card {
        padding: .875rem 1rem;
      }

      .stat-icon {
        width: 36px;
        height: 36px;
        font-size: 1.125rem;
      }
    }

    @media (max-width: 575.98px) {
      .stat-grid {
        grid-template-columns: 1fr;
      }

      .topbar {
        padding: 0 .75rem;
      }

      .tb-title {
        max-width: 34vw;
        font-size: .875rem;
      }

      .tb-search input {
        width: 112px;
        padding-left: 1.75rem;
      }

      .tb-btn,
      .dark-toggle,
      .tb-avatar {
        width: 32px;
        height: 32px;
      }

      .page-header .btn,
      .page-title-box .btn {
        width: 100%;
        justify-content: center;
      }

      .page-header form,
      .page-title-box form,
      .page-header a.btn,
      .page-title-box a.btn {
        width: 100%;
      }

      .breadcrumb {
        font-size: .75rem;
      }

      .table {
        min-width: 560px;
      }

      .table thead th,
      .table tbody td {
        padding: .625rem .75rem;
        font-size: .75rem;
      }

      .dataTables_wrapper {
        padding: 0 .75rem .75rem;
      }

      .dataTables_wrapper .dataTables_paginate .paginate_button,
      .pagination .page-link {
        min-width: 34px;
        min-height: 34px;
        padding: .35rem .55rem;
        font-size: .8125rem;
      }

      .empty-state {
        padding: 2rem 1rem;
      }
    }
    /* ======= FINAL SHELL STABILIZERS ======= */
    :root {
      --sb-w: 224px;
    }

    body {
      background: #f7f7f8;
    }

    .sidebar {
      width: var(--sb-w);
      background: #ffffff;
    }

    .sb-body {
      padding: .85rem .75rem;
    }

    .sb-section {
      margin-bottom: .4rem;
    }

    .sb-label {
      padding: .4rem .45rem .2rem;
      font-size: .58rem;
      letter-spacing: .08em;
    }

    .sb-link {
      min-height: 34px;
      padding: .42rem .55rem;
      border-radius: 8px;
      font-size: .8125rem;
      gap: .55rem;
    }

    .sb-link-title {
      line-height: 1.2;
    }

    .sb-foot {
      padding: .8rem .9rem;
    }

    .sidebar,
    .sb-body,
    .sb-foot {
      border: none !important;
      box-shadow: none !important;
    }

    .sb-user-name {
      font-size: .765rem;
    }

    .sb-user-role {
      font-size: .625rem;
    }

    .topbar {
      height: 54px;
      margin-left: var(--sb-w);
      padding: 0 1.15rem;
      gap: .85rem;
      background: rgba(255, 255, 255, .94);
      backdrop-filter: blur(10px);
    }

    .tb-title {
      font-size: .96rem;
      font-weight: 650;
    }

    .tb-search input {
      width: 200px;
      height: 36px;
      padding: .35rem .75rem .35rem 1.95rem;
      font-size: .8125rem;
    }

    .tb-btn,
    .tb-avatar {
      width: 34px;
      height: 34px;
    }

    .main {
      margin-left: var(--sb-w);
      padding: 1.15rem 1.35rem 1.35rem;
      min-height: calc(100vh - 54px);
    }

    .layout-footer {
      margin-left: var(--sb-w);
      padding: .75rem 1.35rem 1rem;
      font-size: .75rem;
      color: var(--txt-3);
    }

    .page-header,
    .page-title-box,
    .ms-page-head {
      margin-bottom: 1rem;
    }

    .ms-page {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .ms-page-head {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .ms-page-kicker {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      margin-bottom: .35rem;
      font-size: .7rem;
      font-weight: 600;
      color: var(--txt-3);
      text-transform: uppercase;
      letter-spacing: .08em;
    }

    .ms-page-title {
      margin: 0;
      font-size: 1.55rem;
      font-weight: 700;
      letter-spacing: -.03em;
      color: var(--txt);
    }

    .ms-page-actions {
      display: flex;
      align-items: center;
      gap: .5rem;
      flex-wrap: wrap;
    }

    .ms-panel {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 14px;
      box-shadow: var(--shadow-sm);
      overflow: hidden;
    }

    .ms-panel-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .75rem;
      padding: .95rem 1rem;
      border-bottom: 1px solid var(--border);
      background: color-mix(in srgb, var(--surface) 94%, var(--surface-2));
    }

    .ms-panel-title {
      margin: 0;
      font-size: .92rem;
      font-weight: 650;
      color: var(--txt);
    }

    .ms-stat-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 14px;
      margin-bottom: 16px;
    }

    .ms-stat-card {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      min-height: 108px;
      padding: 16px 18px;
      border: 1px solid var(--border);
      border-radius: 16px;
      background: var(--surface);
      box-shadow: none;
      overflow: hidden;
      position: relative;
    }

    .ms-stat-card::before {
      content: "";
      position: absolute;
      inset: 0 auto 0 0;
      width: 3px;
      background: var(--stat-accent, var(--blue));
      opacity: .9;
    }

    .ms-stat-card > * {
      position: relative;
      z-index: 1;
    }

    .ms-stat-icon {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      background: color-mix(in srgb, var(--stat-accent, var(--blue)) 10%, var(--surface));
      color: var(--stat-accent, var(--blue));
      border: 1px solid color-mix(in srgb, var(--stat-accent, var(--blue)) 22%, var(--border));
    }

    .ms-stat-card .ms-stat-label {
      font-size: .72rem;
      font-weight: 600;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: var(--txt-3);
      margin-bottom: 6px;
    }

    .ms-stat-card .ms-stat-value {
      font-size: 1.65rem;
      line-height: 1;
      letter-spacing: -.04em;
      font-weight: 700;
      color: var(--txt);
    }

    .ms-stat-card .ms-stat-meta {
      margin-top: 8px;
      font-size: .82rem;
      color: var(--txt-3);
    }

    .ms-inline-kpis {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    @media (max-width: 1199.98px) {
      .ms-stat-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 767.98px) {
      .ms-stat-grid {
        grid-template-columns: 1fr;
      }
    }

    .ms-panel-body {
      padding: 1rem;
    }

    .ms-panel-foot {
      padding: .85rem 1rem;
      border-top: 1px solid var(--border);
      background: var(--surface);
    }

    .ms-table-shell {
      padding: 0 1rem 1rem;
    }

    .ms-table-shell .table-responsive {
      border: 1px solid var(--border);
      border-radius: 12px;
      background: var(--surface);
    }

    .ms-btn,
    .ms-btn-secondary,
    .ms-btn-ghost {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .45rem;
      min-height: 34px;
      padding: .44rem .78rem;
      border-radius: 8px;
      font-size: .79rem;
      font-weight: 600;
      text-decoration: none;
      border: 1px solid transparent;
      transition: background-color .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease;
      white-space: nowrap;
    }

    .ms-btn {
      background: var(--blue);
      border-color: var(--blue);
      color: #fff;
      box-shadow: 0 6px 14px rgba(249, 115, 22, .12);
    }

    .ms-btn:hover {
      background: var(--blue-dk);
      border-color: var(--blue-dk);
      color: #fff;
    }

    .ms-btn-secondary {
      background: var(--surface-2);
      border-color: var(--border);
      color: var(--txt-2);
    }

    .ms-btn-secondary:hover {
      background: var(--surface);
      color: var(--txt);
      border-color: color-mix(in srgb, var(--blue) 18%, var(--border));
    }

    .ms-btn-ghost {
      background: transparent;
      border-color: transparent;
      color: var(--txt-2);
    }

    .ms-btn-ghost:hover {
      background: var(--surface-2);
      color: var(--txt);
      border-color: var(--border);
    }

    .ms-chip,
    .ms-kpi-chip {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      min-height: 32px;
      padding: .35rem .65rem;
      border-radius: 999px;
      font-size: .73rem;
      font-weight: 600;
      border: 1px solid var(--border);
      background: var(--surface-2);
      color: var(--txt-2);
    }

    .ms-kpi-chip.is-success {
      background: color-mix(in srgb, var(--nk-success) 12%, var(--surface));
      color: color-mix(in srgb, var(--nk-success) 72%, var(--txt));
      border-color: color-mix(in srgb, var(--nk-success) 26%, var(--border));
    }

    .ms-detail-card {
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: .95rem 1rem;
      background: var(--surface);
    }

    .ms-detail-card.is-soft {
      background: color-mix(in srgb, var(--surface) 92%, var(--surface-2));
    }

    .ms-detail-label {
      font-size: .68rem;
      font-weight: 700;
      color: var(--txt-3);
      text-transform: uppercase;
      letter-spacing: .08em;
      margin-bottom: .25rem;
    }

    .ms-detail-value {
      font-size: 1.35rem;
      font-weight: 700;
      color: var(--txt);
      letter-spacing: -.03em;
      line-height: 1.1;
    }

    .chart-wrap {
      min-height: 220px;
    }

    .metric-row {
      display: flex;
      flex-direction: column;
      gap: .75rem;
    }

    .metric-item {
      display: grid;
      grid-template-columns: minmax(0, 1fr) 92px 32px;
      gap: .65rem;
      align-items: center;
    }

    .metric-label,
    .metric-val {
      font-size: .75rem;
      color: var(--txt-2);
    }

    .metric-bar-wrap {
      height: 7px;
      border-radius: 999px;
      background: color-mix(in srgb, var(--border) 70%, var(--surface-2));
      overflow: hidden;
    }

    .metric-bar {
      height: 100%;
      border-radius: 999px;
      background: var(--blue);
    }

    .btn,
    .btn-sm,
    .btn-group-sm > .btn,
    .btn-pop,
    .page-header .btn,
    .page-title-box .btn {
      min-height: 34px;
      padding: .42rem .72rem;
      border-radius: 8px !important;
      font-size: .79rem !important;
      font-weight: 600;
      line-height: 1.2;
      box-shadow: none !important;
      transform: none !important;
      margin-bottom: 0 !important;
      border-bottom-width: 1px !important;
    }

    .btn-sm,
    .btn-group-sm > .btn {
      min-height: 30px;
      padding: .32rem .58rem;
      font-size: .74rem !important;
    }

    .btn-primary,
    .btn-success,
    .btn-danger,
    .btn-secondary,
    .btn-outline-primary,
    .btn-outline-secondary,
    .btn-outline-danger,
    .btn-light {
      transition: background-color .15s ease, border-color .15s ease, color .15s ease;
    }

    .btn-primary {
      background: var(--blue);
      border-color: var(--blue);
      color: #fff;
    }

    .btn-primary:hover {
      background: var(--blue-dk);
      border-color: var(--blue-dk);
      color: #fff;
    }

    .btn-secondary,
    .btn-light {
      background: #fff;
      border-color: var(--border);
      color: var(--txt-2);
    }

    .btn-secondary:hover,
    .btn-light:hover,
    .btn-outline-secondary:hover {
      background: #f8fafc;
      border-color: #cbd5e1;
      color: var(--txt);
    }

    .btn-outline-primary {
      background: #fff;
      border-color: #cbd5e1;
      color: var(--txt-2);
    }

    .btn-outline-primary:hover {
      background: var(--blue-lt);
      border-color: var(--blue-md);
      color: var(--blue-dk);
    }

    .btn-success {
      background: #16a34a;
      border-color: #16a34a;
      color: #fff;
    }

    .btn-success:hover {
      background: #15803d;
      border-color: #15803d;
      color: #fff;
    }

    .btn-danger,
    .btn-outline-danger:hover {
      background: #dc2626;
      border-color: #dc2626;
      color: #fff;
    }

    .btn-outline-danger {
      background: #fff;
      border-color: #fecaca;
      color: #dc2626;
    }

    .btn-outline-danger:hover {
      color: #fff;
    }

    .form-control,
    .form-select,
    .input-group-text {
      min-height: 32px;
      font-size: .84rem;
    }

    .select2-container--bootstrap-5 .select2-selection {
      min-height: 32px;
      border-radius: 8px;
      font-size: .84rem;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
      line-height: 36px;
      padding-left: .75rem;
      font-size: .84rem;
      color: var(--txt);
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
      height: 36px;
      width: 28px;
    }

    .select2-container--bootstrap-5 .select2-dropdown {
      border-radius: 10px;
      padding: .25rem;
      border-color: rgba(148, 163, 184, .24);
    }

    .select2-container--bootstrap-5 .select2-search .select2-search__field {
      min-height: 34px;
      border-radius: 8px;
      font-size: .84rem;
    }

    .select2-container--bootstrap-5 .select2-results__option {
      border-radius: 8px;
      padding: .48rem .6rem;
      font-size: .83rem;
    }

    .pagination,
    .dataTables_wrapper .dataTables_paginate {
      gap: .3rem;
    }

    .pagination .page-link,
    .dataTables_wrapper .dataTables_paginate .paginate_button {
      min-width: 32px;
      min-height: 32px;
      padding: .28rem .52rem;
      font-size: .76rem;
      border-radius: 8px !important;
    }

    .dataTables_wrapper {
      padding: 0 .9rem .9rem;
    }

    .dataTables_wrapper .dataTables_filter input {
      min-width: 180px;
      min-height: 36px;
      padding: .4rem .75rem;
    }

    .dataTables_wrapper .dataTables_length select {
      min-height: 34px;
      font-size: .78rem;
    }

    @media (max-width: 991.98px) {
      .topbar {
        margin-left: 0;
      }

      .main,
      .layout-footer {
        margin-left: 0;
      }

      .ms-page-head {
        align-items: stretch;
      }

      .ms-page-actions {
        width: 100%;
      }

      .ms-page-actions > * {
        width: auto;
      }

      .stat-grid {
        gap: .9rem;
      }

      .metric-item {
        grid-template-columns: minmax(0, 1fr) 86px 28px;
      }
    }

    @media (max-width: 575.98px) {
      :root {
        --sb-w: 224px;
      }

      .main {
        padding: 1rem .85rem 1.1rem;
      }

      .ms-panel-head,
      .ms-panel-body,
      .ms-panel-foot,
      .ms-table-shell {
        padding-left: .85rem;
        padding-right: .85rem;
      }

      .ms-table-shell .table-responsive {
        border-radius: 10px;
      }

      .ms-page-title {
        font-size: 1.32rem;
      }

      .tb-search input {
        width: 132px;
      }
    }

    /* ======= FINAL LINEAR-LIKE SHELL OVERRIDES ======= */
    html {
      color-scheme: light;
    }

    html[data-theme="dark"] {
      color-scheme: dark;
      --blue: #5e6ad2;
      --blue-dk: #4f58c9;
      --blue-lt: rgba(94, 106, 210, 0.16);
      --blue-md: rgba(94, 106, 210, 0.22);
      --blue-glow: rgba(94, 106, 210, 0.32);
      --bg: #09090b;
      --surface: #111113;
      --surface-2: #161619;
      --border: rgba(255, 255, 255, 0.08);
      --bd-dk: rgba(255, 255, 255, 0.1);
      --txt: #f4f4f5;
      --txt-2: #a1a1aa;
      --txt-3: #71717a;
      --shadow-xs: none;
      --shadow-sm: 0 0 0 1px rgba(255,255,255,0.03);
      --shadow-md: 0 0 0 1px rgba(255,255,255,0.04), 0 18px 40px rgba(0,0,0,0.32);
      --shadow-lg: 0 0 0 1px rgba(255,255,255,0.05), 0 24px 60px rgba(0,0,0,0.4);
      --nk-primary: #5e6ad2;
      --nk-success: #4ade80;
      --nk-warning: #fbbf24;
      --nk-danger: #fb7185;
      --nk-info: #60a5fa;
      --nk-bg: #09090b;
      --nk-surface: #111113;
      --nk-border: rgba(255,255,255,0.08);
      --nk-text: #f4f4f5;
      --nk-text-2: #a1a1aa;
      --nk-text-muted: #71717a;
    }

    html:not([data-theme="dark"]) {
      --blue: #1e293b;
      --blue-dk: #0f172a;
      --blue-lt: rgba(30, 41, 59, 0.1);
      --blue-md: rgba(30, 41, 59, 0.16);
      --blue-glow: rgba(30, 41, 59, 0.24);
      --bg: #f7f7f8;
      --surface: #ffffff;
      --surface-2: #fcfcfd;
      --border: #e7e7ec;
      --bd-dk: #d4d4dc;
      --txt: #18181b;
      --txt-2: #52525b;
      --txt-3: #71717a;
      --shadow-xs: none;
      --shadow-sm: 0 1px 2px rgba(16, 24, 40, 0.04);
      --shadow-md: 0 8px 24px rgba(16, 24, 40, 0.06);
      --shadow-lg: 0 14px 36px rgba(16, 24, 40, 0.08);
      --nk-primary: #1e293b;
      --nk-success: #16a34a;
      --nk-warning: #d97706;
      --nk-danger: #e11d48;
      --nk-info: #2563eb;
      --nk-bg: #f7f7f8;
      --nk-surface: #ffffff;
      --nk-border: #e7e7ec;
      --nk-text: #18181b;
      --nk-text-2: #52525b;
      --nk-text-muted: #71717a;
    }

    body {
      background: var(--bg) !important;
      color: var(--txt);
    }

    .sidebar,
    .topbar,
    .layout-footer,
    .card,
    .ms-panel,
    .ms-detail-card,
    .ms-table-shell,
    .dropdown-menu,
    .search-results,
    .notif-menu,
    .modal-content,
    .swal2-popup {
      background: var(--surface) !important;
      border-color: var(--border) !important;
      box-shadow: var(--shadow-sm) !important;
    }

    .sidebar {
      background: var(--bg) !important;
      padding-top: 10px;
    }

    .topbar {
      background: color-mix(in srgb, var(--surface) 92%, transparent) !important;
      border-bottom-color: var(--border) !important;
      backdrop-filter: blur(12px);
      box-shadow: none !important;
    }

    .tb-title,
    .sb-logo-name,
    .sb-user-name,
    .dropdown-item,
    .search-result-title {
      color: var(--txt) !important;
    }

    .sb-label,
    .sb-user-role,
    .ms-page-kicker,
    .ms-page-subtitle,
    .ms-panel-subtitle,
    .search-result-sub,
    .notif-item-meta,
    .text-muted {
      color: var(--txt-3) !important;
    }

    .sb-section {
      margin-bottom: .34rem;
    }

    .sb-linear-head {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: .42rem;
      padding: .04rem .1rem .52rem;
    }

    .sb-linear-spacer {
      flex: 1 1 auto;
      min-width: 0;
    }

    .sb-linear-actions {
      display: inline-flex;
      align-items: center;
      justify-content: flex-start;
      gap: .4rem;
      margin-left: 0;
    }

    .sb-head-btn {
      width: 28px;
      height: 28px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border: none;
      background: transparent;
      border-radius: 999px;
      color: var(--txt-3);
      transition: color .18s ease, background-color .18s ease;
    }

    .sb-head-icon {
      width: 15px;
      height: 15px;
      display: block;
    }

    .sb-head-btn:hover,
    .sb-head-btn:focus {
      color: var(--txt);
      background: var(--surface-2);
    }

    .tb-search.tb-search-popover {
      position: absolute !important;
      top: 58px;
      left: .7rem;
      z-index: 40;
      width: calc(100% - 1.4rem);
      max-width: none;
      display: none;
      align-items: center;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 14px;
      box-shadow: 0 18px 34px rgba(0, 0, 0, 0.24);
      padding: .15rem;
      margin: 0;
    }

    .tb-search.tb-search-popover.is-open {
      display: flex !important;
    }

    .tb-search.tb-search-popover input {
      width: 100% !important;
      height: 38px;
      border-radius: 12px;
      background: transparent;
      border-color: transparent;
      box-shadow: none !important;
    }

    .tb-search.tb-search-popover i {
      left: .72rem;
    }

    .tb-search.tb-search-popover .search-results {
      top: calc(100% + 8px);
      left: 0;
      right: auto;
      min-width: 100%;
      width: 100%;
      max-width: none;
    }

    .sb-linear-mark {
      width: 32px;
      height: 32px;
      border-radius: 999px;
      background: #3b82f6;
      color: #eff6ff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .76rem;
      font-weight: 700;
      letter-spacing: -.02em;
      flex-shrink: 0;
    }

    .sb-user-mark {
      width: 30px;
      height: 30px;
      font-size: .72rem;
    }

    .sb-linear-caret {
      color: var(--txt-3);
      font-size: .9rem;
      flex-shrink: 0;
    }

    .sb-user-trigger {
      display: inline-flex;
      align-items: center;
      gap: .45rem;
      padding: 0;
      background: transparent;
      border: none;
      color: var(--txt-2);
      text-decoration: none;
    }

    .sb-user-trigger:hover,
    .sb-user-trigger:focus {
      color: var(--txt);
    }

    .sb-user-menu {
      min-width: 200px;
      padding: .35rem 0;
      border-radius: 14px;
    }

    .sb-user-menu-head {
      padding: .1rem .9rem .55rem;
    }

    .sb-user-menu-name {
      font-size: .82rem;
      font-weight: 600;
      color: var(--txt);
      line-height: 1.25;
    }

    .sb-user-menu-role {
      font-size: .68rem;
      color: var(--txt-3);
      margin-top: .1rem;
    }

    .sb-primary-rail {
      display: none;
    }

    .sb-quick-link {
      display: flex;
      align-items: center;
      gap: .625rem;
      min-height: 40px;
      padding: .55rem .7rem;
      border-radius: 13px;
      text-decoration: none;
      color: var(--txt);
      background: color-mix(in srgb, var(--surface) 86%, var(--surface-2));
      border: 1px solid transparent;
    }

    .sb-quick-link:hover {
      color: var(--txt);
      background: var(--surface-2);
    }

    .sb-quick-link.active {
      background: #1c1c20;
      color: #fff;
      border-color: rgba(255,255,255,.08);
    }

    html[data-theme="light"] .sb-quick-link {
      background: #f1f1f3;
    }

    html[data-theme="light"] .sb-quick-link.active {
      background: #171717;
      border-color: rgba(23,23,23,.12);
    }

    .sb-label {
      padding: .22rem .42rem .16rem;
      font-size: .58rem;
      font-weight: 700;
      letter-spacing: .12em;
    }

    .sb-section-main {
      margin-bottom: .48rem;
    }

    .sb-group {
      display: block;
      margin: 0 0 .12rem;
    }

    .sb-group > summary {
      list-style: none;
    }

    .sb-group > summary::-webkit-details-marker {
      display: none;
    }

    .sb-group-summary {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .5rem;
      min-height: 34px;
      padding: .3rem .42rem;
      border-radius: 11px;
      color: var(--txt-3);
      cursor: pointer;
      user-select: none;
      border: 1px solid transparent;
      transition: color .18s ease, background-color .18s ease, border-color .18s ease;
    }

    .sb-group-summary:hover {
      color: var(--txt-2);
      background: var(--surface-2);
      border-color: var(--border);
    }

    .sb-group-label {
      font-size: .8rem;
      font-weight: 500;
      letter-spacing: -.01em;
      line-height: 1.15;
    }

    .sb-group-caret {
      font-size: .9rem;
      color: inherit;
      transition: transform .18s ease;
    }

    .sb-group[open] .sb-group-caret {
      transform: rotate(90deg);
    }

    .sb-group[open] .sb-group-summary {
      color: var(--txt-2);
    }

    .sb-group-items {
      margin-top: .08rem;
      padding-left: .56rem;
      display: grid;
      gap: .08rem;
    }

    .sb-link {
      color: var(--txt-2);
      border: 1px solid transparent;
      min-height: 36px;
      padding: .4rem .62rem;
      border-radius: 11px;
      gap: .58rem;
    }

    .sb-icon-wrap {
      width: 18px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .sb-link-title {
      font-size: .88rem;
      letter-spacing: -.01em;
      font-weight: 500;
    }

    .sb-logo-name {
      font-size: .89rem !important;
      letter-spacing: -.02em;
    }

    .sb-user-name {
      font-size: .8rem !important;
    }

    .sb-user-role {
      font-size: .64rem !important;
    }

    .tb-title {
      font-size: 1rem !important;
      letter-spacing: -.015em;
    }

    .tb-search input {
      font-size: .82rem !important;
    }

    .sb-inline-heading {
      display: none;
    }

    /* ======= FINAL GLOBAL DENSITY OVERRIDES ======= */
    body,
    .table,
    .dropdown-menu,
    .form-control,
    .form-select,
    .btn,
    .dropdown-item,
    .page-link,
    .dataTables_wrapper,
    .search-result-sub,
    .notif-item,
    .ms-panel-subtitle,
    .ms-page-subtitle,
    .card,
    .modal-content {
      font-size: .82rem !important;
    }

    .tb-search input,
    .dataTables_wrapper .dataTables_filter input,
    .select2-container--bootstrap-5 .select2-selection,
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
      font-size: .78rem !important;
    }

    h1, .h1 { font-size: 1.68rem !important; }
    h2, .h2 { font-size: 1.18rem !important; }
    h3, .h3 { font-size: .98rem !important; }
    h4, .h4 { font-size: .88rem !important; }
    h5, .h5 { font-size: .82rem !important; }
    h6, .h6 { font-size: .76rem !important; }

    .main {
      padding: 1.2rem 1.85rem !important;
    }

    .page-title-box h4,
    .page-header h4 {
      font-size: .92rem !important;
    }

    .breadcrumb,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label {
      font-size: .74rem !important;
    }

    .tb-btn,
    .dark-toggle,
    .tb-avatar {
      width: 33px !important;
      height: 33px !important;
      font-size: .92rem !important;
    }

    .sb-link:hover {
      background: color-mix(in srgb, var(--surface) 84%, var(--blue-lt)) !important;
      border-color: transparent !important;
      color: var(--txt) !important;
    }

    .sb-link.active {
      background: color-mix(in srgb, var(--blue) 16%, var(--surface)) !important;
      border-color: color-mix(in srgb, var(--blue) 26%, var(--border)) !important;
      color: var(--txt) !important;
      box-shadow: none !important;
    }

    .sb-link.active .sb-icon,
    .sb-link.active .sb-link-title {
      color: var(--txt) !important;
    }

    html[data-theme="dark"] .sb-link.active {
      background: color-mix(in srgb, var(--blue) 18%, var(--surface)) !important;
      border-color: color-mix(in srgb, var(--blue) 28%, var(--border)) !important;
    }

    .sidebar {
      font-size: .82rem !important;
    }

    .sidebar .sb-group-label,
    .sidebar .sb-link-title,
    .sidebar .dropdown-item,
    .sidebar .sb-user-menu-name,
    .sidebar .sb-user-menu-role {
      font-size: .8rem !important;
    }

    .sidebar .sb-label {
      font-size: .62rem !important;
    }

    .main,
    .topbar {
      font-size: .82rem !important;
    }

    .main .table,
    .main .table th,
    .main .table td,
    .main .badge,
    .main .btn,
    .main .form-control,
    .main .form-select,
    .main .dropdown-item,
    .main .dataTables_info,
    .main .page-link,
    .main .card-body,
    .main .card-header,
    .main small,
    .main .small {
      font-size: .71rem !important;
    }

    .main .card,
    .main .modal-content {
      border-radius: 16px !important;
      overflow: hidden;
    }

    .main .card-header {
      padding: .92rem 1rem !important;
      border-bottom-color: var(--border) !important;
    }

    .main .card-body {
      padding: 1rem !important;
    }

    .main .table {
      margin-bottom: 0;
      border-color: var(--border) !important;
    }

    .main .table > :not(caption) > * > * {
      padding: .78rem .88rem;
      border-bottom-color: var(--border) !important;
      vertical-align: middle;
    }

    .main .table > thead > tr > th {
      font-size: .68rem !important;
      font-weight: 600 !important;
      letter-spacing: .05em;
      text-transform: uppercase;
      color: var(--txt-3) !important;
      white-space: nowrap;
    }

    .main .table > tbody > tr > td {
      color: var(--txt-2) !important;
    }

    .main .table > tbody > tr > td .text-muted,
    .main .table > tbody > tr > td small,
    .main .table > tbody > tr > td .small {
      color: var(--txt-3) !important;
    }

    .main .form-label {
      font-size: .72rem !important;
      font-weight: 600;
      letter-spacing: -.01em;
      color: var(--txt-2) !important;
      margin-bottom: .42rem;
    }

    .tb-search input,
    .form-control,
    .form-select,
    .select2-container--bootstrap-5 .select2-selection,
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
      background: var(--surface-2) !important;
      border-color: var(--border) !important;
      color: var(--txt) !important;
      box-shadow: none !important;
    }

    .form-control,
    .form-select,
    .select2-container--bootstrap-5 .select2-selection,
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
      min-height: 38px !important;
      border-radius: 11px !important;
      padding-top: .42rem !important;
      padding-bottom: .42rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered,
    .select2-container--bootstrap-5 .select2-selection__rendered {
      line-height: 1.25 !important;
    }

    .tb-search input::placeholder,
    .form-control::placeholder {
      color: var(--txt-3) !important;
    }

    .bg-white,
    .bg-light,
    .list-group-item,
    .modal-content,
    .dropdown-menu,
    .offcanvas,
    .offcanvas-header,
    .offcanvas-body {
      background: var(--surface) !important;
      color: var(--txt) !important;
      border-color: var(--border) !important;
    }

    .text-dark,
    .text-body,
    .text-body-emphasis {
      color: var(--txt) !important;
    }

    .text-secondary,
    .text-muted,
    .text-body-secondary {
      color: var(--txt-3) !important;
    }

    .table-light,
    .table > thead,
    .table > thead th {
      background: transparent !important;
      color: var(--txt-3) !important;
      border-color: var(--border) !important;
    }

    .alert-success,
    .alert-danger,
    .alert-warning,
    .alert-info {
      border-width: 1px !important;
      border-style: solid !important;
    }

    .alert-success {
      background-color: color-mix(in srgb, var(--nk-success) 10%, var(--surface)) !important;
      border-color: color-mix(in srgb, var(--nk-success) 24%, var(--border)) !important;
      color: color-mix(in srgb, var(--nk-success) 70%, var(--txt)) !important;
    }

    .alert-danger {
      background-color: color-mix(in srgb, var(--nk-danger) 10%, var(--surface)) !important;
      border-color: color-mix(in srgb, var(--nk-danger) 24%, var(--border)) !important;
      color: color-mix(in srgb, var(--nk-danger) 70%, var(--txt)) !important;
    }

    .alert-warning {
      background-color: color-mix(in srgb, var(--nk-warning) 10%, var(--surface)) !important;
      border-color: color-mix(in srgb, var(--nk-warning) 24%, var(--border)) !important;
      color: color-mix(in srgb, var(--nk-warning) 72%, var(--txt)) !important;
    }

    .alert-info {
      background-color: color-mix(in srgb, var(--nk-info) 10%, var(--surface)) !important;
      border-color: color-mix(in srgb, var(--nk-info) 24%, var(--border)) !important;
      color: color-mix(in srgb, var(--nk-info) 72%, var(--txt)) !important;
    }

    .tb-btn,
    .dark-toggle,
    .tb-avatar,
    .sb-collapse-btn {
      background: var(--surface-2) !important;
      border-color: var(--border) !important;
      color: var(--txt-2) !important;
      box-shadow: none !important;
    }

    .tb-btn:hover,
    .dark-toggle:hover,
    .sb-collapse-btn:hover {
      background: var(--surface) !important;
      border-color: color-mix(in srgb, var(--blue) 22%, var(--border)) !important;
      color: var(--txt) !important;
    }

    .tb-avatar {
      color: #fff !important;
      background: var(--blue) !important;
      border-color: transparent !important;
    }

    .btn,
    .ms-btn-primary,
    .ms-btn-secondary {
      min-height: 36px;
      border-radius: 10px !important;
      padding: .46rem .8rem !important;
      font-weight: 500 !important;
    }

    .btn-primary,
    .ms-btn-primary {
      background: var(--blue) !important;
      border-color: var(--blue) !important;
      color: #fff !important;
      box-shadow: none !important;
    }

    .btn-outline-primary,
    .ms-btn-secondary {
      background: var(--surface-2) !important;
      border-color: var(--border) !important;
      color: var(--txt) !important;
    }

    /* ── NKPager: custom pagination, zero Bootstrap dependency ── */
    .nk-pager-wrap {
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 8px; padding: 0.6rem 1rem;
    }
    .nk-pager-info {
      display: none !important; /* Disembunyikan agar tidak dobel dengan teks manual */
    }
    .nk-pager {
      display: flex; align-items: center; gap: 0.5rem; justify-content: flex-end; width: 100%;
    }
    @media (min-width: 576px) { .nk-pager { width: auto; } }
    .nk-pager-nav, .nk-pager-num {
      display: inline-flex !important;
      align-items: center;
      justify-content: center;
      min-width: 32px; height: 32px;
      padding: 0 0.5rem !important;
      border: 1px solid var(--border) !important;
      background: var(--surface) !important;
      color: var(--txt) !important;
      border-radius: 8px !important;
      font-size: 0.85rem !important;
      font-weight: 600;
      transition: all 0.15s;
      text-decoration: none !important;
    }
    .nk-pager-nav:hover:not(.disabled), .nk-pager-num:hover:not(.disabled) {
      background: var(--bg) !important;
      border-color: var(--txt-3) !important;
    }
    .nk-pager-num { font-variant-numeric: tabular-nums; }
    .nk-pager-num.active {
      background: var(--blue, #2563eb) !important;
      color: white !important;
      border-color: var(--blue, #2563eb) !important;
      font-weight: 600; cursor: default;
    }
    .nk-pager-nav.disabled, .nk-pager-num.disabled {
      opacity: 0.5; cursor: not-allowed; pointer-events: none;
      background: var(--bg) !important;
    }

    /* ── Table flat: no outer box, only row separators ── */
    .table-flat {
      border-collapse: collapse !important;
      width: 100%;
      --bs-table-border-color: transparent;
    }
    .table-flat thead th {
      background: transparent !important;
      font-size: .7rem; font-weight: 600; text-transform: uppercase;
      letter-spacing: .05em; color: var(--txt-3) !important;
      border-bottom: 1px solid var(--border) !important;
      border-top: none !important; padding: .65rem 1rem;
    }
    .table-flat tbody td {
      border-top: none !important;
      border-bottom: 1px solid var(--border) !important;
      border-left: none !important; border-right: none !important;
      padding: .6rem 1rem; font-size: .8125rem; color: var(--txt);
      vertical-align: middle;
    }
    .table-flat tbody tr:last-child td { border-bottom: none !important; }
    .table-flat tbody tr:hover td { background: var(--surface-2) !important; }
    .table-flat tfoot td {
      border-top: 1px solid var(--border) !important;
      border-bottom: none !important; font-weight: 600;
      padding: .75rem 1rem;
    }

    /* ── NK Badge (shadcn style) ── */
    .nk-badge {
      display: inline-flex; align-items: center; gap: .25rem;
      font-size: .7rem; font-weight: 500; white-space: nowrap;
      border-radius: 999px; padding: .15rem .6rem; line-height: 1.5;
      border: 1px solid currentColor;
      background: transparent; color: #52525b;
      border-color: #d4d4d8;
    }
    .nk-badge-sm { font-size: .65rem; padding: .1rem .45rem; }
    /* shadcn color variants — light */
    .nk-badge-green  { color: #16a34a; border-color: #bbf7d0; background: #f0fdf4; }
    .nk-badge-red    { color: #dc2626; border-color: #fecaca; background: #fef2f2; }
    .nk-badge-orange { color: #ea580c; border-color: #fed7aa; background: #fff7ed; }
    .nk-badge-blue   { color: #2563eb; border-color: #bfdbfe; background: #eff6ff; }
    .nk-badge-purple { color: #7c3aed; border-color: #ddd6fe; background: #f5f3ff; }
    .nk-badge-yellow { color: #ca8a04; border-color: #fef08a; background: #fefce8; }
    .nk-badge-cyan   { color: #0891b2; border-color: #a5f3fc; background: #ecfeff; }
    /* dark */
    [data-theme="dark"] .nk-badge                { color: #a1a1aa; border-color: #3f3f46; background: transparent; }
    [data-theme="dark"] .nk-badge-green          { color: #4ade80; border-color: #166534; background: rgba(74,222,128,.08); }
    [data-theme="dark"] .nk-badge-red            { color: #f87171; border-color: #991b1b; background: rgba(248,113,113,.08); }
    [data-theme="dark"] .nk-badge-orange         { color: #fb923c; border-color: #9a3412; background: rgba(251,146,60,.08); }
    [data-theme="dark"] .nk-badge-blue           { color: #60a5fa; border-color: #1e40af; background: rgba(96,165,250,.08); }
    [data-theme="dark"] .nk-badge-purple         { color: #a78bfa; border-color: #5b21b6; background: rgba(167,139,250,.08); }
    [data-theme="dark"] .nk-badge-yellow         { color: #facc15; border-color: #713f12; background: rgba(250,204,21,.08); }
    [data-theme="dark"] .nk-badge-cyan           { color: #22d3ee; border-color: #0e7490; background: rgba(34,211,238,.08); }

    /* ── Table: no border + selectable ── */
    .table-selectable tbody tr {
      cursor: pointer;
      transition: background .12s;
    }
    .table-selectable tbody tr:hover td {
      background: color-mix(in srgb, var(--blue) 5%, var(--surface)) !important;
    }
    .table-selectable tbody tr.row-selected td {
      background: color-mix(in srgb, var(--blue) 9%, var(--surface)) !important;
    }
    .table-selectable tbody tr.row-selected td:first-child {
      box-shadow: inset 2px 0 0 var(--blue);
    }

    /* ── Table: force remove all borders for table-borderless ── */
    .table-borderless > :not(caption) > * > * {
      border-bottom-width: 0 !important;
      border-top-width: 0 !important;
      border-width: 0 !important;
    }
    .table-borderless thead th {
      border-bottom: 1px solid var(--border) !important;
    }

    .dropdown-item:hover,
    .dropdown-item:focus {
      background: var(--surface-2) !important;
      color: var(--txt) !important;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
      margin-top: .6rem;
    }

    .dataTables_wrapper .dataTables_filter input {
      min-height: 36px !important;
      border-radius: 11px !important;
    }

    .select2-dropdown,
    .select2-results__option,
    .select2-search--dropdown .select2-search__field {
      background: var(--surface) !important;
      color: var(--txt) !important;
      border-color: var(--border) !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected],
    .select2-container--default .select2-results__option--highlighted[data-selected] {
      background: var(--blue-lt) !important;
      color: var(--txt) !important;
    }

    .workspace-shell {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 22px;
      overflow: hidden;
    }

    .workspace-shell .topbar,
    .workspace-shell .main,
    .workspace-shell .layout-footer {
      margin-left: 0 !important;
      border: none !important;
      border-radius: 0 !important;
      background: transparent !important;
      box-shadow: none !important;
    }

    .workspace-shell .topbar {
      border-bottom: none !important;
      padding-left: 1.1rem;
      padding-right: 1.1rem;
    }

    .workspace-shell .main {
      min-height: calc(100vh - 130px);
    }

    .workspace-shell .layout-footer {
      border-top: none !important;
      padding-top: .85rem;
      padding-bottom: .95rem;
      display: none !important;
    }

    html[data-theme="dark"] .workspace-shell .topbar,
    html[data-theme="dark"] .workspace-shell .main,
    html[data-theme="dark"] .workspace-shell .layout-footer {
      box-shadow: none !important;
    }

    .sb-collapse-btn {
      display: none !important;
    }

    @media (min-width: 992px) {
      body {
        overflow: hidden;
      }

      .workspace-shell {
        position: fixed;
        top: 8px;
        right: 10px;
        bottom: 10px;
        left: calc(var(--sb-w) - 1px);
        margin: 0;
        display: flex;
        flex-direction: column;
      }

      .workspace-shell .topbar {
        flex: 0 0 56px;
      }

      .workspace-shell .main {
        flex: 1 1 auto;
        min-height: 0;
        overflow: auto;
        overscroll-behavior: contain;
      }

      .workspace-shell .layout-footer {
        flex: 0 0 auto;
      }

      html[data-sidebar="collapsed"] .sidebar,
      html[data-sidebar="collapsed"] .topbar,
      html[data-sidebar="collapsed"] .main,
      html[data-sidebar="collapsed"] .layout-footer {
        width: auto !important;
        margin-left: var(--sb-w) !important;
      }

      html[data-sidebar="collapsed"] .sidebar {
        width: var(--sb-w) !important;
      }

      html[data-sidebar="collapsed"] .sb-body,
      html[data-sidebar="collapsed"] .sb-foot {
        padding-left: .75rem !important;
        padding-right: .75rem !important;
      }

      html[data-sidebar="collapsed"] .sb-label,
      html[data-sidebar="collapsed"] .sb-link-copy,
      html[data-sidebar="collapsed"] .sb-user-name,
      html[data-sidebar="collapsed"] .sb-user-role {
        display: initial !important;
      }

      html[data-sidebar="collapsed"] .sb-link {
        justify-content: flex-start !important;
      }
    }

    @media (max-width: 991.98px) {
      body {
        overflow: auto;
      }

      .workspace-shell {
        margin: 0;
        position: static;
        min-height: auto;
      }

      .workspace-shell .topbar,
      .workspace-shell .main,
      .workspace-shell .layout-footer {
        border-left: none;
        border-right: none;
        border-radius: 0;
      }

      .workspace-shell .topbar {
        border-top: none;
      }

      .workspace-shell .layout-footer {
        border-bottom: none;
      }
    }

    /* ===== FINAL CONFLICT SHIELD ===== */
    .workspace-shell .main .card,
    .workspace-shell .main .bg-white,
    .workspace-shell .main .bg-light,
    .workspace-shell .main .modal-content,
    .workspace-shell .main .dropdown-menu,
    .workspace-shell .main .offcanvas,
    .workspace-shell .main .list-group-item {
      background: var(--surface);
      color: var(--txt);
      border-color: var(--border);
    }

    .workspace-shell .main .table,
    .workspace-shell .main .table td,
    .workspace-shell .main .table th,
    .workspace-shell .main .dataTables_wrapper,
    .workspace-shell .main .dataTables_wrapper .dataTables_info,
    .workspace-shell .main .dataTables_wrapper .dataTables_length,
    .workspace-shell .main .dataTables_wrapper .dataTables_filter {
      color: var(--txt);
    }

    .workspace-shell .main .table > :not(caption) > * > * {
      background: transparent !important;
      border-color: var(--border) !important;
      box-shadow: none !important;
    }

    .workspace-shell .main .table thead th,
    .workspace-shell .main .table-light th,
    .workspace-shell .main .table-light td {
      color: var(--txt-2) !important;
      background: transparent !important;
      border-color: var(--border) !important;
    }

    .workspace-shell .main .table-responsive,
    .workspace-shell .main .dataTables_scrollBody,
    .workspace-shell .main .dataTables_scrollHead {
      border-color: var(--border);
      background: transparent;
    }

    .workspace-shell .main .form-control,
    .workspace-shell .main .form-select,
    .workspace-shell .main .input-group-text,
    .workspace-shell .main .select2-container--default .select2-selection--single,
    .workspace-shell .main .select2-container--default .select2-selection--multiple {
      background: var(--surface) !important;
      color: var(--txt) !important;
      border-color: var(--border) !important;
      box-shadow: none !important;
    }

    .workspace-shell .main .select2-dropdown,
    .workspace-shell .main .select2-results__option,
    .workspace-shell .main .select2-search__field {
      background: var(--surface) !important;
      color: var(--txt) !important;
      border-color: var(--border) !important;
    }

    .workspace-shell .main .text-muted,
    .workspace-shell .main small,
    .workspace-shell .main .form-text,
    .workspace-shell .main .text-secondary {
      color: var(--txt-3) !important;
    }

    .workspace-shell .main code,
    .workspace-shell .main pre,
    .workspace-shell .main .badge,
    .workspace-shell .main .badge-status,
    .workspace-shell .main .nav-tabs,
    .workspace-shell .main .nav-tabs .nav-link,
    .workspace-shell .main .ms-data-table th,
    .workspace-shell .main .ms-data-table td,
    .workspace-shell .main .dataTables_wrapper .dataTables_paginate .paginate_button,
    .workspace-shell .main .dataTables_wrapper .dataTables_filter input,
    .workspace-shell .main .dataTables_wrapper .dataTables_length select {
      border-color: var(--border) !important;
    }

    .workspace-shell .main code,
    .workspace-shell .main pre {
      background: var(--surface-2) !important;
      color: var(--blue) !important;
    }

    .workspace-shell .main .nav-tabs {
      border-bottom-color: var(--border) !important;
    }

    .workspace-shell .main .nav-tabs .nav-link {
      color: var(--txt-3) !important;
      background: transparent !important;
    }

    .workspace-shell .main .nav-tabs .nav-link.active,
    .workspace-shell .main .nav-tabs .nav-link:hover {
      color: var(--txt) !important;
      background: var(--surface) !important;
    }

    .workspace-shell .main .ms-data-table th,
    .workspace-shell .main .ms-data-table td {
      background: transparent !important;
      color: var(--txt-2) !important;
    }

    .workspace-shell .main .ms-data-table th {
      color: var(--txt-3) !important;
    }

    .workspace-shell .main .dataTables_wrapper .dataTables_filter input,
    .workspace-shell .main .dataTables_wrapper .dataTables_length select {
      background: var(--surface) !important;
      color: var(--txt) !important;
    }

    .workspace-shell .main .ms-panel,
    .workspace-shell .main .ms-table-shell,
    .workspace-shell .main .ms-detail-card,
    .workspace-shell .main .ms-stat-card,
    .workspace-shell .main .ms-chip,
    .workspace-shell .main .ms-kpi-chip {
      background: var(--surface) !important;
      border-color: var(--border) !important;
      color: var(--txt) !important;
    }

    .workspace-shell .main .ms-stat-meta,
    .workspace-shell .main .ms-panel-subtitle,
    .workspace-shell .main .ms-detail-note,
    .workspace-shell .main .ms-detail-label,
    .workspace-shell .main .ms-chip,
    .workspace-shell .main .ms-kpi-chip {
      color: var(--txt-3) !important;
    }

    .workspace-shell .main .badge-status.badge-active {
      background: color-mix(in srgb, var(--nk-success) 12%, var(--surface)) !important;
      color: color-mix(in srgb, var(--nk-success) 82%, var(--txt)) !important;
      border-color: color-mix(in srgb, var(--nk-success) 24%, var(--border)) !important;
    }

    .workspace-shell .main .badge-status.badge-inactive,
    .workspace-shell .main .badge-status.badge-warning {
      background: color-mix(in srgb, var(--border) 50%, var(--surface)) !important;
      color: var(--txt-3) !important;
      border-color: var(--border) !important;
    }

    .workspace-shell .main .badge-status.badge-pending {
      background: color-mix(in srgb, var(--nk-warning) 12%, var(--surface)) !important;
      color: color-mix(in srgb, var(--nk-warning) 84%, var(--txt)) !important;
      border-color: color-mix(in srgb, var(--nk-warning) 24%, var(--border)) !important;
    }

    .workspace-shell .main .badge-status.badge-failed,
    .workspace-shell .main .badge-status.badge-danger {
      background: color-mix(in srgb, var(--nk-danger) 12%, var(--surface)) !important;
      color: color-mix(in srgb, var(--nk-danger) 82%, var(--txt)) !important;
      border-color: color-mix(in srgb, var(--nk-danger) 24%, var(--border)) !important;
    }

    .workspace-shell .main .ms-table-shell {
      overflow: hidden;
    }

    .workspace-shell .main .ms-table-shell .table-responsive {
      overflow-x: auto !important;
      overflow-y: hidden !important;
      -webkit-overflow-scrolling: touch;
    }

    .workspace-shell .main .table.ms-table-wide,
    .workspace-shell .main .dataTables_scrollHeadInner > table,
    .workspace-shell .main .dataTables_scrollBody > table {
      width: max-content !important;
      min-width: 100% !important;
      table-layout: auto !important;
      border-collapse: separate;
      border-spacing: 0;
    }

    .workspace-shell .main .table.ms-table-wide th,
    .workspace-shell .main .table.ms-table-wide td,
    .workspace-shell .main .dataTables_scrollHeadInner > table th,
    .workspace-shell .main .dataTables_scrollBody > table td {
      white-space: normal !important;
      word-break: normal !important;
      overflow: visible !important;
      text-overflow: clip !important;
      vertical-align: top !important;
    }

    .workspace-shell .main .table td code,
    .workspace-shell .main .table .badge-status,
    .workspace-shell .main .table .ms-chip,
    .workspace-shell .main .table .ms-kpi-chip {
      white-space: nowrap !important;
    }

    .workspace-shell .main .dataTables_wrapper .d-flex.justify-content-between,
    .workspace-shell .main .dataTables_wrapper .d-flex.justify-content-between.align-items-center,
    .workspace-shell .main .dataTables_wrapper .d-flex.justify-content-between.align-items-center.mb-3,
    .workspace-shell .main .dataTables_wrapper .d-flex.justify-content-between.align-items-center.mt-3 {
      gap: 12px !important;
      flex-wrap: wrap !important;
    }

    .workspace-shell .main .dataTables_wrapper .dataTables_filter,
    .workspace-shell .main .dataTables_wrapper .dataTables_length,
    .workspace-shell .main .dataTables_wrapper .dataTables_info,
    .workspace-shell .main .dataTables_wrapper .dataTables_paginate {
      float: none !important;
      width: auto !important;
      margin: 0 !important;
    }

    .workspace-shell .main .alert {
      border-color: var(--border);
    }

    /* ======= FINAL CONSISTENCY PASS: TABLE / DASHBOARD / PAGINATION ======= */
    .workspace-shell .main {
      --nk-table-cell-x: 14px;
      --nk-table-cell-y: 11px;
      --nk-page-size: 34px;
      --nk-page-radius: 10px;
      --nk-page-font: .79rem;
    }

    .workspace-shell .main .ms-table-shell,
    .workspace-shell .main .ops-panel,
    .workspace-shell .main .card {
      max-width: 100%;
    }

    .workspace-shell .main .ms-table-shell {
      border-radius: 16px !important;
      border: 1px solid var(--border) !important;
      background: var(--surface) !important;
      overflow: hidden !important;
    }

    .workspace-shell .main .ms-panel > .ms-table-shell {
      padding: 0 !important;
      margin: 0 !important;
      border: 0 !important;
      border-top: 1px solid var(--border) !important;
      border-radius: 0 0 18px 18px !important;
      background: transparent !important;
      box-shadow: none !important;
    }

    .workspace-shell .main .ms-panel > .ms-table-shell > .table-responsive,
    .workspace-shell .main .ms-panel > .ms-table-shell .table-responsive {
      margin-top: 0 !important;
      border: 0 !important;
      border-radius: 0 0 18px 18px !important;
      background: transparent !important;
      box-shadow: none !important;
    }

    .workspace-shell .main .ms-panel > .ms-table-shell .dataTables_wrapper {
      padding: 0 !important;
    }

    .workspace-shell .main .ms-table-shell > .table-responsive,
    .workspace-shell .main .card .table-responsive,
    .workspace-shell .main .ops-table-wrap,
    .workspace-shell .main .table-responsive {
      width: 100%;
      overflow-x: auto !important;
      overflow-y: hidden !important;
      -webkit-overflow-scrolling: touch;
      scrollbar-width: thin;
    }

    .workspace-shell .main .ms-table-shell .table,
    .workspace-shell .main .table.ms-table-wide,
    .workspace-shell .main .table.dataTable,
    .workspace-shell .main .dataTables_scrollHeadInner > table,
    .workspace-shell .main .dataTables_scrollBody > table {
      width: max-content !important;
      min-width: 100% !important;
      margin: 0 !important;
      table-layout: auto !important;
      border-collapse: separate !important;
      border-spacing: 0 !important;
    }

    .workspace-shell .main .ms-table-shell .table > :not(caption) > * > *,
    .workspace-shell .main .table.dataTable > :not(caption) > * > *,
    .workspace-shell .main .ops-table > * > * > * {
      padding: var(--nk-table-cell-y) var(--nk-table-cell-x) !important;
      vertical-align: middle !important;
    }

    .workspace-shell .main .ms-table-shell .table thead th,
    .workspace-shell .main .table.dataTable thead th,
    .workspace-shell .main .ops-table th {
      white-space: nowrap !important;
      line-height: 1.25 !important;
      font-size: .69rem !important;
      font-weight: 650 !important;
      letter-spacing: .07em !important;
      text-transform: uppercase !important;
      color: var(--txt-3) !important;
      background: color-mix(in srgb, var(--surface-2) 76%, var(--surface)) !important;
      border-bottom: 1px solid var(--border) !important;
    }

    .workspace-shell .main .ms-table-shell .table tbody td,
    .workspace-shell .main .table.dataTable tbody td,
    .workspace-shell .main .ops-table td {
      white-space: normal !important;
      word-break: normal !important;
      line-height: 1.42 !important;
      font-size: .83rem !important;
      color: var(--txt-2) !important;
      border-top: 0 !important;
      border-bottom: 1px solid var(--border) !important;
      vertical-align: middle !important;
    }

    .workspace-shell .main .ms-table-shell .table tbody tr:last-child td,
    .workspace-shell .main .table.dataTable tbody tr:last-child td,
    .workspace-shell .main .ops-table tbody tr:last-child td {
      border-bottom-color: transparent !important;
    }

    .workspace-shell .main .ms-table-shell .table td .btn,
    .workspace-shell .main .ms-table-shell .table td .btn-group,
    .workspace-shell .main .ms-table-shell .table td .badge-status,
    .workspace-shell .main .ms-table-shell .table td code,
    .workspace-shell .main .ms-table-shell .table td .ms-chip,
    .workspace-shell .main .ms-table-shell .table td .ms-kpi-chip {
      white-space: nowrap !important;
    }

    .workspace-shell .main .dataTables_wrapper {
      padding: 0 !important;
    }

    .workspace-shell .main .dataTables_wrapper .row {
      --bs-gutter-x: 0;
      row-gap: 10px;
    }

    .workspace-shell .main .dataTables_wrapper .dataTables_length,
    .workspace-shell .main .dataTables_wrapper .dataTables_filter,
    .workspace-shell .main .dataTables_wrapper .dataTables_info,
    .workspace-shell .main .dataTables_wrapper .dataTables_paginate {
      display: flex !important;
      align-items: center;
      gap: 8px;
      min-height: 34px;
    }

    .workspace-shell .main .dataTables_wrapper .dataTables_filter,
    .workspace-shell .main .dataTables_wrapper .dataTables_paginate {
      justify-content: flex-end;
    }

    .workspace-shell .main .dataTables_wrapper .dataTables_length label,
    .workspace-shell .main .dataTables_wrapper .dataTables_filter label {
      display: flex !important;
      align-items: center;
      gap: 8px;
      margin: 0 !important;
      font-size: .77rem !important;
      color: var(--txt-3) !important;
    }

    .workspace-shell .main .dataTables_wrapper .dataTables_filter input,
    .workspace-shell .main .dataTables_wrapper .dataTables_length select {
      height: 34px !important;
      min-height: 34px !important;
      border-radius: 10px !important;
      font-size: .79rem !important;
      padding-top: 0 !important;
      padding-bottom: 0 !important;
    }

    .workspace-shell .main .dataTables_wrapper .dataTables_length select,
    .workspace-shell .main .dataTables_wrapper .dataTables_length select option,
    .workspace-shell .main .form-select,
    .workspace-shell .main .form-select option,
    .workspace-shell .main .form-select optgroup {
      background: var(--surface) !important;
      color: var(--txt) !important;
    }
    .workspace-shell .main .form-select optgroup {
      font-weight: 600;
      color: var(--txt-3) !important;
    }

    .workspace-shell .main .pagination,
    .workspace-shell .main .dataTables_wrapper .dataTables_paginate {
      display: flex !important;
      align-items: center;
      flex-wrap: wrap;
      gap: 6px !important;
    }

    .workspace-shell .main .dataTables_wrapper .dataTables_paginate span {
      display: contents !important;
    }

    /* ── DataTables + Bootstrap5 pagination → shadcn style ── */
    /* Reset Bootstrap CSS vars on ALL pagination containers */
    .workspace-shell .main .pagination,
    .workspace-shell .main .dataTables_wrapper .pagination {
    .workspace-shell .main .dataTables_wrapper .dataTables_info,
    .workspace-shell .main .pagination-meta,
    .workspace-shell .main .dataTables_wrapper .dataTables_length,
    .workspace-shell .main .dataTables_wrapper .dataTables_filter {
      font-size: .77rem !important;
      color: var(--txt-3) !important;
    }

    .workspace-shell .main .dashboard-page .ops-panel,
    .workspace-shell .main .dashboard-page .ops-kpi,
    .workspace-shell .main .dashboard-page .ops-network-card {
      border-radius: 16px !important;
      overflow: hidden;
    }

    .workspace-shell .main .dashboard-page .ops-kpis,
    .workspace-shell .main .dashboard-page .ops-network-grid {
      align-items: stretch;
    }

    .workspace-shell .main .dashboard-page .ops-table-wrap {
      padding-bottom: 2px;
    }

    .workspace-shell .main .dashboard-page .ops-kpi-icon,
    .workspace-shell .main .dashboard-page .ops-pill i,
    .workspace-shell .main .dashboard-page .ops-quick-link i,
    .workspace-shell .main .dashboard-page .ops-network-card .ops-kpi-icon {
      color: var(--txt-2) !important;
    }

    .workspace-shell .main .dashboard-page .ops-kpi-icon i,
    .workspace-shell .main .dashboard-page .ops-pill i,
    .workspace-shell .main .dashboard-page .ops-quick-link i {
      display: inline-flex !important;
      align-items: center;
      justify-content: center;
      font-size: 1rem !important;
      line-height: 1 !important;
      opacity: 1 !important;
      color: inherit !important;
      font-family: 'boxicons' !important;
      font-style: normal !important;
      font-weight: 400 !important;
    }

    @media (max-width: 991.98px) {
      .workspace-shell .main {
        --nk-table-cell-x: 12px;
        --nk-table-cell-y: 10px;
        --nk-page-size: 32px;
      }

      .workspace-shell .main .dataTables_wrapper .dataTables_filter,
      .workspace-shell .main .dataTables_wrapper .dataTables_paginate {
        justify-content: flex-start;
      }
    }

    /* ── Command Palette ── */
    .cmd-backdrop {
      position: fixed;
      inset: 0;
      z-index: 9998;
      background: rgba(0,0,0,.5);
      backdrop-filter: blur(3px);
      -webkit-backdrop-filter: blur(3px);
    }
    .cmd-palette {
      position: fixed;
      top: 12vh;
      left: 50%;
      transform: translateX(-50%);
      z-index: 9999;
      width: min(640px, 94vw);
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 16px;
      box-shadow: 0 24px 56px rgba(0,0,0,.28), 0 4px 16px rgba(0,0,0,.14);
      overflow: hidden;
    }
    .cmd-header {
      display: flex;
      align-items: center;
      gap: .75rem;
      padding: .875rem 1.125rem;
      border-bottom: 1px solid var(--border);
    }
    .cmd-header-icon {
      color: var(--txt-3);
      font-size: 1.125rem;
      flex-shrink: 0;
    }
    .cmd-input {
      flex: 1;
      background: transparent !important;
      border: none !important;
      outline: none !important;
      box-shadow: none !important;
      font-size: .9375rem;
      font-family: inherit;
      color: var(--txt) !important;
      padding: 0 !important;
    }
    .cmd-input::placeholder { color: var(--txt-3) !important; }
    .cmd-kbd {
      background: var(--bg);
      border: 1px solid var(--border);
      border-radius: 5px;
      padding: .1rem .35rem;
      font-size: .6875rem;
      color: var(--txt-3);
      font-family: inherit;
      flex-shrink: 0;
    }
    .cmd-body {
      max-height: 420px;
      overflow-y: auto;
    }
    .cmd-group-label {
      padding: .5rem 1.125rem .25rem;
      font-size: .6875rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: var(--txt-3);
    }
    .cmd-item {
      display: flex;
      align-items: center;
      gap: .75rem;
      padding: .6rem 1.125rem;
      text-decoration: none !important;
      color: var(--txt) !important;
      cursor: pointer;
      transition: background .08s;
      border-radius: 0;
    }
    .cmd-item:hover, .cmd-item.is-active {
      background: var(--blue-lt, rgba(91,99,211,.1));
      color: var(--blue) !important;
    }
    .cmd-item-icon {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      background: var(--bg);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .9375rem;
      color: var(--blue);
      flex-shrink: 0;
    }
    .cmd-item:hover .cmd-item-icon, .cmd-item.is-active .cmd-item-icon {
      background: var(--blue);
      color: #fff;
    }
    .cmd-item-title {
      font-size: .8125rem;
      font-weight: 500;
      line-height: 1.3;
    }
    .cmd-item-sub {
      font-size: .6875rem;
      color: var(--txt-3);
      line-height: 1.3;
    }
    .cmd-item:hover .cmd-item-sub, .cmd-item.is-active .cmd-item-sub { color: var(--blue); opacity: .7; }
    .cmd-empty {
      padding: 2.5rem 1rem;
      text-align: center;
      color: var(--txt-3);
      font-size: .8125rem;
    }
    .cmd-empty i { font-size: 2rem; display: block; margin-bottom: .5rem; opacity: .5; }
    .cmd-loading {
      padding: 1.5rem 1rem;
      text-align: center;
      color: var(--txt-3);
      font-size: .8125rem;
    }
    .cmd-footer {
      display: flex;
      align-items: center;
      gap: 1.25rem;
      padding: .5rem 1.125rem;
      border-top: 1px solid var(--border);
      font-size: .6875rem;
      color: var(--txt-3);
    }
    .cmd-footer kbd {
      background: var(--surface-2);
      border: 1px solid var(--border);
      border-radius: 4px;
      padding: .1rem .3rem;
      font-size: .6rem;
      font-family: inherit;
      color: var(--txt-2);
    }
    [data-theme="dark"] .cmd-backdrop { background: rgba(0,0,0,.65); }
    [data-theme="dark"] .cmd-item-icon { background: rgba(255,255,255,.06); }

    /* Dark mode: Linear-style — unified page background, no card elevation */
    [data-theme="dark"] .workspace-shell .main .ms-panel {
        background: var(--bg) !important;
        border: none !important; border-radius: 0 !important; box-shadow: none !important;
    }
    [data-theme="dark"] .workspace-shell .main .ms-panel-head { border-bottom: 1px solid var(--border) !important; }
    [data-theme="dark"] .workspace-shell .main .ms-toolbar { border-bottom: 1px solid var(--border) !important; }
    [data-theme="dark"] .workspace-shell .main .ms-table-shell {
        background: var(--bg) !important;
        border: none !important; border-radius: 0 !important; box-shadow: none !important;
    }
    [data-theme="dark"] .workspace-shell .main .ms-panel > .ms-table-shell {
        background: transparent !important;
        border-top: 1px solid var(--border) !important; border-radius: 0 !important;
    }
    [data-theme="dark"] .workspace-shell .main .ms-table-shell .table-responsive {
        border: none !important; border-radius: 0 !important; background: transparent !important;
    }
    /* thead bg same as page — override workspace-shell color-mix rule */
    [data-theme="dark"] .workspace-shell .main .ms-table-shell .table thead th,
    [data-theme="dark"] .workspace-shell .main .table.dataTable thead th {
        background: var(--bg) !important;
    }
    [data-theme="dark"] .workspace-shell .main .ms-table-shell .table tbody td { background: transparent !important; }
    [data-theme="dark"] .workspace-shell .main .ms-table-shell .table tbody tr:hover td { background: rgba(255,255,255,.04) !important; }
    /* ONT table: override workspace-shell min-width so fixed column widths work */
    .workspace-shell .main .ms-table-shell #ont-table { min-width: 0 !important; width: 1035px !important; }

    /* ============================================================
       NK-LIST-PAGE — global shared table-list page styles
       ============================================================ */
    .nk-list-page .ms-panel { border:none!important; box-shadow:none!important; background:transparent!important; border-radius:0!important; }
    .nk-list-page .ms-panel-head { border-bottom:1px solid var(--border)!important; border-radius:0!important; background:transparent!important; }
    .nk-list-page .ms-panel-body { background:transparent!important; }
    .nk-list-page .ms-table-shell { padding:0!important; border:0!important; background:transparent!important; box-shadow:none!important; }
    .nk-list-page .ms-table-shell .table-responsive { border:0!important; background:transparent!important; }
    .nk-list-page .ms-table-shell .dataTables_wrapper { padding:0!important; }
    .nk-list-page td { padding:.45rem .75rem!important; font-size:.8125rem; }
    .nk-list-page th { padding:.5rem .75rem!important; font-size:.73rem; text-transform:uppercase; letter-spacing:.4px; }
    /* Filter tabs */
    .nk-filter-tabs { display:inline-flex; align-items:center; gap:1px; }
    .nk-filter-tab { display:inline-flex; align-items:center; padding:.25rem .75rem; font-size:.78rem; font-weight:500; border-radius:6px; border:none; background:transparent; color:var(--txt-3); cursor:pointer; transition:color .12s,background .12s; white-space:nowrap; line-height:1.5; }
    .nk-filter-tab:hover { color:var(--txt); background:var(--surface-2,color-mix(in srgb,var(--surface) 70%,var(--border))); }
    .nk-filter-tab.active { color:var(--txt); font-weight:600; background:var(--surface-2,color-mix(in srgb,var(--surface) 70%,var(--border))); }
    /* Action buttons */
    .nk-action-btn { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:6px; cursor:pointer; text-decoration:none; transition:opacity .12s; border:1px solid transparent; }
    .nk-action-btn i { font-size:.9rem; }
    .nk-action-btn.view   { color:var(--blue); background:color-mix(in srgb,var(--blue) 10%,var(--surface)); border-color:color-mix(in srgb,var(--blue) 22%,var(--border)); }
    .nk-action-btn.edit   { color:var(--orange,#f97316); background:color-mix(in srgb,var(--orange,#f97316) 10%,var(--surface)); border-color:color-mix(in srgb,var(--orange,#f97316) 22%,var(--border)); }
    .nk-action-btn.pay    { color:var(--green); background:color-mix(in srgb,var(--green) 10%,var(--surface)); border-color:color-mix(in srgb,var(--green) 22%,var(--border)); }
    .nk-action-btn.delete { color:var(--red); background:color-mix(in srgb,var(--red) 10%,var(--surface)); border-color:color-mix(in srgb,var(--red) 22%,var(--border)); }
    .nk-action-btn:hover { opacity:.75; }
    /* Code badges inside tables */
    .nk-list-page table code { background:color-mix(in srgb,var(--blue) 8%,var(--surface)); color:color-mix(in srgb,var(--blue) 80%,var(--txt)); border:1px solid color-mix(in srgb,var(--blue) 18%,var(--border)); padding:2px 7px; border-radius:6px; font-size:.78rem; font-weight:600; }
    /* Table controls bar */
    .nk-table-controls { display:flex; flex-wrap:wrap; gap:.75rem; align-items:center; justify-content:space-between; padding:.6rem 0 .4rem; }
    .nk-search-wrap { position:relative; display:flex; align-items:center; }
    .nk-search-wrap i {
      position:absolute;
      left:.35rem;
      color:var(--txt-3);
      font-size:.92rem;
      pointer-events:auto;
      cursor:text;
      transition:color .15s ease;
    }
    .nk-search-input {
      height:30px;
      padding:.2rem .2rem .2rem 1.4rem;
      font-size:.81rem;
      border:none;
      border-bottom:1px solid color-mix(in srgb,var(--blue) 22%,var(--border));
      border-radius:0;
      background:transparent;
      color:var(--txt);
      outline:none;
      width:250px;
      max-width:100%;
      font-family:inherit;
      transition:border-color .15s ease, color .15s ease;
    }
    .nk-search-input:focus {
      border-color: color-mix(in srgb,var(--blue) 70%,var(--border));
      box-shadow:none;
    }
    .nk-search-input::placeholder {
      color:var(--txt-3);
    }
    .nk-search-wrap:focus-within i { color:var(--blue); }
    .nk-search-wrap.nk-table-search-trigger {
      width:auto;
      height:auto;
      border:none;
      border-radius:0;
      justify-content:center;
      cursor:pointer;
      background:transparent;
      transition:opacity .15s ease;
    }
    .nk-search-wrap.nk-table-search-trigger:hover {
      opacity:.8;
    }
    .nk-search-wrap.nk-table-search-trigger i {
      position:static;
      color:var(--blue);
      cursor:pointer;
    }
    .nk-search-wrap.nk-table-search-trigger .nk-search-input {
      display:none !important;
    }
    .nk-length-select { padding:.3rem .4rem; font-size:.8125rem; border:1px solid var(--border); border-radius:6px; background:var(--surface); color:var(--txt); outline:none; font-family:inherit; cursor:pointer; width:70px; }

    /* === COMPACT OVERRIDES: smaller dropdowns & pagination everywhere === */
    .workspace-shell .main .form-select-sm,
    .workspace-shell .main select.form-select,
    .workspace-shell .main .dataTables_wrapper select,
    .workspace-shell .main .nk-length-select {
      padding: .25rem .5rem !important;
      font-size: .75rem !important;
      min-height: 30px !important;
      height: 30px !important;
      border-radius: 6px !important;
      box-shadow: none !important;
    }
    /* Select2 compact */
    .workspace-shell .main .select2-container .select2-selection--single {
      min-height: 30px !important;
      height: 30px !important;
      font-size: .75rem !important;
      border-radius: 6px !important;
      padding-top: 0 !important;
      padding-bottom: 0 !important;
      display: flex !important;
      align-items: center !important;
    }
    .workspace-shell .main .select2-container .select2-selection__rendered {
      line-height: 28px !important;
      font-size: .75rem !important;
      padding-top: 0 !important;
      padding-bottom: 0 !important;
      padding-left: .6rem !important;
      padding-right: 1.8rem !important;
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
    }
    .workspace-shell .main .select2-container .select2-selection__arrow {
      height: 28px !important;
      width: 24px !important;
      top: 1px !important;
      right: 4px !important;
    }
    .workspace-shell .main .select2-container--bootstrap-5 .select2-dropdown {
      padding: .25rem !important;
      border-radius: 8px !important;
      margin-top: 4px !important;
      overflow: hidden !important;
      border: 1px solid color-mix(in srgb, var(--blue) 16%, var(--border)) !important;
      box-shadow: 0 10px 28px rgba(15, 23, 42, .12) !important;
    }
    .workspace-shell .main .select2-search--dropdown {
      padding: 0 0 .25rem 0 !important;
    }
    .workspace-shell .main .select2-search--dropdown .select2-search__field,
    .workspace-shell .main .select2-container--bootstrap-5 .select2-search .select2-search__field {
      min-height: 30px !important;
      height: 30px !important;
      font-size: .75rem !important;
      padding: .2rem .55rem !important;
      border-radius: 6px !important;
      margin: 0 !important;
    }
    .workspace-shell .main .select2-results > .select2-results__options {
      max-height: 260px !important;
    }
    .workspace-shell .main .select2-results__option {
      font-size: .73rem !important;
      line-height: 1.2 !important;
      padding: .34rem .5rem !important;
      border-radius: 6px !important;
      white-space: normal !important;
    }
    .workspace-shell .main .select2-container .select2-selection__placeholder {
      color: var(--txt-3) !important;
    }
    .workspace-shell .main .select2-container .select2-selection__arrow b {
      border-width: 5px 4px 0 4px !important;
    }
    /* Tables: borderless everywhere */
    .workspace-shell .main .table,
    .workspace-shell .main .table th,
    .workspace-shell .main .table td {
      border: none !important;
    }
    .workspace-shell .main .table > :not(caption) > * > * {
      border-bottom: 1px solid var(--border, #f1f5f9) !important;
      box-shadow: none !important;
    }
    .workspace-shell .main .table thead th {
      border-bottom: 1px solid var(--border, #e2e8f0) !important;
      background: transparent !important;
    }
    .workspace-shell .main .dataTables_wrapper .table {
      border-collapse: separate !important;
      border-spacing: 0 !important;
    }
    /* Conflicting pagination styles removed to allow global .pagination styles to apply */

    /* ── Design Tokens ── */
    :root {
        --nk-primary: #2563eb;
        --nk-height: 34px;
        --nk-height-sm: 30px;
        --nk-radius: 6px;
    }

    /* ── Select2 Unified ── */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: var(--nk-height) !important;
        height: var(--nk-height) !important;
        border-radius: var(--nk-radius) !important;
        font-size: .8125rem !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .select2-container--bootstrap-5.select2-container--open .select2-selection {
        border-color: var(--nk-primary) !important;
        box-shadow: 0 0 0 2px rgba(37,99,235,.12) !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        font-size: .8125rem !important;
        line-height: var(--nk-height) !important;
    }
    .select2-container--bootstrap-5 .select2-dropdown {
        border-radius: var(--nk-radius) !important;
        box-shadow: 0 4px 16px rgba(0,0,0,.1) !important;
    }
    .select2-container--bootstrap-5 .select2-results__option--highlighted:not(.select2-results__option--selected) {
        background: rgba(37,99,235,.1) !important;
        color: var(--nk-primary) !important;
    }
    .select2-container--bootstrap-5 .select2-results__option--selected {
        background: var(--nk-primary) !important;
        color: #fff !important;
    }
    [data-theme="dark"] .select2-container--bootstrap-5 .select2-selection {
        background: var(--surface) !important;
        border-color: var(--border) !important;
        color: var(--txt) !important;
    }
    [data-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: var(--txt) !important;
    }
    [data-theme="dark"] .select2-dropdown {
        background: var(--surface) !important;
        border-color: var(--border) !important;
    }
    [data-theme="dark"] .select2-search__field {
        background: var(--bg) !important;
        color: var(--txt) !important;
        border-color: var(--border) !important;
    }
    [data-theme="dark"] .select2-results__option {
        color: var(--txt) !important;
    }

    /* ── Buttons Unified ── */
    .main .btn {
        height: var(--nk-height) !important;
        border-radius: var(--nk-radius) !important;
        font-size: .8125rem !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: .3rem !important;
    }
    .main .btn-sm {
        height: var(--nk-height-sm) !important;
        font-size: .72rem !important;
        padding: .25rem .5rem !important;
    }
    .main .btn-primary { background-color: var(--nk-primary) !important; border-color: var(--nk-primary) !important; color: #fff !important; }
    .main .btn-primary:hover { background-color: #1d4ed8 !important; border-color: #1d4ed8 !important; }

    /* ── Modal Unified ── */
    .modal-content {
        border-radius: 12px !important;
        box-shadow: 0 8px 32px rgba(0,0,0,.18) !important;
        border: none !important;
    }
    .modal-header { padding: 1rem !important; border-bottom: 1px solid var(--border) !important; }
    .modal-body { padding: 1.25rem !important; }
    .modal-footer { padding: .75rem 1rem !important; border-top: 1px solid var(--border) !important; }
    [data-theme="dark"] .modal-content { background: var(--surface) !important; }

    /* ── Form Inputs Unified ── */
    .main .form-control,
    .main .form-select,
    .main select,
    .main input[type="text"],
    .main input[type="number"],
    .main input[type="date"],
    .main input[type="search"] {
        font-size: .8125rem !important;
        height: var(--nk-height) !important;
        min-height: var(--nk-height) !important;
        border-radius: var(--nk-radius) !important;
        line-height: var(--nk-height) !important;
        padding: 0 .65rem !important;
    }
    .main textarea.form-control {
        height: auto !important;
        line-height: 1.5 !important;
        padding: .5rem .65rem !important;
    }
    .main .form-select-sm,
    .main .form-control-sm {
        height: var(--nk-height-sm) !important;
        min-height: var(--nk-height-sm) !important;
        font-size: .75rem !important;
        line-height: var(--nk-height-sm) !important;
    }
    .main .form-control:focus,
    .main .form-select:focus {
        border-color: var(--nk-primary) !important;
        box-shadow: 0 0 0 2px rgba(37,99,235,.12) !important;
    }

    /* ── Global Advanced Dropdown Styles ── */
    .dropdown-menu {
        border-radius: 12px !important;
        border: 1px solid var(--border) !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
        padding: 0.5rem !important;
    }
    .dropdown-item {
        display: flex !important;
        align-items: center !important;
        gap: 0.75rem !important;
        padding: 0.4rem 0.75rem !important;
        border-radius: 6px !important;
        font-size: 0.85rem !important;
        font-weight: 500 !important;
        transition: background 0.1s !important;
    }
    .dropdown-item:hover {
        background: var(--bg) !important;
    }
    .dropdown-item i { font-size: 1.1rem; color: var(--txt-3); }
    .dropdown-item .shortcut { margin-left: auto; color: var(--txt-3); font-size: 0.75rem; font-weight: 600; }
    .dropdown-divider { border-top: 1px solid var(--border) !important; margin: 0.5rem 0 !important; }
    
    /* Submenu Global CSS */
    .dropdown-submenu { position: relative; }
    .dropdown-submenu > .dropdown-menu {
        top: 0; left: 100%; margin-top: -6px; margin-left: 0.1rem; display: none; position: absolute;
    }
    .dropdown-submenu:hover > .dropdown-menu { display: block; }
    .dropdown-submenu-caret { margin-left: auto; font-size: 1rem !important; }
    
    /* Checkbox inside Dropdown */
    .dropdown-item input[type="checkbox"] {
        accent-color: var(--blue, #2563eb);
        width: 16px; height: 16px; cursor: pointer;
    }
    
    /* Table Dropdown Toggle Styling */
    .table .dropdown-toggle.btn-light {
        background: var(--surface) !important;
        border: 1px solid var(--border) !important;
        color: var(--txt) !important;
        font-weight: 600 !important;
        padding: 0.4rem 0.8rem !important;
        border-radius: 8px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
        transition: all 0.15s !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04) !important;
    }
    .table .dropdown-toggle.btn-light:hover, 
    .table .dropdown-toggle.btn-light[aria-expanded="true"] {
        background: var(--bg) !important;
        border-color: var(--txt-3) !important;
    }

    /* ── Global Advanced Pagination Styles ── */
    .pagination { gap: 0.5rem; margin-bottom: 0; }
    .page-item .page-link,
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        min-width: 32px !important; height: 32px !important;
        padding: 0 0.5rem !important;
        border: 1px solid var(--border) !important;
        background: var(--surface) !important;
        color: var(--txt) !important;
        border-radius: 8px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        transition: all 0.15s !important;
        box-shadow: none !important;
        line-height: 1 !important;
        margin-left: 0.2rem !important;
    }
    .page-item.active .page-link,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #2563eb !important;
        color: white !important;
        border-color: #2563eb !important;
    }
    .page-item.disabled .page-link,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5 !important;
        background: var(--surface) !important;
        color: var(--txt-3) !important;
    }
    .page-item .page-link:hover:not(:disabled):not(.active),
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) {
        background: var(--bg) !important;
        border-color: var(--txt-3) !important;
        color: var(--txt) !important;
    }
    
    /* Advanced Pagination Container for DataTables Wrapper */
    .dataTables_wrapper > .d-flex.justify-content-between.align-items-center.mt-3 {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 1rem !important;
        border: 1px solid var(--border) !important;
        border-radius: 12px !important;
        background: var(--surface) !important;
        margin-top: 1.5rem !important;
    }
    .dataTables_info {
        font-size: 0.85rem !important;
        color: var(--txt-2) !important;
        font-weight: 500 !important;
        padding-top: 0 !important;
    }
    .dataTables_paginate {
        display: flex !important;
        align-items: center !important;
        gap: 0.2rem !important;
        padding-top: 0 !important;
    }
    /* Remove default Bootstrap override for first/last elements since we use gap */
    .page-item:first-child .page-link, .page-item:last-child .page-link {
        border-radius: 8px !important;
    }
    
    /* Hide Laravel's default info text to prevent duplicates with view's custom text */
    nav .d-none.flex-sm-fill.d-sm-flex > div:first-child {
        display: none !important;
    }
    nav .d-flex.justify-content-between.flex-fill.d-sm-none {
        display: none !important;
    }
    
    /* DataTables Pagination Specific Fixes */
    .dataTables_wrapper .dataTables_paginate .pagination {
        justify-content: flex-end;
        margin: 0;
    }
    .dataTables_wrapper .dataTables_info {
        font-size: 0.85rem;
        color: var(--txt-2) !important;
        font-weight: 500;
        padding-top: 0.5rem;
    }
  </style>
  @yield('styles')
  @livewireStyles
</head>

<body>
  <!-- NETKING UI VERSION 1.6 - STABILIZED SHELL -->
<script>
  (function(){
    try {
      localStorage.removeItem('nk_sb_collapsed');
    } catch (e) {}
  })();
</script>

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sb">
    <div class="sb-body">
      @include('layouts.sidebar')
    </div>
  </aside>

  <div class="workspace-shell">
  <!-- TOPBAR -->
  <header class="topbar">
    <button class="tb-burger tb-btn" onclick="sbToggle()" type="button"><i class='bx bx-menu'></i></button>
    <span class="tb-title">@yield('title', 'Dasbor')</span>
    <div class="tb-spacer"></div>

    <!-- Notifications -->
    <div class="dropdown">
      <button class="tb-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="notif-bell" aria-expanded="false">
        <i class='bx bx-bell'></i>
        <span class="notif-badge" id="notif-badge" hidden>0</span>
      </button>
      <div class="dropdown-menu dropdown-menu-end notif-menu" id="notif-dropdown">
        <div class="notif-panel-header d-flex align-items-center justify-content-between gap-2">
          <div>
            <div class="fw-semibold" style="font-size:.875rem;">Notifikasi</div>
            <div style="font-size:.7rem;color:var(--txt-3);">
              <span id="notif-count-label">Memuat notifikasi...</span>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button
              type="button"
              class="btn btn-sm p-0"
              style="font-size:.75rem;color:var(--txt-2);border:none;background:none;"
              id="notif-refresh-btn"
              title="Segarkan notifikasi"
            >
              <i class='bx bx-refresh'></i>
            </button>
            <button
              type="button"
              class="btn btn-sm p-0"
              style="font-size:.72rem;color:var(--blue);border:none;background:none;"
              id="mark-all-read"
              onclick="markAllNotifRead()"
            >
              Tandai semua dibaca
            </button>
          </div>
        </div>
        <div id="notif-list" class="notif-list">
          <div class="notif-empty">
            <i class='bx bx-loader-alt bx-spin'></i>
            <span style="font-size:.8rem;">Memuat...</span>
          </div>
        </div>
        <div class="notif-panel-footer d-flex align-items-center justify-content-between gap-2">
          <span style="font-size:.7rem;color:var(--txt-3);">Auto refresh aktif</span>
          <a href="{{ route('admin.activity-log') }}" style="font-size:.75rem;color:var(--blue);text-decoration:none;">Lihat Log Aktivitas →</a>
        </div>
      </div>
    </div>

  </header>

  <!-- MAIN CONTENT -->
  <main class="main">

    <x-admin-flash icon-set="boxicons" />

    @yield('content')

  </main>

  <footer class="layout-footer"></footer>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

  <script>
    var _sb = document.getElementById('sb');

    function sbToggle() {
      _sb && _sb.classList.toggle('open');
    }

    // Close on outside click (mobile)
    document.addEventListener('click', function(e) {
      if (window.innerWidth >= 992) return;
      if (_sb && _sb.classList.contains('open') &&
        !_sb.contains(e.target) && !e.target.closest('.tb-burger')) {
        _sb.classList.remove('open');
      }
    });

    // Auto-dismiss alerts
    setTimeout(function() {
      document.querySelectorAll('.alert.show').forEach(function(el) {
        try {
          bootstrap.Alert.getOrCreateInstance(el).close();
        } catch (e) {}
      });
    }, 5000);

    // Select2 init
    $(function() {
      if (typeof $.fn.select2 === 'undefined') return;
      function buildSelect2Options($el, extra) {
        var placeholder = $el.data('placeholder') ||
          $el.find('option[value=""]').first().text() || 'Pilih...';
        var optionCount = $el.find('option').length;
        var hideSearch = $el.hasClass('form-select-sm') || $el.is('[data-hide-search]') || optionCount <= 8;
        return Object.assign({
          theme: 'bootstrap-5',
          width: '100%',
          placeholder: placeholder,
          allowClear: $el.find('option[value=""]').length > 0,
          minimumResultsForSearch: hideSearch ? Infinity : 10
        }, extra || {});
      }

      $('.form-select').not('.no-select2').not('[data-select2-id]').each(function() {
        var $el = $(this);
        $el.select2(buildSelect2Options($el));
      });
      document.querySelectorAll('.modal').forEach(function(modal) {
        modal.addEventListener('shown.bs.modal', function() {
          $(modal).find('.form-select').not('.no-select2').not('[data-select2-id]').each(function() {
            var $el = $(this);
            $el.select2(buildSelect2Options($el, {
              dropdownParent: $(modal)
            }));
          });
        });
      });
    });
  </script>


  <!-- DataTables -->
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

  <!-- Global Confirm Dialog (SweetAlert2) -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Override native confirm() globally
    window._nkConfirm = function(msg, callback) {
      if (typeof Swal === 'undefined') { if (confirm(msg)) callback(); return; }
      Swal.fire({
        text: msg,
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#e5e7eb',
        confirmButtonText: 'Ya, lanjutkan',
        cancelButtonText: 'Batal',
        showClass: { popup: 'animate__animated animate__fadeIn animate__faster' },
        hideClass: { popup: 'animate__animated animate__fadeOut animate__faster' },
        customClass: {
          popup: 'nk-confirm-popup',
          confirmButton: 'nk-confirm-btn',
          cancelButton: 'nk-cancel-btn'
        }
      }).then(function(r) { if (r.isConfirmed) callback(); });
    };

    // Auto-attach to all forms with data-confirm
    document.querySelectorAll('form[data-confirm]').forEach(function(form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        var f = this;
        window._nkConfirm(f.dataset.confirm, function() { f.submit(); });
      });
    });

    // Override onsubmit="return confirm(...)" pattern
    document.querySelectorAll('[onclick*="confirm("]').forEach(function(el) {
      var origOnclick = el.getAttribute('onclick');
      var match = origOnclick.match(/confirm\(['"](.+?)['"]\)/);
      if (match) {
        el.removeAttribute('onclick');
        el.addEventListener('click', function(e) {
          e.preventDefault();
          window._nkConfirm(match[1], function() {
            if (el.tagName === 'A') window.location = el.href;
            else if (el.closest('form')) el.closest('form').submit();
          });
        });
      }
    });
  });
  </script>

  <style>
  .nk-confirm-popup { border-radius: 12px !important; padding: 1.5rem !important; font-family: 'Inter', sans-serif !important; max-width: 340px !important; }
  .nk-confirm-popup .swal2-icon { display: none !important; }
  .nk-confirm-popup .swal2-title { display: none !important; }
  .nk-confirm-popup .swal2-html-container { font-size: .9rem !important; font-weight: 500 !important; color: #1f2937 !important; margin: 0 !important; padding: 0 !important; }
  .nk-confirm-popup .swal2-actions { margin-top: 1.25rem !important; gap: .5rem !important; }
  .nk-confirm-btn { font-size: .8125rem !important; padding: .5rem 1.25rem !important; border-radius: 8px !important; font-weight: 600 !important; }
  .nk-cancel-btn { font-size: .8125rem !important; padding: .5rem 1.25rem !important; border-radius: 8px !important; font-weight: 500 !important; color: #374151 !important; background: #f3f4f6 !important; border: 1px solid #e5e7eb !important; }
  .nk-cancel-btn:hover { background: #e5e7eb !important; }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Flatpickr -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <!-- ApexCharts -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <!-- Dropzone -->
  <script src="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.js"></script>
  <!-- Toastr -->
  <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
  <!-- CountUp.js -->
  <script src="https://cdn.jsdelivr.net/npm/countup.js@2.8.0/dist/countUp.umd.js"></script>
  <!-- NProgress -->
  <script src="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.min.js"></script>

  @livewireScripts
  @yield('scripts')
  @stack('scripts')

  <script>
    /* ── Global Initializers ── */

    window.nkNotify = function(type, title, message) {
      if (window.Swal) {
        var accent = {
          success: '#22c55e',
          error: '#ef4444',
          warning: '#f59e0b',
          info: '#3b82f6'
        };

        Swal.fire({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 4200,
          timerProgressBar: true,
          customClass: {
            popup: 'nk-toast-popup'
          },
          background: '#ffffff',
          color: '#0f172a',
          html:
            '<div class="nk-toast-shell nk-toast-' + type + '">' +
              '<div class="nk-toast-title">' + String(title || '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</div>' +
              '<div class="nk-toast-message">' + String(message || '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</div>' +
            '</div>',
          didOpen: function(toast) {
            toast.style.borderLeft = '4px solid ' + (accent[type] || accent.info);
            toast.style.boxShadow = '0 16px 38px rgba(15,23,42,.14)';
            toast.style.border = '1px solid #e2e8f0';
            toast.style.borderRadius = '14px';
            toast.style.padding = '0';
            toast.style.minWidth = '320px';
            toast.style.maxWidth = '360px';
            toast.style.overflow = 'hidden';
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
          }
        });
        return;
      }

      if (window.toastr) {
        toastr[type] ? toastr[type](message, title) : toastr.info(message, title);
      } else {
        alert((title ? title + ': ' : '') + message);
      }
    };

    // Toastr config
    toastr.options = {
      positionClass: 'toast-top-right',
      closeButton: true,
      progressBar: true,
      timeOut: 4000,
      showEasing: 'swing',
      hideEasing: 'linear',
      showMethod: 'fadeIn',
      hideMethod: 'fadeOut'
    };

    // NProgress
    NProgress.configure({
      showSpinner: false,
      speed: 400,
      minimum: 0.15
    });
    document.addEventListener('click', function(e) {
      var a = e.target.closest('a[href]:not([href^="#"]):not([href^="javascript"]):not([target])');
      if (a && a.href && !a.href.includes('#')) NProgress.start();
    });
    window.addEventListener('load', function() {
      NProgress.done();
    });

    // Bootstrap Tooltips auto-init
    document.querySelectorAll('[data-bs-toggle="tooltip"], [title]:not(a):not(option)').forEach(function(el) {
      if (el.title && !el.getAttribute('data-bs-toggle')) {
        el.setAttribute('data-bs-toggle', 'tooltip');
      }
    });
    var tooltipList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipList.map(function(el) {
      return new bootstrap.Tooltip(el);
    });

    // SweetAlert2 global delete interceptor
    document.querySelectorAll('form[data-confirm]').forEach(function(form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        var msg = form.getAttribute('data-confirm') || 'Apakah Anda yakin?';
        Swal.fire({
          title: msg,
          text: 'Tindakan ini tidak dapat dibatalkan.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#94a3b8',
          confirmButtonText: 'Ya, hapus!',
          cancelButtonText: 'Batal',
          customClass: {
            popup: 'animate__animated animate__fadeInDown'
          }
        }).then(function(result) {
          if (result.isConfirmed) form.submit();
        });
      });
    });

    // Dropzone global config
    if (typeof Dropzone !== 'undefined') {
      Dropzone.autoDiscover = false;
    }

    // ── Notification Bell ──
    var _notifLoaded = false;
    var _csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    var _notifBell = document.getElementById('notif-bell');
    var _notifList = document.getElementById('notif-list');
    var _notifBadge = document.getElementById('notif-badge');
    var _notifCountLabel = document.getElementById('notif-count-label');
    var _notifRefreshBtn = document.getElementById('notif-refresh-btn');

    if (_notifBell) {
      _notifBell.addEventListener('click', function() {
        if (!_notifLoaded) {
          fetchNotifications();
          _notifLoaded = true;
        }
      });

      _notifBell.addEventListener('shown.bs.dropdown', function() {
        fetchNotifications(true);
      });
    }

    if (_notifRefreshBtn) {
      _notifRefreshBtn.addEventListener('click', function() {
        fetchNotifications();
      });
    }

    function escapeHtml(str) {
      return String(str || '').replace(/[&<>"']/g, function(chr) {
        return ({
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#39;'
        })[chr];
      });
    }

    function updateNotifBadge(count) {
      if (!_notifBadge || !_notifBell) return;

      if (count > 0) {
        _notifBadge.hidden = false;
        _notifBadge.textContent = count > 99 ? '99+' : String(count);
        _notifBell.classList.add('has-unread');
      } else {
        _notifBadge.hidden = true;
        _notifBell.classList.remove('has-unread');
      }
    }

    function renderNotifEmpty(message, icon) {
      _notifList.innerHTML =
        '<div class="notif-empty">' +
          '<i class="bx ' + (icon || 'bx-bell-off') + '"></i>' +
          '<span style="font-size:.8rem;">' + escapeHtml(message) + '</span>' +
        '</div>';
    }

    function renderNotifications(data) {
      updateNotifBadge(data.unread_count || 0);

      if (_notifCountLabel) {
        if (data.unread_count > 0) {
          _notifCountLabel.textContent = data.unread_count + ' notifikasi belum dibaca';
        } else {
          _notifCountLabel.textContent = 'Semua notifikasi sudah dibaca';
        }
      }

      if (!data.notifications || !data.notifications.length) {
        renderNotifEmpty('Belum ada notifikasi', 'bx-bell-off');
        return;
      }

      var colors = {
        blue: 'var(--blue)',
        green: '#22c55e',
        red: '#ef4444',
        orange: '#f97316',
        cyan: '#06b6d4'
      };

      var html = '';
      data.notifications.forEach(function(n) {
        var color = colors[n.color] || 'var(--txt-2)';
        var icon = escapeHtml(n.icon || 'bx-bell');
        var title = escapeHtml(n.title);
        var message = escapeHtml(n.message);
        var link = escapeHtml(n.link || '');
        var itemClass = 'notif-item' + (n.read ? '' : ' unread');

        html += '<a href="' + (link || '#') + '" class="' + itemClass + '" data-notification-id="' + n.id + '" data-link="' + link + '">';
        html +=   '<div class="notif-item-icon" style="background:' + color + '15;color:' + color + ';"><i class="bx ' + icon + '"></i></div>';
        html +=   '<div class="notif-item-body">';
        html +=     '<div class="notif-item-title">' + title + '</div>';
        html +=     '<div class="notif-item-message">' + message + '</div>';
        html +=     '<div class="notif-item-meta"><span>' + timeAgo(n.created_at) + '</span>' + (n.type ? '<span>•</span><span>' + escapeHtml(n.type) + '</span>' : '') + '</div>';
        html +=   '</div>';
        html += '</a>';
      });

      _notifList.innerHTML = html;

      _notifList.querySelectorAll('[data-notification-id]').forEach(function(item) {
        item.addEventListener('click', function(e) {
          markNotifRead(e, this.getAttribute('data-notification-id'), this.getAttribute('data-link'));
        });
      });
    }

    function fetchNotifications(silent) {
      if (!silent) {
        renderNotifEmpty('Memuat...', 'bx-loader-alt bx-spin');
      }

      return fetch('/admin/notifications/recent', {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': _csrfToken
        }
      })
        .then(function(r) {
          if (!r.ok) throw new Error('Failed to load notifications');
          return r.json();
        })
        .then(function(data) {
          renderNotifications(data);
          return data;
        })
        .catch(function() {
          if (!silent) {
            renderNotifEmpty('Gagal memuat notifikasi', 'bx-error-circle');
          }
        });
    }

    function markAllNotifRead() {
      fetch('/admin/notifications/mark-all-read', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': _csrfToken,
          'Accept': 'application/json'
        }
      })
      .then(function(r) {
        if (!r.ok) throw new Error('Failed');
        return r.json();
      })
      .then(function(data) {
        updateNotifBadge(0);
        fetchNotifications(true);
        if (window.toastr && data.message) {
          toastr.success(data.message);
        }
      })
      .catch(function() {
        if (window.toastr) toastr.error('Gagal menandai semua notifikasi.');
      });
    }

    function markNotifRead(event, notificationId, link) {
      if (event) event.preventDefault();

      fetch('/admin/notifications/' + notificationId + '/read', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': _csrfToken,
          'Accept': 'application/json'
        }
      })
      .then(function() {
        fetchNotifications(true);
      })
      .finally(function() {
        if (link && link !== '#') {
          window.location.href = link;
        }
      });

      return false;
    }

    function timeAgo(dt) {
      var diff = Math.floor((Date.now() - new Date(dt).getTime()) / 1000);
      if (diff < 60) return 'Baru saja';
      if (diff < 3600) return Math.floor(diff/60) + ' mnt lalu';
      if (diff < 86400) return Math.floor(diff/3600) + ' jam lalu';
      return Math.floor(diff/86400) + ' hari lalu';
    }

    // Near real-time poll while page is visible
    setTimeout(function() {
      fetchNotifications(true);
      _notifLoaded = true;
    }, 1200);

    setInterval(function() {
      if (!document.hidden) {
        fetchNotifications(true);
      }
    }, 15000);

    window.addEventListener('focus', function() {
      fetchNotifications(true);
    });

    document.addEventListener('visibilitychange', function() {
      if (!document.hidden) {
        fetchNotifications(true);
      }
    });
  </script>

  <!-- Quill Rich Text Editor JS -->
  <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
  <!-- jQuery Sparkline -->
  <script src="https://cdn.jsdelivr.net/npm/jquery-sparkline@2.4.0/jquery.sparkline.min.js"></script>
  <!-- FullCalendar -->
  <script src="https://unpkg.com/fullcalendar@6.1.15/index.global.min.js"></script>
  <!-- Clipboard.js -->
  <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.11/dist/clipboard.min.js"></script>
  <!-- QRCode.js -->
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  <!-- Shepherd.js -->
  <link href="https://unpkg.com/shepherd.js@13.0.3/dist/css/shepherd.css" rel="stylesheet">
  <script src="https://unpkg.com/shepherd.js@13.0.3/dist/js/shepherd.min.js"></script>

  <script>
    // Global Clipboard.js init — auto-init for all .btn-clipboard
    if (typeof ClipboardJS !== 'undefined') {
      var clipboard = new ClipboardJS('.btn-clipboard');
      clipboard.on('success', function(e) {
        var btn = e.trigger;
        var origHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bx bx-check"></i>';
        btn.classList.add('text-success');
        setTimeout(function() {
          btn.innerHTML = origHTML;
          btn.classList.remove('text-success');
        }, 1500);
        e.clearSelection();
      });
    }

    function nkSetTheme(theme) {
      var html = document.documentElement;
      html.setAttribute('data-theme', theme);
      try {
        localStorage.setItem('nk_theme', theme);
      } catch (e) {}
      nkRefreshThemeToggle();
    }

    function nkRefreshThemeToggle() {
      var btn = document.getElementById('theme-toggle');
      var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      if (btn) {
        var icon = btn.querySelector('i');
        if (icon) {
          icon.className = isDark ? 'bx bx-sun' : 'bx bx-moon';
        }
        btn.setAttribute('aria-label', isDark ? 'Ganti ke mode terang' : 'Ganti ke mode gelap');
      }
      var menuBtn = document.getElementById('theme-toggle-menu');
      if (menuBtn) {
        var menuIcon = menuBtn.querySelector('i');
        var menuText = menuBtn.querySelector('span');
        if (menuIcon) {
          menuIcon.className = isDark ? 'bx bx-sun me-2' : 'bx bx-moon me-2';
        }
        if (menuText) {
          menuText.textContent = isDark ? 'Mode terang' : 'Mode gelap';
        }
        menuBtn.setAttribute('aria-label', isDark ? 'Ganti ke mode terang' : 'Ganti ke mode gelap');
      }
    }

    function nkToggleDark() {
      var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      nkSetTheme(isDark ? 'light' : 'dark');
      return false;
    }

    var nkTableSearchInput = null;
    var nkGlobalSearchDefaultPlaceholder = '';
    function nkApplyTableSearchValue(input, value) {
      if (!input) return;
      input.value = value || '';
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new Event('keyup', { bubbles: true }));
    }

    function nkOpenGlobalSearch() {
      var backdrop = document.getElementById('cmd-backdrop');
      var palette  = document.getElementById('cmd-palette');
      var input    = document.getElementById('global-search');
      var body     = document.getElementById('cmd-body');
      if (!palette) return false;
      nkTableSearchInput = null;
      if (input && !nkGlobalSearchDefaultPlaceholder) {
        nkGlobalSearchDefaultPlaceholder = input.getAttribute('placeholder') || '';
      }
      if (input && nkGlobalSearchDefaultPlaceholder) {
        input.setAttribute('placeholder', nkGlobalSearchDefaultPlaceholder);
      }
      if (body) {
        body.innerHTML = '<div class="cmd-empty" id="cmd-hint"><i class="bx bx-search-alt"></i>Ketik minimal 2 karakter untuk mencari...</div>';
      }
      if (palette.style.display !== 'none') { nkCloseGlobalSearch(); return false; }
      backdrop.style.display = 'block';
      palette.style.display  = 'block';
      setTimeout(function() { if (input) { input.focus(); input.select(); } }, 30);
      return false;
    }

    function nkOpenTableSearch(tableInput) {
      var backdrop = document.getElementById('cmd-backdrop');
      var palette  = document.getElementById('cmd-palette');
      var input    = document.getElementById('global-search');
      var body     = document.getElementById('cmd-body');
      if (!palette || !input || !tableInput) return false;

      if (!nkGlobalSearchDefaultPlaceholder) {
        nkGlobalSearchDefaultPlaceholder = input.getAttribute('placeholder') || '';
      }

      nkTableSearchInput = tableInput;
      input.setAttribute('placeholder', 'Cari di tabel ini...');
      input.value = tableInput.value || '';

      if (body) {
        body.innerHTML = '<div class="cmd-empty" id="cmd-hint"><i class="bx bx-search-alt"></i>Ketik untuk memfilter tabel ini, lalu tekan Enter.</div>';
      }

      backdrop.style.display = 'block';
      palette.style.display  = 'block';
      setTimeout(function() { input.focus(); input.select(); }, 30);
      return false;
    }

    function nkCloseGlobalSearch() {
      var backdrop = document.getElementById('cmd-backdrop');
      var palette  = document.getElementById('cmd-palette');
      var input    = document.getElementById('global-search');
      var body     = document.getElementById('cmd-body');
      nkTableSearchInput = null;
      if (backdrop) backdrop.style.display = 'none';
      if (palette)  palette.style.display  = 'none';
      if (input)    {
        input.value = '';
        if (nkGlobalSearchDefaultPlaceholder) {
          input.setAttribute('placeholder', nkGlobalSearchDefaultPlaceholder);
        }
        input.blur();
      }
      if (body)     body.innerHTML = '<div class="cmd-empty" id="cmd-hint"><i class="bx bx-search-alt"></i>Ketik minimal 2 karakter untuk mencari...</div>';
      nkCmdIdx = -1;
      return false;
    }

    document.addEventListener('DOMContentLoaded', function() {
      if (typeof nkRefreshThemeToggle === 'function') {
        nkRefreshThemeToggle();
      }

      document.addEventListener('click', function(event) {
        var targetWrap = event.target.closest('.nk-search-wrap');
        if (!targetWrap) return;
        var icon = event.target.closest('.nk-search-wrap i');
        if (!icon && !event.target.closest('.nk-search-wrap.nk-table-search-trigger')) return;

        var input = targetWrap.querySelector('input, textarea');
        if (!input) return;

        var isTableSearch = targetWrap.classList.contains('nk-table-search-trigger');
        if (!isTableSearch) {
          input.focus();
          return;
        }

        event.preventDefault();
        nkOpenTableSearch(input);
      });
    });
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        nkCloseGlobalSearch();
      }
      if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
        event.preventDefault();
        nkOpenGlobalSearch();
      }
    });

    // Sidebar stays fixed on desktop. Mobile still uses sbToggle().
    function nkToggleSidebar() {
      return false;
    }

    // ======= COMMAND PALETTE SEARCH =======
    var nkCmdIdx = -1;
    (function() {
      var input   = document.getElementById('global-search');
      var body    = document.getElementById('cmd-body');
      if (!input || !body) return;
      var timer = null;

      function renderResults(data, q) {
        if (!data.results || !data.results.length) {
          body.innerHTML = '<div class="cmd-empty"><i class="bx bx-search-alt"></i>Tidak ada hasil untuk "<strong>' + q + '</strong>"</div>';
          return;
        }
        var html = '';
        var lastType = '';
        data.results.forEach(function(r, i) {
          if (r.type !== lastType) {
            html += '<div class="cmd-group-label">' + r.type + '</div>';
            lastType = r.type;
          }
          html += '<a href="' + r.url + '" class="cmd-item" data-idx="' + i + '">';
          html += '<div class="cmd-item-icon"><i class="bx ' + (r.icon || 'bx-link') + '"></i></div>';
          html += '<div style="flex:1;min-width:0;">';
          html += '<div class="cmd-item-title">' + r.title + '</div>';
          if (r.subtitle) html += '<div class="cmd-item-sub">' + r.subtitle + '</div>';
          html += '</div>';
          if (r.badge) html += '<span style="font-size:.6rem;padding:.15rem .4rem;border-radius:4px;background:var(--bg);color:var(--txt-3);border:1px solid var(--border);">' + r.badge + '</span>';
          html += '</a>';
        });
        body.innerHTML = html;
        nkCmdIdx = -1;
      }

      input.addEventListener('input', function() {
        clearTimeout(timer);
        var q = input.value.trim();

        if (nkTableSearchInput) {
          nkApplyTableSearchValue(nkTableSearchInput, input.value);
          if (!q.length) {
            body.innerHTML = '<div class="cmd-empty" id="cmd-hint"><i class="bx bx-search-alt"></i>Ketik untuk memfilter tabel ini, lalu tekan Enter.</div>';
          } else {
            body.innerHTML = '<div class="cmd-empty"><i class="bx bx-filter-alt"></i>Filter tabel aktif.</div>';
          }
          nkCmdIdx = -1;
          return;
        }

        if (q.length < 2) {
          body.innerHTML = '<div class="cmd-empty" id="cmd-hint"><i class="bx bx-search-alt"></i>Ketik minimal 2 karakter untuk mencari...</div>';
          nkCmdIdx = -1;
          return;
        }
        body.innerHTML = '<div class="cmd-loading"><i class="bx bx-loader-alt bx-spin"></i> Mencari...</div>';
        timer = setTimeout(function() {
          fetch('/admin/search?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) { renderResults(data, q); })
            .catch(function() {
              body.innerHTML = '<div class="cmd-empty"><i class="bx bx-error-circle"></i>Gagal mencari, coba lagi.</div>';
            });
        }, 250);
      });

      // Keyboard navigation
      input.addEventListener('keydown', function(e) {
        if (nkTableSearchInput) {
          if (e.key === 'Enter') {
            e.preventDefault();
            nkCloseGlobalSearch();
          }
          return;
        }
        var items = body.querySelectorAll('.cmd-item');
        if (!items.length) return;
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          nkCmdIdx = Math.min(nkCmdIdx + 1, items.length - 1);
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          nkCmdIdx = Math.max(nkCmdIdx - 1, 0);
        } else if (e.key === 'Enter' && nkCmdIdx >= 0) {
          e.preventDefault();
          items[nkCmdIdx].click();
          return;
        } else { return; }
        items.forEach(function(el, i) { el.classList.toggle('is-active', i === nkCmdIdx); });
        if (items[nkCmdIdx]) items[nkCmdIdx].scrollIntoView({ block: 'nearest' });
      });
    })();
  </script>
</body>

</html>








