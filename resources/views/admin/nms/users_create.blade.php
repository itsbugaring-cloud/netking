@extends('layouts.app')
@section('title', 'Create User')

@section('content')
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4>Create User</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-sm" style="background:#f5f5f9; color:#1e293b;">
        <i class='bx bx-arrow-back me-1'></i> Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class='bx bx-user-plus me-2' style="color:#2563eb;"></i>New User</h5>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select id="role-select" name="role" class="form-select @error('role') is-invalid @enderror"
                            required onchange="toggleAreaField()">
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="partner" {{ old('role') === 'partner' ? 'selected' : '' }}>Partner</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Area — required only for partner --}}
                    <div class="mb-3" id="area-field" style="display:{{ old('role') === 'partner' ? 'block' : 'none' }}">
                        <label class="form-label">Area <span class="text-danger">*</span>
                            <small class="text-muted">(required for Partner)</small>
                        </label>
                        <select name="area_id" class="form-select @error('area_id') is-invalid @enderror">
                            <option value="">-- Select Area --</option>
                            @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>
                                {{ $area->name }} — {{ $area->router_ip }}
                            </option>
                            @endforeach
                        </select>
                        @error('area_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between" style="border-top:1px solid #dbdade;">
                    <a href="{{ route('admin.users.index') }}" class="btn" style="color:#1e293b;">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class='bx bx-save me-1'></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleAreaField() {
        var role = document.getElementById('role-select').value;
        document.getElementById('area-field').style.display = (role === 'partner') ? 'block' : 'none';
    }
</script>
@endsection