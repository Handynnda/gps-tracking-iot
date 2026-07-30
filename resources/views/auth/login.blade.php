<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GPS Tracking</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-900 via-slate-900 to-black">

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

            <div class="mt-12">
                <i class="fa-solid fa-motorcycle text-cyan-400 text-8xl"></i>
            </div>

        </div>

    </div>

    <!-- KANAN -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6">

        <div class="w-full max-w-md glass-card rounded-3xl p-8">

            <div class="text-center mb-8">

                <div class="mb-4">
                    <i class="fa-solid fa-shield-halved text-cyan-400 text-5xl"></i>
                </div>

                <h2 class="text-3xl font-bold text-white">
                    Selamat Datang
                </h2>

                <p class="text-gray-300 mt-2">
                    Login untuk mengakses Dashboard GPS Tracking
                </p>

            </div>

            @if(session('status'))
                <div class="mb-4 p-3 rounded-xl bg-green-500/20 text-green-300 border border-green-400">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- EMAIL -->
                <div class="mb-5">

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
                            id="email-input"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="form-input"
                            placeholder="email@gmail.com"
                            autocomplete="off">

                    </div>
                    
                    <!-- DATALIST UNTUK DROPDOWN RIWAYAT EMAIL -->
                    <datalist id="email-options">
                        @if(isset($savedEmails))
                            @foreach($savedEmails as $savedEmail)
                                <option value="{{ $savedEmail }}"></option>
                            @endforeach
                        @endif
                    </datalist>

                    @error('email')
                        <p class="text-red-300 text-sm mt-2">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <!-- PASSWORD -->
                <div class="mb-5">

                    <label class="text-gray-200 text-sm">
                        Password
                    </label>

                    <div class="relative mt-2">

                        <span class="absolute left-4 top-3 text-gray-300">
                            <i class="fa-solid fa-lock"></i>
                        </span>

                        <input
                            type="password"
                            name="password"
                            required
                            class="form-input"
                            placeholder="••••••••">

                    </div>

                    @error('password')
                        <p class="text-red-300 text-sm mt-2">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <!-- REMEMBER & LUPA PASSWORD -->
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center gap-2 text-sm text-gray-300">
                        <input type="checkbox" name="remember" class="rounded border-gray-500">
                        Remember Me
                    </label>

                    <a href="{{ route('password.request') }}" class="text-cyan-400 hover:text-cyan-300 text-sm transition-colors">
                        Lupa Password?
                    </a>
                </div>

                <!-- BUTTON LOGIN -->
                <button
                    type="submit"
                    class="login-btn">

                    <i class="fa-solid mr-2"></i>
                    Login

                </button>

            </form>

        </div>

    </div>

</div>

<!-- SCRIPT datalist -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.getElementById('email-input');

        if(emailInput) {
            emailInput.addEventListener('input', function() {
                if (this.value.length > 0) {
                    this.setAttribute('list', 'email-options');
                } else {
                    this.removeAttribute('list');
                }
            });
        }
    });
</script>

</body>
</html>