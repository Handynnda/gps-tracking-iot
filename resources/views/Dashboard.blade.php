<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Keamanan & Pelacakan Kendaraan - Dashboard</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-800">

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
            <a href="{{ route('notifications.index', $idPerangkatSession) }}" class="...">
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

        <li>
            @if(session('user_data') && session('user_data')['email'] === 'handynandaf@gmail.com')
                <a href="{{ route('perangkat.index') }}">
                    <span>Kelola Perangkat</span>
                </a>
            @endif
        </li>

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

<!-- MAIN CONTENT -->
<div class="main-content p-6">

    <!-- TOPBAR / HEADER -->
    <div class="bg-white rounded-xl p-5 shadow-sm mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <!-- JUDUL -->
        <div>
            <h1 class="text-xl font-bold text-gray-800">
                Sistem Keamanan dan Pelacakan Kendaraan Berbasis IoT
            </h1>

            <p class="text-sm text-gray-500 font-medium">
                Geofencing dengan Algoritma Haversine pada ESP32
            </p>
        </div>
        <!-- BAGIAN KANAN -->
        <div class="flex flex-col items-end gap-2">
            <!-- ONLINE + JAM -->
            <div class="flex items-center gap-4">
                <!-- STATUS ONLINE -->
                <div class="flex items-center gap-2 bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-full text-xs font-semibold border border-emerald-200">
                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span id="conn-status">
                        Online
                    </span>
                </div>
                <!-- JAM & TANGGAL -->
                <div class="text-right">
                    <div id="realtime-clock"
                        class="text-sm font-bold text-gray-700">
                        --:--:--
                    </div>
                    <div id="realtime-date"
                        class="text-xs text-gray-500">
                        -- --- ----
                    </div>
                </div>
            </div>

            <!-- DROPDOWN PERANGKAT -->
            <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-lg shadow-sm">
                <i class="fas fa-microchip text-blue-600"></i>
                <label for="device-select" class="text-xs font-bold text-gray-700">
                    Pilih Perangkat:
                </label>
                <select
                    id="device-select"
                    class="bg-white border border-gray-300 text-gray-800 text-xs rounded-md focus:ring-blue-500 focus:border-blue-500 p-1 font-semibold cursor-pointer">
                    <!-- Opsi terisi otomatis dari Firebase -->
                    <option value="">Memuat perangkat...</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- TOP METRIC CARDS (5 CARD) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        
        <!-- Latitude -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="overflow-hidden">
                <small class="text-xs text-gray-400 font-semibold block uppercase">Latitude</small>
                <h3 id="lat" class="text-base font-bold text-gray-800 truncate">0.000000</h3>
                <small class="text-[10px] text-gray-400">Koordinat Saat Ini</small>
            </div>
        </div>

        <!-- Longitude -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-location-crosshairs"></i>
            </div>
            <div class="overflow-hidden">
                <small class="text-xs text-gray-400 font-semibold block uppercase">Longitude</small>
                <h3 id="lng" class="text-base font-bold text-gray-800 truncate">0.000000</h3>
                <small class="text-[10px] text-gray-400">Koordinat Saat Ini</small>
            </div>
        </div>

        <!-- Kecepatan -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-gauge-high"></i>
            </div>
            <div>
                <small class="text-xs text-gray-400 font-semibold block uppercase">Kecepatan</small>
                <h3 class="text-base font-bold text-gray-800"><span id="speed">0</span> km/h</h3>
                <small class="text-[10px] text-gray-400">Kecepatan Kendaraan</small>
            </div>
        </div>

        <!-- Jarak dari Pusat -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-ruler-combined"></i>
            </div>
            <div>
                <small class="text-xs text-gray-400 font-semibold block uppercase">Jarak dari Pusat</small>
                <h3 class="text-base font-bold text-gray-800"><span id="jarak">0</span> m</h3>
                <small class="text-[10px] text-gray-400">(Haversine)</small>
            </div>
        </div>

        <!-- Status Area -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div id="status-icon-bg" class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl shrink-0">
                <i id="status-icon" class="fas fa-shield-check"></i>
            </div>
            <div>
                <small class="text-xs text-gray-400 font-semibold block uppercase">Status</small>
                <span id="status-badge" class="inline-block px-2 py-0.5 text-xs font-bold rounded bg-emerald-100 text-emerald-700 mt-0.5">
                    DALAM AREA
                </span>
                <small id="status-desc" class="text-[10px] text-gray-400 block mt-0.5">Kendaraan Aman</small>
            </div>
        </div>

    </div>

    <!-- MIDDLE SECTION: MAP (2/3) + INFO PANELS (1/3) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <!-- PETA LOKASI KENDARAAN -->
        <div class="lg:col-span-2 bg-white rounded-xl p-5 shadow-sm border border-gray-100 flex flex-col">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-map-location-dot text-blue-600"></i> Peta Lokasi Kendaraan
                </h2>
                <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">
                    LIVE
                </span>
            </div>

            <!-- Leaflet Container -->
            <div class="relative flex-1 min-h-[380px] rounded-lg overflow-hidden border border-gray-200">
                <div id="map" class="w-full h-full min-h-[380px]"></div>

                <!-- Custom Overlay Legend -->
                <div class="absolute bottom-3 right-3 bg-white/95 backdrop-blur-sm p-3 rounded-lg shadow-md border border-gray-200 text-xs z-[1000] space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 border border-white shadow-sm inline-block"></span>
                        <span class="text-gray-700 font-medium">Pusat Geofence</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-500 border border-white shadow-sm inline-block"></span>
                        <span class="text-gray-700 font-medium">Posisi Kendaraan</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full border-2 border-dashed border-blue-500 bg-blue-100/40 inline-block"></span>
                        <span class="text-gray-700 font-medium">Area Geofence (Radius <span id="legend-radius">50</span> m)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANEL INFORMASI KENDARAAN & STATUS SISI KANAN -->
        <div class="space-y-6">
            
            <!-- Card Informasi Kendaraan -->
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <h3 class="text-sm font-bold text-gray-800 pb-3 border-b border-gray-100 flex items-center gap-2">
                    <i class="fas fa-motorcycle text-gray-600"></i> Informasi Kendaraan
                </h3>
                <div class="mt-3 space-y-2.5 text-xs text-gray-600">
                    <div class="flex justify-between">
                        <span class="text-gray-400">ID Perangkat</span>
                        <span id="info-id-perangkat" class="font-semibold text-gray-800">GPS001</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Nama Kendaraan</span>
                        <span class="font-semibold text-gray-800">Motor Pribadi</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Waktu Terakhir</span>
                        <span id="info-last-time" class="font-semibold text-gray-800">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Jarak dari Pusat</span>
                        <span class="font-semibold text-gray-800"><span id="info-jarak">0</span> m</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Radius Geofence</span>
                        <span class="font-semibold text-gray-800"><span id="info-radius">50</span> m</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Status</span>
                        <span id="info-status-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                            DALAM AREA
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card Status Sistem Keamanan -->
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <h3 class="text-sm font-bold text-gray-800 pb-3 border-b border-gray-100 flex items-center gap-2">
                    <i class="fas fa-shield-halved text-gray-600"></i> Status Sistem Keamanan
                </h3>
                <div class="mt-3 space-y-2.5 text-xs text-gray-600">
                    {{-- <div class="flex justify-between items-center">
                        <span class="text-gray-400">Alarm</span>
                        <span id="alarm-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600">
                            NONAKTIF
                        </span>
                    </div> --}}
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Relay (Engine Cut Off)</span>
                        <span id="relay-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                            AKTIF
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Notifikasi</span>
                        <span id="notif-status-text" class="font-semibold text-gray-800">Gmail & App</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Koneksi IoT</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                            TERHUBUNG
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Sinyal GPS</span>
                        <span class="font-semibold text-gray-800 flex items-center gap-1">
                            <i class="fas fa-satellite text-blue-500"></i>
                            <span id="satelit">0</span> Satelit
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- BOTTOM SECTION: TABEL RIWAYAT + GRAFIK CHARTS + NOTIFIKASI -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- KOLOM 1: Riwayat Lokasi Terbaru -->
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-clock-rotate-left text-blue-600"></i> Riwayat Lokasi Terbaru
                </h3>
                <a href="{{ route('history.index') }}" class="text-xs text-blue-600 font-semibold hover:underline">
                    Lihat Semua
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[11px] font-bold text-gray-400 uppercase border-b border-gray-100">
                            <th class="pb-2">Waktu</th>
                            <th class="pb-2">Lat</th>
                            <th class="pb-2">Lng</th>
                            <th class="pb-2 text-right">Jarak</th>
                        </tr>
                    </thead>
                    <tbody id="history-table-body" class="text-xs divide-y divide-gray-50">
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-400">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- KOLOM 2: Grafik Jarak dari Pusat Geofence (Chart.js) -->
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-chart-line text-emerald-600"></i> Grafik Jarak Geofence
                </h3>
            </div>
            <p class="text-[11px] text-gray-400 mb-4">Pergerakan jarak real-time terhadap batas radius geofence.</p>
            <div class="h-48 relative">
                <canvas id="geofenceChart"></canvas>
            </div>
        </div>

        <!-- KOLOM 3: Notifikasi Terbaru -->
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-bell text-amber-500"></i> Notifikasi Terbaru
                </h3>
                <a href="{{ route('notifications.index', $idPerangkatSession) }}" class="text-xs text-blue-600 font-semibold hover:underline">
                    Lihat Semua
                </a>
            </div>

            <div id="recent-notifications-list" class="space-y-3">
                <div class="flex items-start gap-3 p-2 bg-gray-50 rounded-lg">
                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full mt-1.5 shrink-0"></span>
                    <div>
                        <p class="text-xs font-semibold text-gray-700">Kendaraan dalam area aman</p>
                        <span class="text-[10px] text-gray-400">Sistem Berjalan Normal</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- LEAFLET SCRIPT -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<!-- FIREBASE MODULE & LOGIC INTEGRATION -->
