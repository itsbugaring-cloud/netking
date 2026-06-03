@extends('layouts.app')

@section('title', 'Edit Package')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Edit Package: {{ $package->name }}</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <form action="{{ route('admin.packages.update', $package) }}" method="POST" class="card">
                    @csrf
                    @method('PUT')
                    <div class="card-header">
                        <h4 class="card-title">Package Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">Package Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $package->name) }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">Package Code</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code', $package->code) }}" required>
                                    @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label required">Download Speed (Mbps)</label>
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
                                    <label class="form-label required">Upload Speed (Mbps)</label>
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
                                    <label class="form-label required">Monthly Price</label>
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">Package Type</label>
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
                                    <label class="form-label">MikroTik Profile Name</label>
                                    <input type="text" class="form-control @error('mikrotik_profile') is-invalid @enderror" name="mikrotik_profile" value="{{ old('mikrotik_profile', $package->mikrotik_profile) }}">
                                    @error('mikrotik_profile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Area / Router</label>
                                    <select class="form-select @error('area_id') is-invalid @enderror" name="area_id">
                                        <option value="">— Global (All Areas) —</option>
                                        @foreach($areas ?? [] as $area)
                                        <option value="{{ $area->id }}" {{ old('area_id', $package->area_id) == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-hint">Assign to a specific area/router, or leave global for all.</small>
                                    @error('area_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description', $package->description) }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $package->is_active) ? 'checked' : '' }}>
                                <span class="form-check-label">Active (available for new customers)</span>
                            </label>
                        </div>

                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">Update Package</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection