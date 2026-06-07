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

        /* ── Form elements compact styling ── */
        .page-body .form-select,
        .page-body .form-control,
        .page-body select,
        .page-body input[type="text"],
        .page-body input[type="number"],
        .page-body input[type="date"],
        .page-body input[type="search"] {
            font-size: .8125rem !important;
            padding: .4rem .75rem !important;
            border-radius: 6px !important;
            border: 1px solid var(--tblr-border-color, #e6e7e9) !important;
            height: 34px !important;
            min-height: 34px !important;
            line-height: 34px !important;
            box-shadow: none !important;
            vertical-align: middle !important;
        }
        .page-body textarea.form-control {
            height: auto !important;
            line-height: 1.5 !important;
        }
        .page-body .form-select-sm,
        .page-body .form-control-sm {
            font-size: .75rem !important;
            padding: .3rem .5rem !important;
            height: 30px !important;
            min-height: 30px !important;
        }
        .page-body .form-select:focus,
        .page-body .form-control:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37,99,235,.12) !important;
            outline: none !important;
        }
        .page-body .btn {
            font-size: .8125rem !important;
            border-radius: 6px !important;
            height: 34px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: .3rem !important;
        }
        .page-body .btn-sm {
            font-size: .75rem !important;
            padding: .25rem .5rem !important;
            height: 30px !important;
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

    @livewireScripts

    @stack('scripts')
</body>

</html>