<script type="module">
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import { getDatabase, ref, onValue } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

const firebaseConfig = {
    apiKey: "ISI_API_KEY",
    databaseURL: "https://gps-tracking-6ba6f-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "gps-tracking-6ba6f",
};

const app = initializeApp(firebaseConfig);
const db = getDatabase(app);

// --- STATE PENGATURAN PERANGKAT ---
let currentDeviceId = ""; // ID Perangkat yang sedang aktif
let historyLog = []; // Penampung riwayat lokal
let globalPerangkatData = {}; // Cache data PERANGKAT
let globalGeofenceData = {};  // Cache data GEOFENCE

// --- 1. CLOCK REAL-TIME ---
function updateClock() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    const dateStr = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    
    const clockEl = document.getElementById('realtime-clock');
    const dateEl = document.getElementById('realtime-date');
    if (clockEl) clockEl.innerText = timeStr;
    if (dateEl) dateEl.innerText = dateStr;
}
setInterval(updateClock, 1000);
updateClock();

// --- 2. LEAFLET MAP SETUP ---
// Koordinat awal sementara saat peta pertama kali di-render sebelum data Firebase masuk
const initialLat = -6.914732;
const initialLng = 107.609810;
const defaultRadius = 50;

const map = L.map('map').setView([initialLat, initialLng], 16);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
}).addTo(map);

