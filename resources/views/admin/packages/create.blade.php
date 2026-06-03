@extends('layouts.app')

@section('title', 'Buat Paket')

@section('content')
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-package'></i> Katalog Layanan</div>
            <h1 class="ms-page-title">Buat Paket</h1>
        </div>
        <div class="ms-page-actions">
            <a href="{{ route('admin.packages.index') }}" class="ms-btn-secondary">
                <i class='bx bx-arrow-back'></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <form action="{{ route('admin.packages.store') }}" method="POST" class="ms-panel">
        @csrf
        <div class="ms-panel-head">
            <h5 class="ms-panel-title">Detail Paket</h5>
        </div>
        <div class="ms-panel-body">
            <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">Nama Paket</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Nama paket" required autofocus>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">Kode Paket</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code') }}" placeholder="mis. HOME-20M" required>
                                    <small class="form-hint">Pengenal unik untuk paket ini.</small>
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
                                        <input type="number" class="form-control @error('speed_down') is-invalid @enderror" name="speed_down" value="{{ old('speed_down') }}" min="1" required>
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
                                        <input type="number" class="form-control @error('speed_up') is-invalid @enderror" name="speed_up" value="{{ old('speed_up') }}" min="1" required>
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
                                        <input type="number" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ old('price') }}" min="0" step="500" required>
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
                                        <option value="residential" {{ old('type') == 'residential' ? 'selected' : '' }}>Residential (Rumahan)</option>
                                        <option value="business" {{ old('type') == 'business' ? 'selected' : '' }}>Business (Bisnis)</option>
                                        <option value="corporate" {{ old('type') == 'corporate' ? 'selected' : '' }}>Corporate (Korporat)</option>
                                    </select>
                                    @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Profil MikroTik</label>
                                    <input type="text" class="form-control @error('mikrotik_profile') is-invalid @enderror" name="mikrotik_profile" value="{{ old('mikrotik_profile') }}" placeholder="mis. profile_20m">
                                    <small class="form-hint">Harus sama persis dengan nama profil di router MikroTik Anda.</small>
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
                                        <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
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
                <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3" placeholder="Deskripsi singkat paket...">{{ old('description') }}</textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-0">
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <span class="form-check-label">Aktif (tersedia untuk pelanggan baru)</span>
                </label>
            </div>
        </div>
        <div class="ms-panel-foot d-flex justify-content-end">
            <button type="submit" class="ms-btn">
                <i class='bx bx-save'></i> Buat Paket
            </button>
        </div>
    </form>
</div>
@endsection
