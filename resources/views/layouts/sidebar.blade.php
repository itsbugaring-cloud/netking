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
          'icon' => 'bx bx-wifi',
          'title' => 'PPPoE',
        ] : null,
        $isAdmin ? [
          'route' => route('admin.olts.index'),
          'active' => request()->routeIs('admin.olts*'),
          'icon' => 'bx bx-server',
          'title' => 'OLT & ONT',
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
          'route' => route('admin.invoices.index'),
          'active' => request()->routeIs('admin.invoices*'),
          'icon' => 'bx bx-receipt',
          'title' => 'Tagihan',
        ],
        $isAdmin ? [
          'route' => route('admin.invoices.paymentQueue'),
          'active' => request()->routeIs('admin.invoices.paymentQueue'),
          'icon' => 'bx bx-image-check',
          'title' => 'Review Bukti Bayar',
          'badge' => \App\Models\Invoice::where('payment_review_status','submitted')->where('status','unpaid')->count() ?: null,
        ] : null,
        $isAdmin ? [
          'route' => route('admin.commissions.index'),
          'active' => request()->routeIs('admin.commissions*'),
          'icon' => 'bx bx-dollar-circle',
          'title' => 'Komisi',
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

    if ($isAdmin) {
      $sections[] = [
      'label' => 'Monitoring',
      'items' => [
        [
          'route' => route('admin.nms.dashboard'),
          'active' => request()->routeIs('admin.nms.dashboard'),
          'icon' => 'bx bx-pulse',
          'title' => 'Ringkasan NMS',
        ],
        [
          'route' => route('admin.nms.devices'),
          'active' => request()->routeIs('admin.nms.devices'),
          'icon' => 'bx bx-chip',
          'title' => 'Perangkat NMS',
        ],
        [
          'route' => route('admin.nms.ports'),
          'active' => request()->routeIs('admin.nms.ports'),
          'icon' => 'bx bx-transfer',
          'title' => 'Monitor Port',
        ],
        [
          'route' => route('admin.nms.alerts'),
          'active' => request()->routeIs('admin.nms.alerts'),
          'icon' => 'bx bx-bell',
          'title' => 'Aturan Alert',
        ],
        [
          'route' => route('admin.nms.syslog'),
          'active' => request()->routeIs('admin.nms.syslog'),
          'icon' => 'bx bx-file',
          'title' => 'Log Sistem',
        ],
        [
          'route' => route('admin.nms.topology'),
          'active' => request()->routeIs('admin.nms.topology'),
          'icon' => 'bx bx-git-repo-forked',
          'title' => 'Peta Topologi',
        ],
        [
          'route' => route('admin.nms.live-traffic'),
          'active' => request()->routeIs('admin.nms.live-traffic*'),
          'icon' => 'bx bx-pulse',
          'title' => 'Live Traffic',
        ],
        [
          'route' => route('admin.nms.ip-pool'),
          'active' => request()->routeIs('admin.nms.ip-pool*'),
          'icon' => 'bx bx-network-chart',
          'title' => 'IP Pool',
        ],
        [
          'route' => route('admin.nms.bgp'),
          'active' => request()->routeIs('admin.nms.bgp*'),
          'icon' => 'bx bx-git-branch',
          'title' => 'BGP Monitor',
        ],
        [
          'route' => route('admin.nms.diagnostics'),
          'active' => request()->routeIs('admin.nms.diagnostics*'),
          'icon' => 'bx bx-terminal',
          'title' => 'Diagnostics',
        ],
      ],
      ];
    }
  }

  if (! $isFinance) {
    $sections[] = [
      'label' => 'Tools',
      'items' => [
        [
          'route'  => route('admin.signal.index'),
          'active' => request()->routeIs('admin.signal*'),
          'icon'   => 'bx bx-wifi-2',
          'title'  => 'Cek Sinyal ONT',
        ],
      ],
    ];
  }

  if (! $isFinance) {
    $sections[] = [
      'label' => 'Dukungan',
      'items' => array_values(array_filter([
        $isAdmin ? [
          'route' => route('admin.whatsapp.index'),
          'active' => request()->routeIs('admin.whatsapp*'),
          'icon' => 'bx bxl-whatsapp',
          'title' => 'WhatsApp',
        ] : null,
      ])),
    ];
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
      'label' => 'Inventaris',
      'items' => [
        [
          'route'  => route('admin.inventory.dashboard'),
          'active' => request()->routeIs('admin.inventory.dashboard'),
          'icon'   => 'bx bx-tachometer',
          'title'  => 'Ringkasan Inventaris',
        ],
        [
          'route'  => route('admin.inventory.units.index'),
          'active' => request()->routeIs('admin.inventory.units*'),
          'icon'   => 'bx bx-chip',
          'title'  => 'Unit Perangkat',
        ],
        [
          'route'  => route('admin.inventory.kabel.index'),
          'active' => request()->routeIs('admin.inventory.kabel*'),
          'icon'   => 'bx bx-transfer',
          'title'  => 'Stok Kabel',
        ],
        [
          'route'  => route('admin.inventory.qty.index'),
          'active' => request()->routeIs('admin.inventory.qty*'),
          'icon'   => 'bx bx-box',
          'title'  => 'Stok Barang',
        ],
        [
          'route'  => route('admin.inventory.master-barang.index'),
          'active' => request()->routeIs('admin.inventory.master-barang*'),
          'icon'   => 'bx bx-list-ul',
          'title'  => 'Data Barang',
        ],
        [
          'route'  => route('admin.inventory.lokasi.index'),
          'active' => request()->routeIs('admin.inventory.lokasi*'),
          'icon'   => 'bx bx-map-pin',
          'title'  => 'Lokasi Gudang',
        ],
        [
          'route'  => route('admin.inventory.history.index'),
          'active' => request()->routeIs('admin.inventory.history*'),
          'icon'   => 'bx bx-history',
          'title'  => 'Riwayat Transaksi',
        ],
      ],
    ];
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
    <input type="text" id="global-search" class="cmd-input" placeholder="Cari pelanggan, invoice, area, OLT..." autocomplete="off" spellcheck="false">
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