// Custom Marker Icons
const greenCenterIcon = L.divIcon({
    className: 'custom-pin-center',
    html: `<div style="background-color:#10b981; width:18px; height:18px; border-radius:50%; border:3px solid white; box-shadow:0 2px 5px rgba(0,0,0,0.3);"></div>`,
    iconSize: [18, 18],
    iconAnchor: [9, 9]
});

const redVehicleIcon = L.divIcon({
    className: 'custom-pin-vehicle',
    html: `<div style="background-color:#ef4444; color:white; width:26px; height:26px; border-radius:50%; border:2px solid white; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(0,0,0,0.4);"><i class="fas fa-motorcycle text-xs"></i></div>`,
    iconSize: [26, 26],
    iconAnchor: [13, 13]
});

let centerMarker = L.marker([initialLat, initialLng], { icon: greenCenterIcon }).addTo(map);
let vehicleMarker = L.marker([initialLat, initialLng], { icon: redVehicleIcon }).addTo(map);

let geofenceCircle = L.circle([initialLat, initialLng], {
    radius: defaultRadius,
    color: '#3b82f6',
    dashArray: '6, 6',
    fillColor: '#3b82f6',
    fillOpacity: 0.12,
    weight: 2
}).addTo(map);

let distanceLine = L.polyline([[initialLat, initialLng], [initialLat, initialLng]], {
    color: '#ef4444',
    dashArray: '4, 4',
    weight: 2
}).addTo(map);

