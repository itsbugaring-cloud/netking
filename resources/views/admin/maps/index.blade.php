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

  <div class="ms-panel mb-3">
    <div class="ms-panel-body p-3">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label text-muted" style="font-size:12px; font-weight:600;">Pencarian (Nama / SN)</label>
          <input type="text" id="search-input" class="form-control" placeholder="Cari nama, kode, SN...">
        </div>
        <div class="col-md-3">
          <label class="form-label text-muted" style="font-size:12px; font-weight:600;">Filter Area</label>
          <select id="area-filter" class="form-select">
            <option value="">Semua Area</option>
            @foreach($areas as $area)
              <option value="{{ $area->id }}">{{ $area->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label text-muted" style="font-size:12px; font-weight:600;">Filter Status</label>
          <select id="status-filter" class="form-select">
            <option value="">Semua Status</option>
            <option value="active">Aktif (Hijau)</option>
            <option value="suspended">Diisolir/Suspended (Kuning)</option>
            <option value="failed">Gagal (Merah)</option>
            <option value="provisioning">Dalam Proses</option>
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="toggle-lines" checked>
            <label class="form-check-label text-muted" style="font-size:12px; font-weight:600; cursor:pointer;" for="toggle-lines">
              Garis ONT
            </label>
          </div>
          <button id="reset-filter" class="btn btn-secondary flex-grow-1">Reset</button>
        </div>
      </div>
    </div>
  </div>

  <div class="ms-panel">
    <div class="ms-panel-body p-0">
      <div id="map" style="height: 70vh; width: 100%; border-radius: 8px;"></div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<style>
@keyframes pulse-server {
  0%, 100% { box-shadow: 0 0 20px rgba(239,68,68,0.6), 0 0 40px rgba(239,68,68,0.3); }
  50% { box-shadow: 0 0 30px rgba(239,68,68,0.8), 0 0 60px rgba(239,68,68,0.4); }
}
</style>

<script>
  $(function() {
    var map = L.map('map').setView([-6.2088, 106.8456], 11);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
      maxZoom: 19,
      attribution: '&copy; CartoDB'
    }).addTo(map);

    var allCustomers = @json($customers);
    var markersLayer = L.markerClusterGroup({
      maxClusterRadius: 30,
      spiderfyOnMaxZoom: true,
      showCoverageOnHover: false,
      zoomToBoundsOnClick: true
    });
    
    var customerLinesLayer = L.layerGroup();
    map.addLayer(customerLinesLayer);
    
    map.addLayer(markersLayer);

    function renderMarkers(customersList) {
      markersLayer.clearLayers();
      customerLinesLayer.clearLayers();
      
      var hasValidMarkers = false;
      var bounds = L.latLngBounds();
      var showLines = $('#toggle-lines').is(':checked');

      customersList.forEach(function(cust) {
        if (cust.latitude && cust.longitude && cust.latitude != 0 && cust.longitude != 0) {
          hasValidMarkers = true;
          var statusColor = cust.status === 'active' ? 'green' : (cust.status === 'suspended' ? 'orange' : 'red');
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

          var bgColor = cust.status === 'active' ? '#10b981' : (cust.status === 'suspended' ? '#f59e0b' : '#ef4444');
          var glowColor = cust.status === 'active' ? 'rgba(16,185,129,0.4)' : (cust.status === 'suspended' ? 'rgba(245,158,11,0.4)' : 'rgba(239,68,68,0.4)');
          var customIcon = L.divIcon({
            className: '',
            html: `<div style="width:20px;height:20px;background:${bgColor};border-radius:50% 50% 50% 0;border:2px solid white;box-shadow:0 0 8px ${glowColor};transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;">
              <svg width="10" height="10" fill="white" viewBox="0 0 24 24" style="transform:rotate(45deg);"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </div>`,
            iconSize: [20, 20],
            iconAnchor: [10, 20]
          });

          var marker = L.marker([cust.latitude, cust.longitude], {icon: customIcon}).bindPopup(popupContent);
          markersLayer.addLayer(marker);
          bounds.extend([cust.latitude, cust.longitude]);

          if (showLines && cust.area && cust.area.latitude && cust.area.longitude) {
            var lineColor = cust.status === 'active' ? '#10b981' : (cust.status === 'suspended' ? '#f59e0b' : '#ef4444');
            L.polyline([[cust.area.latitude, cust.area.longitude], [cust.latitude, cust.longitude]], {
              color: lineColor,
              weight: 2,
              opacity: 0.6,
              dashArray: '4, 6'
            }).addTo(customerLinesLayer);
          }
        }
      });

      if (hasValidMarkers) {
        // Only zoom to bounds if there's an active filter, otherwise keep default or initial zoom
        if ($('#search-input').val().trim() !== '' || $('#area-filter').val() !== '' || $('#status-filter').val() !== '') {
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 16 });
        } else {
            // Initial load bounds
            map.fitBounds(bounds, { padding: [30, 30] });
        }
      }
    }

    function applyFilters() {
      var search = $('#search-input').val().toLowerCase().trim();
      var areaId = $('#area-filter').val();
      var status = $('#status-filter').val();

      var filtered = allCustomers.filter(function(cust) {
        // Check Area
        if (areaId && cust.area_id != areaId) return false;
        
        // Check Status
        if (status && cust.status !== status) return false;

        // Check Search
        if (search) {
          var nameMatch = cust.name && cust.name.toLowerCase().includes(search);
          var codeMatch = cust.customer_code && cust.customer_code.toLowerCase().includes(search);
          var snMatch = cust.ont_sn && cust.ont_sn.toLowerCase().includes(search);
          
          if (!nameMatch && !codeMatch && !snMatch) return false;
        }

        return true;
      });

      renderMarkers(filtered);
    }

    // Event Listeners
    $('#search-input').on('keyup', applyFilters);
    $('#area-filter, #status-filter, #toggle-lines').on('change', applyFilters);

    $('#reset-filter').on('click', function() {
      $('#search-input').val('');
      $('#area-filter').val('');
      $('#status-filter').val('');
      applyFilters();
    });

    // Initial Render
    renderMarkers(allCustomers);

    // === Server Marker (Netking Server Utama) ===
    var serverLatLng = [-6.9502503, 107.6614869];
    var serverIcon = L.divIcon({
      className: '',
      html: `<div style="position:relative;text-align:center;">
        <div style="width:40px;height:40px;background:linear-gradient(135deg,#ef4444,#dc2626);border-radius:10px;border:2px solid #fca5a5;box-shadow:0 0 20px rgba(239,68,68,0.6),0 0 40px rgba(239,68,68,0.3);display:flex;align-items:center;justify-content:center;animation:pulse-server 2s infinite;">
          <svg width="22" height="22" fill="white" viewBox="0 0 24 24"><path d="M4 1h16v6H4zM4 9h16v6H4zM4 17h16v6H4zM7 4h2v1H7zM7 12h2v1H7zM7 20h2v1H7z"/></svg>
        </div>
        <div style="font-size:9px;font-weight:700;color:#fca5a5;margin-top:2px;text-shadow:0 1px 3px rgba(0,0,0,0.8);letter-spacing:1px;">SERVER</div>
      </div>`,
      iconSize: [40, 55],
      iconAnchor: [20, 20]
    });
    L.marker(serverLatLng, {icon: serverIcon, zIndexOffset: 1000})
      .bindPopup('<div style="min-width:200px;"><h6 style="margin:0 0 6px;font-weight:700;font-size:14px;">🖥️ Server Utama Netking</h6><div style="font-size:12px;color:#64748b;">Bandung<br>Koordinat: -6.9502503, 107.6614869</div></div>')
      .addTo(map);

    // === Area Router Markers + Backbone Lines ===
    var areasWithCoords = @json($areasWithCoords ?? []);
    areasWithCoords.forEach(function(area) {
      if (!area.latitude || !area.longitude) return;

      var areaLatLng = [area.latitude, area.longitude];

      // Router icon - NOC style with blue glow
      var routerIcon = L.divIcon({
        className: '',
        html: `<div style="position:relative;text-align:center;">
          <div style="width:32px;height:32px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);border-radius:8px;border:2px solid #93c5fd;box-shadow:0 0 14px rgba(59,130,246,0.5);display:flex;align-items:center;justify-content:center;">
            <svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
          </div>
          <div style="font-size:8px;font-weight:600;color:#93c5fd;margin-top:1px;text-shadow:0 1px 2px rgba(0,0,0,0.9);white-space:nowrap;max-width:80px;overflow:hidden;text-overflow:ellipsis;">${area.name}</div>
        </div>`,
        iconSize: [32, 48],
        iconAnchor: [16, 16]
      });

      var popupHtml = '<div style="min-width:200px;">' +
        '<h6 style="margin:0 0 6px;font-weight:700;font-size:14px;">📡 ' + area.name + '</h6>' +
        '<table style="font-size:12px;width:100%;line-height:1.8;">' +
        '<tr><td style="color:#64748b;width:85px;">Router IP:</td><td style="font-weight:600;font-family:monospace;">' + (area.router_ip || '-') + '</td></tr>' +
        '<tr><td style="color:#64748b;">VLAN PPPoE:</td><td style="font-weight:600;">' + (area.vlan_pppoe || '-') + '</td></tr>' +
        '<tr><td style="color:#64748b;">VLAN MGMT:</td><td style="font-weight:600;">' + (area.vlan_mgmt || '-') + '</td></tr>' +
        '</table></div>';

      L.marker(areaLatLng, {icon: routerIcon, zIndexOffset: 500})
        .bindPopup(popupHtml)
        .addTo(map);

      // Backbone line from server to router (dashed blue glow)
      L.polyline([serverLatLng, areaLatLng], {
        color: '#60a5fa',
        weight: 2,
        opacity: 0.4,
        dashArray: '6, 8'
      }).addTo(map);
    });
  });
</script>
@endsection
