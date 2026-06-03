@extends('layouts.app')
@section('title', 'ODP Mapping')
@section('content')

<div class="page-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h4>ODP Mapping</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">ODP Mapping</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.odps.create') }}" class="btn btn-primary btn-sm">
        <i class='bx bx-plus me-1'></i>Add ODP
    </a>
</div>

{{-- Map --}}
<div class="card mb-4">
    <div class="card-header"><span class="card-title"><i class='bx bx-map me-2' style="color:#2563eb;"></i>ODP Map</span></div>
    <div id="odp-map" style="height:380px;border-radius:0 0 .5rem .5rem;"></div>
</div>

{{-- Filter + Table --}}
<div class="card mb-3" style="border-bottom:none;border-radius:.5rem .5rem 0 0;">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2">
            <select name="area_id" class="form-select form-select-sm" style="max-width:180px;" onchange="this.form.submit()">
                <option value="">All Areas</option>
                @foreach($areas as $area)
                <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-sm" style="max-width:160px;" onchange="this.form.submit()">
                <option value="">All Status</option>
                @foreach(['active','full','maintenance','inactive'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <a href="{{ route('admin.odps.index') }}" class="btn btn-sm btn-light">Clear</a>
        </form>
    </div>
</div>

<div class="card" style="border-radius:0 0 .5rem .5rem;">
    <div class="table-responsive">
        <table class="table table-sm mb-0" id="odps-table">
            <thead>
                <tr>
                    <th>ODP</th>
                    <th>Area</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @php $statusColors = ['active'=>'#22c55e','full'=>'#ef4444','maintenance'=>'#f59e0b','inactive'=>'#94a3b8']; @endphp
                @forelse($odps as $odp)
                <tr>
                    <td>
                        <div style="font-weight:500;font-size:.875rem;">{{ $odp->name }}</div>
                        <div class="text-muted" style="font-size:.75rem;">{{ $odp->code ?? '—' }}</div>
                    </td>
                    <td style="font-size:.8125rem;">{{ $odp->area?->name ?? '—' }}</td>
                    <td style="font-size:.8125rem;">{{ ucfirst($odp->odp_type) }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:80px;height:6px;background:#f1f5f9;border-radius:9px;overflow:hidden;">
                                <div style="width:{{ $odp->usage_percent }}%;height:100%;background:{{ $odp->isFull() ? '#ef4444' : '#2563eb' }};border-radius:9px;"></div>
                            </div>
                            <span style="font-size:.75rem;color:#64748b;">{{ $odp->used_capacity }}/{{ $odp->max_capacity }}</span>
                        </div>
                    </td>
                    <td>
                        @php $sc = $statusColors[$odp->status] ?? '#64748b'; @endphp
                        <span style="padding:2px 8px;border-radius:4px;font-size:.75rem;background:{{ $sc }}18;color:{{ $sc }};border:1px solid {{ $sc }}33;">
                            {{ ucfirst($odp->status) }}
                        </span>
                    </td>
                    <td style="font-size:.8125rem;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $odp->address ?? ($odp->latitude ? "{$odp->latitude}, {$odp->longitude}" : '—') }}
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.odps.edit', $odp) }}" class="btn btn-sm btn-outline-primary" style="padding:2px 8px;font-size:.75rem;"><i class='bx bx-edit'></i></a>
                            <form method="POST" action="{{ route('admin.odps.destroy', $odp) }}" class="d-inline" data-confirm="Delete ODP {{ $odp->name }}?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="padding:2px 8px;font-size:.75rem;"><i class='bx bx-trash'></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">No ODP data yet. <a href="{{ route('admin.odps.create') }}">Add first ODP →</a></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const map = L.map('odp-map').setView([-2.5, 117.8], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    const statusColors = {
        active: '#22c55e',
        full: '#ef4444',
        maintenance: '#f59e0b',
        inactive: '#94a3b8'
    };
    const odps = @json($mapData);

    odps.forEach(odp => {
        const color = statusColors[odp.status] || '#2563eb';
        const icon = L.divIcon({
            className: '',
            html: `<div style="width:14px;height:14px;background:${color};border:2px solid white;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,.3);"></div>`,
            iconSize: [14, 14],
            iconAnchor: [7, 7],
        });
        const marker = L.marker([odp.lat, odp.lng], {
            icon
        }).addTo(map);
        marker.bindPopup(`
    <b>${odp.name}</b> ${odp.code ? `<small>(${odp.code})</small>` : ''}<br>
    Area: ${odp.area}<br>
    Capacity: ${odp.used}/${odp.max} (${odp.pct}%)<br>
    Status: <b style="color:${color}">${odp.status}</b><br>
    <a href="${odp.url}" style="color:#2563eb;font-size:12px;">Edit ODP →</a>
  `);
    });

    if (odps.length > 0) {
        const group = L.featureGroup(odps.map(o => L.marker([o.lat, o.lng])));
        map.fitBounds(group.getBounds().pad(0.2));
    }
</script>
@endpush
@endsection