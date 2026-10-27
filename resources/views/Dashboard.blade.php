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
        <div class="logo-brand">
            <i class="fas fa-car"></i>
            <span>GPS Tracking</span>
        </div>
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
            $isOwner = session('user_data') && (
                (session('user_data')['email'] ?? '') === 'handynandaf@gmail.com' || 
                strtolower(session('user_data')['role'] ?? '') === 'owner'
            );
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
            <a href="{{ route('lokasi.aman') }}">
                <span>Ubah Koordinat</span>
            </a>
        </li>

        @if($isOwner)
            <li>
                <a href="{{ route('perangkat.index') }}">
                    <span>Kelola Perangkat</span>
                </a>
            </li>

            <li>
                <a href="{{ route('register') }}">
                    <span>Daftarkan User</span>
                </a>
            </li>
        @endif
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

    <!-- Flash Message Alert -->
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-100 text-red-700 border border-red-300 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-xl"></i>
                <span class="font-medium text-sm">{{ session('error') }}</span>
            </div>
        </div>
    @endif
    @if(session('status'))
        <div class="mb-6 p-4 rounded-xl bg-green-100 text-green-700 border border-green-300 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fas fa-check-circle text-xl"></i>
                <span class="font-medium text-sm">{{ session('status') }}</span>
            </div>
        </div>
    @endif

    <!-- TOPBAR / HEADER -->
    <div class="bg-white rounded-xl p-5 shadow-sm mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">
                Sistem Keamanan dan Pelacakan Kendaraan Berbasis IoT
            </h1>
            <p class="text-sm text-gray-500 font-medium">
                Geofencing dengan Algoritma Haversine pada ESP32
            </p>
        </div>

        <div class="flex flex-col items-end gap-2">
            <div class="flex items-center gap-4">
                <div id="conn-status-container" class="flex items-center gap-2 bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-full text-xs font-semibold border border-emerald-200">
                    <span id="conn-status-dot" class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span id="conn-status">Online</span>
                </div>

                <div class="text-right">
                    <div id="realtime-clock" class="text-sm font-bold text-gray-700">--:--:--</div>
                    <div id="realtime-date" class="text-xs text-gray-500">-- --- ----</div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Info Akun & Role Badge -->

                <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-lg shadow-sm">
                    <i class="fas fa-microchip text-blue-600"></i>
                    <label for="device-select" class="text-xs font-bold text-gray-700">Pilih Perangkat:</label>
                    <select id="device-select" class="bg-white border border-gray-300 text-gray-800 text-xs rounded-md focus:ring-blue-500 focus:border-blue-500 p-1 font-semibold cursor-pointer">
                        <option value="">Memuat perangkat...</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- TOP METRIC CARDS (5 CARD) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
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

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div id="status-icon-bg" class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl shrink-0">
                <i id="status-icon" class="fas fa-shield-check"></i>
            </div>
            <div>
                <small class="text-xs text-gray-400 font-semibold block uppercase">Status</small>
                <span id="status-badge" class="inline-block px-2 py-0.5 text-xs font-bold rounded bg-emerald-100 text-emerald-700 mt-0.5">DALAM AREA</span>
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
                <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">LIVE</span>
            </div>

            <div class="relative flex-1 min-h-[380px] rounded-lg overflow-hidden border border-gray-200">
                <div id="map" class="w-full h-full min-h-[380px]"></div>
                <button id="btn-center-route"
                    class="absolute top-3 right-3 z-[1000] bg-white px-3 py-2 rounded-lg shadow-md text-xs font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-location-crosshairs text-blue-600 mr-1"></i>
                    Pusatkan Perjalanan
                </button>

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

        <!-- PANEL KANAN: TOMBOL AKSI CUT-OFF & INFORMASI KENDARAAN -->
        <div class="space-y-6">
            
            <!-- CARD KONTROL CUT OFF & NYALAKAN KEMBALI -->
            <div id="action-buttons-card" class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <h3 class="text-sm font-bold text-gray-800 pb-3 border-b border-gray-100 flex items-center gap-2">
                    <i class="fas fa-power-off text-red-500"></i> Kontrol Engine (Relay)
                </h3>

                <div class="grid grid-cols-2 gap-2 mt-3">
                    <!-- Tombol Cut-Off (Ubah Relay jadi OFF) -->
                    <form action="{{ route('relay.off') }}" method="POST"
                        onsubmit="return confirm('Yakin ingin mematikan mesin kendaraan (Cut-off)?');">
                        @csrf
                        <input type="hidden" name="id_perangkat" id="input-cutoff-id" value="GPS001">
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-3 py-2.5 rounded-lg shadow-sm transition">
                            <i class="fas fa-power-off"></i> Cut-off
                        </button>
                    </form>

                    <!-- Tombol Nyalakan Kembali (Ubah Relay jadi ON) -->
                    <form action="{{ route('relay.on') }}" method="POST"
                        onsubmit="return confirm('Yakin ingin menyalakan kembali mesin kendaraan?');">
                        @csrf
                        <input type="hidden" name="id_perangkat" id="input-resume-id" value="GPS001">
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-2.5 rounded-lg shadow-sm transition">
                            <i class="fas fa-check-circle"></i> Nyalakan
                        </button>
                    </form>
                </div>

                <!-- Tombol Tangani Notifikasi (Muncul saat luar area) -->
                <div class="mt-2" id="form-respon-wrapper" style="display: none;">
                    <form action="{{ route('notif.respon') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_perangkat" id="input-respon-id" value="GPS001">
                        {{-- <button type="submit"
                            class="w-full flex items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-3 py-2 rounded-lg shadow-sm transition">
                            <i class="fas fa-check"></i> Tangani Peringatan
                        </button> --}}
                    </form>
                </div>
            </div>

            <!-- Card Informasi Kendaraan -->
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <h3 class="text-sm font-bold text-gray-800 pb-3 border-b border-gray-100 flex items-center gap-2">
                    <i class="fas fa-car text-gray-600"></i> Informasi Kendaraan
                </h3>
                <div class="mt-3 space-y-2.5 text-xs text-gray-600">
                    <div class="flex justify-between">
                        <span class="text-gray-400">ID Perangkat</span>
                        <span id="info-id-perangkat" class="font-semibold text-gray-800">GPS001</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Nama Kendaraan</span>
                        <span id="info-nama-kendaraan" class="font-semibold text-gray-800">-</span>
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
                        <span id="info-status-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">DALAM AREA</span>
                    </div>
                </div>
            </div>

            <!-- Card Status Sistem Keamanan -->
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <h3 class="text-sm font-bold text-gray-800 pb-3 border-b border-gray-100 flex items-center gap-2">
                    <i class="fas fa-shield-halved text-gray-600"></i> Status Sistem Keamanan
                </h3>
                <div class="mt-3 space-y-2.5 text-xs text-gray-600">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Relay (Engine Cut Off)</span>
                        <span id="relay-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">AKTIF</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Notifikasi</span>
                        <span id="notif-status-text" class="font-semibold text-gray-800">Gmail & App</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Koneksi IoT</span>
                        <span id="iot-conn-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">TERHUBUNG</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Sinyal GPS</span>
                        <span id="sinyal-gps-wrapper" class="font-semibold text-gray-800 flex items-center gap-1">
                            <i class="fas fa-satellite text-blue-500"></i>
                            <span id="satelit">0</span> Satelit
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- BOTTOM SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-clock-rotate-left text-blue-600"></i> Riwayat Lokasi Terbaru
                </h3>
                <a href="{{ route('history.index') }}" class="text-xs text-blue-600 font-semibold hover:underline">Lihat Semua</a>
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
                        <tr><td colspan="4" class="py-4 text-center text-gray-400">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

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

        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-bell text-amber-500"></i> Notifikasi Terbaru
                </h3>
                <a href="{{ route('notifications.index', $idPerangkatSession) }}" class="text-xs text-blue-600 font-semibold hover:underline">Lihat Semua</a>
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
import { getDatabase, ref, onValue, off } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

