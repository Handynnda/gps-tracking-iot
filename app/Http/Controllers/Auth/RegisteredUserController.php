<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        // Validasi input (tanpa aturan unique ke tabel users MySQL)
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 1. Buat ID unik untuk user baru
        $userId = uniqid('user_');

        // 2. Siapkan data yang akan dikirim ke Firebase
        $userData = [
            'id' => $userId,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Wajib di-hash demi keamanan
            'role' => 'user',
            'created_at' => now()->toDateTimeString()
        ];

        // 3. Kirim data ke Firebase (Path: /GPS_TRACKING/AKUN/ID_USER)
        $firebaseUrl = env('FIREBASE_DATABASE_URL') . '/GPS_TRACKING/AKUN/' . $userId . '.json';
        Http::put($firebaseUrl, $userData);

        // 4. Buat session manual untuk menandai user sudah login
        session([
            'is_logged_in' => true,
            'user_data' => $userData
        ]);

        return redirect(route('dashboard', absolute: false));
    }
}