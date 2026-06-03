@extends('layouts.app')
@section('title', 'Buat Voucher')
@section('content')
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-purchase-tag'></i> Manajemen Voucher</div>
            <h1 class="ms-page-title">Buat Voucher</h1>
        </div>
    </div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="ms-panel">
            <div class="ms-panel-head"><span class="ms-panel-title"><i class='bx bx-cog me-2' style="color:#2563eb;"></i>Pengaturan Voucher</span></div>
            <form method="POST" action="{{ route('admin.vouchers.generate') }}">
                @csrf
                <div class="ms-panel-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nama Batch <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="Nama batch" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select name="type" class="form-select">
                                <option value="hotspot" {{ old('type') === 'hotspot' ? 'selected' : '' }}>Hotspot</option>
                                <option value="pppoe" {{ old('type') === 'pppoe' ? 'selected' : '' }}>PPPoE</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" min="1" max="500" value="{{ old('quantity', 10) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Durasi (hari) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_days" class="form-control" min="1" value="{{ old('duration_days', 30) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Harga (Rp)</label>
                            <input type="number" name="price" class="form-control" min="0" value="{{ old('price', 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prefix Kode</label>
                            <input type="text" name="prefix" class="form-control" maxlength="6" placeholder="NK" value="{{ old('prefix', 'NK') }}" style="text-transform:uppercase;">
                            <div class="form-text">Maks. 6 karakter. Mis. NK → NK-XXXXXX</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Batas Kecepatan</label>
                            <input type="text" name="speed_limit" class="form-control" placeholder="10M/10M" value="{{ old('speed_limit') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Profil MikroTik</label>
                            <input type="text" name="profile" class="form-control" placeholder="default" value="{{ old('profile', 'default') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Area (opsional)</label>
                            <select name="area_id" class="form-select">
                                <option value="">— Semua Area —</option>
                                @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="ms-panel-foot d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.vouchers.index') }}" class="ms-btn-secondary">Batal</a>
                    <button type="submit" class="ms-btn"><i class='bx bx-zap me-1'></i>Buat Voucher</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="ms-panel">
            <div class="ms-panel-head"><span class="ms-panel-title"><i class='bx bx-info-circle me-2' style="color:#2563eb;"></i>Info</span></div>
            <div class="ms-panel-body">
                <ul class="mb-0" style="font-size:.875rem;line-height:1.8;">
                    <li>Maks. <strong>500 voucher</strong> per batch.</li>
                    <li>Setiap kode: <code>PREFIX-XXXXXX</code> (6 karakter acak)</li>
                    <li>Kode bersifat <strong>unik</strong> di semua batch</li>
                    <li>Voucher hotspot dapat ditukar oleh pelanggan melalui portal</li>
                    <li>Voucher PPPoE akan otomatis membuat secret PPPoE di MikroTik</li>
                </ul>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