const firebaseConfig = {
    apiKey: "AIzaSyDyq3zHtWrnQPaalfkwAD4_rkesBTicj0k",
    databaseURL: "https://gps-tracking-6ba6f-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "gps-tracking-6ba6f",
};

const app = initializeApp(firebaseConfig);
const db = getDatabase(app);

let currentDeviceId = "";
let historyLog = [];
let globalPerangkatData = {};
let globalGeofenceData = {};

let lastTs = 0;
let offlineTimer = null;
const OFFLINE_TIMEOUT_MS = 8000; // 8 detik (4x siklus pengiriman) tidak ada data = offline

// --- UPDATE STATUS KONEKSI ONLINE/OFFLINE ---
function updateConnectionStatus(isOnline) {
    const container = document.getElementById('conn-status-container');
    const dot       = document.getElementById('conn-status-dot');
    const text      = document.getElementById('conn-status');
    const iotBadge  = document.getElementById('iot-conn-badge');
    const sinyalEl  = document.getElementById('satelit');
    const sinyalWrapper = document.getElementById('sinyal-gps-wrapper');

    if (isOnline) {
        // --- Badge koneksi header ---
        if (container) container.className = 'flex items-center gap-2 bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-full text-xs font-semibold border border-emerald-200';
        if (dot)       dot.className       = 'w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse';
        if (text)      text.innerText      = 'Online';

        // --- Badge Koneksi IoT ---
        if (iotBadge) {
            iotBadge.className  = 'px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700';
            iotBadge.innerText  = 'TERHUBUNG';
        }

        // --- Sinyal GPS: tampil normal ---
        if (sinyalWrapper) sinyalWrapper.className = 'font-semibold text-gray-800 flex items-center gap-1';

    } else {
        // --- Badge koneksi header ---
        if (container) container.className = 'flex items-center gap-2 bg-red-50 text-red-600 px-3 py-1.5 rounded-full text-xs font-semibold border border-red-200';
        if (dot)       dot.className       = 'w-2.5 h-2.5 bg-red-500 rounded-full';
        if (text)      text.innerText      = 'Offline';

        // --- Badge Koneksi IoT ---
        if (iotBadge) {
            iotBadge.className  = 'px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700';
            iotBadge.innerText  = 'TERPUTUS';
        }

        // --- Sinyal GPS: reset ke 0 & warna abu ---
        if (sinyalEl)     sinyalEl.innerText     = '0';
        if (sinyalWrapper) sinyalWrapper.className = 'font-semibold text-gray-400 flex items-center gap-1';
    }
}


