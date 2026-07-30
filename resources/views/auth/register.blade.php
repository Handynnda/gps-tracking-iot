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
                <i class="fas fa-right-from-bracket"></i> Logout
            </button>
        </form>
    </div>
</div>

<!-- CONTENT -->
<div class="main-content">
    <div class="topbar">
        <div>
            <h1>Manajemen User</h1>
        </div>
        <div class="profile-box">
            <i class="fas fa-user-circle"></i>
            {{ session('user_data')['name'] ?? 'Pengguna' }}        
        </div>
    </div>

    <!-- Alert Pesan Sukses -->
    @if(session('status'))
        <div class="mt-6 p-4 rounded-lg bg-green-100 text-green-700 border border-green-300 flex items-center">
            <i class="fas fa-check-circle mr-2 text-xl"></i> {{ session('status') }}
        </div>
    @endif

    <!-- BAGIAN ATAS: FORM REGISTER -->
    <div class="mt-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h2 class="text-lg font-bold text-gray-800 mb-6 border-b pb-3">
                <i class="fas fa-user-plus text-blue-600 mr-2"></i> Form Registrasi Akun
            </h2>

            <!-- Form dibuat berjejer 2 kolom untuk menghemat ruang vertikal -->
            <form method="POST" action="{{ route('register') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf

                <!-- Nama -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border border-gray-300 rounded-md p-2.5 bg-gray-50 outline-none focus:ring focus:ring-blue-200"
                           placeholder="Masukkan nama lengkap">
                    <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-500 text-sm" />
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full border border-gray-300 rounded-md p-2.5 bg-gray-50 outline-none focus:ring focus:ring-blue-200"
                           placeholder="email@domain.com">
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500 text-sm" />
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full border border-gray-300 rounded-md p-2.5 bg-gray-50 outline-none focus:ring focus:ring-blue-200"
                           placeholder="Minimal 8 karakter">
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500 text-sm" />
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full border border-gray-300 rounded-md p-2.5 bg-gray-50 outline-none focus:ring focus:ring-blue-200"
                           placeholder="Ulangi password di atas">
                </div>

                <!-- Tombol Submit (Membentang penuh di bawah form) -->
                <div class="md:col-span-2 pt-2">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-200 shadow-md">
                        <i class="fas fa-check-circle mr-2"></i> Daftarkan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- BAGIAN BAWAH: TABEL USER -->
    <div class="mt-6 bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-10">
        <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-3">
            <i class="fas fa-users text-blue-600 mr-2"></i> Daftar Akun Terdaftar
        </h2>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 rounded-t-lg">
                    <tr>
                        <th class="px-6 py-3 rounded-tl-lg">No</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3 rounded-tr-lg text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $id => $user)
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $user['name'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $user['email'] ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded border border-blue-200">
                                    {{ $user['role'] ?? 'User' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 flex justify-center gap-2">
                                <a href="#" class="text-white bg-amber-500 hover:bg-amber-600 px-3 py-1.5 rounded-md text-xs font-semibold transition-colors">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                
                                <form action="#" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user['name'] ?? 'ini' }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-white bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded-md text-xs font-semibold transition-colors">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-folder-open text-3xl mb-3 text-gray-300 block"></i>
                                Belum ada user yang terdaftar di Firebase.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>