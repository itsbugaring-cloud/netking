@extends('layouts.app')
@section('title', 'Peta Pelanggan')

@section('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<!-- MarkerCluster CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<style>
    #map {
        height: 650px;
        width: 100%;
        border-radius: 16px;
        border: 1px solid var(--border);
        z-index: 1;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 12px;
        background: var(--surface);
        color: var(--txt);
        box-shadow: 0 10px 25px rgba(0,0,0,.15);
        border: 1px solid var(--border);
    }
    .leaflet-popup-tip {
        background: var(--surface);
    }
    .map-popup-header {
        font-weight: 800;
        font-size: 1.05rem;
        margin-bottom: 0.35rem;
        color: var(--txt);
    }
    .map-popup-body {
        font-size: 0.85rem;
        color: var(--txt-2);
        line-height: 1.6;
    }
    .map-popup-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 700;
        margin-top: 0.5rem;
    }
    .map-badge-active { background: color-mix(in srgb, var(--green) 15%, var(--surface)); color: var(--green); }
    .map-badge-isolated { background: color-mix(in srgb, var(--red) 15%, var(--surface)); color: var(--red); }
    
    /* Overlay Control Panel */
    .map-overlay {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        width: 320px;
        max-width: calc(100vw - 40px);
        transition: all 0.3s ease;
    }
    html[data-theme="dark"] .map-overlay {
        background: rgba(30, 41, 59, 0.85);
        border-color: rgba(255, 255, 255, 0.05);
        box-shadow: 0 15px 35px rgba(0,0,0,0.3);
    }
    
    /* Customize Leaflet Controls */
    .leaflet-control-layers {
        border-radius: 12px !important;
        border: 1px solid var(--border) !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08) !important;
        background: var(--surface) !important;
        color: var(--txt) !important;
    }
    .leaflet-control-zoom {
        border: none !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08) !important;
    }
    .leaflet-control-zoom a {
        background: var(--surface) !important;
        color: var(--txt) !important;
        border-color: var(--border) !important;
    }
    .leaflet-control-zoom a:hover {
        background: var(--surface-2) !important;
    }
    
    /* Custom MarkerCluster Colors */
    .marker-cluster-small { background-color: rgba(99, 102, 241, 0.6); }
    .marker-cluster-small div { background-color: rgba(99, 102, 241, 0.9); color: white; }
    .marker-cluster-medium { background-color: rgba(79, 70, 229, 0.6); }
    .marker-cluster-medium div { background-color: rgba(79, 70, 229, 0.9); color: white; }
    .marker-cluster-large { background-color: rgba(67, 56, 202, 0.6); }
    .marker-cluster-large div { background-color: rgba(67, 56, 202, 0.9); color: white; }
</style>
@endsection