function resetOfflineTimer() {
    if (offlineTimer) clearTimeout(offlineTimer);
    updateConnectionStatus(true);
    offlineTimer = setTimeout(() => {
        updateConnectionStatus(false);
    }, OFFLINE_TIMEOUT_MS);
}


let currentHistoryRef = null;
let historyListenerDeviceId = "";
let autoCenterRoute = false;
let programmaticMapMove = false;
let routePoints = [];
let routeSegments = [];
let routeMarkers = [];

// --- CLOCK ---
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

// --- LEAFLET MAP ---
const initialLat = -6.863045;
const initialLng = 108.584259;
const defaultRadius = 50;

const map = L.map('map').setView([initialLat, initialLng], 16);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

const greenCenterIcon = L.divIcon({
    className: 'custom-pin-center',
    html: `<div style="background-color:#10b981; width:18px; height:18px; border-radius:50%; border:3px solid white; box-shadow:0 2px 5px rgba(0,0,0,0.3);"></div>`,
    iconSize: [18, 18], iconAnchor: [9, 9]
});

const redVehicleIcon = L.divIcon({
    className: 'custom-pin-vehicle',
    html: `<div style="background-color:#ef4444; color:white; width:26px; height:26px; border-radius:50%; border:2px solid white; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(0,0,0,0.4);"><i class="fas fa-motorcycle text-xs"></i></div>`,
    iconSize: [26, 26], iconAnchor: [13, 13]
});

