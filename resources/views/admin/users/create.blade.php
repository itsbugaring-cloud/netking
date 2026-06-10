@extends('layouts.app')
@section('title', 'Buat Pengguna')

@section('content')
<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-user-plus'></i> Kontrol Akses</div>
      <h1 class="ms-page-title">Buat Pengguna</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.users.index') }}" class="ms-btn-secondary">
        <i class='bx bx-arrow-back'></i> Kembali
      </a>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">
      <div class="ms-panel">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-user-plus me-2' style="color:#2563eb;"></i>Pengguna Baru</h5>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST">
          @csrf
          <div class="ms-panel-body">
            <div class="mb-3">
              <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Telegram Username</label>
              <div class="input-group">
                <span class="input-group-text">@</span>
                <input type="text" name="telegram_username" class="form-control @error('telegram_username') is-invalid @enderror" value="{{ old('telegram_username') }}" placeholder="contoh: caesarbugar">
              </div>
              <div class="form-text">Opsional. Dipakai untuk lock akses bot Telegram per user.</div>
              @error('telegram_username')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Peran <span class="text-danger">*</span></label>
              <select id="role-select" name="role" class="form-select @error('role') is-invalid @enderror" required onchange="toggleAreaField()">
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="finance" {{ old('role') === 'finance' ? 'selected' : '' }}>Finance</option>
                <option value="partner" {{ old('role') === 'partner' ? 'selected' : '' }}>PIC</option>
              </select>
              @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3" id="area-field" style="display:{{ old('role') === 'partner' ? 'block' : 'none' }}">
              <label class="form-label">Area <span class="text-danger">*</span></label>
              <select name="area_id" class="form-select @error('area_id') is-invalid @enderror">
                <option value="">Pilih Area</option>
                @foreach($areas as $area)
                <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>
                  {{ $area->name }} — {{ $area->router_ip }}
                </option>
                @endforeach
              </select>
              <div class="form-text">Wajib diisi hanya untuk akun PIC.</div>
              @error('area_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Password <span class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
              @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-0">
              <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
              <input type="password" name="password_confirmation" class="form-control" required>
            </div>
          </div>
          <div class="ms-panel-foot d-flex justify-content-between">
            <a href="{{ route('admin.users.index') }}" class="ms-btn-ghost">Batal</a>
            <button type="submit" class="ms-btn">
              <i class='bx bx-save'></i> Buat Pengguna
            </button>
          </div>
        </form>
      </div>
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
