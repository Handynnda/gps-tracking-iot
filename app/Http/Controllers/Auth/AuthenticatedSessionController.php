<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->hasCookie('remember_firebase_user')) {
            $userData = json_decode($request->cookie('remember_firebase_user'), true);
            
            // Ambil id_perangkat atau fallback ke 'perangkat_1'
            $idPerangkat = $userData['id_perangkat'] ?? 'perangkat_1';

            session([
                'is_logged_in' => true,
                'user_data' => $userData,
                'id_perangkat' => $idPerangkat
            ]);

            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Ambil semua data akun dari Firebase (menggunakan withoutVerifying agar aman di localhost)
        $firebaseUrl = rtrim(env('FIREBASE_DATABASE_URL'), '/') . '/GPS_TRACKING/AKUN.json';
        $response = Http::withoutVerifying()->get($firebaseUrl);
        $users = $response->json();

        // Cari kecocokan email dan password
        $authenticatedUser = null;

        if ($users && is_array($users)) {
            foreach ($users as $id => $user) {
                if (isset($user['email'], $user['password']) && 
                    $user['email'] === $request->email && 
                    Hash::check($request->password, $user['password'])) {
                    
                    $authenticatedUser = $user;
                    
                    // PENYEMPURNAAN: Pastikan key 'id_perangkat' selalu ada.
                    // Jika di node Firebase belum ada 'id_perangkat', gunakan $id atau fallback ke 'perangkat_1'
                    $authenticatedUser['id_perangkat'] = $user['id_perangkat'] ?? $user['device_id'] ?? 'perangkat_1';
                    
                    break;
                }
            }
        }

        if ($authenticatedUser) {
            // Simpan ke Session
            session([
                'is_logged_in' => true,
                'user_data' => $authenticatedUser,
                'id_perangkat' => $authenticatedUser['id_perangkat'] // <-- Tersimpan presisi di session
            ]);

            // Jika centang 'Remember Me'
            if ($request->has('remember')) {
                Cookie::queue('remember_firebase_user', json_encode($authenticatedUser), 1440);
            }

            return redirect()->intended(route('dashboard', absolute: false));
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request): RedirectResponse
    {
        session()->forget(['is_logged_in', 'user_data', 'id_perangkat']);
        session()->flush();
        
        Cookie::queue(Cookie::forget('remember_firebase_user'));
    
        return redirect()->route('login');
    }
}