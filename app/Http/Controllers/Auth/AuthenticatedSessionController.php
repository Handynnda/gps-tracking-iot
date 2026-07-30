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
        // 1. CEK COOKIE: Jika ada cookie remember me, langsung buatkan session dan lempar ke dashboard
        if ($request->hasCookie('remember_firebase_user')) {
            $userData = json_decode($request->cookie('remember_firebase_user'), true);
            
            session([
                'is_logged_in' => true,
                'user_data' => $userData
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

        // Ambil semua data akun dari Firebase
        $firebaseUrl = env('FIREBASE_DATABASE_URL') . '/GPS_TRACKING/AKUN.json';
        $response = Http::get($firebaseUrl);
        $users = $response->json();

        // Cari kecocokan email dan password
        $authenticatedUser = null;

        if ($users) {
            foreach ($users as $id => $user) {
                if ($user['email'] === $request->email && Hash::check($request->password, $user['password'])) {
                    $authenticatedUser = $user;
                    break;
                }
            }
        }

        if ($authenticatedUser) {
            session([
                'is_logged_in' => true,
                'user_data' => $authenticatedUser
            ]);

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
        session()->forget(['is_logged_in', 'user_data']);
        session()->flush();
        
        Cookie::queue(Cookie::forget('remember_firebase_user'));
    
        return redirect()->route('login');
    }
}