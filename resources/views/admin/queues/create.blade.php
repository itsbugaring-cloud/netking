@extends('layouts.app')
@section('title', 'Create Simple Queue')

@section('content')
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-tachometer'></i> Bandwidth Control</div>
            <h1 class="ms-page-title">Create Simple Queue</h1>
        </div>
        <div class="ms-page-actions">
            <a href="{{ route('admin.queues.index', ['area_id' => $area->id]) }}" class="ms-btn-secondary">
                <i class='bx bx-arrow-back'></i> Back to Queues
            </a>
        </div>
    </div>

    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title">Queue Settings — {{ $area->name }}</h5>
                <div class="ms-panel-subtitle">Create a new simple queue for a customer on {{ $area->router_ip }}</div>
            </div>
        </div>
        <div class="ms-panel-body">
            <form method="POST" action="{{ route('admin.queues.store') }}">
                @csrf
                <input type="hidden" name="area_id" value="{{ $area->id }}">

                <div class="row g-3">
                    {{-- Customer --}}
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">— Pilih Customer —</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} — {{ $customer->pppoe_user }} ({{ $customer->remote_ip }})
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Queue name: nk-{customer_id}, Target: customer remote_ip/32</small>
                        @error('customer_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Speed --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Upload Speed (Mbps) <span class="text-danger">*</span></label>
                        <input type="number" name="upload_speed" class="form-control" value="{{ old('upload_speed') }}" min="1" required placeholder="e.g. 10">
                        @error('upload_speed')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Download Speed (Mbps) <span class="text-danger">*</span></label>
                        <input type="number" name="download_speed" class="form-control" value="{{ old('download_speed') }}" min="1" required placeholder="e.g. 20">
                        @error('download_speed')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Burst --}}
                    <div class="col-12">
                        <hr class="my-2">
                        <p class="text-muted mb-2" style="font-size:.8rem;"><i class='bx bx-info-circle'></i> Burst parameters are optional. Leave empty to skip burst configuration.</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Burst Upload (Mbps)</label>
                        <input type="number" name="burst_upload" class="form-control" value="{{ old('burst_upload') }}" min="0" placeholder="e.g. 15">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Burst Download (Mbps)</label>
                        <input type="number" name="burst_download" class="form-control" value="{{ old('burst_download') }}" min="0" placeholder="e.g. 30">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Burst Threshold Upload (Mbps)</label>
                        <input type="number" name="burst_threshold_up" class="form-control" value="{{ old('burst_threshold_up') }}" min="0" placeholder="e.g. 8">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Burst Threshold Download (Mbps)</label>
                        <input type="number" name="burst_threshold_down" class="form-control" value="{{ old('burst_threshold_down') }}" min="0" placeholder="e.g. 16">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Burst Time (seconds)</label>
                        <input type="number" name="burst_time" class="form-control" value="{{ old('burst_time') }}" min="0" placeholder="e.g. 10">
                        <small class="text-muted">Applied to both upload and download</small>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="ms-btn">
                        <i class='bx bx-plus'></i> Create Queue
                    </button>
                    <a href="{{ route('admin.queues.index', ['area_id' => $area->id]) }}" class="ms-btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
