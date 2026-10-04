<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histori Perjalanan - GPS Tracking</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        .strava-stats { background-color: #1a1a1a; color: white; border-radius: 8px; padding: 20px; }
        .strava-orange { color: #0230fc; }
    </style>
</head>
<body class="bg-gray-50">

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="logo">
        <i class="fas fa-motorcycle"></i>
        <span>GPS Tracking</span>
    </div>

    <ul class="menu">
        <li>
            <a href="{{ route('dashboard') }}">
                Dashboard
            </a>
        </li>

        <li>
            <a href="{{ route('history.index') }}">
                Histori Perjalanan
            </a>
        </li>        
        
        @php
            $idPerangkatSession = session('user_data')['id_perangkat'] ?? session('id_perangkat') ?? 'perangkat_1';
        @endphp
        <li>
            <a href="{{ route('notifications.index', $idPerangkatSession) }}">
                Notifikasi
            </a>
        </li>

        <li>
            <a href="{{ route('profile.edit') }}">
                Profile
            </a>
        </li>

        <li>
            @if(session('user_data') && session('user_data')['email'] === 'handynandaf@gmail.com')
                <a href="{{ route('lokasi.aman') }}">
                    <span>Ubah Koordinat</span>
                </a>
            @endif
        </li>

        @if(session('user_data.email') === 'handynandaf@gmail.com')
            <li>
                <a href="{{ route('perangkat.index') }}">
                    <span>Kelola Perangkat</span>
                </a>
            </li>
        @endif

        <li>
            @if(session('user_data') && session('user_data')['email'] === 'handynandaf@gmail.com')
                <a href="{{ route('register') }}">
                    <span>Daftarkan User</span>
                </a>
            @endif
        </li>
    </ul>

    <div class="logout-box">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="fas fa-right-from-bracket"></i>
                Logout
            </button>
        </form>
    </div>

</div>

<!-- CONTENT -->
<div class="main-content">
    <div class="bg-white rounded-xl p-5 shadow-sm mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Histori Perjalanan</h1>
            <p>Rekam jejak rute kendaraan & status lokasi aman</p>
        </div>
        <div class="profile-box flex items-center gap-3 bg-gray-50 border border-gray-200 px-4 py-3 rounded-xl">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-user text-blue-600"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">
                    Akun Saat Ini
                </p>
                <p class="text-sm font-semibold text-gray-700">
                    {{ session('user_data')['name'] ?? 'Pengguna' }}
                </p>
            </div>
        </div>
    </div>

    <!-- FILTER PERANGKAT & TANGGAL -->
    <div class="mt-6 bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-wrap items-center justify-between gap-4">
        <!-- Select Perangkat -->
        <div class="flex items-center gap-3">
            <i class="fas fa-microchip text-blue-600 text-xl"></i>
            <h2 class="font-bold text-gray-800 text-lg">Pilih Perangkat:</h2>
            <select id="device-select" class="border border-gray-300 rounded-md p-2 shadow-sm focus:ring focus:ring-blue-200 outline-none font-semibold text-gray-700 bg-white">
                <option value="GPS001" selected>GPS001</option>
                <option value="GPS002">GPS002</option>
                <option value="GPS003">GPS003</option>
            </select>
        </div>

        <!-- Date Picker Tanggal -->
        <div class="flex items-center gap-3">
            <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
            <h2 class="font-bold text-gray-800 text-lg">Pilih Tanggal:</h2>
            <input type="date" id="history-date" class="border border-gray-300 rounded-md p-2 shadow-sm focus:ring focus:ring-blue-200 outline-none font-semibold text-gray-700">
        </div>
    </div>

    <!-- KARTU STATISTIK -->
    <div class="mt-4 grid grid-cols-2 gap-4 strava-stats shadow-lg">
        <div>
            <p class="text-sm text-gray-400">Total Jarak Tempuh</p>
            <h2 class="text-3xl font-bold"><span id="total-distance">0.00</span> <span class="text-lg font-normal">km</span></h2>
        </div>
        <div>
            <p class="text-sm text-gray-400">Top Speed</p>
            <h2 class="text-3xl font-bold"><span id="top-speed">0</span> <span class="text-lg font-normal">km/h</span></h2>
        </div>
    </div>

    <!-- PETA HISTORY RUTE (FULL WIDTH) -->
    <div class="mt-6 bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-col">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold"><i class="fas fa-route strava-orange mr-2"></i> Peta History Rute </h2>
            
            <button id="btn-center-map" class="bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-bold py-1.5 px-3 rounded-lg border border-blue-300 shadow-sm transition-all">
                <i class="fas fa-crosshairs mr-1"></i> Pusatkan Garis
            </button>
        </div>
        <div id="map" class="w-full h-[500px] rounded-lg border border-gray-300 z-10"></div>
    </div>

    <!-- TABEL LOG HISTORI (FULL WIDTH DI BAWAH PETA) -->
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col mb-10">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">Log Koordinat: <span id="label-tanggal-tabel" class="text-blue-600"></span></h2>
            <span class="text-xs text-gray-500 font-medium">Menampilkan histori titik pergerakan</span>
        </div>
        
        <!-- Area tabel penuh -->
        <div class="overflow-x-auto p-4 max-h-[450px] overflow-y-auto">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 bg-white z-10 shadow-sm">
                    <tr class="text-gray-600 text-xs uppercase tracking-wider border-b">
                        <th class="p-3">Waktu</th>
                        <th class="p-3">Latitude</th>
                        <th class="p-3">Longitude</th>
                        <th class="p-3">Kecepatan</th>
                        <th class="p-3 text-center">Status Geofence</th>
                    </tr>
                </thead>
                <tbody id="history-table-body" class="text-sm text-gray-700">
                    <tr><td colspan="5" class="p-4 text-center text-gray-500">Memuat data rute...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
    import { getDatabase, ref, onValue, off, get } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

    const firebaseConfig = {
        apiKey: "AIzaSyDyq3zHtWrnQPaalfkwAD4_rkesBTicj0k",
        databaseURL: "https://gps-tracking-6ba6f-default-rtdb.asia-southeast1.firebasedatabase.app",
        projectId: "gps-tracking-6ba6f",
    };

    const app = initializeApp(firebaseConfig);
    const db = getDatabase(app);

    // Inisialisasi Peta
    const map = L.map('map').setView([-6.862989, 108.584027], 13);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    let routeLine;
    let geofenceCircle;
    let geofenceCenterMarker;
    let currentListenerRef = null;

    // Variabel Penampung Geofence
    let safeLat = -6.862989;
    let safeLng = 108.584027;
    let safeRadius = 500; // Dalam Meter

    // Algoritma Haversine
    function hitungJarakHaversine(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    // Fungsi Mengambil Settings Geofence
    async function loadGeofenceSettings(deviceId) {
        try {
            const settingsSnap = await get(ref(db, 'GPS_TRACKING/SETTINGS'));
            if (settingsSnap.exists()) {
                const settingsData = settingsSnap.val();
                if (settingsData.lat) safeLat = parseFloat(settingsData.lat);
                if (settingsData.lng) safeLng = parseFloat(settingsData.lng);
                if (settingsData.radius) safeRadius = parseFloat(settingsData.radius);
            }

            const devSnap = await get(ref(db, `GPS_TRACKING/PERANGKAT/${deviceId}/radius_geofencing`));
            if (devSnap.exists()) {
                safeRadius = parseFloat(devSnap.val());
            }

            if (geofenceCircle) map.removeLayer(geofenceCircle);
            if (geofenceCenterMarker) map.removeLayer(geofenceCenterMarker);

            geofenceCircle = L.circle([safeLat, safeLng], {
                color: '#2563eb',
                fillColor: '#60a5fa',
                fillOpacity: 0.2,
                radius: safeRadius,
                weight: 2,
                dashArray: '6, 6'
            }).addTo(map).bindPopup(`<b>Lokasi Aman / Geofence</b><br>Radius: ${safeRadius} Meter`);

            geofenceCenterMarker = L.circleMarker([safeLat, safeLng], {
                radius: 5,
                color: '#1d4ed8',
                fillColor: '#3b82f6',
                fillOpacity: 1
            }).addTo(map).bindPopup("<b>Pusat Lokasi Aman</b>");

        } catch (err) {
            console.error("Gagal memuat settings Geofence:", err);
        }
    }

    // Fungsi Utama: Load History
    async function loadHistory(deviceId, tanggal) {
        const tableBody = document.getElementById("history-table-body");
        document.getElementById("label-tanggal-tabel").innerText = `${deviceId} (${tanggal})`;

        await loadGeofenceSettings(deviceId);

        if (routeLine) map.removeLayer(routeLine);
        if (window.routeMarkers) {
            window.routeMarkers.forEach(m => map.removeLayer(m));
        }
        window.routeMarkers = [];

        document.getElementById("total-distance").innerText = "0.00";
        document.getElementById("top-speed").innerText = "0";
        tableBody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i>Mencari data dari Firebase...</td></tr>';

        if (currentListenerRef) {
            off(currentListenerRef);
        }

        currentListenerRef = ref(db, `GPS_TRACKING/HISTORY/${deviceId}/${tanggal}`);

        onValue(currentListenerRef, (snapshot) => {
            const data = snapshot.val();
            
            if (!data) {
                tableBody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-red-500 font-semibold">Belum ada histori perjalanan untuk perangkat & tanggal ini.</td></tr>';
                return;
            }

            tableBody.innerHTML = '';
            
            let allSegments = [];
            let currentSegment = [];
            
            let totalJarakKm = 0;
            let topSpeed = 0;
            let lastLat = null;
            let lastLng = null;
            let tableRows = [];

            Object.keys(data).forEach((key) => {
                const point = data[key];
                
                if(point.lat && point.lng) {
                    const lat = parseFloat(point.lat);
                    const lng = parseFloat(point.lng);

                    // Status Geofence
                    const jarakKePusatMeter = hitungJarakHaversine(safeLat, safeLng, lat, lng) * 1000;
                    const isInsideGeofence = jarakKePusatMeter <= safeRadius;

                    const statusBadge = isInsideGeofence
                        ? `<span class="bg-green-100 text-green-700 font-bold px-2.5 py-1 rounded-full text-xs">Aman</span>`
                        : `<span class="bg-red-100 text-red-700 font-bold px-2.5 py-1 rounded-full text-xs">Keluar Area</span>`;

                    let isJump = false;

                    if(lastLat !== null && lastLng !== null) {
                        const jarakTitik = hitungJarakHaversine(lastLat, lastLng, lat, lng);
                        
                        if(jarakTitik > 0.5) {
                            isJump = true;
                        } else {
                            totalJarakKm += jarakTitik;
                        }
                    }

                    if(isJump) {
                        if(currentSegment.length > 0) allSegments.push(currentSegment);
                        currentSegment = [];
                    }

                    currentSegment.push([lat, lng]);
                    
                    const currentSpeed = point.speed || 0;
                    if(currentSpeed > topSpeed) topSpeed = currentSpeed;

                    lastLat = lat;
                    lastLng = lng;
                    
                    tableRows.push(`
                        <tr class="border-b hover:bg-gray-50 transition-all">
                            <td class="p-3 font-semibold text-gray-700">${point.waktu || '-'}</td>
                            <td class="p-3 font-mono text-gray-600">${lat}</td>
                            <td class="p-3 font-mono text-gray-600">${lng}</td>
                            <td class="p-3 font-semibold text-blue-600">${Math.round(currentSpeed)} km/h</td>
                            <td class="p-3 text-center">${statusBadge}</td>
                        </tr>
                    `);
                }
            });

            if(currentSegment.length > 0) {
                allSegments.push(currentSegment);
            }

            // Tampilkan 20 log terakhir agar tabel terisi lebih banyak dan jelas
            if (tableRows.length > 0) {
                tableBody.innerHTML = tableRows.slice(-20).join('');
            } else {
                tableBody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-gray-500">Data rute tidak valid.</td></tr>';
            }

            document.getElementById("total-distance").innerText = totalJarakKm.toFixed(2);
            document.getElementById("top-speed").innerText = Math.round(topSpeed);

            if (allSegments.length > 0) {
                if (routeLine) map.removeLayer(routeLine);

                routeLine = L.polyline(allSegments, {
                    color: '#0000CD', 
                    weight: 3,
                    opacity: 0.8,
                    smoothFactor: 1
                }).addTo(map);

                allSegments.forEach((segment, index) => {
                    const titikAwal = segment[0];
                    const titikAkhir = segment[segment.length - 1];

                    let markerMulai = L.circleMarker(titikAwal, { radius: 6, color: 'green', fillColor: 'white', fillOpacity: 1 })
                        .addTo(map).bindPopup(`<b>Titik Awal</b> (Sesi ${index + 1})`);
                    
                    let markerBerhenti = L.circleMarker(titikAkhir, { radius: 6, color: 'red', fillColor: 'white', fillOpacity: 1 })
                        .addTo(map).bindPopup(`<b>Titik Berhenti</b> (Sesi ${index + 1})`);

                    window.routeMarkers.push(markerMulai, markerBerhenti);
                });

                map.fitBounds(routeLine.getBounds(), { padding: [30, 30] });
            }
        });
    }

    // ========== EVENT LISTENER FILTER ==========
    const dateInput = document.getElementById('history-date');
    const deviceSelect = document.getElementById('device-select');

    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    dateInput.value = `${year}-${month}-${day}`;

    function triggerLoadHistory() {
        const selectedDevice = deviceSelect.value;
        const selectedDate = dateInput.value;
        if (selectedDevice && selectedDate) {
            loadHistory(selectedDevice, selectedDate);
        }
    }

    triggerLoadHistory();

    dateInput.addEventListener('change', triggerLoadHistory);
    deviceSelect.addEventListener('change', triggerLoadHistory);

    document.getElementById('btn-center-map').addEventListener('click', function() {
        if (routeLine) {
            map.fitBounds(routeLine.getBounds(), { padding: [30, 30], animate: true });
        } else {
            alert("Belum ada garis rute untuk dipusatkan!");
        }
    });
</script>
</body>
</html>