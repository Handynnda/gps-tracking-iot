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
        .strava-orange { color: #fc4c02; }
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
                <i class="fas fa-right-from-bracket"></i> Logout
            </button>
        </form>
    </div>
</div>

<!-- CONTENT -->
<div class="main-content">
    <div class="topbar">
        <div>
            <h1>Histori Perjalanan</h1>
            <p>Rekam jejak rute kendaraan</p>
        </div>
        <div class="profile-box">
            <i class="fas fa-user-circle"></i>
            {{ session('user_data')['name'] ?? 'Pengguna' }}        
        </div>
    </div>

    <!-- FILTER TANGGAL -->
    <div class="mt-6 bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
            <h2 class="font-bold text-gray-800 text-lg">Pilih Tanggal:</h2>
        </div>
        <div>
            <input type="date" id="history-date" class="border border-gray-300 rounded-md p-2 shadow-sm focus:ring focus:ring-blue-200 outline-none font-semibold text-gray-700">
        </div>
    </div>

    <!-- STATS ALA STRAVA -->
    <div class="mt-4 grid grid-cols-2 gap-4 strava-stats shadow-lg">
        <div>
            <p class="text-sm text-gray-400">Total Distance (Haversine)</p>
            <h2 class="text-3xl font-bold"><span id="total-distance">0.00</span> <span class="text-lg font-normal">km</span></h2>
        </div>
        <div>
            <p class="text-sm text-gray-400">Top Speed</p>
            <h2 class="text-3xl font-bold"><span id="top-speed">0</span> <span class="text-lg font-normal">km/h</span></h2>
        </div>
    </div>

    {{-- <!-- MAP CONTAINER -->
    <div class="mt-6 bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold"><i class="fas fa-route strava-orange mr-2"></i> Peta Rute (Polyline)</h2>
        </div>
        <div id="map" class="w-full h-[400px] rounded-lg border border-gray-300 z-10"></div>
    </div>
    <!-- TABEL LOG HISTORI -->
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-10">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-800">Log Koordinat: <span id="label-tanggal-tabel" class="text-blue-600"></span></h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm">
                        <th class="p-4 border-b">Waktu</th>
                        <th class="p-4 border-b">Latitude</th>
                        <th class="p-4 border-b">Longitude</th>
                        <th class="p-4 border-b">Kecepatan</th>
                    </tr>
                </thead>
                <tbody id="history-table-body" class="text-sm text-gray-700">
                    <tr><td colspan="4" class="p-4 text-center text-gray-500">Memuat data rute...</td></tr>
                </tbody>
            </table>
        </div>
    </div> --}}

    <!--  MAP & TABEL -->
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10 items-start">
        
        <!-- MAP CONTAINER (KIRI, LEBIH BESAR: col-span-2) -->
        {{-- <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 lg:col-span-2 flex flex-col h-[550px]">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold"><i class="fas fa-route strava-orange mr-2"></i> Peta History Rute </h2>
            </div>
            <div id="map" class="w-full rounded-lg border border-gray-300 z-10 flex-grow"></div>
        </div> --}}

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 lg:col-span-2 flex flex-col h-[550px]">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold"><i class="fas fa-route strava-orange mr-2"></i> Peta Rute (Polyline)</h2>
                
                <button id="btn-center-map" class="bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-bold py-1.5 px-3 rounded-lg border border-blue-300 shadow-sm transition-all">
                    <i class="fas fa-crosshairs mr-1"></i> Pusatkan Garis
                </button>
            </div>
            <div id="map" class="w-full rounded-lg border border-gray-300 z-10 flex-grow"></div>
        </div>

        <!-- TABEL LOG HISTORI (KANAN, NGEPRESS: col-span-1) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col h-[550px]">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                <h2 class="text-lg font-bold text-gray-800">Log Koordinat: <span id="label-tanggal-tabel" class="text-blue-600"></span></h2>
            </div>
            
            <!-- Area tabel yang bisa di-scroll -->
            <div class="overflow-y-auto flex-grow p-2">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-white z-10 shadow-sm">
                        <tr class="text-gray-600 text-[11px] uppercase tracking-wider">
                            <th class="p-2 border-b">Waktu</th>
                            <th class="p-2 border-b">Lat</th>
                            <th class="p-2 border-b">Lng</th>
                            <th class="p-2 border-b">Speed</th>
                        </tr>
                    </thead>
                    <tbody id="history-table-body" class="text-xs text-gray-700">
                        <tr><td colspan="4" class="p-4 text-center text-gray-500">Memuat data rute...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
    // Mengimpor 'off' untuk memutus koneksi listener lama saat mengganti tanggal
    import { getDatabase, ref, onValue, off, query, limitToLast } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";
    const firebaseConfig = {
        // PASTIKAN MENGISI API KEY KAMU DI SINI SEBELUM MENGGUNAKAN
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
    let startMarker;
    let endMarker;
    let currentListenerRef = null; // Menyimpan referensi Firebase yang sedang aktif

    // Algoritma Haversine di Javascript untuk mengakumulasi jarak tempuh
    function hitungJarakHaversine(lat1, lon1, lat2, lon2) {
        const R = 6371; // Radius bumi dalam kilometer
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c; // Mengembalikan hasil dalam km
    }

    // Fungsi Utama: Mengambil Data Berdasarkan Tanggal yang Dipilih
    function loadHistoryByDate(tanggal) {
        const tableBody = document.getElementById("history-table-body");
        document.getElementById("label-tanggal-tabel").innerText = tanggal;

        // Reset Peta & Statistik sebelum memuat data baru
        if (routeLine) map.removeLayer(routeLine);
        if (startMarker) map.removeLayer(startMarker);
        if (endMarker) map.removeLayer(endMarker);
        document.getElementById("total-distance").innerText = "0.00";
        document.getElementById("top-speed").innerText = "0";
        tableBody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i>Mencari data dari Firebase...</td></tr>';

        // Hentikan pendengar (listener) Firebase lama agar data tidak bertumpuk
        if (currentListenerRef) {
            off(currentListenerRef);
        }

        // Tentukan path Firebase berdasarkan tanggal yang dikirim
        currentListenerRef = ref(db, `GPS_TRACKING/HISTORY/${tanggal}`);

        // kode lama yang tidak mendukung garis putus
        // onValue(currentListenerRef, (snapshot) => {
        //     const data = snapshot.val();
            
        //     if (!data) {
        //         tableBody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-red-500 font-semibold">Belum ada histori perjalanan pada tanggal ini.</td></tr>';
        //         return;
        //     }

        //     tableBody.innerHTML = '';
        //     const latlngs = [];
            
        //     let totalJarakKm = 0;
        //     let topSpeed = 0;
        //     let lastLat = null;
        //     let lastLng = null;
        //     let tableRows = [];

        //     // Membaca setiap titik data koordinat
        //     Object.keys(data).forEach((key) => {
        //         const point = data[key];
                
        //         if(point.lat && point.lng) {
        //             // Konversi ke angka desimal (Float) agar Leaflet Maps tidak error
        //             const lat = parseFloat(point.lat);
        //             const lng = parseFloat(point.lng);

        //             // 1. Masukkan ke array Polyline untuk digambar full di peta
        //             latlngs.push([lat, lng]);
                    
        //             // 2. Hitung Kecepatan Maksimal (Top Speed)
        //             const currentSpeed = point.speed || 0;
        //             if(currentSpeed > topSpeed) topSpeed = currentSpeed;

        //             // 3. Akumulasi Jarak Tempuh (Haversine)
        //             if(lastLat !== null && lastLng !== null) {
        //                 totalJarakKm += hitungJarakHaversine(lastLat, lastLng, lat, lng);
        //             }
        //             lastLat = lat;
        //             lastLng = lng;
                    
        //             // 4. Simpan baris HTML ke dalam array tableRows
        //             //tableRows.push(`
        //                 //<tr class="border-b hover:bg-gray-50">
        //                     //<td class="p-4"><span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-bold">${point.waktu || '-'}</span></td>
        //                     //<td class="p-4 font-mono text-xs">${lat}</td>
        //                     //<td class="p-4 font-mono text-xs">${lng}</td>
        //                     //<td class="p-4 font-semibold text-blue-600">${Math.round(currentSpeed)} km/h</td>
        //                 //</tr>
        //             //`);
        //             tableRows.push(`
        //                 <tr class="border-b hover:bg-gray-50">
        //                     <td class="p-2"><span class="bg-gray-100 text-gray-700 px-1 py-1 rounded text-[10px] font-bold">${point.waktu || '-'}</span></td>
        //                     <td class="p-2 font-mono text-[11px]">${lat}</td>
        //                     <td class="p-2 font-mono text-[11px]">${lng}</td>
        //                     <td class="p-2 font-semibold text-blue-600 text-[11px]">${Math.round(currentSpeed)} km/h</td>
        //                 </tr>
        //             `);
        //         }
        //     });

        //     // POTONG ARRAY TABEL: Ambil hanya 10 baris terakhir menggunakan .slice(-10)
        //     if (tableRows.length > 0) {
        //         tableBody.innerHTML = tableRows.slice(-13).join('');
        //     } else {
        //         tableBody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-gray-500">Data rute tidak valid.</td></tr>';
        //     }

        //     // Update UI Statistik (Jarak & Kecepatan)
        //     document.getElementById("total-distance").innerText = totalJarakKm.toFixed(2);
        //     document.getElementById("top-speed").innerText = Math.round(topSpeed);

        //     // Gambar Garis Polyline (Rute Full)
        //     if (latlngs.length > 0) {
        //         if (routeLine) map.removeLayer(routeLine);
        //         if (startMarker) map.removeLayer(startMarker);
        //         if (endMarker) map.removeLayer(endMarker);

        //         routeLine = L.polyline(latlngs, {
        //             color: '#fc4c02', 
        //             weight: 5,
        //             opacity: 0.8,
        //             smoothFactor: 1
        //         }).addTo(map);

        //         // logika untuk menyesuaikan zoom dan posisi peta agar seluruh rute terlihat
        //         // map.fitBounds(routeLine.getBounds(), { padding: [30, 30] });

        //         startMarker = L.circleMarker(latlngs[0], { radius: 6, color: 'green', fillColor: 'white', fillOpacity: 1 }).addTo(map).bindPopup("Titik Awal");
        //         endMarker = L.circleMarker(latlngs[latlngs.length - 1], { radius: 6, color: 'red', fillColor: 'white', fillOpacity: 1 }).addTo(map).bindPopup("Titik Terakhir");
        //     }
        // });

        // kode baru yang mendukung garis putus
        onValue(currentListenerRef, (snapshot) => {
            const data = snapshot.val();
            
            if (!data) {
                tableBody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-red-500 font-semibold">Belum ada histori perjalanan pada tanggal ini.</td></tr>';
                return;
            }

            tableBody.innerHTML = '';
            
            // KITA UBAH STRUKTUR ARRAY-NYA UNTUK MENDUKUNG GARIS PUTUS
            let allSegments = [];    // Menyimpan kumpulan garis yang putus-putus
            let currentSegment = []; // Menyimpan garis yang sedang berjalan (nyambung)
            
            let totalJarakKm = 0;
            let topSpeed = 0;
            let lastLat = null;
            let lastLng = null;
            let tableRows = [];

            // Membaca setiap titik data koordinat
            Object.keys(data).forEach((key) => {
                const point = data[key];
                
                if(point.lat && point.lng) {
                    const lat = parseFloat(point.lat);
                    const lng = parseFloat(point.lng);

                    let isJump = false; // Penanda apakah alat habis mati/lompat

                    // 1. Deteksi "Teleportasi" / Alat Mati
                    if(lastLat !== null && lastLng !== null) {
                        const jarakTitik = hitungJarakHaversine(lastLat, lastLng, lat, lng);
                        
                        // Jika titik melompat lebih dari 0.5 km (500 meter) secara tiba-tiba,
                        // asumsikan GPS sempat dimatikan dan putus garisnya!
                        if(jarakTitik > 0.5) {
                            isJump = true;
                        } else {
                            // Hanya tambahkan ke Total Jarak jika pergerakannya wajar
                            totalJarakKm += jarakTitik;
                        }
                    }

                    // 2. Jika terdeteksi lompat, simpan garis yang lama dan buat kanvas baru
                    if(isJump) {
                        if(currentSegment.length > 0) allSegments.push(currentSegment);
                        currentSegment = []; // Reset jadi kosong untuk garis baru di kampus
                    }

                    // 3. Masukkan titik ke dalam segmen garis yang sedang aktif
                    currentSegment.push([lat, lng]);
                    
                    // 4. Hitung Kecepatan Maksimal (Top Speed)
                    const currentSpeed = point.speed || 0;
                    if(currentSpeed > topSpeed) topSpeed = currentSpeed;

                    lastLat = lat;
                    lastLng = lng;
                    
                    // 5. Simpan baris HTML ke dalam array tableRows
                    tableRows.push(`
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-2"><span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm font-bold">${point.waktu || '-'}</span></td>
                            <td class="p-2 font-mono text-sm">${lat}</td>
                            <td class="p-2 font-mono text-sm">${lng}</td>
                            <td class="p-2 font-semibold text-blue-600 text-sm">${Math.round(currentSpeed)} km/h</td>
                        </tr>
                    `);
                }
            });

            // Masukkan sisa segmen garis terakhir ke dalam kumpulan
            if(currentSegment.length > 0) {
                allSegments.push(currentSegment);
            }

            // POTONG ARRAY TABEL: Ambil hanya 10 baris terakhir
            if (tableRows.length > 0) {
                tableBody.innerHTML = tableRows.slice(-10).join('');
            } else {
                tableBody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-gray-500">Data rute tidak valid.</td></tr>';
            }

            // Update UI Statistik (Jarak yang akurat & Kecepatan)
            document.getElementById("total-distance").innerText = totalJarakKm.toFixed(2);
            document.getElementById("top-speed").innerText = Math.round(topSpeed);

            // Gambar Garis Polyline (Mendukung Garis Terputus)
            if (allSegments.length > 0) {
                if (routeLine) map.removeLayer(routeLine);
                if (startMarker) map.removeLayer(startMarker);
                if (endMarker) map.removeLayer(endMarker);

                // Kehebatan Leaflet: Otomatis membaca Array di dalam Array untuk memutus garis!
                routeLine = L.polyline(allSegments, {
                    color: '#fc4c02', 
                    weight: 5,
                    opacity: 0.8,
                    smoothFactor: 1
                }).addTo(map);

                // Pasang marker di ujung-ujungnya
                const titikAwal = allSegments[0][0];
                const segmenTerakhir = allSegments[allSegments.length - 1];
                const titikAkhir = segmenTerakhir[segmenTerakhir.length - 1];

                startMarker = L.circleMarker(titikAwal, { radius: 6, color: 'green', fillColor: 'white', fillOpacity: 1 }).addTo(map).bindPopup("Titik Awal");
                endMarker = L.circleMarker(titikAkhir, { radius: 6, color: 'red', fillColor: 'white', fillOpacity: 1 }).addTo(map).bindPopup("Titik Terakhir");
            }
        });

    }

    // ========== EVENT LISTENER DATE PICKER ==========
    const dateInput = document.getElementById('history-date');

    // Code untuk mendapatkan tanggal hari ini
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    const tanggalHariIni = `${year}-${month}-${day}`;
    
    // Set default value Date Picker ke hari ini
    dateInput.value = tanggalHariIni;

    // Jalankan pemuatan data pertama kali untuk hari ini
    loadHistoryByDate(tanggalHariIni);

    // Deteksi setiap kali pengguna mengganti tanggal
    dateInput.addEventListener('change', function() {
        const selectedDate = this.value; 
        if (selectedDate) {
            loadHistoryByDate(selectedDate);
        }
    });

    // ========== EVENT LISTENER TOMBOL PUSATKAN PETA ==========
    document.getElementById('btn-center-map').addEventListener('click', function() {
        if (routeLine) {
            // Animasi peta menuju ke tengah garis rute
            map.fitBounds(routeLine.getBounds(), { padding: [30, 30], animate: true });
        } else {
            alert("Belum ada garis rute untuk dipusatkan!");
        }
    });
</script>
</body>
</html>