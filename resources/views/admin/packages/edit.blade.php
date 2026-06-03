@extends('layouts.app')

@section('title', 'Ubah Paket')

@section('content')
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-package'></i> Katalog Layanan</div>
            <h1 class="ms-page-title">Ubah Paket</h1>
        </div>
        <div class="ms-page-actions">
            <a href="{{ route('admin.packages.index') }}" class="ms-btn-secondary">
                <i class='bx bx-arrow-back'></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <form action="{{ route('admin.packages.update', $package) }}" method="POST" class="ms-panel">
        @csrf
        @method('PUT')
        <div class="ms-panel-head">
            <h5 class="ms-panel-title">Detail Paket</h5>
        </div>
        <div class="ms-panel-body">
            <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">Nama Paket</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $package->name) }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">Kode Paket</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code', $package->code) }}" required>
                                    @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
            </div>

            <div class="row g-3">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label required">Kecepatan Download (Mbps)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('speed_down') is-invalid @enderror" name="speed_down" value="{{ old('speed_down', $package->speed_down) }}" min="1" required>
                                        <span class="input-group-text">Mbps</span>
                                    </div>
                                    @error('speed_down')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label required">Kecepatan Upload (Mbps)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('speed_up') is-invalid @enderror" name="speed_up" value="{{ old('speed_up', $package->speed_up) }}" min="1" required>
                                        <span class="input-group-text">Mbps</span>
                                    </div>
                                    @error('speed_up')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label required">Harga Bulanan</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ old('price', $package->price) }}" min="0" step="500" required>
                                    </div>
                                    @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
            </div>

            <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">Tipe Paket</label>
                                    <select class="form-select @error('type') is-invalid @enderror" name="type" required>
                                        <option value="residential" {{ old('type', $package->type) == 'residential' ? 'selected' : '' }}>Residential (Rumahan)</option>
                                        <option value="business" {{ old('type', $package->type) == 'business' ? 'selected' : '' }}>Business (Bisnis)</option>
                                        <option value="corporate" {{ old('type', $package->type) == 'corporate' ? 'selected' : '' }}>Corporate (Korporat)</option>
                                    </select>
                                    @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Profil MikroTik</label>
                                    <input type="text" class="form-control @error('mikrotik_profile') is-invalid @enderror" name="mikrotik_profile" value="{{ old('mikrotik_profile', $package->mikrotik_profile) }}">
                                    @error('mikrotik_profile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
            </div>

            <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Area / Router</label>
                                    <select class="form-select @error('area_id') is-invalid @enderror" name="area_id">
                                        <option value="">— Global (Semua Area) —</option>
                                        @foreach($areas ?? [] as $area)
                                        <option value="{{ $area->id }}" {{ old('area_id', $package->area_id) == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-hint">Tetapkan ke area/router tertentu, atau biarkan global untuk semua.</small>
                                    @error('area_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description', $package->description) }}</textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-0">
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $package->is_active) ? 'checked' : '' }}>
                    <span class="form-check-label">Aktif (tersedia untuk pelanggan baru)</span>
                </label>
            </div>
        </div>
        <div class="ms-panel-foot d-flex justify-content-end">
            <button type="submit" class="ms-btn">
                <i class='bx bx-save'></i> Perbarui Paket
            </button>
        </div>
    </form>
</div>
@endsection
