@extends('layouts.app')
@section('title', 'Tambah Perangkat OLT')

@section('content')
<div class="ms-page">
<div class="ms-page-head">
    <div>
        <div class="ms-page-kicker"><i class='bx bx-server'></i> Inventaris OLT</div>
        <h1 class="ms-page-title">Tambah Perangkat OLT</h1>
    </div>
</div>

<form action="{{ route('admin.olts.store') }}" method="POST">
    @csrf

    <div class="row g-3">
        {{-- Informasi Dasar --}}
        <div class="col-md-6">
            <div class="ms-panel">
                <div class="ms-panel-head fw-semibold" style="font-size:.9375rem;">
                    <i class='bx bx-server me-2'></i>Informasi Dasar
                </div>
                <div class="ms-panel-body">
                    <div class="mb-3">
                        <label class="form-label">Nama OLT <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="mis. OLT Cicaheum" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Merek <span class="text-danger">*</span></label>
                            <input type="text" name="brand" class="form-control @error('brand') is-invalid @enderror"
                                value="{{ old('brand') }}" placeholder="mis. Tenda / C-Data / HSGQ" required>
                            @error('brand') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Model <span class="text-danger">*</span></label>
                            <input type="text" name="model" class="form-control @error('model') is-invalid @enderror"
                                value="{{ old('model') }}" placeholder="mis. TES7001 / FD1602S" required>
                            @error('model') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">IP Address <span class="text-danger">*</span></label>
                        <input type="text" name="ip_address" class="form-control @error('ip_address') is-invalid @enderror"
                            value="{{ old('ip_address') }}" placeholder="10.88.0.x" required>
                        @error('ip_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Area</label>
                        <select name="area_id" class="form-select">
                            <option value="">— Pilih Area —</option>
                            @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Protokol Utama <span class="text-danger">*</span></label>
                        <select name="preferred_protocol" class="form-select" id="protocolSelect">
                            @foreach(['ssh' => 'SSH', 'snmp' => 'SNMP', 'telnet' => 'Telnet', 'rest' => 'REST API'] as $k => $v)
                            <option value="{{ $k }}" {{ old('preferred_protocol', 'ssh') === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kredensial Protokol --}}
        <div class="col-md-6">
            {{-- SSH --}}
            <div class="ms-panel mb-3" id="sshCard">
                <div class="ms-panel-head fw-semibold" style="font-size:.9375rem;">
                    <i class='bx bx-terminal me-2'></i>Kredensial SSH
                </div>
                <div class="ms-panel-body">
                    <div class="row g-3">
                        <div class="col-8">
                            <label class="form-label">Username</label>
                            <input type="text" name="ssh_user" class="form-control" value="{{ old('ssh_user') }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Port</label>
                            <input type="number" name="ssh_port" class="form-control" value="{{ old('ssh_port', 22) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Password</label>
                            <input type="password" name="ssh_pass" class="form-control" value="{{ old('ssh_pass') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- SNMP --}}
            <div class="ms-panel mb-3" id="snmpCard">
                <div class="ms-panel-head fw-semibold" style="font-size:.9375rem;">
                    <i class='bx bx-broadcast me-2'></i>Pengaturan SNMP
                </div>
                <div class="ms-panel-body">
                    <div class="row g-3">
                        <div class="col-8">
                            <label class="form-label">Community String</label>
                            <input type="text" name="snmp_community" class="form-control" value="{{ old('snmp_community', 'public') }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Versi</label>
                            <select name="snmp_version" class="form-select">
                                <option value="2c" {{ old('snmp_version') === '2c' ? 'selected' : '' }}>v2c</option>
                                <option value="1" {{ old('snmp_version') === '1' ? 'selected' : '' }}>v1</option>
                                <option value="3" {{ old('snmp_version') === '3' ? 'selected' : '' }}>v3</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Telnet --}}
            <div class="ms-panel mb-3" id="telnetCard">
                <div class="ms-panel-head fw-semibold" style="font-size:.9375rem;">
                    <i class='bx bx-link me-2'></i>Kredensial Telnet
                </div>
                <div class="ms-panel-body">
                    <div class="row g-3">
                        <div class="col-8">
                            <label class="form-label">Username</label>
                            <input type="text" name="telnet_user" class="form-control" value="{{ old('telnet_user') }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Port</label>
                            <input type="number" name="telnet_port" class="form-control" value="{{ old('telnet_port', 23) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Password</label>
                            <input type="password" name="telnet_pass" class="form-control" value="{{ old('telnet_pass') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- REST --}}
            <div class="ms-panel mb-3" id="restCard">
                <div class="ms-panel-head fw-semibold" style="font-size:.9375rem;">
                    <i class='bx bx-code-alt me-2'></i>REST API
                </div>
                <div class="ms-panel-body">
                    <div class="mb-3">
                        <label class="form-label">URL API</label>
                        <input type="text" name="api_url" class="form-control" value="{{ old('api_url') }}" placeholder="https://olt.example.com/api">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Token API</label>
                        <input type="text" name="api_token" class="form-control" value="{{ old('api_token') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2">
        <button type="submit" class="ms-btn"><i class='bx bx-save me-1'></i> Simpan OLT</button>
        <a href="{{ route('admin.olts.index') }}" class="ms-btn-secondary">Batal</a>
    </div>
</form>
</div>
@endsection
@section('scripts')
<script>
$(function() {
    const cards = { ssh: $('#sshCard'), snmp: $('#snmpCard'), telnet: $('#telnetCard'), rest: $('#restCard') };
    function toggleCards(proto) {
        $.each(cards, function(k, el) { el.hide(); });
        if (cards[proto]) cards[proto].show();
    }
    $('#protocolSelect').on('change', function() { toggleCards($(this).val()); });
    toggleCards($('#protocolSelect').val() || 'telnet');
});
</script>
@endsection
