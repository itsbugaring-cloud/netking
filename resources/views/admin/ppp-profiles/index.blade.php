@extends('layouts.app')

@section('title', 'PPPoE Profiles')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto">
                <h2 class="page-title">PPPoE Profiles</h2>
                <div class="text-muted mt-1">Kelola profile PPPoE langsung di router MikroTik</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        {{-- Area Selector --}}
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.ppp-profiles.index') }}" class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label">Pilih Area / Router</label>
                        <select name="area_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Pilih Area --</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ ($selectedArea && $selectedArea->id == $area->id) ? 'selected' : '' }}>
                                    {{ $area->name }} ({{ $area->router_ip }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if($selectedArea)
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                            <i class="ti ti-plus"></i> Buat Profile
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        @if($error)
            <div class="alert alert-warning">
                <i class="ti ti-alert-triangle"></i> {{ $error }}
            </div>
        @endif

        @if($selectedArea && !$error)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Profiles pada {{ $selectedArea->name }}</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Rate Limit</th>
                            <th>Local Address</th>
                            <th>Remote Address</th>
                            <th>Subscribers</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($profiles as $profile)
                        <tr>
                            <td><strong>{{ $profile['name'] }}</strong></td>
                            <td><code>{{ $profile['rate-limit'] ?: '-' }}</code></td>
                            <td>{{ $profile['local-address'] ?: '-' }}</td>
                            <td>{{ $profile['remote-address'] ?: '-' }}</td>
                            <td>
                                <span class="badge bg-blue-lt">{{ $profile['subscribers'] }}</span>
                            </td>
                            <td>
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
                                    <i class="ti ti-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.ppp-profiles.destroy') }}" class="d-inline"
                                    onsubmit="return confirm('Hapus profile {{ $profile['name'] }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="area_id" value="{{ $selectedArea->id }}">
                                    <input type="hidden" name="profile_id" value="{{ $profile['id'] }}">
                                    <input type="hidden" name="profile_name" value="{{ $profile['name'] }}">
                                    <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                                </form>
                                @else
                                <span class="text-muted">System</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Tidak ada profile ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
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