// --- 3. CHART.JS SETUP ---
const ctx = document.getElementById('geofenceChart').getContext('2d');
const geofenceChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [
            {
                label: 'Jarak Kendaraan (m)',
                data: [],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            },
            {
                label: `Batas Radius (${defaultRadius}m)`,
                data: [],
                borderColor: '#ef4444',
                borderWidth: 1.5,
                borderDash: [5, 5],
                pointRadius: 0,
                fill: false
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 9 } } },
            y: { beginAtZero: true, ticks: { font: { size: 9 } } }
        }
    }
});

// Reset grafik & tabel riwayat ketika ganti perangkat
function resetDashboardState() {
    historyLog = [];
    geofenceChart.data.labels = [];
    geofenceChart.data.datasets[0].data = [];
    geofenceChart.data.datasets[1].data = [];
    geofenceChart.update();

    const tbody = document.getElementById("history-table-body");
    if (tbody) tbody.innerHTML = `<tr><td colspan="4" class="py-2 text-center text-gray-400">Memuat data...</td></tr>`;
}

// --- 4. DROPDOWN EVENT LISTENER ---
const deviceSelectEl = document.getElementById('device-select');
if (deviceSelectEl) {
    deviceSelectEl.addEventListener('change', (e) => {
        currentDeviceId = e.target.value;
        resetDashboardState();
        updateDashboardUI();
    });
}

// Auto Populate Dropdown dari Firebase
function populateDeviceDropdown(perangkatDict) {
    if (!deviceSelectEl) return;

    const deviceKeys = Object.keys(perangkatDict);
    if (deviceKeys.length === 0) {
        deviceSelectEl.innerHTML = '<option value="">Tidak ada perangkat</option>';
        return;
    }

    const previousSelected = deviceSelectEl.value || currentDeviceId;
    deviceSelectEl.innerHTML = '';

    deviceKeys.forEach((devId) => {
        const item = perangkatDict[devId] || {};
        const label = item.nama_kendaraan ? `${devId} - ${item.nama_kendaraan}` : devId;
        
        const opt = document.createElement('option');
        opt.value = devId;
        opt.textContent = label;
        deviceSelectEl.appendChild(opt);
    });

    if (previousSelected && deviceKeys.includes(previousSelected)) {
        deviceSelectEl.value = previousSelected;
        currentDeviceId = previousSelected;
    } else {
        deviceSelectEl.value = deviceKeys[0];
        currentDeviceId = deviceKeys[0];
    }
}

// --- 5. FIREBASE REALTIME DATABASE LISTENER (ROOT GPS_TRACKING) ---
const rootGpsRef = ref(db, 'GPS_TRACKING');

onValue(rootGpsRef, (snapshot) => {
    const rootData = snapshot.val();
    if (!rootData) return;

    globalPerangkatData = rootData.PERANGKAT || {};
    globalGeofenceData = rootData.GEOFENCE || {};

    // Populate opsi dropdown berdasarkan node PERANGKAT
    populateDeviceDropdown(globalPerangkatData);

    // Update UI berdasarkan perangkat terpilih
    updateDashboardUI();
});

