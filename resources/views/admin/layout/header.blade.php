@php
$unpaidInvoices = \App\Models\Invoice::where('status', 'unpaid')->count();
$notifCount = $unpaidInvoices;
@endphp
<header class="navbar navbar-expand-md d-none d-lg-flex d-print-none">
    <div class="container-xl">
        <div class="navbar-nav flex-row order-md-last">
            <!-- Notifications Dropdown -->
            <div class="nav-item dropdown d-none d-md-flex me-3">
                <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Show notifications">
                    <i class="ti ti-bell"></i>
                    @if($notifCount > 0)
                    <span class="badge bg-red badge-notification badge-blink">{{ $notifCount }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Notifikasi</h3>
                        </div>
                        <div class="list-group list-group-flush list-group-hoverable">
                            @if($unpaidInvoices > 0)
                            <a href="{{ route('admin.invoices.index', ['status' => 'unpaid']) }}" class="list-group-item list-group-item-action">
                                <div class="row align-items-center">
                                    <div class="col-auto"><span class="status-dot bg-yellow d-block"></span></div>
                                    <div class="col text-truncate">
                                        <div class="d-block text-reset fw-semibold">{{ $unpaidInvoices }} Tagihan Belum Lunas</div>
                                        <div class="d-block text-muted text-truncate mt-n1">Menunggu pembayaran</div>
                                    </div>
                                </div>
                            </a>
                            @endif
                            @if($notifCount === 0)
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col-auto"><span class="status-dot status-dot-animated bg-green d-block"></span></div>
                                    <div class="col text-truncate">
                                        <div class="d-block text-reset">Semua beres</div>
                                        <div class="d-block text-muted text-truncate mt-n1">Tidak ada item tertunda</div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Dropdown -->
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                    <span class="avatar avatar-sm" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=206bc4&color=fff)"></span>
                    <div class="d-none d-xl-block ps-2">
                        <div>{{ auth()->user()->name ?? 'Administrator' }}</div>
                        <div class="mt-1 small text-muted">{{ ucfirst(auth()->user()->role ?? 'admin') }}</div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="{{ route('admin.profile') }}" class="dropdown-item">
                        <i class="ti ti-user icon dropdown-item-icon"></i>
                        Profil
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('admin.logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="ti ti-logout icon dropdown-item-icon"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
