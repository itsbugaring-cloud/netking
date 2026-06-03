@extends('layouts.app')
@section('title', 'Add Customer')

@section('content')
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4>Add Customer</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.customers.index') }}" class="btn btn-sm" style="background:var(--hover-bg); color:var(--txt);">
        <i class='bx bx-arrow-back me-1'></i> Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <form action="{{ route('admin.customers.store') }}" method="POST">
            @csrf

            {{-- Customer Info --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class='bx bx-user me-2' style="color:var(--blue);"></i>Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone') }}" placeholder="e.g. 081234567890">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                            value="{{ old('address') }}" placeholder="Street address">
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ONT Serial Number</label>
                                <input type="text" name="ont_sn" class="form-control @error('ont_sn') is-invalid @enderror"
                                    value="{{ old('ont_sn') }}" placeholder="e.g. HWTC12345678">
                                @error('ont_sn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Area <span class="text-danger">*</span></label>
                                <select name="area_id" id="area-select" class="form-select @error('area_id') is-invalid @enderror" required>
                                    <option value="">— Select Area —</option>
                                    @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ old('area_id', auth()->user()->role === 'partner' ? auth()->user()->area_id : '') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                    @endforeach
                                </select>
                                @error('area_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Partner (Reseller)</label>
                                <select name="partner_id" class="form-select @error('partner_id') is-invalid @enderror">
                                    <option value="">— Direct / No Partner —</option>
                                    @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id ? 'selected' : '' }}>{{ $partner->name }}</option>
                                    @endforeach
                                </select>
                                @error('partner_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ODP</label>
                                <select name="odp_id" class="form-select @error('odp_id') is-invalid @enderror">
                                    <option value="">— No ODP —</option>
                                    @foreach($odps ?? [] as $odp)
                                    <option value="{{ $odp->id }}" {{ old('odp_id') == $odp->id ? 'selected' : '' }}>{{ $odp->name }} ({{ $odp->code }}) — {{ $odp->available_slots }} slots</option>
                                    @endforeach
                                </select>
                                @error('odp_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ODP Port</label>
                                <input type="number" name="odp_port" class="form-control @error('odp_port') is-invalid @enderror"
                                    value="{{ old('odp_port') }}" min="1" max="128" placeholder="Port number">
                                @error('odp_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PPPoE Config --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class='bx bx-wifi me-2' style="color:var(--blue);"></i>PPPoE & Billing</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">PPPoE Username <span class="text-danger">*</span></label>
                                <input type="text" name="pppoe_user" class="form-control @error('pppoe_user') is-invalid @enderror"
                                    value="{{ old('pppoe_user') }}" placeholder="e.g. pelanggan001" required>
                                @error('pppoe_user')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">PPPoE Password <span class="text-danger">*</span></label>
                                <input type="text" name="pppoe_pass" class="form-control @error('pppoe_pass') is-invalid @enderror"
                                    value="{{ old('pppoe_pass') }}" placeholder="PPPoE password" required>
                                @error('pppoe_pass')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Package / Profile <span class="text-danger">*</span>
                                    <small class="text-muted">(MikroTik PPPoE Profile)</small>
                                </label>
                                <select name="package_id" id="package-select" class="form-select @error('package_id') is-invalid @enderror" required>
                                    <option value="">— Select Package —</option>
                                    @foreach($packages ?? [] as $pkg)
                                    <option value="{{ $pkg->id }}"
                                        data-price="{{ $pkg->price }}"
                                        data-profile="{{ $pkg->mikrotik_profile }}"
                                        data-speed="{{ $pkg->speed_down }}/{{ $pkg->speed_up }}"
                                        {{ old('package_id') == $pkg->id ? 'selected' : '' }}>
                                        {{ $pkg->name }} — {{ $pkg->speed_down }}M/{{ $pkg->speed_up }}M · Rp {{ number_format($pkg->price, 0, ',', '.') }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Local Address
                                    <small class="text-muted">(pilih existing atau ketik manual)</small>
                                </label>
                                <select id="local-address-select" class="form-select mb-2" style="display:none;">
                                    <option value="">— Pilih dari existing —</option>
                                </select>
                                <input type="text" name="local_address" id="local-address"
                                    class="form-control @error('local_address') is-invalid @enderror"
                                    value="{{ old('local_address') }}" placeholder="Pilih area dulu...">
                                @error('local_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">App/Portal Password
                                    <span class="text-danger">*</span>
                                    <small class="text-muted">(for Android app login)</small>
                                </label>
                                <input type="text" name="portal_password" class="form-control @error('portal_password') is-invalid @enderror"
                                    value="{{ old('portal_password') }}" placeholder="App login password" required>
                                @error('portal_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Monthly Package Price (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="package_price" id="package-price" class="form-control @error('package_price') is-invalid @enderror"
                                    value="{{ old('package_price', 100000) }}" min="0" step="1000" required>
                                @error('package_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 mb-0" style="font-size:.85rem; background:var(--blue-lt); border-color:var(--blue-md); color:var(--txt);">
                        <i class='bx bx-info-circle me-1'></i>
                        Customer will be created with <strong>Provisioning</strong> status. PPPoE secret will be pushed to the MikroTik router automatically via the queue worker.
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between" style="border-top:1px solid var(--border);">
                    <a href="{{ route('admin.customers.index') }}" class="btn" style="color:var(--txt-2);">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class='bx bx-user-plus me-1'></i> Create Customer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(function() {
    var packageUrl = '{{ route("admin.api.packages-by-area") }}';
    var $area = $('#area-select');
    var $pkg  = $('#package-select');
    var $price = $('#package-price');
    var oldPkg = '{{ old("package_id", "") }}';

    var $localAddr = $('#local-address');
    var $localSelect = $('#local-address-select');

    function loadPackages(areaId) {
        if (!areaId) {
            $pkg.html('<option value="">— Pilih Area dulu —</option>').prop('disabled', true);
            $localAddr.attr('placeholder', 'Pilih area dulu...');
            $localSelect.hide().html('<option value="">— Pilih dari existing —</option>');
            return;
        }
        $pkg.html('<option value="">⏳ Connecting to MikroTik...</option>').prop('disabled', true);
        $localAddr.attr('placeholder', 'Connecting...');
        $localSelect.hide();
        $.getJSON(packageUrl, { area_id: areaId }, function(data) {
            if (data.error) {
                $pkg.html('<option value="">⚠ ' + data.error + '</option>').prop('disabled', true);
                return;
            }

            // Populate profiles dropdown
            var profiles = data.profiles || data;
            var html = '<option value="">— Select Profile —</option>';
            if (profiles.length === 0) {
                html = '<option value="">Tidak ada profile di router ini</option>';
            }
            $.each(profiles, function(i, p) {
                var label = p.name;
                if (p.rate_limit) {
                    label += ' [' + p.rate_limit + ']';
                } else if (p.speed_down > 0 || p.speed_up > 0) {
                    label += ' [' + p.speed_down + 'M/' + p.speed_up + 'M]';
                }
                if (p.price > 0) {
                    label += ' · Rp ' + Number(p.price).toLocaleString('id-ID');
                }
                var val = p.id || p.mikrotik_profile;
                var sel = (oldPkg == val) ? 'selected' : '';
                html += '<option value="' + (p.id || '') + '" data-price="' + p.price + '" data-profile="' + p.mikrotik_profile + '" ' + sel + '>' + label + '</option>';
            });
            $pkg.html(html).prop('disabled', false);
            if (oldPkg) {
                var opt = $pkg.find(':selected');
                var price = opt.data('price');
                if (price) $price.val(price);
            }

            // Populate local address dropdown
            var localAddresses = data.local_addresses || [];
            if (localAddresses.length > 0) {
                var laHtml = '<option value="">— Pilih dari existing —</option>';
                laHtml += '<option value="__manual__">✏ Ketik manual...</option>';
                $.each(localAddresses, function(i, addr) {
                    laHtml += '<option value="' + addr + '">' + addr + '</option>';
                });
                $localSelect.html(laHtml).show();
                $localAddr.attr('placeholder', 'Atau ketik manual di sini...');
            } else {
                $localSelect.hide();
                $localAddr.attr('placeholder', 'e.g. 10.10.10.2');
            }

        }).fail(function(xhr) {
            var msg = 'Error loading profiles';
            try {
                var err = JSON.parse(xhr.responseText);
                if (err.error) msg = '⚠ ' + err.error;
            } catch(e) {}
            $pkg.html('<option value="">' + msg + '</option>').prop('disabled', true);
        });
    }

    // When existing address selected from dropdown → fill text input
    $localSelect.on('change', function() {
        var val = $(this).val();
        if (val && val !== '__manual__') {
            $localAddr.val(val);
        } else {
            $localAddr.val('').focus();
        }
    });

    $area.on('change', function() {
        oldPkg = '';
        $localAddr.val('');
        $localSelect.val('');
        loadPackages($(this).val());
    });

    $pkg.on('change', function() {
        var opt = $(this).find(':selected');
        var price = opt.data('price');
        if (price !== undefined && price !== '') {
            $price.val(price);
        }
    });

    // Partner: area pre-selected → auto-load from router
    // Admin: no area selected → show "Pilih Area dulu"
    loadPackages($area.val());
});
</script>
@endsection