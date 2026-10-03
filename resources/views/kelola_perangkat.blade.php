<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Perangkat - GPS Tracking</title>

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

<!-- KONTEN UTAMA KELOLA PERANGKAT -->
<div class="main-content">
<div class="p-6 space-y-6">
    
    <!-- HEADER KONTEN -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Kelola Perangkat GPS</h1>
            <p class="text-sm text-gray-500">Manajemen unit modul ESP32, label kendaraan, dan radius geofencing</p>
        </div>
        <div>
            <button type="button" onclick="openModalTambah()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition duration-200 shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Tambah Perangkat Baru
            </button>
        </div>
    </div>

    <!-- ALERT NOTIFIKASI STATUS -->
    @if(session('status'))
        <div class="p-4 rounded-xl bg-green-50 text-green-700 border border-green-200 text-sm flex items-center gap-2">
            <i class="fas fa-check-circle text-lg"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-red-50 text-red-700 border border-red-200 text-sm flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-lg"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- TABEL DAFTAR PERANGKAT -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="p-4">ID Perangkat</th>
                        <th class="p-4">Nama Kendaraan</th>
                        <th class="p-4">Plat Nomor</th>
                        <th class="p-4">Radius Geofence</th>
                        <th class="p-4">Status Sesi</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @if(!empty($perangkatList) && count($perangkatList) > 0)
                        @foreach($perangkatList as $item)
                            @php
                                $isCurrentActive = isset($item['id_perangkat']) && ($item['id_perangkat'] === $activeId);
                            @endphp
                            <tr class="hover:bg-gray-50/80 transition {{ $isCurrentActive ? 'bg-blue-50/30' : '' }}">
                                <td class="p-4 font-bold text-gray-900 font-mono">
                                    {{ $item['id_perangkat'] ?? '-' }}
                                </td>
                                <td class="p-4 font-medium">
                                    {{ $item['nama_kendaraan'] ?? 'Belum Diatur' }}
                                </td>
                                <td class="p-4">
                                    <span class="bg-gray-100 text-gray-800 text-xs font-mono px-2.5 py-1 rounded-md border border-gray-200 font-bold">
                                        {{ $item['plat_nomor'] ?? '-' }}
                                    </span>
                                </td>
                                <td class="p-4 font-medium">
                                    {{ $item['radius_geofencing'] ?? 100 }} Meter
                                </td>
                                <td class="p-4">
                                    @if($isCurrentActive)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
                                            <i class="fa-solid fa-circle text-[6px]"></i> Aktif Dipilih
                                        </span>
                                    @else
                                        <form action="{{ route('perangkat.setActive') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="id_perangkat" value="{{ $item['id_perangkat'] ?? '' }}">
                                            <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 hover:underline font-semibold">
                                                Pilih Perangkat Ini
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center justify-center gap-1">
                                        <!-- Edit -->
                                        <button type="button" 
                                                onclick="openModalEdit('{{ addslashes($item['id_perangkat'] ?? '') }}', '{{ addslashes($item['nama_kendaraan'] ?? '') }}', '{{ addslashes($item['plat_nomor'] ?? '') }}', '{{ $item['radius_geofencing'] ?? 100 }}')" 
                                                class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit Perangkat">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <!-- Hapus -->
                                        <form action="{{ route('perangkat.destroy', $item['id_perangkat'] ?? '') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus perangkat ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="Hapus Perangkat">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400">
                                Belum ada perangkat yang terdaftar di Firebase.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL TAMBAH PERANGKAT -->
<div id="modalTambahPerangkat" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-md w-full p-6 relative transition-all transform scale-95 opacity-0 duration-200" id="cardTambah">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-microchip text-blue-600"></i> Registrasi Perangkat Baru
            </h3>
            <button type="button" onclick="closeModalTambah()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg transition">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <form action="{{ route('perangkat.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    ID Perangkat / Seri ESP32 <span class="text-red-500">*</span>
                </label>
                <input type="text" name="id_perangkat" placeholder="Contoh: GPS001 atau Perangkat_01" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Nama / Label Kendaraan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama_kendaraan" placeholder="Contoh: Jupiter MX" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Plat Nomor Kendaraan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="plat_nomor" placeholder="Contoh: E 1234 ABC" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm uppercase focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Radius Geofencing (Meter) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="radius_geofencing" value="100" min="10" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeModalTambah()" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition">Simpan Perangkat</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT PERANGKAT -->
<div id="modalEditPerangkat" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-md w-full p-6 relative transition-all transform scale-95 opacity-0 duration-200" id="cardEdit">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-blue-600"></i> Edit Data Perangkat
            </h3>
            <button type="button" onclick="closeModalEdit()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg transition">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <form id="formEditPerangkat" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">ID Perangkat (Fixed)</label>
                <input type="text" id="edit_id_display" disabled class="w-full px-3 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-500 font-bold">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Nama Kendaraan</label>
                <input type="text" name="nama_kendaraan" id="edit_nama_kendaraan" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Plat Nomor</label>
                <input type="text" name="plat_nomor" id="edit_plat_nomor" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm uppercase focus:ring-2 focus:ring-blue-500 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Radius Geofencing (Fixed)</label>
                <input type="number" name="radius_geofencing" id="edit_radius_geofencing" readonly class="w-full px-3 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-500 font-bold cursor-not-allowed">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeModalEdit()" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition">Perbarui Data</button>
            </div>
        </form>
    </div>
</div>

</div>

<script>
    function openModalTambah() {
        const modal = document.getElementById('modalTambahPerangkat');
        const card = document.getElementById('cardTambah');
        modal.classList.remove('hidden');
        modal.classList.add('flex'); // Tambahkan flex saat dibuka
        setTimeout(() => {
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeModalTambah() {
        const modal = document.getElementById('modalTambahPerangkat');
        const card = document.getElementById('cardTambah');
        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');
        setTimeout(() => { 
            modal.classList.add('hidden'); 
            modal.classList.remove('flex');
        }, 200);
    }

    function openModalEdit(id, nama, plat, radius) {
        const modal = document.getElementById('modalEditPerangkat');
        const card = document.getElementById('cardEdit');
        const form = document.getElementById('formEditPerangkat');

        form.action = `/kelola-perangkat/${id}`;
        document.getElementById('edit_id_display').value = id;
        document.getElementById('edit_nama_kendaraan').value = nama;
        document.getElementById('edit_plat_nomor').value = plat;
        document.getElementById('edit_radius_geofencing').value = radius;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeModalEdit() {
        const modal = document.getElementById('modalEditPerangkat');
        const card = document.getElementById('cardEdit');
        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');
        setTimeout(() => { 
            modal.classList.add('hidden'); 
            modal.classList.remove('flex');
        }, 200);
    }
</script>


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