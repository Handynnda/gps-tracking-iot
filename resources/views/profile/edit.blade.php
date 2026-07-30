<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Akun - GPS Tracking</title>

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
            <a href="{{ route('lokasi.aman') }}">
                Ubah Koordinat
            </a>
        </li>
        <li>
            <a href="{{ route('profile.edit') }}">
                Profile
            </a>
        </li>
        <li>
            @if(session('user_data') && session('user_data')['email'] === 'uzmaizzatul0906@gmail.com')
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
            <h1>Pengaturan Profil</h1>
            <p>Perbarui informasi akun dan keamanan Anda</p>
        </div>

        <div class="profile-box">
            <i class="fas fa-user-circle"></i>
            {{ session('user_data')['name'] ?? 'Pengguna' }}        
        </div>
    </div>

    <!-- FORM PROFILE -->
    <div class="mt-8 max-w-3xl bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        
        @if (session('status') === 'profile-updated')
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-md text-sm font-medium">
                <i class="fas fa-check-circle mr-2"></i> Data profil berhasil disimpan ke Firebase!
            </div>
        @endif

        <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf
            @method('patch')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user['name']) }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5" />
                @error('name') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Alamat Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user['email']) }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5" />
                @error('email') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <hr class="border-gray-200 my-6">

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru <span class="text-gray-400 font-normal">(Kosongkan jika tidak ingin mengubah)</span></label>
                <input id="password" name="password" type="password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5" />
                @error('password') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5" />
            </div>

            <div class="pt-4 flex items-center gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-lg transition duration-200 shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>