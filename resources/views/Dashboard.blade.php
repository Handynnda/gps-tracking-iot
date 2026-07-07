<!-- {{-- <!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard GPS Tracking Sepeda Motor</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Penyesuaian agar map tidak menutupi elemen lain */
        #map {
            z-index: 10;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">

    <nav class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center gap-2">
                        <i class="fa-solid fa-motorcycle text-blue-600 text-2xl"></i>
                        <span class="font-bold text-xl text-gray-800 tracking-tight">IoT GPS Tracking</span>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <a href="#" class="text-gray-500 hover:text-gray-700 font-medium text-sm flex items-center gap-2 transition duration-150">
                        <i class="fa-solid fa-user-circle text-lg"></i>
                        Profil Saya
                    </a>
                    <button class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-4 py-2 rounded-md text-sm font-semibold transition duration-150 flex items-center gap-2">
                        <i class="fa-solid fa-sign-out-alt"></i>
                        Keluar
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Monitoring</h1>
                <p class="text-sm text-gray-500">Pemantauan lokasi dan keamanan secara real-time</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                        <i class="fa-solid fa-motorcycle fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Kendaraan</p>
                        <h3 class="text-lg font-bold text-gray-800" id="kendaraan">Yamaha Jupiter MX 135</h3>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div class="p-3 rounded-full bg-green-50 text-green-600">
                        <i class="fa-solid fa-shield-check fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Status Sistem</p>
                        <h3 class="text-lg font-bold text-gray-800" id="status">Aktif</h3>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div class="p-3 rounded-full bg-purple-50 text-purple-600">
                        <i class="fa-solid fa-route fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Jarak Geofence</p>
                        <h3 class="text-lg font-bold text-gray-800"><span id="jarak">0</span> <span class="text-sm font-medium text-gray-500">meter</span></h3>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div class="p-3 rounded-full bg-orange-50 text-orange-600" id="relay-icon-bg">
                        <i class="fa-solid fa-power-off fa-lg" id="relay-icon"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Engine Relay</p>
                        <h3 class="text-lg font-bold text-gray-800" id="relay">-</h3>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div class="p-3 rounded-full bg-indigo-50 text-indigo-600">
                        <i class="fa-solid fa-satellite fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Satelit GPS</p>
                        <h3 class="text-lg font-bold text-gray-800" id="satelit">0</h3>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div class="p-3 rounded-full bg-gray-50 text-gray-600">
                        <i class="fa-solid fa-map-pin fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Latitude</p>
                        <h3 class="text-sm font-bold text-gray-800" id="lat">0</h3>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div class="p-3 rounded-full bg-gray-50 text-gray-600">
                        <i class="fa-solid fa-map-pin fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Longitude</p>
                        <h3 class="text-sm font-bold text-gray-800" id="lng">0</h3>
                    </div>
                </div>

            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="flex justify-between items-center mb-4 px-2">
                    <h2 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-map-location-dot mr-2 text-blue-500"></i> Peta Real-time</h2>
                    <span class="flex items-center text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full border border-green-200">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span> Live Sync
                    </span>
                </div>
                <div id="map" class="w-full h-[500px] rounded-lg border border-gray-200"></div>
            </div>

        </div>
    </main>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
        import { getDatabase, ref, onValue } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

        const firebaseConfig = {
            apiKey: "API_KEY_ANDA",
            databaseURL: "https://gps-tracking-6ba6f-default-rtdb.asia-southeast1.firebasedatabase.app",
            projectId: "gps-tracking-6ba6f",
        };

        const app = initializeApp(firebaseConfig);
        const db = getDatabase(app);

        // Inisialisasi Peta
        const map = L.map('map').setView([-6.862989, 108.584027], 15);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
        let marker = L.marker([-6.862989, 108.584027]).addTo(map);

        const gpsRef = ref(db, 'gps');

        onValue(gpsRef, (snapshot) => {
            const data = snapshot.val();
            if(!data) return;

            // Update Teks DOM
            document.getElementById("lat").innerText = data.lat ?? 0;
            document.getElementById("lng").innerText = data.lng ?? 0;
            document.getElementById("jarak").innerText = data.jarak ?? 0;
            document.getElementById("satelit").innerText = data.satelit ?? 0;
            
            // Logika UI Relay
            let relayStatus = data.jarak > 500 ? "OFF (Cut-off)" : "ON";
            let relayEl = document.getElementById("relay");
            let relayIconBg = document.getElementById("relay-icon-bg");
            let relayIcon = document.getElementById("relay-icon");

            relayEl.innerText = relayStatus;

            // Mempercantik warna relay berdasarkan status
            if (data.jarak > 500) {
                relayEl.classList.remove("text-gray-800");
                relayEl.classList.add("text-red-600");
                relayIconBg.className = "p-3 rounded-full bg-red-50 text-red-600";
            } else {
                relayEl.classList.remove("text-red-600");
                relayEl.classList.add("text-gray-800");
                relayIconBg.className = "p-3 rounded-full bg-green-50 text-green-600";
            }

            // Update Peta
            marker.setLatLng([data.lat, data.lng]);
            map.panTo([data.lat, data.lng]);
        });
    </script>
</body>
</html> --}} -->

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPS Tracking Dashboard</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

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

        <li>
            <a href="{{ route('register') }}">
                Daftarkan User
            </a>
        </li>

        <li>
            <a href="{{ route('lokasi.aman') }}">
                Ubah Koordinat
            </a>
        </li>

        <li>
            <a href="{{ route('profile.edit') }}">
                Profile
            </a>
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

    <div class="topbar">
        <div>
            <h1>Dashboard Monitoring</h1>
            <p>Monitoring Kendaraan Real Time</p>
        </div>

        <div class="profile-box">
            <i class="fas fa-user-circle"></i>
            {{ session('user_data')['name'] ?? 'Pengguna' }}        
        </div>
    </div>

    <!-- CARD -->
    <div class="cards">

        <div class="card">
            <div class="icon green">
                <i class="fas fa-shield-halved"></i>
            </div>

            <div>
                <small>STATUS</small>
                <h3 id="status">AKTIF</h3>
            </div>
        </div>

        <div class="card">
            <div class="icon orange">
                <i class="fas fa-route"></i>
            </div>

            <div>
                <small>JARAK</small>
                <h3><span id="jarak">0</span> m</h3>
            </div>
        </div>

        <div class="card">
            <div id="relay-icon-bg" class="icon red">
                <i id="relay-icon" class="fas fa-power-off"></i>
            </div>

            <div>
                <small>RELAY</small>
                <h3 id="relay">-</h3>
            </div>
        </div>

        <div class="card">
            <div class="icon purple">
                <i class="fas fa-satellite"></i>
            </div>

            <div>
                <small>SATELIT</small>
                <h3 id="satelit">0</h3>
            </div>
        </div>

        <div class="card">
            <div class="icon gray">
                <i class="fas fa-location-dot"></i>
            </div>

            <div>
                <small>LATITUDE</small>
                <h3 id="lat">0</h3>
            </div>
        </div>

        <div class="card">
            <div class="icon gray">
                <i class="fas fa-location-dot"></i>
            </div>

            <div>
                <small>LONGITUDE</small>
                <h3 id="lng">0</h3>
            </div>
        </div>

    </div>

    <!-- MAP -->
    <div class="map-container">

        <div class="map-header">

            <h2>
                <i class="fas fa-map-location-dot"></i>
                Lokasi Kendaraan
            </h2>

            <span class="live-badge">
                LIVE
            </span>

        </div>

        <div id="map"></div>

    </div>

