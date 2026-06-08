<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Admin')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <!-- CSS files -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet" />
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --tblr-font-sans-serif: 'Inter', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }

        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        /* ── Pagination fix: override Tabler's pill/oval active style ── */
        .pagination { gap: 2px; }
        .page-link {
            padding: 0.375rem 0.625rem !important;
            font-size: 0.75rem !important;
            min-width: 32px;
            text-align: center;
            border-radius: 4px !important;
            border: 1px solid var(--tblr-border-color, #e6e7e9) !important;
            color: var(--tblr-secondary, #626976) !important;
            background: var(--tblr-bg-surface, #fff) !important;
            box-shadow: none !important;
        }
        .page-link:hover {
            background: var(--tblr-bg-surface-secondary, #f6f8fb) !important;
            color: var(--tblr-body-color, #1d273b) !important;
        }
        .page-item.active .page-link {
            background: #2563eb !important;
            border-color: #2563eb !important;
            color: #fff !important;
            border-radius: 4px !important;
        }
        .page-item.disabled .page-link {
            opacity: 0.5;
            cursor: not-allowed;
        }
        /* DataTables paginate buttons */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.625rem !important;
            font-size: 0.75rem !important;
            border-radius: 4px !important;
            border: 1px solid var(--tblr-border-color, #e6e7e9) !important;
            color: var(--tblr-secondary, #626976) !important;
            background: var(--tblr-bg-surface, #fff) !important;
            margin: 0 1px;
            box-shadow: none !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--tblr-bg-surface-secondary, #f6f8fb) !important;
            color: var(--tblr-body-color, #1d273b) !important;
            border-color: var(--tblr-border-color, #e6e7e9) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #2563eb !important;
            border-color: #2563eb !important;
            color: #fff !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            background: var(--tblr-bg-surface, #fff) !important;
        }

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

        /* ── Buttons Unified ── */
        .page-body .btn {
            height: var(--nk-height) !important;
            border-radius: var(--nk-radius) !important;
            font-size: .8125rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: .3rem !important;
        }
        .page-body .btn-sm {
            height: var(--nk-height-sm) !important;
            font-size: .75rem !important;
            padding: .25rem .5rem !important;
        }
        .page-body .btn-primary { background-color: var(--nk-primary) !important; border-color: var(--nk-primary) !important; color: #fff !important; }
        .page-body .btn-primary:hover { background-color: #1d4ed8 !important; border-color: #1d4ed8 !important; }

        /* ── Modal Unified ── */
        .modal-content {
            border-radius: 12px !important;
            box-shadow: 0 8px 32px rgba(0,0,0,.18) !important;
            border: none !important;
        }
        .modal-header { padding: 1rem !important; border-bottom: 1px solid var(--tblr-border-color, #e6e7e9) !important; }
        .modal-body { padding: 1.25rem !important; }
        .modal-footer { padding: .75rem 1rem !important; border-top: 1px solid var(--tblr-border-color, #e6e7e9) !important; }

        /* ── Form Inputs ── */
        .page-body .form-select,
        .page-body .form-control {
            height: var(--nk-height) !important;
            min-height: var(--nk-height) !important;
            border-radius: var(--nk-radius) !important;
            font-size: .8125rem !important;
            line-height: var(--nk-height) !important;
            padding: 0 .75rem !important;
        }
        .page-body .form-select:focus,
        .page-body .form-control:focus {
            border-color: var(--nk-primary) !important;
            box-shadow: 0 0 0 2px rgba(37,99,235,.12) !important;
        }
        .page-body .form-label {
            font-size: .8rem !important;
            font-weight: 600 !important;
            margin-bottom: .3rem !important;
        }
    </style>

    @stack('styles')
    @livewireStyles
</head>

<body>
    <div class="page">
        @include('admin.layout.sidebar')

        <div class="page-wrapper">
            @include('admin.layout.header')

            <div class="page-body">
                <div class="container-xl">
                    <x-admin-flash />
                    @yield('content')
                </div>
            </div>

            @include('admin.layout.footer')
        </div>
    </div>

    <!-- Tabler Core -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    @livewireScripts

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(function() {
        if (typeof $.fn.select2 === 'undefined') return;
        $('.form-select').not('.no-select2').not('[data-select2-id]').each(function() {
            var $el = $(this);
            var placeholder = $el.data('placeholder') || $el.find('option[value=""]').first().text() || 'Pilih...';
            $el.select2({ theme: 'bootstrap-5', width: '100%', placeholder: placeholder, allowClear: $el.find('option[value=""]').length > 0 });
        });
        document.querySelectorAll('.modal').forEach(function(modal) {
            modal.addEventListener('shown.bs.modal', function() {
                $(modal).find('.form-select').not('.no-select2').not('[data-select2-id]').select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $(modal) });
            });
        });

        // Global confirm handler
        $('form[data-confirm]').on('submit', function(e) {
            e.preventDefault();
            var form = this;
            if (typeof Swal === 'undefined') { form.submit(); return; }
            Swal.fire({
                title: $(form).data('confirm'),
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, lanjutkan',
                cancelButtonText: 'Batal'
            }).then(function(r) { if (r.isConfirmed) form.submit(); });
        });
    });
    </script>

    @stack('scripts')
</body>

</html>
