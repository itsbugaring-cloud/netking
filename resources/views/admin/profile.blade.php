@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-user-circle'></i> Akun</div>
      <h1 class="ms-page-title">Profil Saya</h1>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-xl-6">
      <div class="ms-panel h-100">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-user me-2' style="color:#2563eb;"></i>Informasi Profil</h5>
        </div>
        <form action="{{ route('admin.profile.update') }}" method="POST">
          @csrf
          @method('PUT')
          <div class="ms-panel-body">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3" style="border-bottom:1px solid #eef2f7;">
              <div class="avatar avatar-lg" style="width:60px;height:60px;font-size:1.45rem;background:#2563eb;">
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
              </div>
              <div>
                <div style="font-weight:600;color:#1e293b;">{{ auth()->user()->name }}</div>
                <div style="font-size:.8rem;color:#64748b;">{{ ucfirst(auth()->user()->role ?? 'admin') }}</div>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', auth()->user()->name) }}" required>
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Alamat Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', auth()->user()->email) }}" required>
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-0">
              <label class="form-label">Peran</label>
              <input type="text" class="form-control" value="{{ ucfirst(auth()->user()->role ?? 'admin') }}" disabled>
            </div>
          </div>
          <div class="ms-panel-foot d-flex justify-content-end">
            <button type="submit" class="ms-btn">
              <i class='bx bx-save'></i> Perbarui Profil
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="col-xl-6">
      <div class="ms-panel h-100">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-lock-alt me-2' style="color:#2563eb;"></i>Ganti Password</h5>
        </div>
        <form action="{{ route('admin.password.update') }}" method="POST">
          @csrf
          @method('PUT')
          <div class="ms-panel-body">
            <div class="mb-3">
              <label class="form-label">Password Lama</label>
              <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
              @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Password Baru</label>
              <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
              <div class="form-text">Minimal 8 karakter.</div>
              @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-0">
              <label class="form-label">Konfirmasi Password Baru</label>
              <input type="password" name="password_confirmation" class="form-control" required>
            </div>
          </div>
          <div class="ms-panel-foot d-flex justify-content-end">
            <button type="submit" class="ms-btn">
              <i class='bx bx-key'></i> Ganti Password
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
