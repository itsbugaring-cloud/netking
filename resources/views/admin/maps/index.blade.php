@extends('layouts.app')
@section('title', 'Peta Pelanggan & Tracing ONT')

@section('content')
<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-map'></i> Jaringan</div>
      <h1 class="ms-page-title">Peta Pelanggan & Tracing ONT</h1>
    </div>
  </div>

  <div class="ms-panel">
    <div class="ms-panel-body p-0">
      <div id="map" style="height: 75vh; width: 100%; border-radius: 8px;"></div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
  $(function() {
    var map = L.map('map').setView([-6.2088, 106.8456], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap'
    }).addTo(map);

    var customers = @json($customers);
    var markers = L.featureGroup();

    customers.forEach(function(cust) {
      if (cust.latitude && cust.longitude) {
        var statusColor = cust.status === 'active' ? 'green' : (cust.status === 'isolated' ? 'orange' : 'red');
        var sn = cust.ont_sn ? cust.ont_sn : '<i class="text-muted">Kosong</i>';
        
        var popupContent = `
          <div style="min-width: 220px; font-family: 'Inter', sans-serif;">
            <div style="margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px;">
              <h6 style="margin: 0; font-weight: 700; font-size: 15px; color: #1e293b;">${cust.name}</h6>
              <div style="font-size: 12px; color: #64748b; font-family: monospace;">${cust.customer_code}</div>
            </div>
            
            <table style="width: 100%; font-size: 13px; margin-bottom: 10px;">
              <tr><td style="color: #64748b; padding: 2px 0; width: 65px;">Area:</td><td style="font-weight: 600;">${cust.area ? cust.area.name : '-'}</td></tr>
              <tr><td style="color: #64748b; padding: 2px 0;">Status:</td><td><span style="color: ${statusColor}; font-weight: 700; text-transform: capitalize;">${cust.status || '-'}</span></td></tr>
              <tr><td style="color: #64748b; padding: 2px 0;">S/N ONT:</td><td style="font-weight: 700; color: #2563eb; font-family: monospace;">${sn}</td></tr>
            </table>
            
            <a href="/admin/customers/${cust.id}" class="btn btn-sm btn-primary w-100" style="padding: 6px; font-weight: 600;">Lihat Detail Pelanggan</a>
          </div>
        `;

        // We can use a custom icon or standard leaflet icon
        var marker = L.marker([cust.latitude, cust.longitude]).bindPopup(popupContent);
        markers.addLayer(marker);
      }
    });

    if (customers.length > 0) {
      markers.addTo(map);
      map.fitBounds(markers.getBounds(), { padding: [30, 30] });
    }
  });
</script>
@endsection