let centerMarker = L.marker([initialLat, initialLng], { 
    icon: greenCenterIcon 
}).addTo(map);
let vehicleMarker = L.marker([initialLat, initialLng], { 
    icon: redVehicleIcon 
}).addTo(map);
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
// Rute diambil dari GPS_TRACKING/HISTORY agar tetap tersedia
// walaupun halaman Dashboard baru saja dibuka.
let routeLine = L.polyline([], {
    color: '#2563eb',
    weight: 4,
    opacity: 0.8,
    smoothFactor: 1
}).addTo(map);

function disableAutoCenterFromUser() {
    if (programmaticMapMove) return;
    autoCenterRoute = false;
}

// Begitu user mulai zoom atau menggeser peta secara manual,
// mode otomatis langsung dimatikan sampai tombol ditekan lagi.
map.on('zoomstart', disableAutoCenterFromUser);
map.on('dragstart', disableAutoCenterFromUser);

function fitRouteToMap() {
    const points = routeLine.getLatLngs();

    if (!points || points.length === 0) {
        const vehiclePosition = vehicleMarker.getLatLng();
        if (vehiclePosition) {
            programmaticMapMove = true;
            map.once('moveend', () => {
                programmaticMapMove = false;
            });
            map.setView(vehiclePosition, 17, { animate: true });
            setTimeout(() => {
                programmaticMapMove = false;
            }, 1000);
        }
        return;
    }

    if (points.length === 1) {
        programmaticMapMove = true;
        map.once('moveend', () => {
            programmaticMapMove = false;
        });
        map.setView(points[0], 17, { animate: true });
        setTimeout(() => {
            programmaticMapMove = false;
        }, 1000);
        return;
    }

    const bounds = routeLine.getBounds();

    if (!bounds.isValid()) return;

    programmaticMapMove = true;
    map.once('moveend', () => {
        programmaticMapMove = false;
    });

    map.fitBounds(bounds, {
        padding: [50, 50],
        maxZoom: 17,
        animate: true
    });

    setTimeout(() => {
        programmaticMapMove = false;
    }, 1000);
}

document.getElementById('btn-center-route').addEventListener('click', () => {
    // Tombol adalah satu-satunya cara untuk mengaktifkan
    // kembali auto-center/auto-zoom.
    autoCenterRoute = true;
    fitRouteToMap();
});

// --- CHART.JS ---
const ctx = document.getElementById('geofenceChart').getContext('2d');
const geofenceChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [
            { label: 'Jarak Kendaraan (m)', data: [], borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', borderWidth: 2, fill: true, tension: 0.3 },
            { label: `Batas Radius (${defaultRadius}m)`, data: [], borderColor: '#ef4444', borderWidth: 1.5, borderDash: [5, 5], pointRadius: 0, fill: false }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 9 } } },
            y: { beginAtZero: true, ticks: { font: { size: 9 } } }
        }
    }
});

function clearRouteMap() {
    routePoints = [];
    routeSegments = [];

    if (routeLine) {
        routeLine.setLatLngs([]);
    }

    routeMarkers.forEach((marker) => {
        if (map.hasLayer(marker)) {
            map.removeLayer(marker);
        }
    });

    routeMarkers = [];
}

function resetDashboardState() {
    historyLog = [];
    autoCenterRoute = false;
    clearRouteMap();

    geofenceChart.data.labels = [];
    geofenceChart.data.datasets[0].data = [];
    geofenceChart.data.datasets[1].data = [];
    geofenceChart.update();

    const tbody = document.getElementById("history-table-body");
    if (tbody) {
        tbody.innerHTML = `<tr><td colspan="4" class="py-2 text-center text-gray-400">Memuat data...</td></tr>`;
    }

    if (document.getElementById("info-nama-kendaraan")) {
        document.getElementById("info-nama-kendaraan").innerText = "Memuat...";
    }
}

