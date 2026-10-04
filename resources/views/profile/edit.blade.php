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

    <!-- HEADER PROFIL -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 mb-6">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">

            <!-- JUDUL -->
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">
                            Pengaturan Profil
                        </h1>

                        <p class="text-sm text-gray-500 mt-0.5">
                            Perbarui informasi akun dan keamanan Anda
                        </p>
                    </div>
                </div>
            </div>

            <!-- PROFIL USER -->
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
    </div>


    <!-- FORM PROFILE -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        <!-- HEADER FORM -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">

            <div class="flex items-center gap-3">

                <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
                    <i class="fas fa-user-edit text-blue-600"></i>
                </div>

                <div>
                    <h2 class="text-base font-semibold text-gray-800">
                        Informasi Akun
                    </h2>

                    <p class="text-xs text-gray-500 mt-0.5">
                        Kelola informasi pribadi dan keamanan akun Anda
                    </p>
                </div>

            </div>

        </div>


        <!-- ISI FORM -->
        <div class="p-6 md:p-7">

            @if (session('status') === 'profile-updated')
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm font-medium flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    Data profil berhasil disimpan ke Firebase!
                </div>
            @endif


            <form method="post"
                  action="{{ route('profile.update') }}"
                  class="space-y-6">

                @csrf
                @method('patch')


                <!-- NAMA -->
                <div>

                    <label for="name"
                           class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Lengkap
                    </label>

                    <div class="relative">

                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-user text-gray-400 text-sm"></i>
                        </div>

                        <input id="name"
                               name="name"
                               type="text"
                               value="{{ old('name', $user['name']) }}"
                               required
                               class="w-full rounded-lg border border-gray-300
                                      bg-white py-2.5 pl-10 pr-3
                                      text-sm text-gray-700
                                      shadow-sm
                                      focus:border-blue-500
                                      focus:ring-2 focus:ring-blue-100
                                      outline-none transition" />

                    </div>

                    @error('name')
                        <span class="text-red-500 text-sm mt-1 block">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


                <!-- EMAIL -->
                <div>

                    <label for="email"
                           class="block text-sm font-semibold text-gray-700 mb-2">
                        Alamat Email
                    </label>

                    <div class="relative">

                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-envelope text-gray-400 text-sm"></i>
                        </div>

                        <input id="email"
                               name="email"
                               type="email"
                               value="{{ old('email', $user['email']) }}"
                               required
                               class="w-full rounded-lg border border-gray-300
                                      bg-white py-2.5 pl-10 pr-3
                                      text-sm text-gray-700
                                      shadow-sm
                                      focus:border-blue-500
                                      focus:ring-2 focus:ring-blue-100
                                      outline-none transition" />

                    </div>

                    @error('email')
                        <span class="text-red-500 text-sm mt-1 block">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


                <!-- PEMBATAS PASSWORD -->
                <div class="relative py-2">

                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>

                    <div class="relative flex justify-center">
                        <span class="bg-white px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            Keamanan Akun
                        </span>
                    </div>

                </div>


                <!-- PASSWORD BARU -->
                <div>

                    <label for="password"
                           class="block text-sm font-semibold text-gray-700 mb-2">

                        Password Baru

                        <span class="text-gray-400 font-normal text-xs">
                            (Kosongkan jika tidak ingin mengubah)
                        </span>

                    </label>

                    <div class="relative">

                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-lock text-gray-400 text-sm"></i>
                        </div>

                        <input id="password"
                               name="password"
                               type="password"
                               class="w-full rounded-lg border border-gray-300
                                      bg-white py-2.5 pl-10 pr-3
                                      text-sm text-gray-700
                                      shadow-sm
                                      focus:border-blue-500
                                      focus:ring-2 focus:ring-blue-100
                                      outline-none transition" />

                    </div>

                    @error('password')
                        <span class="text-red-500 text-sm mt-1 block">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


                <!-- KONFIRMASI PASSWORD -->
                <div>

                    <label for="password_confirmation"
                           class="block text-sm font-semibold text-gray-700 mb-2">
                        Konfirmasi Password Baru
                    </label>

                    <div class="relative">

                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-lock text-gray-400 text-sm"></i>
                        </div>

                        <input id="password_confirmation"
                               name="password_confirmation"
                               type="password"
                               class="w-full rounded-lg border border-gray-300
                                      bg-white py-2.5 pl-10 pr-3
                                      text-sm text-gray-700
                                      shadow-sm
                                      focus:border-blue-500
                                      focus:ring-2 focus:ring-blue-100
                                      outline-none transition" />

                    </div>

                </div>


                <!-- BUTTON -->
                <div class="pt-3 border-t border-gray-100 flex justify-end">

                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700
                                   text-white font-semibold
                                   py-2.5 px-6
                                   rounded-lg
                                   transition duration-200
                                   shadow-sm
                                   flex items-center gap-2">

                        <i class="fas fa-save"></i>

                        <span>Simpan Perubahan</span>

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>