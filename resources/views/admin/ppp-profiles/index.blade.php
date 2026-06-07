@extends('layouts.app')
@section('title', 'PPPoE Profiles')

@section('styles')
<style>
    .router-kanban { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: .75rem; }
    .router-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: .9rem 1rem; display: flex; flex-direction: column; gap: .45rem; cursor: pointer; text-decoration: none; transition: box-shadow .15s, border-color .15s; }
    .router-card:hover { border-color: color-mix(in srgb, var(--blue) 45%, var(--border)); box-shadow: 0 4px 16px rgba(0,0,0,.08); text-decoration: none; }
    .router-card--active { border-color: var(--blue) !important; background: color-mix(in srgb, var(--blue) 6%, var(--surface)); box-shadow: 0 0 0 3px color-mix(in srgb, var(--blue) 18%, transparent); }
    .router-card-name { font-size: .875rem; font-weight: 700; color: var(--txt); display: flex; align-items: center; gap: .4rem; }
    .router-card-ip { font-size: .7rem; font-family: monospace; background: color-mix(in srgb, var(--orange) 10%, var(--surface-2)); color: var(--orange); padding: .12rem .45rem; border-radius: 5px; border: 1px solid color-mix(in srgb, var(--orange) 20%, var(--border)); display: inline-block; width: fit-content; }
    .router-card-active-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--blue); flex-shrink: 0; }
    .profile-table th { font-size: .75rem; text-transform: uppercase; color: var(--txt-3); font-weight: 600; }
    .profile-table td { font-size: .8125rem; vertical-align: middle; }
    .profile-badge { display: inline-flex; align-items: center; gap: .2rem; font-size: .7rem; padding: .15rem .5rem; border-radius: 999px; font-weight: 600; background: color-mix(in srgb, var(--blue) 12%, var(--surface)); color: var(--blue); border: 1px solid color-mix(in srgb, var(--blue) 25%, var(--border)); }
</style>
@endsection

@section('content')
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-user-circle'></i> MikroTik</div>
            <h1 class="ms-page-title">PPPoE Profiles</h1>
        </div>
        @if($selectedArea)
        <div class="ms-page-actions">
            <button type="button" class="ms-btn" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class='bx bx-plus'></i> Buat Profile
            </button>
        </div>
        @endif
    </div>

    {{-- Router / Area Selector --}}
    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title">Pilih Router / Area</h5>
                <div class="ms-panel-subtitle">Klik kartu area untuk memuat profile PPPoE</div>
            </div>
        </div>
        <div class="ms-panel-body">
            <div class="router-kanban">
                @foreach($areas as $area)
                @php $isActive = $selectedArea?->id == $area->id; @endphp
                <a href="{{ route('admin.ppp-profiles.index', ['area_id' => $area->id]) }}"
                   class="router-card {{ $isActive ? 'router-card--active' : '' }}">
                    <div class="router-card-name">
                        @if($isActive)
                            <div class="router-card-active-dot"></div>
                        @else
                            <i class='bx bx-router' style="color:var(--txt-3);font-size:.95rem;flex-shrink:0;"></i>
                        @endif
                        {{ $area->name }}
                    </div>
                    <div class="router-card-ip">{{ $area->router_ip }}</div>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Error --}}
    @if($error)
    <div class="ms-panel" style="border-color:color-mix(in srgb,var(--red) 25%,var(--border));background:color-mix(in srgb,var(--red) 6%,var(--surface));">
        <div class="ms-panel-body d-flex align-items-start gap-2" style="color:var(--red);">
            <i class='bx bx-error-circle mt-1' style="font-size:1.1rem;"></i>
            <div>
                <strong>Tidak Dapat Terhubung ke Router</strong><br>
                {{ $error }}<br>
                <small>Pastikan router MikroTik aktif, API port 8728 terbuka, dan kredensial benar.</small>
            </div>
        </div>
    </div>
    @endif

    {{-- Profiles Table --}}
    @if($selectedArea && !$error)
    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title">Profiles pada {{ $selectedArea->name }}</h5>
                <div class="ms-panel-subtitle">{{ count($profiles) }} profile ditemukan</div>
            </div>
        </div>
        <div class="ms-panel-body p-0">
            @if(count($profiles) === 0)
            <div style="text-align:center;padding:3rem;color:var(--txt-3);">
                <i class='bx bx-user-circle' style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                <p class="mb-0 fw-semibold">Tidak ada profile ditemukan</p>
                <p class="mb-0 mt-1" style="font-size:.8rem;">Klik "Buat Profile" untuk menambahkan profile baru.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover profile-table mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Rate Limit</th>
                            <th>Local Address</th>
                            <th>Remote Address</th>
                            <th>Subscribers</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($profiles as $profile)
                        <tr>
                            <td><strong>{{ $profile['name'] }}</strong></td>
                            <td><code style="font-size:.75rem;">{{ $profile['rate-limit'] ?: '-' }}</code></td>
                            <td>{{ $profile['local-address'] ?: '-' }}</td>
                            <td>{{ $profile['remote-address'] ?: '-' }}</td>
                            <td>
                                <span class="profile-badge">{{ $profile['subscribers'] }}</span>
                            </td>
                            <td class="text-end">
                                @if(!in_array($profile['name'], ['default', 'default-encryption']))
                                <button class="btn btn-sm btn-outline-primary me-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal"
                                    data-id="{{ $profile['id'] }}"
                                    data-name="{{ $profile['name'] }}"
                                    data-rate-limit="{{ $profile['rate-limit'] }}"
                                    data-local-address="{{ $profile['local-address'] }}"
                                    data-remote-address="{{ $profile['remote-address'] }}"
                                    data-dns-server="{{ $profile['dns-server'] }}"
                                    data-change-tcp-mss="{{ $profile['change-tcp-mss'] }}"
                                    data-only-one="{{ $profile['only-one'] }}">
                                    <i class='bx bx-edit-alt'></i>
                                </button>
                                <form method="POST" action="{{ route('admin.ppp-profiles.destroy') }}" class="d-inline"
                                    onsubmit="return confirm('Hapus profile {{ $profile['name'] }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="area_id" value="{{ $selectedArea->id }}">
                                    <input type="hidden" name="profile_id" value="{{ $profile['id'] }}">
                                    <input type="hidden" name="profile_name" value="{{ $profile['name'] }}">
                                    <button class="btn btn-sm btn-outline-danger"><i class='bx bx-trash'></i></button>
                                </form>
                                @else
                                <span style="font-size:.72rem;color:var(--txt-3);">System</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center" style="color:var(--txt-3);">Tidak ada profile ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Create Modal --}}
