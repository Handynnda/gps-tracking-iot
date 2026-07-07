<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class ProfileController extends Controller
{
    // Menampilkan halaman form profile
    public function edit(Request $request): View
    {
        // Lempar ke login jika tidak ada session
        if (!session()->has('is_logged_in')) {
            abort(403, 'Unauthorized action.');
        }

        // Ambil data user dari session untuk dilempar ke tampilan
        $user = session('user_data');
        return view('profile.edit', compact('user'));
    }

    // Memproses update data ke Firebase
    public function update(Request $request): RedirectResponse
    {
        // Validasi input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], // Password opsional
        ]);

        $user = session('user_data');
        $userId = $user['id'];

        // Susun ulang data yang akan diupdate
        $userData = [
            'id' => $userId,
            'name' => $request->name,
            'email' => $request->email,
            'role' => $user['role'] ?? 'user',
            'created_at' => $user['created_at'] ?? now()->toDateTimeString(),
        ];

        // Jika user mengisi password baru, kita hash. Jika kosong, gunakan password lama.
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        } else {
            $userData['password'] = $user['password']; 
        }

        // Kirim (Timpa) data ke Firebase menggunakan method PUT
        $firebaseUrl = env('FIREBASE_DATABASE_URL', 'https://gps-tracking-6ba6f-default-rtdb.asia-southeast1.firebasedatabase.app') . '/GPS_TRACKING/AKUN/' . $userId . '.json';
        
        $response = Http::put($firebaseUrl, $userData);

        if ($response->successful()) {
            // Update data session agar nama di pojok kanan atas langsung berubah
            session(['user_data' => $userData]);
            
            return redirect()->route('profile.edit')->with('status', 'profile-updated');
        }

        // Jika gagal konek ke Firebase
        return back()->withErrors(['email' => 'Gagal terhubung ke Firebase Server.']);
    }
}