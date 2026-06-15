@section('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<!-- MarkerCluster JS -->
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            zoomControl: false
        });

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

        var markersGroup = L.markerClusterGroup({
            chunkedLoading: true,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true,
            maxClusterRadius: 60
        });

        var currentChart = null;
        var trafficInterval = null;

        function renderMarkers() {
            markersGroup.clearLayers();
            
            var bounds = [];
            var statusFilter = document.getElementById('filter-status').value;
            var areaFilter = document.getElementById('filter-area').value;
            var searchQuery = document.getElementById('map-search').value.toLowerCase();
            var renderedCount = 0;

            customers.forEach(function(c) {
                if (!c.latitude || !c.longitude) return;

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
                        <div class="mb-1"><i class='bx bx-map' style="color:var(--txt-3);width:16px;"></i> ${c.address || '-'}</div>
                        <div>${statusBadge}</div>
                        <hr style="margin: 0.5rem 0; border-color: var(--border);">
                        <div style="font-size:0.75rem;font-weight:600;margin-bottom:0.2rem;display:flex;align-items:center;"><i class='bx bx-pulse' style="color:var(--blue);margin-right:4px;"></i> Live Traffic MRTG</div>
                        <div style="height: 100px; width: 100%; position: relative;">
                            <canvas id="traffic-chart-${c.id}"></canvas>
                        </div>
                        <div id="traffic-status-${c.id}" style="font-size:0.7rem;text-align:center;color:var(--txt-3);margin-top:0.2rem;">Menghubungkan ke MikroTik...</div>
                        <div class="mt-2">
                            <a href="${detailUrl}" class="ms-btn ms-btn-sm w-100" style="padding:0.4rem;font-size:0.75rem;justify-content:center;">Lihat Detail Pelanggan</a>
                        </div>
                    </div>
                `;

                var marker = L.marker([lat, lng], {icon: markerIcon, title: c.name});
                marker.customerData = c;
                marker.bindPopup(popupContent, { minWidth: 240 });
                markersGroup.addLayer(marker);
                bounds.push([lat, lng]);
                renderedCount++;
            });

            map.addLayer(markersGroup);
            
            document.getElementById('map-stats-count').textContent = renderedCount;
            
            if (bounds.length > 0) {
                map.fitBounds(bounds, {padding: [50, 50], maxZoom: 16});
            } else {
                map.setView([-2.5489, 118.0149], 5);
            }
        }

        renderMarkers();

        // Traffic Polling Events
        map.on('popupopen', function(e) {
            var marker = e.popup._source;
            if (!marker || !marker.customerData) return;
            var c = marker.customerData;

            var ctx = document.getElementById('traffic-chart-' + c.id);
            if (!ctx) return;

            if (currentChart) { currentChart.destroy(); }
            clearInterval(trafficInterval);

            var chartData = {
                labels: [],
                datasets: [
                    { label: 'RX (Kbps)', borderColor: '#22c55e', backgroundColor: 'rgba(34, 197, 94, 0.1)', data: [], fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0 },
                    { label: 'TX (Kbps)', borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', data: [], fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0 }
                ]
            };

            currentChart = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 0 },
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { display: false },
                        y: { beginAtZero: true, ticks: { font: { size: 9 } } }
                    }
                }
            });

            function pollTraffic() {
                fetch('/admin/maps/traffic/' + c.id)
                    .then(res => res.json())
                    .then(data => {
                        var statusEl = document.getElementById('traffic-status-' + c.id);
                        if (!statusEl) return;
                        
                        if (!data.success) {
                            statusEl.innerHTML = `<span style="color:var(--red)"><i class="bx bx-error"></i> ${data.error || 'Offline'}</span>`;
                            return;
                        }
                        
                        var rxKbps = Math.round(data.rx / 1024);
                        var txKbps = Math.round(data.tx / 1024);
                        
                        statusEl.innerHTML = `<strong>RX:</strong> <span style="color:#22c55e">${rxKbps} Kbps</span> | <strong>TX:</strong> <span style="color:#3b82f6">${txKbps} Kbps</span>`;

                        var now = new Date();
                        var timeLabel = now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds();
                        
                        if (chartData.labels.length > 15) {
                            chartData.labels.shift();
                            chartData.datasets[0].data.shift();
                            chartData.datasets[1].data.shift();
                        }
                        
                        chartData.labels.push(timeLabel);
                        chartData.datasets[0].data.push(rxKbps);
                        chartData.datasets[1].data.push(txKbps);
                        currentChart.update();
                    })
                    .catch(err => {
                        console.error(err);
                        var statusEl = document.getElementById('traffic-status-' + c.id);
                        if (statusEl) statusEl.innerHTML = `<span style="color:var(--red)"><i class="bx bx-error"></i> Gagal menghubungi server</span>`;
                    });
            }

            pollTraffic();
            trafficInterval = setInterval(pollTraffic, 3000); // 3 seconds
        });

        map.on('popupclose', function(e) {
            if (currentChart) { currentChart.destroy(); currentChart = null; }
            clearInterval(trafficInterval);
        });

        // Auto Refresh Map Polling
        setInterval(function() {
            fetch('/admin/maps/status')
                .then(res => res.json())
                .then(data => {
                    var changed = false;
                    var statusMap = {};
                    data.forEach(d => { statusMap[d.id] = d; });

                    customers.forEach(c => {
                        var upd = statusMap[c.id];
                        if (upd) {
                            if (c.status !== upd.status || c.is_isolated !== upd.is_isolated) {
                                c.status = upd.status;
                                c.is_isolated = upd.is_isolated;
                                changed = true;
                            }
                        }
                    });

                    if (changed) {
                        // Only rerender if someone's status changed
                        renderMarkers();
                    }
                })
                .catch(err => console.error("Auto refresh map error", err));
        }, 15000); // Poll every 15s

        // Bind Events
        document.getElementById('filter-status').addEventListener('change', renderMarkers);
        document.getElementById('filter-area').addEventListener('change', renderMarkers);
        
        var searchTimeout;
        document.getElementById('map-search').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(renderMarkers, 400); // Debounce
        });
        
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