// --- 6. FUNGSI UPDATE DASHBOARD UI UTAMA ---
function updateDashboardUI() {
    if (!currentDeviceId || !globalPerangkatData[currentDeviceId]) return;

    const deviceData = globalPerangkatData[currentDeviceId] || {};
    const geofenceData = globalGeofenceData[currentDeviceId] || {};

    // 1. Ambil Titik Pusat Geofence dari GPS_TRACKING/GEOFENCE/{currentDeviceId}
    //    Jika key di database berupa latitude/longitude atau lat/lng/lokasi_aman_lat
    const centerLat = parseFloat(geofenceData.latitude ?? geofenceData.lat ?? geofenceData.lokasi_aman_lat ?? deviceData.lokasi_aman_lat ?? initialLat);
    const centerLng = parseFloat(geofenceData.longitude ?? geofenceData.lng ?? geofenceData.lokasi_aman_lng ?? deviceData.lokasi_aman_lng ?? initialLng);
    const currentRadius = parseInt(geofenceData.radius ?? geofenceData.radius_geofence ?? deviceData.radius_geofence ?? defaultRadius);

    // 2. Ambil Posisi Kendaraan dari GPS_TRACKING/PERANGKAT/{currentDeviceId}
    const lat = parseFloat(deviceData.lat ?? centerLat);
    const lng = parseFloat(deviceData.lng ?? centerLng);
    const jarak = Math.round(deviceData.jarak || 0);
    const speed = Math.round(deviceData.kecepatan || 0);
    const satelit = deviceData.satelit || 0;
    const battery = deviceData.baterai || 80;
    const isOut = jarak > currentRadius;

    // Update Top Metric Cards
    if (document.getElementById("lat")) document.getElementById("lat").innerText = lat.toFixed(6);
    if (document.getElementById("lng")) document.getElementById("lng").innerText = lng.toFixed(6);
    if (document.getElementById("jarak")) document.getElementById("jarak").innerText = jarak;
    if (document.getElementById("speed")) document.getElementById("speed").innerText = speed;
    if (document.getElementById("satelit")) document.getElementById("satelit").innerText = satelit;

    // Update Status Area Card
    const statusBadge = document.getElementById("status-badge");
    const statusIconBg = document.getElementById("status-icon-bg");
    const statusIcon = document.getElementById("status-icon");
    const statusDesc = document.getElementById("status-desc");

    if (isOut) {
        if (statusBadge) { statusBadge.className = "inline-block px-2 py-0.5 text-xs font-bold rounded bg-red-100 text-red-700 mt-0.5"; statusBadge.innerText = "LUAR AREA"; }
        if (statusIconBg) statusIconBg.className = "w-12 h-12 bg-red-50 text-red-600 rounded-xl flex items-center justify-center text-xl shrink-0";
        if (statusIcon) statusIcon.className = "fas fa-triangle-exclamation";
        if (statusDesc) statusDesc.innerText = "Bahaya! Keluar Geofence";
    } else {
        if (statusBadge) { statusBadge.className = "inline-block px-2 py-0.5 text-xs font-bold rounded bg-emerald-100 text-emerald-700 mt-0.5"; statusBadge.innerText = "DALAM AREA"; }
        if (statusIconBg) statusIconBg.className = "w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl shrink-0";
        if (statusIcon) statusIcon.className = "fas fa-shield-check";
        if (statusDesc) statusDesc.innerText = "Kendaraan Aman";
    }

    // Update Panel Informasi Kendaraan Sisi Kanan
    if (document.getElementById("info-jarak")) document.getElementById("info-jarak").innerText = jarak;
    if (document.getElementById("info-radius")) document.getElementById("info-radius").innerText = currentRadius;
    if (document.getElementById("legend-radius")) document.getElementById("legend-radius").innerText = currentRadius;
    if (document.getElementById("battery-bar")) document.getElementById("battery-bar").style.width = `${battery}%`;
    if (document.getElementById("battery-text")) document.getElementById("battery-text").innerText = `${battery}%`;
    
    const nowTimeStr = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    if (document.getElementById("info-last-time")) document.getElementById("info-last-time").innerText = nowTimeStr;

    const infoStatusBadge = document.getElementById("info-status-badge");
    if (infoStatusBadge) {
        infoStatusBadge.className = isOut ? "px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700" : "px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700";
        infoStatusBadge.innerText = isOut ? "LUAR AREA" : "DALAM AREA";
    }

    // Update Panel Keamanan (Relay & Alarm)
    const relayBadge = document.getElementById("relay-badge");
    const relayActive = deviceData.relay === "ON" || deviceData.relay === 1 || !isOut;
    if (relayBadge) {
        relayBadge.className = relayActive ? "px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700" : "px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700";
        relayBadge.innerText = relayActive ? "AKTIF" : "NONAKTIF";
    }

    const alarmBadge = document.getElementById("alarm-badge");
    if (alarmBadge) {
        alarmBadge.className = isOut ? "px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700" : "px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600";
        alarmBadge.innerText = isOut ? "AKTIF" : "NONAKTIF";
    }

    // --- UPDATE ELEMEN PETA LEAFLET BERDASARKAN NODE GEOFENCE PERANGKAT ---
    centerMarker.setLatLng([centerLat, centerLng]);
    vehicleMarker.setLatLng([lat, lng]);
    geofenceCircle.setLatLng([centerLat, centerLng]);
    geofenceCircle.setRadius(currentRadius);
    distanceLine.setLatLngs([[centerLat, centerLng], [lat, lng]]);
    
    // Peta otomatis berpusat ke lokasi aman (center geofence) perangkat yang dipilih
    map.setView([centerLat, centerLng], 16);

    // Update Chart.js Data Points Real-Time
    if (geofenceChart.data.labels.length > 7) {
        geofenceChart.data.labels.shift();
        geofenceChart.data.datasets[0].data.shift();
        geofenceChart.data.datasets[1].data.shift();
    }
    geofenceChart.data.labels.push(nowTimeStr);
    geofenceChart.data.datasets[0].data.push(jarak);
    geofenceChart.data.datasets[1].data.push(currentRadius);
    geofenceChart.update();

    // Update Tabel Riwayat Mini
    historyLog.unshift({
        waktu: nowTimeStr,
        lat: lat.toFixed(5),
        lng: lng.toFixed(5),
        jarak: jarak
    });
    if (historyLog.length > 5) historyLog.pop();

    const tbody = document.getElementById("history-table-body");
    if (tbody) {
        tbody.innerHTML = historyLog.map(row => `
            <tr class="hover:bg-gray-50">
                <td class="py-2 text-gray-500 font-medium">${row.waktu}</td>
                <td class="py-2 text-gray-700">${row.lat}</td>
                <td class="py-2 text-gray-700">${row.lng}</td>
                <td class="py-2 text-right font-bold text-gray-800">${row.jarak} m</td>
            </tr>
        `).join('');
    }

    // Update Notifikasi Terbaru List
    const notifContainer = document.getElementById("recent-notifications-list");
    if (notifContainer) {
        if (isOut) {
            notifContainer.innerHTML = `
                <div class="flex items-start gap-3 p-2 bg-red-50 rounded-lg border border-red-100">
                    <span class="w-2.5 h-2.5 bg-red-500 rounded-full mt-1.5 shrink-0 animate-ping"></span>
                    <div>
                        <p class="text-xs font-semibold text-red-700">Perangkat ${currentDeviceId} keluar geofence (${jarak}m)!</p>
                        <span class="text-[10px] text-red-400">${nowTimeStr} - Peringatan Terkirim</span>
                    </div>
                </div>
            `;
        } else {
            notifContainer.innerHTML = `
                <div class="flex items-start gap-3 p-2 bg-emerald-50 rounded-lg border border-emerald-100">
                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full mt-1.5 shrink-0"></span>
                    <div>
                        <p class="text-xs font-semibold text-emerald-700">Perangkat ${currentDeviceId} berada dalam area aman (${jarak}m)</p>
                        <span class="text-[10px] text-emerald-500">${nowTimeStr} - Sistem Normal</span>
                    </div>
                </div>
            `;
        }
    }
}
</script>

</body>
</html>