const deviceSelectEl = document.getElementById('device-select');
if (deviceSelectEl) {
    deviceSelectEl.addEventListener('change', (e) => {
        currentDeviceId = e.target.value;
        document.getElementById('input-respon-id').value = currentDeviceId;
        document.getElementById('input-cutoff-id').value = currentDeviceId;
        document.getElementById('input-resume-id').value = currentDeviceId; // Diperbarui

        // Saat ganti perangkat, reset timer dan anggap offline dulu
        // sampai data baru dari Firebase masuk
        if (offlineTimer) clearTimeout(offlineTimer);
        updateConnectionStatus(false);

        resetDashboardState();
        setupHistoryRealtime(currentDeviceId);
        updateDashboardUI();
    });
}

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
        const devName = item.nama_kendaraan || item.nama || (devId === 'GPS001' ? 'TOYOTA AVANZA' : (devId === 'GPS002' ? 'DAIHATSU XENIA' : ''));
        const label = devName ? `${devId} - ${devName}` : devId;
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

    document.getElementById('input-respon-id').value = currentDeviceId;
    document.getElementById('input-cutoff-id').value = currentDeviceId;
    document.getElementById('input-resume-id').value = currentDeviceId; // Diperbarui

    if (currentDeviceId !== historyListenerDeviceId) {
        setupHistoryRealtime(currentDeviceId);
    }
}

function getTodayDateKey() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function renderRealtimeHistoryRoute(data) {
    clearRouteMap();

    if (!data || typeof data !== 'object') {
        return;
    }

    let allSegments = [];
    let currentSegment = [];
    let lastLat = null;
    let lastLng = null;
    const tableRows = [];
    const pointsForTable = [];

    Object.keys(data).forEach((key) => {
        const point = data[key] || {};
        const rawLat = point.lat ?? point.latitude;
        const rawLng = point.lng ?? point.longitude;

        if (rawLat === undefined || rawLng === undefined) return;

        const lat = parseFloat(rawLat);
        const lng = parseFloat(rawLng);

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

        // Pisahkan garis jika Firebase menyimpan loncatan lokasi yang terlalu jauh.
        let isJump = false;

        if (lastLat !== null && lastLng !== null) {
            const jarakTitikKm = hitungJarakAntarTitik(
                lastLat,
                lastLng,
                lat,
                lng
            );

            if (jarakTitikKm > 0.5) {
                isJump = true;
            }
        }

        if (isJump) {
            if (currentSegment.length > 0) {
                allSegments.push(currentSegment);
            }
            currentSegment = [];
        }

        currentSegment.push([lat, lng]);
        pointsForTable.push({
            waktu: point.waktu || '-',
            lat,
            lng,
            speed: Number(point.speed ?? point.kecepatan ?? 0)
        });

        lastLat = lat;
        lastLng = lng;
    });

    if (currentSegment.length > 0) {
        allSegments.push(currentSegment);
    }

    routeSegments = allSegments;
    routePoints = allSegments.flat();

    if (allSegments.length > 0) {
        routeLine.setLatLngs(allSegments);

        allSegments.forEach((segment, index) => {
            if (segment.length === 0) return;

            const titikAwal = segment[0];
            const titikAkhir = segment[segment.length - 1];

            const markerMulai = L.circleMarker(titikAwal, {
                radius: 6,
                color: 'green',
                fillColor: 'white',
                fillOpacity: 1
            }).addTo(map).bindPopup(`<b>Titik Awal</b> (Sesi ${index + 1})`);

            const markerAkhir = L.circleMarker(titikAkhir, {
                radius: 6,
                color: 'red',
                fillColor: 'white',
                fillOpacity: 1
            }).addTo(map).bindPopup(`<b>Titik Akhir</b> (Sesi ${index + 1})`);

            routeMarkers.push(markerMulai, markerAkhir);
        });
    }

    const tbody = document.getElementById('history-table-body');

    if (tbody) {
        const rows = pointsForTable.slice(-5).reverse().map((row) => `
            <tr class="hover:bg-gray-50">
                <td class="py-2 text-gray-500 font-medium">${row.waktu}</td>
                <td class="py-2 text-gray-700">${row.lat.toFixed(5)}</td>
                <td class="py-2 text-gray-700">${row.lng.toFixed(5)}</td>
                <td class="py-2 text-right font-bold text-gray-800">${Math.round(row.speed)} km/h</td>
            </tr>
        `).join('');

        tbody.innerHTML = rows || `
            <tr>
                <td colspan="4" class="py-4 text-center text-gray-400">
                    Belum ada histori perjalanan hari ini.
                </td>
            </tr>`;
    }

    // Hanya auto-fit jika user sebelumnya menekan tombol Pusatkan Perjalanan.
    // Kalau user sedang bebas zoom/drag, route tetap realtime tetapi kamera diam.
    if (autoCenterRoute && routePoints.length > 0) {
        fitRouteToMap();
    }
}