@if($selectedArea)
<div class="modal modal-blur fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.ppp-profiles.store') }}">
            @csrf
            <input type="hidden" name="area_id" value="{{ $selectedArea->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Profile Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Template Cepat</label>
                        <select class="form-select" id="templateSelect">
                            <option value="">-- Manual --</option>
                            <option value="5M/10M">5M/10M</option>
                            <option value="10M/20M">10M/20M</option>
                            <option value="20M/50M">20M/50M</option>
                            <option value="50M/100M">50M/100M</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Nama Profile</label>
                        <input type="text" name="name" class="form-control" id="createName" required
                            pattern="[a-zA-Z0-9\-_]+" placeholder="contoh: 10M-20M">
                        <small class="form-hint">Huruf, angka, strip, underscore saja</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Rate Limit (upload/download)</label>
                        <input type="text" name="rate_limit" class="form-control" id="createRateLimit" required
                            placeholder="5M/10M">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Local Address</label>
                        <input type="text" name="local_address" class="form-control" placeholder="opsional">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remote Address</label>
                        <input type="text" name="remote_address" class="form-control" placeholder="opsional">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">DNS Server</label>
                        <input type="text" name="dns_server" class="form-control" placeholder="8.8.8.8">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Change TCP MSS</label>
                            <select name="change_tcp_mss" class="form-select">
                                <option value="">default</option>
                                <option value="yes">yes</option>
                                <option value="no">no</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Only One</label>
                            <select name="only_one" class="form-select">
                                <option value="">default</option>
                                <option value="yes">yes</option>
                                <option value="no">no</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Buat Profile</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal modal-blur fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.ppp-profiles.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="area_id" value="{{ $selectedArea->id }}">
            <input type="hidden" name="profile_id" id="editId">
            <input type="hidden" name="profile_name" id="editNameHidden">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profile: <span id="editTitle"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Rate Limit (upload/download)</label>
                        <input type="text" name="rate_limit" class="form-control" id="editRateLimit" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Local Address</label>
                        <input type="text" name="local_address" class="form-control" id="editLocalAddress">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remote Address</label>
                        <input type="text" name="remote_address" class="form-control" id="editRemoteAddress">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">DNS Server</label>
                        <input type="text" name="dns_server" class="form-control" id="editDnsServer">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Change TCP MSS</label>
                            <select name="change_tcp_mss" class="form-select" id="editTcpMss">
                                <option value="">default</option>
                                <option value="yes">yes</option>
                                <option value="no">no</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Only One</label>
                            <select name="only_one" class="form-select" id="editOnlyOne">
                                <option value="">default</option>
                                <option value="yes">yes</option>
                                <option value="no">no</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Template quick-fill
    const tmpl = document.getElementById('templateSelect');
    if (tmpl) {
        tmpl.addEventListener('change', function() {
            if (this.value) {
                document.getElementById('createRateLimit').value = this.value;
                document.getElementById('createName').value = this.value.replace('/', '-');
            }
        });
    }

    // Edit modal population
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            document.getElementById('editId').value = btn.dataset.id;
            document.getElementById('editNameHidden').value = btn.dataset.name;
            document.getElementById('editTitle').textContent = btn.dataset.name;
            document.getElementById('editRateLimit').value = btn.dataset.rateLimit;
            document.getElementById('editLocalAddress').value = btn.dataset.localAddress;
            document.getElementById('editRemoteAddress').value = btn.dataset.remoteAddress;
            document.getElementById('editDnsServer').value = btn.dataset.dnsServer;
            document.getElementById('editTcpMss').value = btn.dataset.changeTcpMss;
            document.getElementById('editOnlyOne').value = btn.dataset.onlyOne;
        });
    }
});
</script>
@endsection
