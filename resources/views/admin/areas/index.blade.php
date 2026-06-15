@extends('layouts.app')
@section('title', 'Area')

@section('content')
<style>
.area-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}
.area-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.25rem;
    position: relative;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
}
.area-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.01);
    border-color: var(--blue);
}
.area-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}
.area-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--txt-1);
    margin: 0;
    line-height: 1.3;
}
.area-identity {
    font-size: 0.8rem;
    color: var(--orange, #f97316);
    font-weight: 600;
    background: color-mix(in srgb, var(--orange, #f97316) 10%, transparent);
    padding: 2px 8px;
    border-radius: 6px;
    display: inline-block;
    margin-top: 6px;
}
.area-stats {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1rem;
    background: var(--bg);
    padding: 0.75rem;
    border-radius: 8px;
    font-size: 0.85rem;
    color: var(--txt-2);
}
.stat-item {
    flex: 1;
}
.stat-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--txt-3);
    margin-bottom: 4px;
}
.stat-value {
    font-weight: 600;
    color: var(--txt-1);
}
.area-footer {
    margin-top: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
}
.customer-badge {
    background: color-mix(in srgb, var(--blue) 10%, var(--surface));
    color: var(--blue);
    font-size: 0.8rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.card-dropdown .btn {
    padding: 0.25rem;
    line-height: 1;
    border: none;
    background: transparent;
    color: var(--txt-3);
}
.card-dropdown .btn:hover {
    color: var(--txt-1);
    background: var(--bg);
}
.card-dropdown .dropdown-menu {
    border: 1px solid var(--border);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border-radius: 8px;
}
.vlan-badge {
    color: var(--blue);
    font-weight: 600;
}
</style>

<div class="ms-page nk-list-page areas-index-page">
  <div class="ms-page-head">
    <div>
      <h1 class="ms-page-title">Area Jaringan</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.areas.create') }}" class="ms-btn">
        <i class='bx bx-plus'></i> Tambah Area
      </a>
    </div>
  </div>

  @if($areas->isEmpty())
  <div class="text-center py-5" style="color:var(--txt-3); background: var(--surface); border: 1px dashed var(--border); border-radius: 12px;">
    <i class='bx bx-map-pin fs-1 d-block mb-3'></i>
    <div style="font-size:1.1rem;font-weight:500;color:var(--txt-1);">Belum ada area jaringan</div>
    <p class="mb-4">Tambahkan area untuk mengelompokkan pelanggan berdasarkan lokasi dan OLT.</p>
    <a href="{{ route('admin.areas.create') }}" class="ms-btn">
      <i class='bx bx-plus'></i> Tambah Area Pertama
    </a>
  </div>
  @else
  <div class="area-grid">
    @foreach($areas as $area)
    <div class="area-card">
      <div class="area-header">
        <div>
          <h3 class="area-title">{{ $area->name }}</h3>
          @if($area->router_identity)
          <div class="area-identity"><i class='bx bx-chip me-1'></i>{{ $area->router_identity }}</div>
          @endif
        </div>
        
        <div class="dropdown card-dropdown">
          <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class='bx bx-dots-vertical-rounded fs-5'></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="{{ route('admin.areas.edit', $area) }}"><i class='bx bx-edit me-2'></i> Edit Area</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form action="{{ route('admin.areas.install-isolir', $area) }}" method="POST" class="m-0" data-confirm="Install Rule Isolir Otomatis ke MikroTik ini?">
                @csrf
                <button type="submit" class="dropdown-item"><i class='bx bx-shield-quarter me-2' style="color:var(--orange);"></i> Install Rule Isolir</button>
              </form>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form action="{{ route('admin.areas.destroy', $area) }}" method="POST" class="m-0" data-confirm="Hapus {{ $area->name }}?">
                @csrf @method('DELETE')
                <button type="submit" class="dropdown-item text-danger"><i class='bx bx-trash me-2'></i> Hapus</button>
              </form>
            </li>
          </ul>
        </div>
      </div>

      <div class="area-stats">
        <div class="stat-item">
          <div class="stat-label">Router IP</div>
          <div class="stat-value"><code style="color: var(--txt-1);">{{ $area->router_ip }}</code></div>
        </div>
        <div class="stat-item">
          <div class="stat-label">VLAN PPPoE</div>
          <div class="stat-value vlan-badge">{{ $area->vlan_pppoe ?: '-' }}</div>
        </div>
        <div class="stat-item">
          <div class="stat-label">VLAN MGMT</div>
          <div class="stat-value vlan-badge">{{ $area->vlan_mgmt ?: '-' }}</div>
        </div>
      </div>

      <div class="area-footer">
        <div class="customer-badge">
          <i class='bx bx-group'></i> {{ $area->customers_count ?? 0 }} Pelanggan
        </div>
        <a href="{{ route('admin.areas.edit', $area) }}" class="ms-btn ms-btn-outline ms-btn-sm" style="padding: 4px 12px; font-size: 0.8rem;">
          <i class='bx bx-cog me-1'></i> Config
        </a>
      </div>
    </div>
    @endforeach
  </div>
  @endif
</div>
@endsection

@section('scripts')
<script>
  $(function() {
    $('form[data-confirm]').on('submit', function(e) {
      if (!confirm($(this).data('confirm'))) e.preventDefault();
    });
  });
</script>
@endsection