</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script type="module">

import { initializeApp }
from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";

import {
    getDatabase,
    ref,
    onValue
}
from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

const firebaseConfig = {

    apiKey: "ISI_API_KEY",

    databaseURL:
    "https://gps-tracking-6ba6f-default-rtdb.asia-southeast1.firebasedatabase.app",

    projectId: "gps-tracking-6ba6f",

};

const app = initializeApp(firebaseConfig);
const db = getDatabase(app);

const map = L.map('map').setView([-6.862989,108.584027],15);

L.tileLayer(
'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
{
maxZoom:19
}
).addTo(map);

let marker = L.marker([
-6.862989,
108.584027
]).addTo(map);

const gpsRef = ref(db, 'GPS_TRACKING/GPS001');

onValue(gpsRef,(snapshot)=>{

const data = snapshot.val();

if(!data) return;

document.getElementById("lat").innerText =
data.lat ?? 0;

document.getElementById("lng").innerText =
data.lng ?? 0;

document.getElementById("jarak").innerText =
Math.round(data.jarak ?? 0);

document.getElementById("satelit").innerText =
data.satelit ?? 0;

document.getElementById("status").innerText =
data.status ?? "-";

let relayStatus =
data.jarak > 500 ? "OFF" : "ON";

document.getElementById("relay")
.innerText = relayStatus;

let relayIconBg =
document.getElementById("relay-icon-bg");

if(data.jarak > 500){

relayIconBg.className =
"icon red";

}else{

relayIconBg.className =
"icon green";

}

marker.setLatLng([
data.lat,
data.lng
]);

map.panTo([
data.lat,
data.lng
]);

});

</script>

</body>
</html>