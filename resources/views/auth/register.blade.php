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
    <div class="bg-white rounded-xl p-5 shadow-sm mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Manajemen User</h1>
            <p>Daftarkan akun baru dan kelola akun yang sudah ada</p>
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

                <!-- Tombol Submit -->
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
                    @forelse($users ?? [] as $id =>$user)
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
                                <!-- Tombol Edit Memanggil Modal JS -->
                                {{-- <button type="button" 
                                        onclick="openEditModal('{{ $id }}', '{{ $user['name'] ?? '' }}', '{{$user['email'] ?? '' }}')"
                                        class="text-white bg-amber-500 hover:bg-amber-600 px-3 py-1.5 rounded-md text-xs font-semibold transition-colors flex items-center gap-1">
                                    <i class="fas fa-edit"></i> Edit
                                </button> --}}

                                <!-- Tombol Edit di Tabel -->
                                <button type="button" 
                                        onclick="openEditModal('{{ $id }}', '{{ $user['name'] ?? '' }}', '{{ $user['email'] ?? '' }}', '{{ $user['role'] ?? 'user' }}')"
                                        class="text-white bg-amber-500 hover:bg-amber-600 px-3 py-1.5 rounded-md text-xs font-semibold transition-colors flex items-center gap-1">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                
                                <!-- Form Hapus User -->
                                <form action="{{ route('users.destroy', $id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user['name'] ?? 'ini' }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-white bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded-md text-xs font-semibold transition-colors flex items-center gap-1">
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

<!-- MODAL POPUP EDIT USER -->
<div id="editModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 relative">
        <div class="flex justify-between items-center mb-4 border-b pb-3">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-user-edit text-amber-500 mr-2"></i> Edit Data User
            </h3>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
        </div>

        <form id="editUserForm" method="POST" action="">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" id="edit_name" name="name" required
                           class="w-full border border-gray-300 rounded-md p-2.5 bg-gray-50 outline-none focus:ring focus:ring-blue-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Email</label>
                    <input type="email" id="edit_email" name="email" required
                           class="w-full border border-gray-300 rounded-md p-2.5 bg-gray-50 outline-none focus:ring focus:ring-blue-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role / Hak Akses</label>
                    <select id="edit_role" name="role" required class="w-full border border-gray-300 rounded-md p-2.5 bg-gray-50 outline-none focus:ring focus:ring-blue-200">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Password Baru <span class="text-xs text-gray-400">(Kosongkan jika tidak diubah)</span>
                    </label>
                    <input type="password" name="password" placeholder="Minimal 8 karakter"
                           class="w-full border border-gray-300 rounded-md p-2.5 bg-gray-50 outline-none focus:ring focus:ring-blue-200">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeEditModal()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-amber-500 text-white rounded-lg text-sm font-semibold hover:bg-amber-600 transition-colors shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SCRIPT UNTUK MODAL -->
<script>
    function openEditModal(userId, name, email, role) {
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_role').value = role;
        
        const form = document.getElementById('editUserForm');
        form.action = `/users/${userId}`; 

        // Tampilkan modal dengan menghapus 'hidden' dan menambah 'flex'
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        // Sembunyikan modal dengan menghapus 'flex' dan menambah 'hidden'
        const modal = document.getElementById('editModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>

</body>
</html>