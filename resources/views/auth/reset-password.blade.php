<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - GPS Tracking</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Memanggil CSS yang sama dengan halaman login -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-900 via-slate-900 to-black">

<div class="min-h-screen flex">

    <!-- KIRI (Layout Info Persis Halaman Login) -->
    <div class="hidden lg:flex lg:w-1/2 items-center justify-center p-10">

        <div class="text-center text-white">

            <h1 class="text-5xl font-bold mb-4">
                GPS Tracking System
            </h1>

            <p class="text-xl text-gray-300 max-w-lg mx-auto">
                Sistem monitoring kendaraan rental berbasis IoT,
                Firebase dan Geofencing Real-Time.
            </p>

            <div class="mt-10 flex justify-center gap-8">

                <div>
                    <h2 class="text-3xl font-bold text-cyan-400">24/7</h2>
                    <p class="text-gray-400">Monitoring</p>
                </div>

                <div>
                    <h2 class="text-3xl font-bold text-cyan-400">Live</h2>
                    <p class="text-gray-400">Tracking</p>
                </div>

                <div>
                    <h2 class="text-3xl font-bold text-cyan-400">GPS</h2>
                    <p class="text-gray-400">Realtime</p>
                </div>

            </div>

            <div class="mt-12">
                <i class="fa-solid fa-motorcycle text-cyan-400 text-8xl"></i>
            </div>

        </div>

    </div>

    <!-- KANAN (Area Form Reset Password) -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6">

        <div class="w-full max-w-md glass-card rounded-3xl p-8">

            <div class="text-center mb-8">
                
                <h2 class="text-3xl font-bold text-white">
                    Buat Sandi Baru
                </h2>

                <p class="text-gray-300 mt-2">
                    Silakan masukkan sandi baru untuk akun Anda
                </p>

            </div>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- TOKEN RESET PASSWORD (Wajib ada dan disembunyikan) -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- EMAIL -->
                <div class="mb-5">

                    <label class="text-gray-200 text-sm">
                        Email
                    </label>

                    <div class="relative mt-2">

                        <span class="absolute left-4 top-3 text-gray-300">
                            <i class="fa-solid fa-envelope"></i>
                        </span>

                        <!-- Dibuat readonly agar user tidak bisa mengubah email dari URL -->
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $request->email) }}"
                            required
                            readonly
                            class="form-input"
                            style="opacity: 0.6; cursor: not-allowed;"
                            title="Email tidak dapat diubah pada tahap ini">

                    </div>

                    @error('email')
                        <p class="text-red-300 text-sm mt-2">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <!-- PASSWORD BARU -->
                <div class="mb-5">

                    <label class="text-gray-200 text-sm">
                        Sandi Baru
                    </label>

                    <div class="relative mt-2">

                        <span class="absolute left-4 top-3 text-gray-300">
                            <i class="fa-solid fa-lock"></i>
                        </span>

                        <input
                            type="password"
                            name="password"
                            required
                            autofocus
                            class="form-input"
                            placeholder="Minimal 8 karakter">

                    </div>

                    @error('password')
                        <p class="text-red-300 text-sm mt-2">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <!-- KONFIRMASI PASSWORD -->
                <div class="mb-8">

                    <label class="text-gray-200 text-sm">
                        Konfirmasi Sandi
                    </label>

                    <div class="relative mt-2">

                        <span class="absolute left-4 top-3 text-gray-300">
                            <i class="fa-solid fa-check-double"></i>
                        </span>

                        <input
                            type="password"
                            name="password_confirmation"
                            required
                            class="form-input"
                            placeholder="Ketik ulang sandi baru">

                    </div>
                    
                    @error('password_confirmation')
                        <p class="text-red-300 text-sm mt-2">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <!-- BUTTON SUBMIT (Memakai class login-btn agar senada) -->
                <button
                    type="submit"
                    class="login-btn">

                    Simpan Sandi Baru

                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>