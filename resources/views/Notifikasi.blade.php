<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - GPS Tracking</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
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
        
        @php
            $idPerangkatSession = session('user_data')['id_perangkat'] ?? session('id_perangkat') ?? 'GPS001';
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

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="bg-white rounded-xl p-5 shadow-sm mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Pusat Notifikasi</h1>
            <p>Riwayat peringatan geofencing seluruh kendaraan terdaftar</p>
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

    <!-- MAIN PANEL -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mt-6">
        
        <!-- HEADER BUTTONS -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 border-b border-gray-100 pb-4">
            <h2 class="text-lg font-bold text-gray-800 flex items-center">
                <i class="fas fa-bell text-blue-600 mr-2"></i> Daftar Notifikasi Masuk
            </h2>
            
            <!-- Tombol Tandai Semua Dibaca -->
            <div id="btn-read-all-container">
                @if(!empty($notifications) && count($notifications) > 0)                
                    <form action="{{ route('notifications.readAll', $idPerangkat ?? 'all') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition duration-200 shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-check-double"></i> Tandai Semua Dibaca
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- ALERT STATUS -->
        @if(session('status'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 border border-green-200 text-sm flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- DAFTAR NOTIFIKASI CONTAINER (Real-time Target) -->
        <div class="space-y-3" id="notification-list">
            @forelse($notifications ?? [] as $notif)
                @php
                    $isRead = $notif['is_read'] ?? false;
                    $type = $notif['tipe_notifikasi'] ?? $notif['type'] ?? 'info';
                    $message = $notif['pesan_notifikasi'] ?? $notif['message'] ?? 'Tidak ada pesan detail.';
                    $title = $notif['title'] ?? 'Peringatan Geofencing';
                    $pushId = $notif['id'] ?? '';
                    $devId = $notif['device_id'] ?? $idPerangkat ?? 'GPS';

                    // PARSING TANGGAL MENTAH ISO-8601 KE WIB RAPI
                    $rawWaktu = $notif['waktu_formatted'] ?? $notif['created_at'] ?? $notif['waktu'] ?? $notif['timestamp'] ?? null;
                    if ($rawWaktu && $rawWaktu !== '-') {
                        try {
                            $timestamp = \Carbon\Carbon::parse($rawWaktu)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB';
                        } catch (\Exception $e) {
                            $timestamp = $rawWaktu;
                        }
                    } else {
                        $timestamp = '-';
                    }
                @endphp

                <div class="p-4 rounded-xl border transition flex items-start justify-between gap-4 {{ $isRead ? 'bg-gray-50 border-gray-200 text-gray-500' : 'bg-blue-50/50 border-blue-200 text-gray-800 font-medium' }}">
                    
                    <div class="flex items-start gap-3">
                        <!-- ICON BY TYPE -->
                        <div class="mt-1 shrink-0">
                            @if($type === 'danger')
                                <i class="fa-solid fa-triangle-exclamation text-red-500 text-xl"></i>
                            @elseif($type === 'warning')
                                <i class="fa-solid fa-circle-exclamation text-amber-500 text-xl"></i>
                            @else
                                <i class="fa-solid fa-bell text-blue-600 text-xl"></i>
                            @endif
                        </div>

                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base font-bold {{ $isRead ? 'text-gray-700' : 'text-gray-900' }}">
                                    {{ $title }}
                                </h3>
                                <!-- BADGE ID PERANGKAT -->
                                <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded border border-blue-200 flex items-center gap-1">
                                    <i class="fas fa-microchip text-[10px]"></i> {{ $devId }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-600 mt-1 leading-relaxed">{{ $message }}</p>
                            
                            <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                                <span class="flex items-center gap-1">
                                    <i class="far fa-clock"></i>
                                    {{ $timestamp }}
                                </span>

                                @if(isset($notif['lat']) && isset($notif['lng']))
                                    <a href="https://maps.google.com/?q={{ $notif['lat'] }},{{ $notif['lng'] }}" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1 font-normal">
                                        <i class="fa-solid fa-location-dot"></i> Lihat Lokasi Peta
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="flex items-center gap-1 shrink-0">
                        @if(!$isRead)
                            <form action="{{ route('notifications.read', ['idPerangkat' => $devId, 'pushId' =>$pushId]) }}" method="POST">
                                @csrf
                                <button type="submit" title="Tandai Dibaca" class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-100/50 transition">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('notifications.destroy', ['idPerangkat' => $devId, 'pushId' =>$pushId]) }}" method="POST" onsubmit="return confirm('Hapus notifikasi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Hapus Notifikasi" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-100/50 transition">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>

                </div>
            @empty
                <div class="p-12 text-center text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                    <i class="fa-solid fa-bell-slash text-4xl mb-3 text-gray-300 block"></i>
                    Belum ada notifikasi masuk.
                </div>
            @endforelse
        </div>

    </div>

</div>

<!-- FIREBASE REALTIME LISTENER SCRIPT -->
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>

<script>
    const firebaseConfig = {
        databaseURL: "{{ env('FIREBASE_DATABASE_URL') }}"
    };
    
    if (!firebase.apps.length) {
        firebase.initializeApp(firebaseConfig);
    }

    const csrfToken = "{{ csrf_token() }}";
    const readAllUrl = "{{ route('notifications.readAll', 'all') }}";
    
    // LISTENER KE PARENT NODE: GPS_TRACKING/NOTIFIKASI_LOG (Mencakup semua perangkat)
    const dbRef = firebase.database().ref('GPS_TRACKING/NOTIFIKASI_LOG');

    function formatTimeWib(rawTime) {
        if (!rawTime || rawTime === '-') return '-';

        try {
            // new Date() otomatis mendukung format ISO "2026-10-05T03:57:55+07:00"
            const date = new Date(rawTime);
            if (isNaN(date.getTime())) return rawTime;

            return new Intl.DateTimeFormat('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: 'Asia/Jakarta'
            }).format(date) + ' WIB';
        } catch (e) {
            return rawTime;
        }
    }

    let initialLoad = true;
    dbRef.on('value', (snapshot) => {
        if (initialLoad) {
            initialLoad = false;
            return;
        }

        const dataAll = snapshot.val();
        const container = document.getElementById('notification-list');
        const btnContainer = document.getElementById('btn-read-all-container');

        if (!dataAll || Object.keys(dataAll).length === 0) {
            container.innerHTML = `
                <div class="p-12 text-center text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                    <i class="fa-solid fa-bell-slash text-4xl mb-3 text-gray-300 block"></i>
                    Belum ada notifikasi masuk.
                </div>`;
            if (btnContainer) btnContainer.innerHTML = '';
            return;
        }

        // Kumpulkan semua notifikasi dari setiap sub-node perangkat (GPS001, GPS002, dll)
        let items = [];
        Object.keys(dataAll).forEach(deviceId => {
            const deviceLogs = dataAll[deviceId];
            if (deviceLogs && typeof deviceLogs === 'object') {
                Object.keys(deviceLogs).forEach(pushId => {
                    const item = deviceLogs[pushId];
                    if (item && typeof item === 'object') {
                        items.push({
                            id: pushId,
                            device_id: deviceId,
                            ...item
                        });
                    }
                });
            }
        });

        if (items.length === 0) {
            container.innerHTML = `
                <div class="p-12 text-center text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                    <i class="fa-solid fa-bell-slash text-4xl mb-3 text-gray-300 block"></i>
                    Belum ada notifikasi masuk.
                </div>`;
            if (btnContainer) btnContainer.innerHTML = '';
            return;
        }

        // Urutkan dari waktu yang paling baru
        items.sort((a, b) => {
            const timeA = new Date(a.created_at || a.waktu || a.timestamp || 0).getTime();
            const timeB = new Date(b.created_at || b.waktu || b.timestamp || 0).getTime();
            return timeB - timeA;
        });

        // Render Ulang Tombol Tandai Semua Dibaca jika ada data
        if (btnContainer && !btnContainer.innerHTML.trim()) {
            btnContainer.innerHTML = `
                <form action="${readAllUrl}" method="POST">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition duration-200 shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-check-double"></i> Tandai Semua Dibaca
                    </button>
                </form>`;
        }

        let htmlContent = '';

        items.forEach(item => {
            const isRead = item.is_read || false;
            const type = item.tipe_notifikasi || item.type || 'info';
            const message = item.pesan_notifikasi || item.message || 'Tidak ada pesan detail.';
            const title = item.title || 'Peringatan Geofencing';
            const timestamp = formatTimeWib(item.created_at || item.waktu || '-');
            const pushId = item.id;
            const itemDeviceId = item.device_id || 'GPS';

            let iconHtml = '<i class="fa-solid fa-bell text-blue-600 text-xl"></i>';
            if (type === 'danger') {
                iconHtml = '<i class="fa-solid fa-triangle-exclamation text-red-500 text-xl"></i>';
            } else if (type === 'warning') {
                iconHtml = '<i class="fa-solid fa-circle-exclamation text-amber-500 text-xl"></i>';
            }

            let markReadBtn = '';
            if (!isRead) {
                markReadBtn = `
                    <form action="/notifikasi/${itemDeviceId}/${pushId}/read" method="POST">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <button type="submit" title="Tandai Dibaca" class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-100/50 transition">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </form>`;
            }

            let locationHtml = '';
            if (item.lat && item.lng) {
                locationHtml = `
                    <a href="https://maps.google.com/?q=${item.lat},${item.lng}" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1 font-normal">
                        <i class="fa-solid fa-location-dot"></i> Lihat Lokasi Peta
                    </a>`;
            }

            const cardStyle = isRead 
                ? 'bg-gray-50 border-gray-200 text-gray-500' 
                : 'bg-blue-50/50 border-blue-200 text-gray-800 font-medium';
            const titleStyle = isRead ? 'text-gray-700' : 'text-gray-900';

            htmlContent += `
                <div class="p-4 rounded-xl border transition flex items-start justify-between gap-4 ${cardStyle}">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 shrink-0">
                            ${iconHtml}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base font-bold ${titleStyle}">
                                    ${title}
                                </h3>
                                <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded border border-blue-200 flex items-center gap-1">
                                    <i class="fas fa-microchip text-[10px]"></i> ${itemDeviceId}
                                </span>
                            </div>

                            <p class="text-sm text-gray-600 mt-1 leading-relaxed">${message}</p>
                            
                            <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                                <span class="flex items-center gap-1">
                                    <i class="far fa-clock"></i>
                                    ${timestamp}
                                </span>
                                ${locationHtml}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1 shrink-0">
                        ${markReadBtn}
                        <form action="/notifikasi/${itemDeviceId}/${pushId}" method="POST" onsubmit="return confirm('Hapus notifikasi ini?')">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" title="Hapus Notifikasi" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-100/50 transition">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>`;
        });

        container.innerHTML = htmlContent;
    });
</script>

</body>
</html>