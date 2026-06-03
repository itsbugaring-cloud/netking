@extends('layouts.app')
@section('title', 'Edit Area')

@section('content')
<div class="page-title-box d-flex align-items-center justify-content-between">
  <div>
    <h4>Edit Area</h4>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.areas.index') }}">Areas</a></li>
        <li class="breadcrumb-item active">Edit: {{ $area->name }}</li>
      </ol>
    </nav>
  </div>
  <a href="{{ route('admin.areas.index') }}" class="btn btn-sm" style="background:#f5f5f9; color:#1e293b;">
    <i class='bx bx-arrow-back me-1'></i> Back
  </a>
</div>

<div class="row justify-content-center">
  <div class="col-md-8">
    <form action="{{ route('admin.areas.update', $area) }}" method="POST">
      @csrf @method('PUT')

      {{-- Basic Info --}}
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class='bx bx-map-pin me-2' style="color:#2563eb;"></i>Area Information</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Area Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
              value="{{ old('name', $area->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>

      {{-- MikroTik Router Config --}}
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class='bx bx-chip me-2' style="color:#2563eb;"></i>MikroTik Router Configuration</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Router IP Address <span class="text-danger">*</span></label>
                <input type="text" name="router_ip" class="form-control @error('router_ip') is-invalid @enderror"
                  value="{{ old('router_ip', $area->router_ip) }}" required>
                @error('router_ip')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label class="form-label">Router Username <span class="text-danger">*</span></label>
                <input type="text" name="router_user" class="form-control @error('router_user') is-invalid @enderror"
                  value="{{ old('router_user', $area->router_user) }}" required>
                @error('router_user')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label class="form-label">Router Password <span class="text-danger">*</span></label>
                <input type="password" name="router_pass" class="form-control @error('router_pass') is-invalid @enderror"
                  value="" placeholder="Leave blank to keep current">
                @error('router_pass')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">IP Pool Start <span class="text-danger">*</span></label>
                <input type="text" name="ip_pool_start" class="form-control @error('ip_pool_start') is-invalid @enderror"
                  value="{{ old('ip_pool_start', $area->ip_pool_start) }}" required>
                @error('ip_pool_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">IP Pool End <span class="text-danger">*</span></label>
                <input type="text" name="ip_pool_end" class="form-control @error('ip_pool_end') is-invalid @enderror"
                  value="{{ old('ip_pool_end', $area->ip_pool_end) }}" required>
                @error('ip_pool_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between" style="border-top:1px solid #dbdade;">
          <a href="{{ route('admin.areas.index') }}" class="btn" style="color:#1e293b;">Cancel</a>
          <button type="submit" class="btn btn-primary">
            <i class='bx bx-save me-1'></i> Save Changes
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection