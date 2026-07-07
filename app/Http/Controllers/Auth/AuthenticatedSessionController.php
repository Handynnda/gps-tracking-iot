<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 1. Ambil semua data akun dari Firebase
        $firebaseUrl = env('FIREBASE_DATABASE_URL') . '/GPS_TRACKING/AKUN.json';
        $response = Http::get($firebaseUrl);
        $users = $response->json();

        // 2. Cari kecocokan email dan password
        $authenticatedUser = null;

        if ($users) {
            foreach ($users as $id => $user) {
                if ($user['email'] === $request->email && Hash::check($request->password, $user['password'])) {
                    $authenticatedUser = $user;
                    break;
                }
            }
        }

        // 3. Jika cocok, buat session. Jika gagal, kembalikan error.
        if ($authenticatedUser) {
            session([
                'is_logged_in' => true,
                'user_data' => $authenticatedUser
            ]);

            return redirect()->intended(route('dashboard', absolute: false));
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request): RedirectResponse
    {
        session()->forget(['is_logged_in', 'user_data']);
        session()->flush();
    
        return redirect()->route('login');
    }
}