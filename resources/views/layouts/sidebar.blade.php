{{-- sidebar.blade.php — nav links only, no outer wrapper (layout provides it) --}}
@php
  $sidebarUser = auth()->user();
  $isAdmin = $sidebarUser?->role === 'admin';
  $isFinance = $sidebarUser?->role === 'finance';
  $dashboardActive = request()->routeIs('admin.dashboard');
  $userInitials = strtoupper(substr($sidebarUser?->name ?? 'A', 0, 2));

  $sections = [
    [
      'label' => 'Jaringan',
      'items' => array_values(array_filter([
        $isAdmin ? [
          'route' => route('admin.areas.index'),
          'active' => request()->routeIs('admin.areas*'),
          'icon' => 'bx bx-map-pin',
          'title' => 'Area & Wilayah',
        ] : null,
        $isAdmin ? [
          'route' => route('admin.packages.index'),
          'active' => request()->routeIs('admin.packages*'),
          'icon' => 'bx bx-package',
          'title' => 'Paket Internet',
        ] : null,
        $isAdmin ? [
          'route' => route('admin.pppoe.index'),
          'active' => request()->routeIs('admin.pppoe*'),
          'icon' => 'bx bx-chip',
          'title' => 'MikroTik',
        ] : null,
        $isAdmin ? [
          'route' => route('admin.address-list.index'),
          'active' => request()->routeIs('admin.address-list*'),
          'icon' => 'bx bx-shield-quarter',
          'title' => 'Isolir',
        ] : null,
        $isAdmin ? [
          'route' => route('admin.queues.index'),
          'active' => request()->routeIs('admin.queues*'),
          'icon' => 'bx bx-tachometer',
          'title' => 'Simple Queue',
        ] : null,
        $isAdmin ? [
          'route' => route('admin.system-dashboard'),
          'active' => request()->routeIs('admin.system-dashboard*'),
          'icon' => 'bx bx-desktop',
          'title' => 'System Dashboard',
        ] : null,
        $isAdmin ? [
          'route' => route('admin.backups.index'),
          'active' => request()->routeIs('admin.backups*'),
          'icon' => 'bx bx-data',
          'title' => 'Router Backup',
        ] : null,
      ])),
    ],
    [
      'label' => 'Pelanggan',
      'items' => array_values(array_filter([
        [
          'route' => route('admin.customers.index'),
          'active' => request()->routeIs('admin.customers*'),
          'icon' => 'bx bx-user',
          'title' => 'Data Pelanggan',
        ],
      ])),
    ],
  ];

  if ($isAdmin || $isFinance) {
    $sections[] = [
      'label' => 'Keuangan',
      'items' => array_values(array_filter([
        [
          'route' => route('admin.payments.review'),
          'active' => request()->routeIs('admin.payments*'),
          'icon' => 'bx bx-check-shield',
          'title' => 'Review Pembayaran',
          'badge' => \App\Models\Payment::pending()->count() ?: null,
        ],
        $isAdmin ? [
          'route' => route('admin.reports.payments'),
          'active' => request()->routeIs('admin.reports.payments'),
          'icon' => 'bx bx-money',
          'title' => 'Laporan Pembayaran',
        ] : null,
        $isAdmin ? [
          'route' => route('admin.reports.revenue'),
          'active' => request()->routeIs('admin.reports.revenue'),
          'icon' => 'bx bx-bar-chart-alt-2',
          'title' => 'Laporan Keuangan',
        ] : null,
        $isAdmin ? [
          'route' => route('admin.reports.billing'),
          'active' => request()->routeIs('admin.reports.billing'),
          'icon' => 'bx bx-group',
          'title' => 'Laporan Pelanggan',
        ] : null,
      ])),
    ];

  }

  if (! $isFinance) {
    // Tools section removed
  }

  if ($isAdmin) {
    $sections[] = [
      'label' => 'Automasi',
      'items' => [
        [
          'route' => route('admin.telegram.requests.index'),
          'active' => request()->routeIs('admin.telegram.requests*'),
          'icon' => 'bx bxl-telegram',
          'title' => 'Telegram Request',
        ],
      ],
    ];
  }

  if ($isAdmin) {
    $sections[] = [
      'label' => 'IPAM',
      'items' => [
        [
          'route' => route('admin.ipam.dashboard'),
          'active' => request()->routeIs('admin.ipam.dashboard'),
          'icon' => 'bx bx-network-chart',
          'title' => 'Dashboard IPAM',
        ],
        [
          'route' => route('admin.ipam.routers.index'),
          'active' => request()->routeIs('admin.ipam.routers*'),
          'icon' => 'bx bx-server',
          'title' => 'Routers',
        ],
        [
          'route' => route('admin.ipam.subnets.index'),
          'active' => request()->routeIs('admin.ipam.subnets*'),
          'icon' => 'bx bx-sitemap',
          'title' => 'Subnets',
        ],
        [
          'route' => route('admin.ipam.auditLog'),
          'active' => request()->routeIs('admin.ipam.auditLog'),
          'icon' => 'bx bx-history',
          'title' => 'Audit Log',
        ],
      ],
    ];
  }

  if ($isAdmin) {
    // Inventaris section removed
  }

  $sections[] = [
    'label' => 'Akun',
    'items' => array_values(array_filter([
      $isAdmin ? [
        'route' => route('admin.users.index'),
        'active' => request()->routeIs('admin.users*'),
        'icon' => 'bx bx-group',
        'title' => 'Manajemen Pengguna',
      ] : null,
      [
        'route' => route('admin.profile'),
        'active' => request()->routeIs('admin.profile*'),
        'icon' => 'bx bx-user-circle',
        'title' => 'Profil Saya',
      ],
      $isAdmin ? [
        'route' => route('admin.settings'),
        'active' => request()->routeIs('admin.settings*'),
        'icon' => 'bx bx-cog',
        'title' => 'Pengaturan Sistem',
      ] : null,
    ])),
  ];

  $sections = array_values(array_filter($sections, fn ($section) => !empty($section['items'])));
