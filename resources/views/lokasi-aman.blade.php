<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Lokasi Aman - GPS Tracking</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
        
        <li>
            <a href="{{ route('notifications.index') }}">
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
    <div class="topbar">
        <div>
            <h1>Pengaturan Lokasi Aman</h1>
            <p>Tentukan titik pusat parkir/garasi dan batas radius aman untuk kendaraan Anda</p>
        </div>
        <div class="profile-box">
            <i class="fas fa-user-circle"></i>
            {{ session('user_data')['name'] ?? 'Pengguna' }}        
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        
        <!-- PANEL FORM SETTINGS -->
        <div class="md:col-span-1 bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
            <h2 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-map-marker-alt text-blue-600 mr-2"></i> Form Lokasi Pusat & Radius</h2>
            
            <div class="space-y-4">
                <!-- DROPDOWN PILIH PERANGKAT -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Perangkat</label>
                    <select id="device-select" class="w-full border border-gray-300 rounded-md p-2 bg-white font-semibold text-gray-800 outline-none focus:ring focus:ring-blue-200 cursor-pointer">
                        <option value="">-- Memuat Perangkat... --</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Koordinat Pusat (Latitude)</label>
                    <input type="number" step="any" id="lat" class="w-full border border-gray-300 rounded-md p-2 bg-gray-50 outline-none focus:ring focus:ring-blue-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Koordinat Pusat (Longitude)</label>
                    <input type="number" step="any" id="lng" class="w-full border border-gray-300 rounded-md p-2 bg-gray-50 outline-none focus:ring focus:ring-blue-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Batas Radius Aman (Meter)</label>
                    <input type="number" id="radius" class="w-full border border-gray-300 rounded-md p-2 font-bold text-blue-600 outline-none focus:ring focus:ring-blue-200" value="10">
                </div>
                
                <button id="btn-simpan" class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg transition duration-200 shadow-md">
                    <i class="fas fa-save mr-2"></i> Simpan Pengaturan
                </button>
            </div>
            
            <div class="mt-4 p-3 bg-blue-50 text-blue-700 text-xs rounded-md border border-blue-100">
                <i class="fas fa-info-circle mr-1"></i> <strong>Tips:</strong> Klik atau geser pin merah di peta untuk menentukan titik pusat garasi/kantor tanpa harus mengetik koordinat manual.
            </div>
        </div>

        <!-- PANEL MAP INTERAKTIF -->
        <div class="md:col-span-2 bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div id="map" class="w-full h-[500px] rounded-lg border border-gray-300 z-10"></div>
        </div>

    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
    import { getDatabase, ref, set, get } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

    const firebaseConfig = {
        apiKey: "AIzaSyDyq3zHtWrnQPaalfkwAD4_rkesBTicj0k", 
        databaseURL: "https://gps-tracking-6ba6f-default-rtdb.asia-southeast1.firebasedatabase.app/",
        projectId: "gps-tracking-6ba6f",
    };

    const app = initializeApp(firebaseConfig);
    const db = getDatabase(app);

    // Nilai Default Awal Titik Pusat
    let initLat = -6.853103;
    let initLng = 108.577580;
    let initRadius = 10;

    // Inisialisasi Peta Leaflet
    const map = L.map('map').setView([initLat, initLng], 15);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    // Marker Titik Pusat Garasi/Rumah & Lingkaran Batas Aman
    let centerMarker = L.marker([initLat, initLng], { draggable: true }).addTo(map).bindPopup("Titik Pusat Aman (Garasi/Kantor)");
    let radiusCircle = L.circle([initLat, initLng], {
        color: '#ef4444',
        fillColor: '#ef4444',
        fillOpacity: 0.2,
        radius: initRadius
    }).addTo(map);

    // Elemen DOM
    const deviceSelect = document.getElementById('device-select');
    const latInput = document.getElementById('lat');
    const lngInput = document.getElementById('lng');
    const radiusInput = document.getElementById('radius');

    // Helper untuk memperbarui Tampilan Form & Peta
    function updateFormAndMap(lat, lng, radius) {
        initLat = parseFloat(lat) || -6.853103;
        initLng = parseFloat(lng) || 108.577580;
        initRadius = parseFloat(radius) || 10;

        latInput.value = initLat;
        lngInput.value = initLng;
        radiusInput.value = initRadius;

        const newLatLng = new L.LatLng(initLat, initLng);
        centerMarker.setLatLng(newLatLng);
        radiusCircle.setLatLng(newLatLng);
        radiusCircle.setRadius(initRadius);
        map.panTo(newLatLng);
    }

    // Fungsi membaca setting Geofence khusus dari node GPS_TRACKING/GEOFENCE/{idPerangkat}
    function loadGeofenceData(idPerangkat) {
        const geofenceRef = ref(db, `GPS_TRACKING/GEOFENCE/${idPerangkat}`);
        get(geofenceRef).then((snapshot) => {
            if (snapshot.exists()) {
                const data = snapshot.val();
                updateFormAndMap(data.latitude || data.lat, data.longitude || data.lng, data.radius);
            } else {
                // Fallback jika belum pernah diset untuk ID Perangkat ini
                updateFormAndMap(-6.853103, 108.577580, 10);
            }
        }).catch((err) => {
            console.error("Gagal mengambil data Geofence:", err);
        });
    }

    // 1. MEMUAT DAFTAR PERANGKAT DARI GPS_TRACKING/PERANGKAT SECARA ONE-TIME (get)
    // Menggunakan get() agar tidak ter-reset saat ESP32 terus menerus mengirim GPS baru
    const devicesRef = ref(db, 'GPS_TRACKING/PERANGKAT');
    get(devicesRef).then((snapshot) => {
        const devices = snapshot.val();
        deviceSelect.innerHTML = '';

        if (devices) {
            let firstKey = null;

            Object.keys(devices).forEach((key) => {
                if (!firstKey) firstKey = key;
                const dev = devices[key];
                const label = `${key} - ${dev.nama_kendaraan || 'Kendaraan'} (${dev.plat_nomor || ''})`;
                
                const option = document.createElement('option');
                option.value = key;
                option.textContent = label;
                deviceSelect.appendChild(option);
            });

            // Load data Geofence perangkat pertama saat awal masuk halaman
            if (firstKey) {
                deviceSelect.value = firstKey;
                loadGeofenceData(firstKey);
            }
        } else {
            deviceSelect.innerHTML = '<option value="">Tidak ada perangkat ditemukan</option>';
        }
    }).catch((err) => {
        console.error("Gagal memuat daftar perangkat:", err);
        deviceSelect.innerHTML = '<option value="">Gagal memuat perangkat</option>';
    });

    // 2. EVENT: SAAT PILIHAN PERANGKAT DI DROPDOWN BERUBAH
    deviceSelect.addEventListener('change', function() {
        const selectedId = this.value;
        if (selectedId) {
            loadGeofenceData(selectedId);
        }
    });

    // EVENT: Jika Marker Digeser di Peta
    centerMarker.on('dragend', function (e) {
        const coords = e.target.getLatLng();
        latInput.value = coords.lat.toFixed(6);
        lngInput.value = coords.lng.toFixed(6);
        radiusCircle.setLatLng(coords);
    });

    // EVENT: Jika Peta Diklik
    map.on('click', function(e) {
        centerMarker.setLatLng(e.latlng);
        latInput.value = e.latlng.lat.toFixed(6);
        lngInput.value = e.latlng.lng.toFixed(6);
        radiusCircle.setLatLng(e.latlng);
    });

    // EVENT: Jika Input Latitude/Longitude Diubah Manual lewat Keyboard
    latInput.addEventListener('input', function() {
        const newLat = parseFloat(latInput.value);
        const currentLng = parseFloat(lngInput.value);
        if (!isNaN(newLat) && !isNaN(currentLng)) {
            const newPos = new L.LatLng(newLat, currentLng);
            centerMarker.setLatLng(newPos);
            radiusCircle.setLatLng(newPos);
            map.panTo(newPos);
        }
    });

    lngInput.addEventListener('input', function() {
        const currentLat = parseFloat(latInput.value);
        const newLng = parseFloat(lngInput.value);
        if (!isNaN(currentLat) && !isNaN(newLng)) {
            const newPos = new L.LatLng(currentLat, newLng);
            centerMarker.setLatLng(newPos);
            radiusCircle.setLatLng(newPos);
            map.panTo(newPos);
        }
    });

    // EVENT: Jika Input Radius Diubah Manual
    radiusInput.addEventListener('input', function(e) {
        const newRad = parseFloat(e.target.value) || 0;
        radiusCircle.setRadius(newRad);
    });

    // 3. EVENT: SIMPAN KHUSUS KE NODE GEOFENCE PERANGKAT (HANYA BERJALAN KETIKA TOMBOL DIKLIK)
    document.getElementById('btn-simpan').addEventListener('click', function() {
        const selectedId = deviceSelect.value;
        if (!selectedId) {
            alert('Pilih perangkat terlebih dahulu!');
            return;
        }

        const newLat = parseFloat(latInput.value);
        const newLng = parseFloat(lngInput.value);
        const newRadius = parseFloat(radiusInput.value);

        if (isNaN(newLat) || isNaN(newLng) || isNaN(newRadius)) {
            alert('Pastikan koordinat dan radius terisi dengan benar!');
            return;
        }

        const btn = this;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
        btn.disabled = true;

        // Disimpan murni ke GPS_TRACKING/GEOFENCE/{id_perangkat}
        const targetGeofenceRef = ref(db, `GPS_TRACKING/GEOFENCE/${selectedId}`);

        set(targetGeofenceRef, {
            latitude: newLat,
            longitude: newLng,
            radius: newRadius,
            updated_at: new Date().toISOString()
        }).then(() => {
            btn.innerHTML = '<i class="fas fa-check mr-2"></i> Tersimpan!';
            btn.classList.replace('bg-blue-600', 'bg-green-600');
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-save mr-2"></i> Simpan Pengaturan';
                btn.classList.replace('bg-green-600', 'bg-blue-600');
                btn.disabled = false;
            }, 2000);
        }).catch((error) => {
            alert('Gagal menyimpan: ' + error);
            btn.innerHTML = '<i class="fas fa-save mr-2"></i> Simpan Pengaturan';
            btn.disabled = false;
        });
    });
</script>
</body>
</html>