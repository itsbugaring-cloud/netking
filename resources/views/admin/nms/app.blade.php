<!doctype html>
<html lang="en" data-theme="">
<script>
  // Restore saved theme preference
  (function() {
    var t = localStorage.getItem('nk_theme');
    if (t === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
  })();
</script>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','Dashboard')</title>

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
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
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

    /* Toastr overrides */
    .toast-top-right {
      top: 72px;
    }

    #toast-container>div {
      border-radius: 10px;
      box-shadow: var(--shadow-md);
      font-family: 'Inter', sans-serif;
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
      border: 1px solid var(--border) !important;
      border-radius: 6px !important;
      padding: 4px 10px !important;
      margin: 0 2px !important;
      font-size: .8125rem !important;
      background-color: var(--surface) !important;
      color: var(--txt-2) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.active,
    .pagination .page-item.active .page-link {
      background-color: var(--blue) !important;
      color: #fff !important;
      border-color: var(--blue) !important;
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
      --blue: #f97316;
      --blue-dk: #ea580c;
      --blue-lt: #fff7ed;
      --blue-md: #ffedd5;
      --blue-glow: rgba(249, 115, 22, .12);

      --green: #22c55e;
      --red: #ef4444;
      --orange: #f97316;
      --cyan: #06b6d4;

      --bg: #fafafa;
      --surface: #ffffff;
      --border: #e5e5e5;
      --bd-dk: #d4d4d4;

      --txt: #0a0a0a;
      --txt-2: #525252;
      --txt-3: #a3a3a3;

      --sb-w: 260px;
      --r: 8px;
      --r-sm: 6px;

      --shadow-xs: 0 1px 2px rgba(0, 0, 0, .03);
      --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.5);
      --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
      --hover-bg: #f4f4f5;
      --shadow-lg: 0 12px 28px rgba(0, 0, 0, .08);
    }

    /* ======= DARK MODE — Complete ======= */
    [data-theme="dark"] {
      --blue: #ea580c;
      --blue-dk: #c2410c;
      --blue-lt: rgba(249, 115, 22, .12);
      --blue-md: rgba(249, 115, 22, .2);
      --blue-glow: rgba(249, 115, 22, 0.4);
      --hover-bg: rgba(255, 255, 255, 0.05);
      --bg: #0a0a0a;
      --surface: #141414;
      --border: rgba(255, 255, 255, .08);
      --bd-dk: rgba(255, 255, 255, .12);
      --txt: #fafafa;
      --txt-2: #a3a3a3;
      --txt-3: #525252;
      --shadow-xs: none;
      --shadow-sm: none;
      --shadow-md: 0 4px 12px rgba(0, 0, 0, .5);
      --shadow-lg: 0 12px 28px rgba(0, 0, 0, .6);
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
      border-right: 1px solid var(--border);
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
      font-weight: 700;
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
      font-weight: 600;
    }

    .sb-icon {
      font-size: 1.05rem;
      width: 1.25rem;
      flex-shrink: 0;
      text-align: center;
    }

    .sb-foot {
      padding: 1rem 1.25rem;
      border-top: 1px solid var(--border);
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
      padding: 0.4rem 0.85rem;
      margin: 0 0.15rem;
      border-radius: var(--r-sm);
      font-size: 0.8125rem;
      border: 1px solid var(--border);
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
      background: var(--blue);
      border-color: var(--blue);
      color: #fff;
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
      border-color: var(--blue);
    }

    .btn-primary:hover {
      background: var(--blue-dk);
      border-color: var(--blue-dk);
    }

    .btn-secondary {
      background: #f1f5f9;
      border-color: var(--border);
      color: var(--txt);
    }

    .btn-secondary:hover {
      background: #e2e8f0;
    }

    .btn-outline-primary {
      border-color: var(--blue);
      color: var(--blue);
    }

    .btn-outline-primary:hover {
      background: var(--blue);
      color: #fff;
    }

    .btn-danger {
      background: #ef4444;
      border-color: #ef4444;
    }

    .btn-danger:hover {
      background: #dc2626;
      border-color: #dc2626;
    }

    .btn-success {
      background: #10b981;
      border-color: #10b981;
    }

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
      gap: 2px;
      flex-wrap: wrap;
      margin: 0;
    }

    .pagination .page-link {
      border-radius: var(--r-sm) !important;
      border: 1px solid var(--border);
      color: var(--txt-2);
      font-size: .8125rem;
      font-weight: 500;
      padding: .35rem .7rem;
      background: #fff;
      transition: all .12s;
    }

    .pagination .page-link:hover {
      background: var(--blue-lt);
      color: var(--blue);
      border-color: #bfdbfe;
    }

    .pagination .page-item.active .page-link {
      background: var(--blue);
      border-color: var(--blue);
      color: #fff;
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
      border-radius: var(--r-sm);
      font-size: .875rem;
    }

    .alert-success {
      background: #f0fdf4;
      border-color: #bbf7d0;
      color: #166534;
    }

    .alert-danger {
      background: #fef2f2;
      border-color: #fecaca;
      color: #991b1b;
    }

    .alert-warning {
      background: #fffbeb;
      border-color: #fde68a;
      color: #92400e;
    }

    .alert-info {
      background: #eff6ff;
      border-color: #bfdbfe;
      color: #1e40af;
    }

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
    }
  </style>
  @yield('styles')