@endphp

<div class="sb-linear-head">
  <div class="sb-linear-actions">
    <div class="dropdown">
      <button class="sb-user-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User menu">
        <span class="sb-linear-mark sb-user-mark">{{ $userInitials }}</span>
        <i class='bx bx-chevron-down sb-linear-caret'></i>
      </button>
      <div class="dropdown-menu sb-user-menu">
        <div class="sb-user-menu-head">
          <div class="sb-user-menu-name">{{ $sidebarUser?->name ?? 'Admin' }}</div>
          <div class="sb-user-menu-role">{{ ucfirst($sidebarUser?->role ?? 'admin') }}</div>
        </div>
        <a class="dropdown-item" href="{{ route('admin.profile') }}"><i class='bx bx-user me-2'></i>Profil</a>
        @if($isAdmin)
        <a class="dropdown-item" href="{{ route('admin.settings') }}"><i class='bx bx-cog me-2'></i>Pengaturan</a>
        @endif
        <div class="dropdown-divider"></div>
        <form action="{{ route('admin.logout') }}" method="POST" class="m-0">
          @csrf
          <button type="submit" class="dropdown-item text-danger"><i class='bx bx-log-out me-2'></i>Keluar</button>
        </form>
      </div>
    </div>
    <button type="button" class="sb-head-btn" id="sidebar-search-trigger" onclick="nkOpenGlobalSearch()" aria-label="Search">
      <svg viewBox="0 0 16 16" aria-hidden="true" focusable="false" class="sb-head-icon">
        <circle cx="7" cy="7" r="4.25" fill="none" stroke="currentColor" stroke-width="1.4"></circle>
        <path d="M10.35 10.35L13.25 13.25" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path>
      </svg>
    </button>
  </div>
</div>

{{-- Command Palette Modal --}}
<div id="cmd-backdrop" class="cmd-backdrop" style="display:none;" onclick="nkCloseGlobalSearch()"></div>
<div id="cmd-palette" class="cmd-palette" style="display:none;">
  <div class="cmd-header">
    <i class='bx bx-search cmd-header-icon'></i>
    <input type="text" id="global-search" class="cmd-input" placeholder="Cari pelanggan, pembayaran, area, OLT..." autocomplete="off" spellcheck="false">
    <kbd class="cmd-kbd">ESC</kbd>
  </div>
  <div class="cmd-body" id="cmd-body">
    <div class="cmd-empty" id="cmd-hint">
      <i class='bx bx-search-alt'></i>
      Ketik minimal 2 karakter untuk mencari...
    </div>
  </div>
  <div class="cmd-footer">
    <span><kbd>↑</kbd><kbd>↓</kbd> navigasi</span>
    <span><kbd>↵</kbd> buka</span>
    <span><kbd>ESC</kbd> tutup</span>
  </div>
</div>

{{-- Dashboard standalone (not inside any section) --}}
<a href="{{ route('admin.dashboard') }}"
   class="sb-link {{ $dashboardActive ? 'active' : '' }}"
   data-title="Dashboard">
  <span class="sb-icon-wrap">
    <i class="bx bx-tachometer sb-icon"></i>
  </span>
  <span class="sb-link-copy">
    <span class="sb-link-title">Dashboard</span>
  </span>
</a>

@foreach($sections as $section)
@php
  $sectionActive = collect($section['items'])->contains(fn ($item) => $item['active']);
@endphp
  <details class="sb-group" {{ $sectionActive ? 'open' : '' }}>
    <summary class="sb-group-summary">
      <span class="sb-group-label">{{ $section['label'] }}</span>
      <i class='bx bx-chevron-right sb-group-caret'></i>
    </summary>
    <div class="sb-group-items">
      @foreach($section['items'] as $item)
      <a href="{{ $item['url'] ?? $item['route'] }}"
         class="sb-link {{ $item['active'] ? 'active' : '' }}"
         data-title="{{ $item['title'] }}"
         @isset($item['target']) target="{{ $item['target'] }}" rel="noopener noreferrer" @endisset>
        <span class="sb-icon-wrap">
          <i class="{{ $item['icon'] }} sb-icon"></i>
        </span>
        <span class="sb-link-copy">
          <span class="sb-link-title">{{ $item['title'] }}</span>
          @if(!empty($item['badge']))
            <span style="background:var(--red);color:#fff;font-size:.65rem;font-weight:700;padding:1px 6px;border-radius:10px;margin-left:6px;line-height:1.4;">{{ $item['badge'] }}</span>
          @endif
          @isset($item['target'])<i class='bx bx-link-external' style="font-size:.65rem;opacity:.5;margin-left:3px;"></i>@endisset
        </span>
      </a>
      @endforeach
    </div>
  </details>
@endforeach
