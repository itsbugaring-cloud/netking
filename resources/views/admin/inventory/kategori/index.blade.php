@extends('layouts.app')
@section('title', 'Kategori Barang')

@section('content')
<div class="ms-page inv-kategori-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-category'></i> Inventaris</div>
      <h1 class="ms-page-title">Kategori Barang</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.inventory.master-barang.index') }}" class="ms-btn-secondary">
        <i class='bx bx-list-ul'></i> Master Barang
      </a>
    </div>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif
  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  @php
    $editItem = request('edit') ? $kategoris->firstWhere('id', (int)request('edit')) : null;
  @endphp

  <div class="row g-3">
    <div class="col-md-8">
      <div class="ms-panel">
        <div class="ms-panel-head">
          <div>
            <h5 class="ms-panel-title">Daftar Kategori</h5>
            <div class="ms-panel-subtitle">Semua kategori barang inventori</div>
          </div>
          <div class="ms-toolbar-right">
            <span class="ms-chip"><i class='bx bx-data'></i> {{ $kategoris->count() }} kategori</span>
          </div>
        </div>
        <div class="ms-table-shell">
          <div class="table-responsive">
            <table class="table table-flat mb-0">
              <thead>
                <tr>
                  <th>Nama Kategori</th>
                  <th class="text-end">Jumlah Barang</th>
                  <th style="width:100px">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($kategoris as $kat)
                <tr {{ $editItem && $editItem->id === $kat->id ? 'style=background:color-mix(in srgb,var(--nk-warning) 6%,var(--surface))' : '' }}>
                  <td><span style="font-weight:560;color:var(--txt)">{{ $kat->nama_kategori }}</span></td>
                  <td class="text-end">
                    <span class="ms-chip">{{ $kat->master_barangs_count ?? $kat->inv_master_barangs_count ?? 0 }} item</span>
                  </td>
                  <td>
                    <div class="d-flex gap-1">
                      <a href="{{ route('admin.inventory.kategori.index', ['edit' => $kat->id]) }}"
                         class="btn btn-sm" title="Edit"
                         style="background:#fff7ed;color:#ea580c;width:34px;height:34px;display:flex;align-items:center;justify-content:center;border-radius:10px;padding:0;border:1px solid #fed7aa;">
                        <i class='bx bx-edit'></i>
                      </a>
                      <form action="{{ route('admin.inventory.kategori.destroy', $kat) }}" method="POST" class="m-0"
                            onsubmit="return confirm('Hapus kategori ini? Semua barang terkait akan terpengaruh.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm" title="Hapus"
                                style="background:#fef2f2;color:#ef4444;width:34px;height:34px;display:flex;align-items:center;justify-content:center;border-radius:10px;padding:0;border:1px solid #fecaca;">
                          <i class='bx bx-trash'></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
                @empty
                <tr><td colspan="3">
                  <div class="empty-state">
                    <div class="empty-state-icon"><i class='bx bx-category'></i></div>
                    <div class="empty-state-title">Belum ada kategori</div>
                    <div class="empty-state-desc">Tambahkan kategori pertama melalui form di samping</div>
                  </div>
                </td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="ms-panel">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title">{{ $editItem ? 'Edit Kategori' : 'Tambah Kategori' }}</h5>
        </div>
        <div class="ms-panel-body">
          @if($editItem)
          <form action="{{ route('admin.inventory.kategori.update', $editItem) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
              <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
              <input type="text" name="nama_kategori"
                     value="{{ old('nama_kategori', $editItem->nama_kategori) }}"
                     class="form-control @error('nama_kategori') is-invalid @enderror"
                     placeholder="Contoh: ONU/ONT">
              @error('nama_kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="d-flex gap-2">
              <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Perbarui</button>
              <a href="{{ route('admin.inventory.kategori.index') }}" class="ms-btn-ghost">Batal</a>
            </div>
          </form>
          @else
          <form action="{{ route('admin.inventory.kategori.store') }}" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
              <input type="text" name="nama_kategori"
                     value="{{ old('nama_kategori') }}"
                     class="form-control @error('nama_kategori') is-invalid @enderror"
                     placeholder="Contoh: ONU/ONT">
              @error('nama_kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="ms-btn w-100"><i class='bx bx-plus'></i> Tambah Kategori</button>
          </form>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
