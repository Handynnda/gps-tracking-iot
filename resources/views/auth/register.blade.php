<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftarkan User - GPS Tracking</title>

    <!-- Menggunakan CSS Dashboard Utama -->
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
            <h1>Daftarkan User Baru</h1>
            <p>Berikan akses monitoring kendaraan kepada pengguna lain</p>
        </div>
        <div class="profile-box">
            <i class="fas fa-user-circle"></i>
            {{ session('user_data')['name'] ?? 'Pengguna' }}        
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        
        <!-- PANEL FORM REGISTER -->
        <div class="md:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h2 class="text-lg font-bold text-gray-800 mb-6 border-b pb-3">
                <i class="fas fa-user-plus text-blue-600 mr-2"></i> Form Registrasi Akun
            </h2>

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <!-- Nama -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Lengkap
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border border-gray-300 rounded-md p-2.5 bg-gray-50 outline-none focus:ring focus:ring-blue-200"
                           placeholder="Masukkan nama lengkap">
                    <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-500 text-sm" />
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Alamat Email
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full border border-gray-300 rounded-md p-2.5 bg-gray-50 outline-none focus:ring focus:ring-blue-200"
                           placeholder="email@domain.com">
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500 text-sm" />
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Password
                    </label>
                    <input type="password" name="password" required
                           class="w-full border border-gray-300 rounded-md p-2.5 bg-gray-50 outline-none focus:ring focus:ring-blue-200"
                           placeholder="Minimal 8 karakter">
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500 text-sm" />
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Konfirmasi Password
                    </label>
                    <input type="password" name="password_confirmation" required
                           class="w-full border border-gray-300 rounded-md p-2.5 bg-gray-50 outline-none focus:ring focus:ring-blue-200"
                           placeholder="Ulangi password di atas">
                </div>

                <!-- Tombol Submit -->
                <div class="pt-4">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-200 shadow-md">
                        <i class="fas fa-check-circle mr-2"></i> Daftarkan Sekarang
                    </button>
                </div>
            </form>
        </div>

        <!-- PANEL INFO APLIKASI -->
        <div class="md:col-span-1 bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
            <h2 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-shield-alt text-green-600 mr-2"></i> Keunggulan Sistem
            </h2>
            
            <div class="space-y-4">
                <div class="flex items-start bg-blue-50 p-3 rounded-lg border border-blue-100">
                    <i class="fas fa-clock text-blue-500 mt-1 mr-3 text-lg"></i>
                    <div>
                        <h3 class="font-bold text-sm text-gray-800">24/7 Monitoring</h3>
                        <p class="text-xs text-gray-600 mt-1">Pantau kendaraan kapan saja tanpa henti melalui database Firebase yang responsif.</p>
                    </div>
                </div>

                <div class="flex items-start bg-blue-50 p-3 rounded-lg border border-blue-100">
                    <i class="fas fa-satellite-dish text-blue-500 mt-1 mr-3 text-lg"></i>
                    <div>
                        <h3 class="font-bold text-sm text-gray-800">Live GPS Tracking</h3>
                        <p class="text-xs text-gray-600 mt-1">Lacak posisi koordinat yang akurat dan pergerakan rute secara langsung di atas peta interaktif.</p>
                    </div>
                </div>

                <div class="flex items-start bg-blue-50 p-3 rounded-lg border border-blue-100">
                    <i class="fas fa-draw-polygon text-blue-500 mt-1 mr-3 text-lg"></i>
                    <div>
                        <h3 class="font-bold text-sm text-gray-800">Geofencing Security</h3>
                        <p class="text-xs text-gray-600 mt-1">Amankan kendaraan dengan batas wilayah virtual yang memicu cut-off mesin secara otomatis.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>