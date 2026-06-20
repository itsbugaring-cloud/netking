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
        <div class="col-md-4">
          <label class="form-label text-muted" style="font-size:12px; font-weight:600;">Pencarian (Nama / SN)</label>
          <input type="text" id="search-input" class="form-control" placeholder="Cari nama, kode, atau SN ONT...">
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
            <option value="isolated">Isolir (Orange)</option>
            <option value="suspended">Nonaktif (Merah)</option>
          </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button id="reset-filter" class="btn btn-secondary w-100">Reset Filter</button>
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

<script>
  $(function() {
    var map = L.map('map').setView([-6.2088, 106.8456], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap'
    }).addTo(map);

    var allCustomers = @json($customers);
    var markersLayer = L.markerClusterGroup({
      maxClusterRadius: 30,
      spiderfyOnMaxZoom: true,
      showCoverageOnHover: false,
      zoomToBoundsOnClick: true
    });
    
    map.addLayer(markersLayer);

    function renderMarkers(customersList) {
      markersLayer.clearLayers();
      
      var hasValidMarkers = false;
      var bounds = L.latLngBounds();

      customersList.forEach(function(cust) {
        if (cust.latitude && cust.longitude && cust.latitude != 0 && cust.longitude != 0) {
          hasValidMarkers = true;
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

          var bgColor = cust.status === 'active' ? '#10B981' : (cust.status === 'isolated' ? '#F59E0B' : '#EF4444');
          var customIcon = L.divIcon({
            className: 'custom-div-icon',
            html: `<div style="background-color: ${bgColor}; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 8px rgba(0,0,0,0.5);"></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8]
          });

          var marker = L.marker([cust.latitude, cust.longitude], {icon: customIcon}).bindPopup(popupContent);
          markersLayer.addLayer(marker);
          bounds.extend([cust.latitude, cust.longitude]);
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
    $('#area-filter, #status-filter').on('change', applyFilters);

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
      className: 'custom-div-icon',
      html: '<div style="background-color: #DC2626; width: 22px; height: 22px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 10px rgba(220,38,38,0.7); display:flex; align-items:center; justify-content:center;"><span style="color:white; font-size:12px; font-weight:bold;">S</span></div>',
      iconSize: [22, 22],
      iconAnchor: [11, 11]
    });
    L.marker(serverLatLng, {icon: serverIcon})
      .bindPopup('<div style="min-width:180px;"><h6 style="margin:0 0 4px;font-weight:700;">🖥️ Server Utama Netking</h6><div style="font-size:12px;color:#64748b;">Koordinat: -6.9502503, 107.6614869</div></div>')
      .addTo(map);

    // === Area Router Markers + Backbone Lines ===
    var areasWithCoords = @json($areasWithCoords ?? []);
    areasWithCoords.forEach(function(area) {
      if (!area.latitude || !area.longitude) return;

      var areaLatLng = [area.latitude, area.longitude];

      // Blue marker for router
      var routerIcon = L.divIcon({
        className: 'custom-div-icon',
        html: '<div style="background-color: #2563EB; width: 18px; height: 18px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 8px rgba(37,99,235,0.6); display:flex; align-items:center; justify-content:center;"><span style="color:white; font-size:10px; font-weight:bold;">R</span></div>',
        iconSize: [18, 18],
        iconAnchor: [9, 9]
      });

      var popupHtml = '<div style="min-width:180px;">' +
        '<h6 style="margin:0 0 4px;font-weight:700;">📡 ' + area.name + '</h6>' +
        '<table style="font-size:12px;width:100%;">' +
        '<tr><td style="color:#64748b;">Router IP:</td><td style="font-weight:600;font-family:monospace;">' + (area.router_ip || '-') + '</td></tr>' +
        '<tr><td style="color:#64748b;">VLAN PPPoE:</td><td style="font-weight:600;">' + (area.vlan_pppoe || '-') + '</td></tr>' +
        '</table></div>';

      L.marker(areaLatLng, {icon: routerIcon})
        .bindPopup(popupHtml)
        .addTo(map);

      // Dashed blue backbone line from server to router
      L.polyline([serverLatLng, areaLatLng], {
        color: '#2563EB',
        weight: 2,
        opacity: 0.6,
        dashArray: '8, 6'
      }).addTo(map);
    });
  });
</script>
@endsection
