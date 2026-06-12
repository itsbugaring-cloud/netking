@extends('layouts.app')
@section('title', 'Peta Pelanggan')

@section('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #map {
        height: 600px;
        width: 100%;
        border-radius: 12px;
        border: 1px solid var(--border);
        z-index: 1;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 8px;
        background: var(--surface);
        color: var(--txt);
        box-shadow: 0 4px 16px rgba(0,0,0,.15);
    }
    .leaflet-popup-tip {
        background: var(--surface);
    }
    .map-popup-header {
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 0.25rem;
        color: var(--blue);
    }
    .map-popup-body {
        font-size: 0.85rem;
        color: var(--txt-2);
        line-height: 1.5;
    }
    .map-popup-badge {
        display: inline-block;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-top: 0.4rem;
    }
    .map-badge-active { background: color-mix(in srgb, var(--green) 15%, var(--surface)); color: var(--green); }
    .map-badge-isolated { background: color-mix(in srgb, var(--red) 15%, var(--surface)); color: var(--red); }
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
                <i class='bx bx-user-pin'></i> {{ $customers->count() }} Pelanggan Terpetakan
            </span>
        </div>
    </div>

    <div class="ms-panel">
        <div class="ms-panel-body p-2">
            <div id="map"></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Map
        // Default center to Indonesia if no customers, else center to first customer
        var customers = @json($customers);
        var centerLat = -2.5489;
        var centerLng = 118.0149;
        var zoomLevel = 5;

        if (customers.length > 0) {
            centerLat = customers[0].latitude;
            centerLng = customers[0].longitude;
            zoomLevel = 13;
        }

        var map = L.map('map').setView([centerLat, centerLng], zoomLevel);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        // Define custom icon (Optional, using default for now, but we can change colors based on status)
        var iconActive = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        var iconIsolated = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        var iconDefault = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        // Add markers
        var bounds = [];
        customers.forEach(function(c) {
            if (c.latitude && c.longitude) {
                var lat = parseFloat(c.latitude);
                var lng = parseFloat(c.longitude);
                
                var markerIcon = iconDefault;
                var statusBadge = '';
                
                if (c.is_isolated) {
                    markerIcon = iconIsolated;
                    statusBadge = '<span class="map-popup-badge map-badge-isolated">Terisolir</span>';
                } else if (c.status === 'active') {
                    markerIcon = iconActive;
                    statusBadge = '<span class="map-popup-badge map-badge-active">Aktif</span>';
                }

                var popupContent = `
                    <div class="map-popup-header">${c.name}</div>
                    <div class="map-popup-body">
                        <i class='bx bx-wifi'></i> ${c.pppoe_user || '-'}<br>
                        <i class='bx bx-map'></i> ${c.address || 'Tidak ada alamat'}<br>
                        ${statusBadge}
                    </div>
                `;

                var marker = L.marker([lat, lng], {icon: markerIcon}).addTo(map)
                    .bindPopup(popupContent);
                
                bounds.push([lat, lng]);
            }
        });

        // Fit map to bounds if there are multiple markers
        if (bounds.length > 1) {
            map.fitBounds(bounds);
        }
    });
</script>
@endsection