</head>

<body>
  <!-- NETKING UI VERSION 1.5 - CACHE BUSTER -->
<script>
  // Restore sidebar collapse state
  (function(){
    if(localStorage.getItem('nk_sb_collapsed')==='1'){
      document.documentElement.setAttribute('data-sidebar','collapsed');
    }
  })();
</script>

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sb">
    <button class="sb-collapse-btn" onclick="nkToggleSidebar()">
      <i class='bx bx-chevron-left'></i>
    </button>
    <div class="sb-body">
      @include('layouts.sidebar')
    </div>
    <div class="sb-foot">
      <div class="sb-user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}</div>
      <div>
        <div class="sb-user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
        <div class="sb-user-role">{{ ucfirst(auth()->user()->role ?? 'admin') }}</div>
      </div>
    </div>
  </aside>

  <!-- TOPBAR -->
  <header class="topbar">
    <button class="tb-burger tb-btn" onclick="sbToggle()" type="button"><i class='bx bx-menu'></i></button>
    <span class="tb-title">@yield('title', 'Dashboard')</span>
    <div class="tb-spacer"></div>
    <div class="tb-search" style="position:relative;">
      <i class='bx bx-search'></i>
      <input type="text" placeholder="Search..." id="global-search" autocomplete="off">
      <div class="search-results" id="search-results" style="display:none;"></div>
    </div>

    <!-- Notifications -->
    <!-- Dark Mode Toggle -->
    <button class="dark-toggle mx-2" onclick="nkToggleDark()">
      <i class='bx bx-moon' id="dark-icon" style="font-size:1.15rem;"></i>
    </button>

    <div class="dropdown">
      <button class="tb-btn" data-bs-toggle="dropdown">
        <i class='bx bx-bell'></i>
        <span class="notif-dot"></span>
      </button>
      <div class="dropdown-menu dropdown-menu-end" style="min-width:280px;">
        <div class="px-3 py-2 fw-semibold" style="font-size:.8125rem;border-bottom:1px solid var(--border);">Notifications</div>
        <div class="text-center py-4 text-muted">
          <i class='bx bx-bell-off d-block mb-1' style="font-size:1.5rem;"></i>
          <span style="font-size:.8rem;">No new notifications</span>
        </div>
      </div>
    </div>

    <!-- User avatar with dropdown -->
    <div class="dropdown">
      <button class="tb-avatar" data-bs-toggle="dropdown" style="border:none;">
        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
      </button>
      <div class="dropdown-menu dropdown-menu-end" style="min-width:200px;">
        <div class="px-3 py-2" style="border-bottom:1px solid var(--border);">
          <div class="fw-semibold" style="font-size:.875rem;">{{ auth()->user()->name ?? 'Admin' }}</div>
          <div style="font-size:.75rem;color:var(--txt-3);">{{ auth()->user()->email ?? '' }}</div>
        </div>
        <div class="pt-1">
          <a class="dropdown-item" href="{{ route('admin.profile') }}"><i class='bx bx-user me-2'></i>Profile</a>
          <a class="dropdown-item" href="{{ route('admin.settings') }}"><i class='bx bx-cog me-2'></i>Settings</a>
          <div class="dropdown-divider"></div>
          <form action="{{ route('admin.logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="dropdown-item text-danger"><i class='bx bx-log-out me-2'></i>Sign Out</button>
          </form>
        </div>
      </div>
    </div>
  </header>

  <!-- MAIN CONTENT -->
  <main class="main">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
      <i class='bx bx-check-circle me-1'></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3">
      <i class='bx bx-error-circle me-1'></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show mb-3">
      <i class='bx bx-info-circle me-1'></i>{{ session('warning') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3">
      <strong><i class='bx bx-error-circle me-1'></i>Please fix:</strong>
      <ul class="mb-0 mt-1 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @yield('content')

  </main>

  <footer class="layout-footer">
    © {{ date('Y') }} <strong>NETKING</strong>
    <span class="ms-auto float-end">v2.0</span>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
      $('select:not(.no-select2)').each(function() {
        var placeholder = $(this).data('placeholder') ||
          $(this).find('option[value=""]').first().text() || 'Select...';
        $(this).select2({
          theme: 'bootstrap-5',
          width: '100%',
          placeholder: placeholder,
          allowClear: $(this).find('option[value=""]').length > 0
        });
      });
      document.querySelectorAll('.modal').forEach(function(modal) {
        modal.addEventListener('shown.bs.modal', function() {
          $(modal).find('select:not(.no-select2):not([data-select2-id])').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $(modal)
          });
        });
      });
    });
  </script>


  <!-- DataTables -->
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
  <!-- SweetAlert2 -->
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

  @yield('scripts')
  @stack('scripts')

  <script>
    /* ── Global Initializers ── */

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

    // Laravel flash → Toastr
    @if(session('success'))
    toastr.success(@json(session('success')));
    @endif
    @if(session('error'))
    toastr.error(@json(session('error')));
    @endif
    @if(session('warning'))
    toastr.warning(@json(session('warning')));
    @endif
    @if(session('info'))
    toastr.info(@json(session('info')));
    @endif

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
        var msg = form.getAttribute('data-confirm') || 'Are you sure?';
        Swal.fire({
          title: msg,
          text: 'This action cannot be undone.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#94a3b8',
          confirmButtonText: 'Yes, delete!',
          cancelButtonText: 'Cancel',
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
  </script>

  <!-- Quill Rich Text Editor JS -->
  <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
  <!-- jQuery Sparkline -->
  <script src="https://cdn.jsdelivr.net/npm/jquery-sparkline@2.4.0/jquery.sparkline.min.js"></script>
  <!-- FullCalendar -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
  <!-- Clipboard.js -->
  <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.11/dist/clipboard.min.js"></script>
  <!-- QRCode.js -->
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  <!-- Shepherd.js -->
  <link href="https://cdn.jsdelivr.net/npm/shepherd.js@13.0.3/dist/css/shepherd.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/shepherd.js@13.0.3/dist/js/shepherd.min.js"></script>

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
    // Dark mode toggle — full black
    function nkToggleDark() {
      var html = document.documentElement;
      var isDark = html.getAttribute('data-theme') === 'dark';
      if (isDark) {
        html.setAttribute('data-theme', '');
        localStorage.setItem('nk_theme', 'light');
        document.getElementById('dark-icon').className = 'bx bx-moon';
      } else {
        html.setAttribute('data-theme', 'dark');
        localStorage.setItem('nk_theme', 'dark');
        document.getElementById('dark-icon').className = 'bx bx-sun';
      }
    }
    // Set icon on page load
    (function() {
      var t = localStorage.getItem('nk_theme');
      if (t === 'dark') {
        var ic = document.getElementById('dark-icon');
        if (ic) ic.className = 'bx bx-sun';
      }
    })();

    // ======= SIDEBAR COLLAPSE TOGGLE =======
    function nkToggleSidebar() {
      var html = document.documentElement;
      var isCollapsed = html.getAttribute('data-sidebar') === 'collapsed';
      if (isCollapsed) {
        html.removeAttribute('data-sidebar');
        localStorage.setItem('nk_sb_collapsed', '0');
      } else {
        html.setAttribute('data-sidebar', 'collapsed');
        localStorage.setItem('nk_sb_collapsed', '1');
      }
    }

    // ======= GLOBAL SEARCH AJAX =======
    (function() {
      var input = document.getElementById('global-search');
      var box = document.getElementById('search-results');
      if (!input || !box) return;
      var timer = null;

      input.addEventListener('keyup', function() {
        clearTimeout(timer);
        var q = input.value.trim();
        if (q.length < 2) {
          box.style.display = 'none';
          return;
        }
        timer = setTimeout(function() {
          fetch('/admin/search?q=' + encodeURIComponent(q), {
              headers: {
                'X-Requested-With': 'XMLHttpRequest'
              }
            })
            .then(function(r) {
              return r.json();
            })
            .then(function(data) {
              if (!data.results || !data.results.length) {
                box.innerHTML = '<div class="search-empty"><i class="bx bx-search-alt" style="font-size:1.5rem;display:block;margin-bottom:.25rem;"></i>No results for "' + q + '"</div>';
                box.style.display = 'block';
                return;
              }
              var html = '';
              var lastType = '';
              data.results.forEach(function(r) {
                if (r.type !== lastType) {
                  html += '<div class="search-type-label">' + r.type + 's</div>';
                  lastType = r.type;
                }
                html += '<a href="' + r.url + '" class="search-result-item">';
                html += '<div class="search-result-icon"><i class="bx ' + r.icon + '"></i></div>';
                html += '<div style="flex:1;min-width:0;">';
                html += '<div class="search-result-title">' + r.title + '</div>';
                if (r.subtitle) html += '<div class="search-result-sub">' + r.subtitle + '</div>';
                html += '</div>';
                if (r.badge) html += '<span class="badge-status badge-' + r.badge + '" style="font-size:.6rem;">' + r.badge + '</span>';
                html += '</a>';
              });
              box.innerHTML = html;
              box.style.display = 'block';
            })
            .catch(function() {
              box.style.display = 'none';
            });
        }, 300);
      });

      // Close on click outside
      document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !box.contains(e.target)) {
          box.style.display = 'none';
        }
      });

      input.addEventListener('focus', function() {
        if (box.innerHTML && input.value.trim().length >= 2) box.style.display = 'block';
      });
    })();
  </script>
</body>

</html>