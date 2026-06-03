<!doctype html>
<html lang="en">

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
      background: #2563eb;
      height: 3px;
    }

    #nprogress .peg {
      box-shadow: 0 0 10px #2563eb, 0 0 5px #2563eb;
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
      border-color: #2563eb;
      outline: none;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
    }

    .dataTables_wrapper .dataTables_length select {
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      padding: 4px 8px;
      font-size: 0.8125rem;
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
    }

    .dataTables_wrapper table.dataTable tbody tr:hover td {
      background: var(--blue-lt) !important;
    }

    .dataTables_wrapper table.dataTable tbody tr:last-child td {
      border-bottom: none;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
      border-radius: 6px !important;
      font-size: 0.8125rem !important;
      padding: 4px 10px !important;
      border: 1px solid var(--border) !important;
      color: var(--txt-2) !important;
      margin: 0 2px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
      background: var(--blue) !important;
      border-color: var(--blue) !important;
      color: #fff !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
      background: var(--bg) !important;
      border-color: var(--border) !important;
      color: var(--txt) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
      opacity: 0.4;
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

    /* ── Phase D: Extended Badge System ── */
    .badge-status {
      padding: 3px 10px;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 600;
      display: inline-block;
      white-space: nowrap;
    }

    .badge-active,
    .badge-paid,
    .badge-success {
      background: rgba(16, 185, 129, .12);
      color: #10b981;
    }

    .badge-inactive,
    .badge-closed {
      background: rgba(148, 163, 184, .12);
      color: #64748b;
    }

    .badge-suspended,
    .badge-danger {
      background: rgba(239, 68, 68, .12);
      color: #ef4444;
    }

    .badge-pending,
    .badge-warning,
    .badge-unpaid {
      background: rgba(245, 158, 11, .12);
      color: #f59e0b;
    }

    .badge-provisioning,
    .badge-info {
      background: rgba(37, 99, 235, .12);
      color: #2563eb;
    }

    .badge-failed,
    .badge-overdue {
      background: rgba(255, 61, 0, .12);
      color: #ff3d00;
    }

    .badge-open {
      background: rgba(6, 182, 212, .12);
      color: #06b6d4;
    }

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
      border: 1px solid var(--border, #dde1e7);
      border-radius: 6px;
      padding: 4px 8px;
      font-size: .8125rem;
    }

    .dataTables_wrapper .dataTables_info {
      font-size: .8125rem;
      color: var(--txt-3, #94a3b8);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
      border: 1px solid var(--border, #dde1e7) !important;
      border-radius: 6px !important;
      padding: 4px 10px !important;
      margin: 0 2px !important;
      font-size: .8125rem !important;
      background: #fff !important;
      color: var(--txt-2, #64748b) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
      background: var(--blue) !important;
      color: #fff !important;
      border-color: var(--blue) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
      background: var(--bg-2, #f5f5f9) !important;
      color: var(--txt, #1e293b) !important;
    }


    /* =====================================================
       NETKING — LIGHT THEME (Modern Blue UI)
       ===================================================== */
    :root {
      --blue: #2563eb;
      --blue-dk: #1d4ed8;
      --blue-lt: #eff6ff;
      --blue-md: #dbeafe;
      --blue-glow: rgba(37, 99, 235, .12);

      --green: #10b981;
      --red: #ef4444;
      --orange: #f59e0b;
      --cyan: #06b6d4;

      --bg: #f1f5f9;
      --surface: #ffffff;
      --border: #e2e8f0;
      --bd-dk: #cbd5e1;

      --txt: #0f172a;
      --txt-2: #475569;
      --txt-3: #94a3b8;

      --sb-w: 260px;
      --r: 12px;
      --r-sm: 8px;

      --shadow-xs: 0 1px 2px rgba(15, 23, 42, .04);
      --shadow-sm: 0 1px 4px rgba(15, 23, 42, .06), 0 1px 3px rgba(15, 23, 42, .04);
      --shadow-md: 0 4px 12px rgba(15, 23, 42, .08), 0 2px 4px rgba(15, 23, 42, .04);
      --shadow-lg: 0 12px 28px rgba(15, 23, 42, .1);
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
      font-family: 'Inter', system-ui, sans-serif;
      background: var(--bg);
      color: var(--txt);
      font-size: .875rem;
      -webkit-font-smoothing: antialiased;
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
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: #e2e8f0 transparent;
    }

    .sb-head {
      padding: 1.25rem 1.25rem 1rem;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      gap: .625rem;
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
      background: #fff;
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
      padding: .75rem 1rem;
      background: #f8fafc;
      white-space: nowrap;
    }

    .table tbody td {
      padding: .75rem 1rem;
      vertical-align: middle;
      border-color: var(--border);
      font-size: .875rem;
      color: var(--txt-2);
    }

    .table tbody tr:hover {
      background: #f8fafc;
    }

    .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
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

    /* ======= BADGES ======= */
    .badge-status {
      font-size: .6875rem;
      font-weight: 600;
      padding: .2rem .6rem;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      gap: .2rem;
      border: 1px solid transparent;
    }

    .badge-active {
      background: #ecfdf5;
      color: #059669;
      border-color: #a7f3d0;
    }

    .badge-inactive {
      background: #f8fafc;
      color: var(--txt-3);
      border-color: var(--border);
    }

    .badge-suspended {
      background: #fff7ed;
      color: #c2410c;
      border-color: #fed7aa;
    }

    .badge-failed {
      background: #fef2f2;
      color: #dc2626;
      border-color: #fecaca;
    }

    .badge-paid {
      background: #ecfdf5;
      color: #059669;
      border-color: #a7f3d0;
    }

    .badge-unpaid {
      background: #fefce8;
      color: #ca8a04;
      border-color: #fde047;
    }

    .badge-overdue {
      background: #fef2f2;
      color: #dc2626;
      border-color: #fecaca;
    }

    .badge-cancelled {
      background: #f8fafc;
      color: var(--txt-3);
      border-color: var(--border);
    }

    .badge-provisioning {
      background: #eff6ff;
      color: #2563eb;
      border-color: #bfdbfe;
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

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sb">
    <div class="sb-head">
      <div class="sb-logo-icon"><i class='bx bx-wifi'></i></div>
      <div>
        <div class="sb-logo-name">NETKING</div>
        <div class="sb-logo-sub">Admin Panel</div>
      </div>
    </div>
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
    <div class="tb-search">
      <i class='bx bx-search'></i>
      <input type="text" placeholder="Search anything...">
    </div>

    <!-- Notifications -->
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

  @yield('scripts')
  @stack('scripts')

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
  </script>
</body>

</html>