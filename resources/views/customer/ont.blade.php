@extends('customer.layouts.dashboard')

@section('title', 'ONT Saya - Portal Pelanggan')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Perangkat ONT Saya</h2>
                <div class="text-muted mt-1">Kelola router internet rumah Anda</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <div class="d-flex">
                <div><i class="ti ti-check icon alert-icon"></i></div>
                <div>{{ session('success') }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            <div class="d-flex">
                <div><i class="ti ti-alert-circle icon alert-icon"></i></div>
                <div>{{ session('error') }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
        @endif

        @if(!$customer->ont_sn)
        <div class="empty">
            <div class="empty-img">
                <i class="ti ti-wifi-off text-muted" style="font-size: 64px;"></i>
            </div>
            <p class="empty-title">Tidak Ada Perangkat Terhubung</p>
            <p class="empty-subtitle text-muted">
                Belum ada router ONT yang terhubung ke akun Anda. Silakan hubungi dukungan.
            </p>
        </div>
        @elseif(!$device)
        <div class="empty">
            <div class="empty-img">
                <i class="ti ti-plug-x text-muted" style="font-size: 64px;"></i>
            </div>
            <p class="empty-title">Perangkat Tidak Terhubung</p>
            <p class="empty-subtitle text-muted">
                Router Anda (SN: {{ $customer->ont_sn }}) tidak dapat dijangkau. Pastikan perangkat dalam keadaan menyala.
            </p>
        </div>
        @else

        <div class="row row-cards">
            <!-- ONT Status Card -->
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-router me-2"></i>Status Perangkat</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <span class="avatar avatar-lg bg-{{ $device['online'] ? 'green' : 'red' }}-lt me-3">
                                <i class="ti ti-router" style="font-size: 1.5rem;"></i>
                            </span>
                            <div>
                                <h3 class="mb-0">{{ $device['manufacturer'] }} {{ $device['model'] }}</h3>
                                <div class="text-muted">Nomor Seri: {{ $device['serial'] }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="subheader mb-1">Status Saat Ini</div>
                            @if($device['online'])
                            <span class="badge bg-success"><i class="ti ti-circle-filled me-1" style="font-size: 8px;"></i> Online</span>
                            <span class="text-muted ms-2 fs-5">Terakhir terlihat: {{ $device['last_seen'] }}</span>
                            @else
                            <span class="badge bg-danger"><i class="ti ti-circle-filled me-1" style="font-size: 8px;"></i> Offline</span>
                            <span class="text-muted ms-2 fs-5">Terakhir terlihat: {{ $device['last_seen'] }}</span>
                            @endif
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#modal-reboot" {{ !$device['online'] ? 'disabled' : '' }}>
                                <i class="ti ti-power icon mb-1 me-1"></i> Reboot Router
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WiFi Info & Settings -->
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-wifi me-2"></i>Pengaturan WiFi</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('customer.ont.wifi') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Nama WiFi (SSID)</label>
                                <input type="text" name="ssid" class="form-control @error('ssid') is-invalid @enderror" value="{{ old('ssid', $device['ssid'] ?? '') }}" required minlength="4" maxlength="32">
                                @error('ssid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Password Baru</label>
                                <div class="input-group input-group-flat">
                                    <input type="password" name="password" id="wifi-password" class="form-control @error('password') is-invalid @enderror" placeholder="Kosongkan untuk tetap menggunakan password lama" minlength="8" maxlength="63" autocomplete="new-password">
                                    <span class="input-group-text">
                                        <a href="#" class="link-secondary" data-bs-toggle="tooltip" aria-label="Show password" id="toggle-password">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </span>
                                </div>
                                <small class="form-hint">Kosongkan jika tidak ingin mengubah password.</small>
                                @error('password')<div class="d-block invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100" {{ !$device['online'] ? 'disabled' : '' }}>
                                <i class="ti ti-device-floppy icon me-1"></i> Simpan Pengaturan WiFi
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Technical Info -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Informasi Teknis</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="datagrid datagrid-hover p-3">
                            <div class="datagrid-item">
                                <div class="datagrid-title">WAN IP Address</div>
                                <div class="datagrid-content"><code>{{ $device['wan_ip'] ?? 'Tidak diketahui' }}</code></div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Waktu Aktif</div>
                                <div class="datagrid-content">{{ $device['uptime'] ?? 'Tidak diketahui' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Versi Firmware</div>
                                <div class="datagrid-content">{{ $device['firmware'] ?? 'Tidak diketahui' }}</div>
                            </div>
                            @if($customer->ont)
                            <div class="datagrid-item mt-2">
                                <div class="datagrid-title">Kualitas Sinyal</div>
                                <div class="datagrid-content">
                                    @php $sig = $customer->ont->signal_quality; @endphp
                                    @if($sig == 'excellent') <span class="badge bg-green">Sangat Baik ({{ $customer->ont->rx_power }} dBm)</span>
                                    @elseif($sig == 'good') <span class="badge bg-blue">Baik ({{ $customer->ont->rx_power }} dBm)</span>
                                    @elseif($sig == 'fair') <span class="badge bg-yellow">Cukup ({{ $customer->ont->rx_power }} dBm)</span>
                                    @elseif($sig == 'weak') <span class="badge bg-red">Lemah ({{ $customer->ont->rx_power }} dBm)</span>
                                    @else <span>Tidak diketahui</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Reboot Confirmation Modal -->
        <div class="modal modal-blur fade" id="modal-reboot" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <i class="ti ti-alert-triangle text-warning mb-3" style="font-size: 48px;"></i>
                        <h3>Reboot Router?</h3>
                        <div class="text-muted">Apakah Anda yakin ingin merestart router? Koneksi internet Anda akan terputus sekitar 2-3 menit selama proses restart.</div>
                    </div>
                    <div class="modal-footer">
                        <div class="w-100">
                            <div class="row">
                                <div class="col">
                                    <a href="#" class="btn w-100" data-bs-dismiss="modal">Batal</a>
                                </div>
                                <div class="col">
                                    <form action="{{ route('customer.ont.reboot') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-warning w-100">Ya, reboot sekarang</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @endif

    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggle-password');
        const passInput = document.getElementById('wifi-password');

        if (toggleBtn && passInput) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passInput.setAttribute('type', type);

                const icon = this.querySelector('i');
                if (type === 'password') {
                    icon.classList.remove('ti-eye-off');
                    icon.classList.add('ti-eye');
                } else {
                    icon.classList.remove('ti-eye');
                    icon.classList.add('ti-eye-off');
                }
            });
        }
    });
</script>
@endsection