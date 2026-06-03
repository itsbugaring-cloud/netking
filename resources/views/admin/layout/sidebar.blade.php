<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
    @php
        $role = auth()->user()->role ?? null;
    @endphp
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="{{ route('admin.dashboard') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-world me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                    <path d="M3.6 9h16.8" />
                    <path d="M3.6 15h16.8" />
                    <path d="M11.5 3a17 17 0 0 0 0 18" />
                    <path d="M12.5 3a17 17 0 0 1 0 18" />
                </svg>
                NETKING
            </a>
        </h1>

        <div class="navbar-nav flex-row d-lg-none">
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                    <span class="avatar avatar-sm" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=206bc4&color=fff)"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="{{ route('admin.dashboard') }}" class="dropdown-item">Dasbor</a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Keluar</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
                @if($role !== 'finance')
                <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.dashboard') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-dashboard"></i>
                        </span>
                        <span class="nav-link-title">Dasbor</span>
                    </a>
                </li>
                @endif

                @if($role === 'admin')
                <li class="nav-item {{ request()->routeIs('admin.areas.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.areas.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-map-pin"></i>
                        </span>
                        <span class="nav-link-title">Area</span>
                    </a>
                </li>

                @if(\Illuminate\Support\Facades\Route::has('admin.partners.index'))
                <li class="nav-item {{ request()->routeIs('admin.partners.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.partners.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-users"></i>
                        </span>
                        <span class="nav-link-title">Mitra</span>
                    </a>
                </li>
                @endif
                @endif

                <li class="nav-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.customers.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-user-check"></i>
                        </span>
                        <span class="nav-link-title">Pelanggan</span>
                    </a>
                </li>

                @if($role === 'admin')
                <li class="nav-item {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.packages.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-package"></i>
                        </span>
                        <span class="nav-link-title">Paket</span>
                    </a>
                </li>
                @endif

                <li class="nav-item {{ request()->routeIs('admin.invoices.*') || request()->routeIs('admin.billing.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.invoices.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-file-invoice"></i>
                        </span>
                        <span class="nav-link-title">Tagihan</span>
                    </a>
                </li>

                @if(in_array($role, ['admin', 'finance'], true))
                <li class="nav-item {{ request()->routeIs('admin.billing.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.billing.calendar.view') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-calendar-month"></i>
                        </span>
                        <span class="nav-link-title">Billing</span>
                    </a>
                </li>
                @endif

                @if($role === 'admin')
                <li class="nav-item {{ request()->routeIs('admin.commissions.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.commissions.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-cash"></i>
                        </span>
                        <span class="nav-link-title">Komisi</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.settings') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-settings"></i>
                        </span>
                        <span class="nav-link-title">Pengaturan</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.telegram.requests.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.telegram.requests.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-brand-telegram"></i>
                        </span>
                        <span class="nav-link-title">Telegram Request</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </div>
</aside>