function hitungJarakAntarTitik(lat1, lng1, lat2, lng2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;

    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) *
        Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLng / 2) * Math.sin(dLng / 2);

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

function setupHistoryRealtime(deviceId) {
    if (!deviceId) return;

    if (currentHistoryRef) {
        off(currentHistoryRef);
        currentHistoryRef = null;
    }

    historyListenerDeviceId = deviceId;

    const tanggalHariIni = getTodayDateKey();
    currentHistoryRef = ref(
        db,
        `GPS_TRACKING/HISTORY/${deviceId}/${tanggalHariIni}`
    );

    onValue(currentHistoryRef, (snapshot) => {
        renderRealtimeHistoryRoute(snapshot.val());
    });
}

const rootGpsRef = ref(db, 'GPS_TRACKING');
onValue(rootGpsRef, (snapshot) => {
    const rootData = snapshot.val();
    if (!rootData) return;

    globalPerangkatData = rootData.PERANGKAT || {};
    globalGeofenceData = rootData.GEOFENCE || {};

    populateDeviceDropdown(globalPerangkatData);

    // Cek apakah perangkat yang dipilih memiliki data & timestamp yang baru
    const d = globalPerangkatData[currentDeviceId];
    if (d && d.ts && d.ts !== lastTs) {
        lastTs = d.ts;
        console.log(`[DELAY] ${currentDeviceId} | Firebase -> Dashboard: ${Date.now() - d.ts} ms`);
    }

    // Reset timer offline setiap kali Firebase mengirim update
    resetOfflineTimer();

    updateDashboardUI();
});


