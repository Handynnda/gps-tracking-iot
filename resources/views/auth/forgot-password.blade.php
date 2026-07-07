<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - GPS Tracking</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet"
          href="{{ asset('css/forgot-password.css') }}">
</head>

<body>

<div class="min-h-screen flex">

    <!-- KIRI -->
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

        </div>

    </div>

    <!-- KANAN -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6">

        <div class="glass-card w-full max-w-md rounded-3xl p-8">

            <div class="text-center mb-8">

                <h2 class="text-3xl font-bold text-white">
                    Lupa Password
                </h2>

                <p class="text-gray-300 mt-2">
                    Masukkan email Anda untuk menerima link reset password.
                </p>

            </div>

            @if (session('status'))
                <div class="info-box mb-4">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-6">

                    <label class="text-gray-200 text-sm">
                        Email
                    </label>

                    <div class="relative mt-2">

                        <span class="absolute left-4 top-3 text-gray-300">
                            <i class="fa-solid fa-envelope"></i>
                        </span>

                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="input-custom"
                            placeholder="email@gmail.com">

                    </div>

                    @error('email')
                        <p class="text-red-300 text-sm mt-2">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <button
                    type="submit"
                    class="btn-reset">

                    <i class="fa-solid mr-2"></i>
                    Kirim Link Reset Password

                </button>

                <div class="text-center mt-6">

                    <a href="{{ route('login') }}"
                       class="text-cyan-400 hover:text-cyan-300 font-semibold">

                        Kembali ke Login

                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>