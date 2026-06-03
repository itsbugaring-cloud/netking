@extends('layouts.app')
@section('title', 'Ubah OLT — ' . $olt->name)

@section('content')
<div class="ms-page">
<div class="ms-page-head">
    <div>
        <div class="ms-page-kicker"><i class='bx bx-server'></i> Inventaris OLT</div>
        <h1 class="ms-page-title">Ubah OLT</h1>
    </div>
</div>

<form action="{{ route('admin.olts.update', $olt) }}" method="POST">
    @csrf @method('PUT')

    <div class="row g-3">
        <div class="col-md-6">
            <div class="ms-panel">
                <div class="ms-panel-head fw-semibold"><i class='bx bx-server me-2'></i>Informasi Dasar</div>
                <div class="ms-panel-body">
                    <div class="mb-3">
                        <label class="form-label">Nama OLT <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $olt->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Merek</label>
                            <input type="text" name="brand" class="form-control" value="{{ old('brand', $olt->brand) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control" value="{{ old('model', $olt->model) }}">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">IP Address <span class="text-danger">*</span></label>
                        <input type="text" name="ip_address" class="form-control @error('ip_address') is-invalid @enderror"
                            value="{{ old('ip_address', $olt->ip_address) }}" required>
                        @error('ip_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Area</label>
                        <select name="area_id" class="form-select">
                            <option value="">— Pilih Area —</option>
                            @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ old('area_id', $olt->area_id) == $area->id ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Protokol Utama</label>
                        <select name="preferred_protocol" class="form-select" id="protocolSelect">
                            @foreach(['ssh' => 'SSH', 'snmp' => 'SNMP', 'telnet' => 'Telnet', 'rest' => 'REST API'] as $k => $v)
                            <option value="{{ $k }}" {{ old('preferred_protocol', $olt->preferred_protocol) === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $olt->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="ms-panel mb-3" id="sshCard">
                <div class="ms-panel-head fw-semibold"><i class='bx bx-terminal me-2'></i>SSH</div>
                <div class="ms-panel-body">
                    <div class="row g-3">
                        <div class="col-8"><label class="form-label">Username</label><input type="text" name="ssh_user" class="form-control" value="{{ old('ssh_user', $olt->ssh_user) }}"></div>
                        <div class="col-4"><label class="form-label">Port</label><input type="number" name="ssh_port" class="form-control" value="{{ old('ssh_port', $olt->ssh_port) }}"></div>
                        <div class="col-12"><label class="form-label">Password</label><input type="password" name="ssh_pass" class="form-control" value="{{ old('ssh_pass', $olt->ssh_pass) }}"></div>
                    </div>
                </div>
            </div>
            <div class="ms-panel mb-3" id="snmpCard">
                <div class="ms-panel-head fw-semibold"><i class='bx bx-broadcast me-2'></i>SNMP</div>
                <div class="ms-panel-body">
                    <div class="row g-3">
                        <div class="col-8"><label class="form-label">Community</label><input type="text" name="snmp_community" class="form-control" value="{{ old('snmp_community', $olt->snmp_community) }}"></div>
                        <div class="col-4"><label class="form-label">Ver</label>
                            <select name="snmp_version" class="form-select">
                                @foreach(['2c','1','3'] as $v)<option value="{{ $v }}" {{ old('snmp_version', $olt->snmp_version) === $v ? 'selected' : '' }}>{{ $v }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ms-panel mb-3" id="telnetCard">
                <div class="ms-panel-head fw-semibold"><i class='bx bx-link me-2'></i>Telnet</div>
                <div class="ms-panel-body">
                    <div class="row g-3">
                        <div class="col-8"><label class="form-label">Username</label><input type="text" name="telnet_user" class="form-control" value="{{ old('telnet_user', $olt->telnet_user) }}"></div>
                        <div class="col-4"><label class="form-label">Port</label><input type="number" name="telnet_port" class="form-control" value="{{ old('telnet_port', $olt->telnet_port) }}"></div>
                        <div class="col-12"><label class="form-label">Password</label><input type="password" name="telnet_pass" class="form-control" value="{{ old('telnet_pass', $olt->telnet_pass) }}"></div>
                    </div>
                </div>
            </div>
            <div class="ms-panel" id="restCard">
                <div class="ms-panel-head fw-semibold"><i class='bx bx-code-alt me-2'></i>REST API</div>
                <div class="ms-panel-body">
                    <div class="mb-3"><label class="form-label">URL API</label><input type="text" name="api_url" class="form-control" value="{{ old('api_url', $olt->api_url) }}"></div>
                    <div class="mb-0"><label class="form-label">Token API</label><input type="text" name="api_token" class="form-control" value="{{ old('api_token', $olt->api_token) }}"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2">
        <button type="submit" class="ms-btn"><i class='bx bx-save me-1'></i> Perbarui OLT</button>
        <a href="{{ route('admin.olts.show', $olt) }}" class="ms-btn-secondary">Batal</a>
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