function updateDashboardUI() {
    if (!currentDeviceId || !globalPerangkatData[currentDeviceId]) return;

    const deviceData = globalPerangkatData[currentDeviceId] || {};
    const geofenceData = globalGeofenceData[currentDeviceId] || {};

    const centerLat = parseFloat(geofenceData.latitude ?? geofenceData.lat ?? geofenceData.lokasi_aman_lat ?? deviceData.lokasi_aman_lat ?? initialLat);
    const centerLng = parseFloat(geofenceData.longitude ?? geofenceData.lng ?? geofenceData.lokasi_aman_lng ?? deviceData.lokasi_aman_lng ?? initialLng);
    const currentRadius = parseInt(geofenceData.radius ?? geofenceData.radius_geofence ?? deviceData.radius_geofence ?? defaultRadius);

    const lat = parseFloat(deviceData.lat ?? centerLat);
    const lng = parseFloat(deviceData.lng ?? centerLng);
    const jarak = Math.round(deviceData.jarak || 0);
    const speed = Math.round(deviceData.kecepatan || 0);
    const satelit = deviceData.satelit || 0;
    const isOut = jarak > currentRadius;

    const formResponWrapper = document.getElementById("form-respon-wrapper");
    if (formResponWrapper) {
        if (isOut) {
            formResponWrapper.style.display = "block";
        } else {
            formResponWrapper.style.display = "none";
        }
    }

    if (document.getElementById("lat")) document.getElementById("lat").innerText = lat.toFixed(6);
    if (document.getElementById("lng")) document.getElementById("lng").innerText = lng.toFixed(6);
    if (document.getElementById("jarak")) document.getElementById("jarak").innerText = jarak;
    if (document.getElementById("speed")) document.getElementById("speed").innerText = speed;
    if (document.getElementById("satelit")) document.getElementById("satelit").innerText = satelit;

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

    if (document.getElementById("info-jarak")) document.getElementById("info-jarak").innerText = jarak;
    if (document.getElementById("info-radius")) document.getElementById("info-radius").innerText = currentRadius;
    if (document.getElementById("legend-radius")) document.getElementById("legend-radius").innerText = currentRadius;
    if (document.getElementById("info-id-perangkat")) document.getElementById("info-id-perangkat").innerText = currentDeviceId;
    
    const namaKendaraan = deviceData.nama_kendaraan || deviceData.nama || (currentDeviceId === 'GPS001' ? 'TOYOTA AVANZA' : (currentDeviceId === 'GPS002' ? 'DAIHATSU XENIA' : currentDeviceId));
    if (document.getElementById("info-nama-kendaraan")) document.getElementById("info-nama-kendaraan").innerText = namaKendaraan;
    
    const nowTimeStr = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    if (document.getElementById("info-last-time")) document.getElementById("info-last-time").innerText = nowTimeStr;

    const infoStatusBadge = document.getElementById("info-status-badge");
    if (infoStatusBadge) {
        infoStatusBadge.className = isOut ? "px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700" : "px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700";
        infoStatusBadge.innerText = isOut ? "LUAR AREA" : "DALAM AREA";
    }

    const relayBadge = document.getElementById("relay-badge");
    const relayActive = deviceData.relay === "ON" || deviceData.relay === 1 || !isOut;
    if (relayBadge) {
        relayBadge.className = relayActive ? "px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700" : "px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700";
        relayBadge.innerText = relayActive ? "AKTIF" : "NONAKTIF";
    }

    centerMarker.setLatLng([centerLat, centerLng]);
    vehicleMarker.setLatLng([lat, lng]);
    geofenceCircle.setLatLng([centerLat, centerLng]);
    geofenceCircle.setRadius(currentRadius);
    distanceLine.setLatLngs([[centerLat, centerLng], [lat, lng]]);


    if (geofenceChart.data.labels.length > 7) {
        geofenceChart.data.labels.shift();
        geofenceChart.data.datasets[0].data.shift();
        geofenceChart.data.datasets[1].data.shift();
    }
    geofenceChart.data.labels.push(nowTimeStr);
    geofenceChart.data.datasets[0].data.push(jarak);
    geofenceChart.data.datasets[1].data.push(currentRadius);
    geofenceChart.update();

    historyLog.unshift({ waktu: nowTimeStr, lat: lat.toFixed(5), lng: lng.toFixed(5), jarak: jarak });
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
                </div>`;
        } else {
            notifContainer.innerHTML = `
                <div class="flex items-start gap-3 p-2 bg-emerald-50 rounded-lg border border-emerald-100">
                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full mt-1.5 shrink-0"></span>
                    <div>
                        <p class="text-xs font-semibold text-emerald-700">Perangkat ${currentDeviceId} berada dalam area aman (${jarak}m)</p>
                        <span class="text-[10px] text-emerald-500">${nowTimeStr} - Sistem Normal</span>
                    </div>
                </div>`;
        }
    }
}
</script>
<script src="{{ asset('js/responsive.js') }}"></script>
</body>
</html>