@section('content')
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-map-alt'></i> Jaringan</div>
            <h1 class="ms-page-title">Peta Pelanggan</h1>
        </div>
        <div class="ms-page-actions">
            <span class="ms-chip" style="background:var(--surface-2);border-color:var(--border);">
                <i class='bx bx-user-pin'></i> <span id="map-stats-count">{{ $customers->count() }}</span> Pelanggan
            </span>
        </div>
    </div>

    <div class="ms-panel" style="position: relative;">
        <div class="ms-panel-body p-2">
            <div id="map"></div>
            
            {{-- Glassmorphism Overlay --}}
            <div class="map-overlay">
                <h6 style="font-weight:800;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
                    <div style="background:var(--blue);color:white;width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class='bx bx-filter-alt'></i>
                    </div>
                    Pencarian & Filter
                </h6>
                <div class="mb-3">
                    <div class="nk-search-wrap" style="width:100%;">
                        <i class='bx bx-search'></i>
                        <input type="text" id="map-search" class="nk-search-input" placeholder="Cari nama atau pppoe..." style="width:100%;background:var(--surface);">
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:0.75rem;font-weight:700;color:var(--txt-3);margin-bottom:0.25rem;">STATUS</label>
                        <select id="filter-status" class="form-select form-select-sm no-select2" style="border-radius:8px;background:var(--surface);">
                            <option value="">Semua</option>
                            <option value="active">Aktif</option>
                            <option value="isolated">Terisolir</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:0.75rem;font-weight:700;color:var(--txt-3);margin-bottom:0.25rem;">AREA</label>
                        <select id="filter-area" class="form-select form-select-sm no-select2" style="border-radius:8px;background:var(--surface);">
                            <option value="">Semua Area</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<!-- MarkerCluster JS -->
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var customers = @json($customers);
        var mapContainer = document.getElementById('map');
        
        // Setup Base Layers
        var streetLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 19, attribution: '&copy; CARTO'
        });
        
        var satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19, attribution: '&copy; Esri'
        });

        var darkLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 19, attribution: '&copy; CARTO'
        });

        var currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        var defaultLayer = currentTheme === 'dark' ? darkLayer : streetLayer;

        var map = L.map('map', {
            layers: [defaultLayer],
            zoomControl: false // We will add it manually to top left
        });

        // Add zoom control manually
        L.control.zoom({ position: 'topleft' }).addTo(map);

        var baseMaps = {
            "Peta Modern": streetLayer,
            "Satelit": satelliteLayer,
            "Mode Gelap": darkLayer
        };
        L.control.layers(baseMaps, null, {position: 'topleft'}).addTo(map);

        // Icons
        var iconActive = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });

        var iconIsolated = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });

        var iconDefault = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });

        // Setup Marker Cluster Group
        var markersGroup = L.markerClusterGroup({
            chunkedLoading: true,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true,
            maxClusterRadius: 60
        });

        function renderMarkers() {
            markersGroup.clearLayers();
            
            var bounds = [];
            var statusFilter = document.getElementById('filter-status').value;
            var areaFilter = document.getElementById('filter-area').value;
            var searchQuery = document.getElementById('map-search').value.toLowerCase();
            var renderedCount = 0;

            customers.forEach(function(c) {
                if (!c.latitude || !c.longitude) return;

                // Apply Filters
                if (statusFilter === 'active' && (c.is_isolated || c.status !== 'active')) return;
                if (statusFilter === 'isolated' && !c.is_isolated) return;
                if (areaFilter && c.area_id != areaFilter) return;
                
                if (searchQuery) {
                    var name = (c.name || '').toLowerCase();
                    var pppoe = (c.pppoe_user || '').toLowerCase();
                    var phone = (c.phone || '').toLowerCase();
                    if (!name.includes(searchQuery) && !pppoe.includes(searchQuery) && !phone.includes(searchQuery)) return;
                }

                var lat = parseFloat(c.latitude);
                var lng = parseFloat(c.longitude);
                
                var markerIcon = iconDefault;
                var statusBadge = '';
                
                if (c.is_isolated) {
                    markerIcon = iconIsolated;
                    statusBadge = '<span class="map-popup-badge map-badge-isolated"><i class="bx bx-block"></i> Terisolir</span>';
                } else if (c.status === 'active') {
                    markerIcon = iconActive;
                    statusBadge = '<span class="map-popup-badge map-badge-active"><i class="bx bx-check-circle"></i> Aktif</span>';
                }

                var detailUrl = '/admin/customers/' + c.id;

                var popupContent = `
                    <div class="map-popup-header">${c.name}</div>
                    <div class="map-popup-body">
                        <div class="mb-1"><i class='bx bx-wifi' style="color:var(--txt-3);width:16px;"></i> <span style="font-family:var(--font-mono);">${c.pppoe_user || '-'}</span></div>
                        <div class="mb-1"><i class='bx bx-phone' style="color:var(--txt-3);width:16px;"></i> ${c.phone || '-'}</div>
                        <div class="mb-2"><i class='bx bx-map' style="color:var(--txt-3);width:16px;"></i> ${c.address || 'Tidak ada alamat detail'}</div>
                        <div>${statusBadge}</div>
                        <div class="mt-3">
                            <a href="${detailUrl}" class="ms-btn ms-btn-sm w-100" style="padding:0.4rem;font-size:0.75rem;justify-content:center;">Lihat Detail Pelanggan</a>
                        </div>
                    </div>
                `;

                var marker = L.marker([lat, lng], {icon: markerIcon, title: c.name});
                marker.bindPopup(popupContent);
                markersGroup.addLayer(marker);
                bounds.push([lat, lng]);
                renderedCount++;
            });

            map.addLayer(markersGroup);
            
            // Update stats
            document.getElementById('map-stats-count').textContent = renderedCount;
            
            // Fit bounds
            if (bounds.length > 0) {
                map.fitBounds(bounds, {padding: [50, 50], maxZoom: 16});
            } else {
                // Default center if no matches
                map.setView([-2.5489, 118.0149], 5);
            }
        }

        // Initial Render
        renderMarkers();

        // Bind Events
        document.getElementById('filter-status').addEventListener('change', renderMarkers);
        document.getElementById('filter-area').addEventListener('change', renderMarkers);
        
        var searchTimeout;
        document.getElementById('map-search').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(renderMarkers, 400); // Debounce
        });
        
        // Listen to theme changes to dynamically swap base layer
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === "data-theme") {
                    var newTheme = document.documentElement.getAttribute('data-theme');
                    if (newTheme === 'dark' && map.hasLayer(streetLayer)) {
                        map.removeLayer(streetLayer);
                        map.addLayer(darkLayer);
                    } else if (newTheme === 'light' && map.hasLayer(darkLayer)) {
                        map.removeLayer(darkLayer);
                        map.addLayer(streetLayer);
                    }
                }
            });
        });
        observer.observe(document.documentElement, { attributes: true });
    });
</script>
@endsection
