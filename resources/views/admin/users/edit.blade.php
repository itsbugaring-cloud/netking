@extends('layouts.app')
@section('title', 'Ubah Pengguna')

@section('content')
<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-edit'></i> Kontrol Akses</div>
      <h1 class="ms-page-title">Ubah Pengguna</h1>
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
          <h5 class="ms-panel-title"><i class='bx bx-user me-2' style="color:#2563eb;"></i>{{ $user->name }}</h5>
        </div>
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="ms-panel-body">
            <div class="mb-3">
              <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Telegram Username</label>
              <div class="input-group">
                <span class="input-group-text">@</span>
                <input type="text" name="telegram_username" class="form-control @error('telegram_username') is-invalid @enderror" value="{{ old('telegram_username', $user->telegram_username) }}" placeholder="contoh: caesarbugar">
              </div>
              <div class="form-text">Opsional. Dipakai untuk lock akses bot Telegram per user.</div>
              @error('telegram_username')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Peran <span class="text-danger">*</span></label>
              <select id="role-select" name="role" class="form-select @error('role') is-invalid @enderror" required onchange="toggleAreaField()">
                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="finance" {{ old('role', $user->role) === 'finance' ? 'selected' : '' }}>Finance</option>
                <option value="partner" {{ old('role', $user->role) === 'partner' ? 'selected' : '' }}>PIC</option>
              </select>
              @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3" id="area-field" style="display:{{ old('role', $user->role) === 'partner' ? 'block' : 'none' }}">
              <label class="form-label">Area <span class="text-danger">*</span></label>
              <select name="area_id" class="form-select @error('area_id') is-invalid @enderror">
                <option value="">Pilih Area</option>
                @foreach($areas as $area)
                <option value="{{ $area->id }}" {{ old('area_id', $user->area_id) == $area->id ? 'selected' : '' }}>
                  {{ $area->name }} — {{ $area->router_ip }}
                </option>
                @endforeach
              </select>
              <div class="form-text">Wajib diisi hanya untuk akun PIC.</div>
              @error('area_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Password Baru</label>
              <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
              <div class="form-text">Kosongkan untuk mempertahankan password saat ini.</div>
              @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-0">
              <label class="form-label">Konfirmasi Password Baru</label>
              <input type="password" name="password_confirmation" class="form-control">
            </div>
          </div>
          <div class="ms-panel-foot d-flex justify-content-between">
            <a href="{{ route('admin.users.index') }}" class="ms-btn-ghost">Batal</a>
            <button type="submit" class="ms-btn">
              <i class='bx bx-save'></i> Simpan Perubahan
            </button>
          </div>
        </form>
      </div>

      {{-- Reset Password Panel (Admin Only) --}}
      @if(auth()->user()->role === 'admin')
      <div id="reset-password-panel" class="ms-panel mt-3" style="border: 1.5px solid #fca5a5;">
        <div class="ms-panel-head" style="background: #fff5f5;">
          <h5 class="ms-panel-title text-danger"><i class='bx bx-key me-2'></i>Reset Password Pengguna</h5>
        </div>
        <form action="{{ route('admin.users.reset-password', $user) }}" method="POST">
          @csrf
          <div class="ms-panel-body">
            <p class="text-muted small mb-3">Gunakan ini jika admin, finance, atau PIC lupa password login. Setelah reset, semua sesi lama akan dicabut dan pengguna harus login ulang.</p>
            <div class="mb-3">
              <label class="form-label">Password Baru <span class="text-danger">*</span></label>
              <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" required minlength="8" placeholder="Minimal 8 karakter">
              @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-0">
              <label class="form-label">Konfirmasi Password Baru</label>
              <input type="password" name="new_password_confirmation" class="form-control" placeholder="Opsional, boleh dikosongkan">
              <div class="form-text">Jika dikosongkan, sistem akan memakai nilai Password Baru.</div>
            </div>
          </div>
          <div class="ms-panel-foot">
            <button type="submit" class="ms-btn ms-btn-danger w-100" onclick="return confirm('Reset password {{ $user->name }}? Semua sesi login lama akan dicabut.')">
              <i class='bx bx-reset'></i> Reset Password &amp; Cabut Token Lama
            </button>
          </div>
        </form>
      </div>
      @endif
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
