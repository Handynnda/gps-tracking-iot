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
            <a href="{{ route('notifikasi.index') }}">
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
data.jarak > 50000 ? "OFF" : "ON";

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