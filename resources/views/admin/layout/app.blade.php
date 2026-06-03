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
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <div class="d-flex">
                            <div>
                                <i class="ti ti-check icon alert-icon"></i>
                            </div>
                            <div>
                                <h4 class="alert-title">Berhasil!</h4>
                                <div class="text-muted">{{ session('success') }}</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex">
                            <div>
                                <i class="ti ti-alert-circle icon alert-icon"></i>
                            </div>
                            <div>
                                <h4 class="alert-title">Error!</h4>
                                <div class="text-muted">{{ session('error') }}</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

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
