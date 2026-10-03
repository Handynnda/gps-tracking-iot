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
    public function create(): View|RedirectResponse
    {
        if (!session('user_data') || session('user_data')['email'] !== 'handynandaf@gmail.com') {
            return redirect()->route('dashboard');
        }

        $firebaseUrl = env('FIREBASE_DATABASE_URL') . '/GPS_TRACKING/AKUN.json';
        $response = Http::get($firebaseUrl);
        
        $users = $response->json() ?? []; 

        return view('auth.register', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (!session('user_data') || session('user_data')['email'] !== 'handynandaf@gmail.com') {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $userId = uniqid('user_');

        $userData = [
            'id' => $userId,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'created_at' => now()->toDateTimeString()
        ];

        $firebaseUrl = env('FIREBASE_DATABASE_URL') . '/GPS_TRACKING/AKUN/' . $userId . '.json';
        Http::put($firebaseUrl, $userData);

        return back()->with('status', 'User baru berhasil didaftarkan!');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        if (!session('user_data') || session('user_data')['email'] !== 'handynandaf@gmail.com') {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:user,admin'], // Validasi role
            'password' => ['nullable', Rules\Password::defaults()],
        ]);

        $firebaseUrl = env('FIREBASE_DATABASE_URL') . '/GPS_TRACKING/AKUN/' . $id . '.json';

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role, // Simpan perubahan role ke Firebase
            'updated_at' => now()->toDateTimeString(),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        Http::patch($firebaseUrl, $updateData);

        return back()->with('status', 'Data user & role berhasil diperbarui!');
    }

    public function destroy(string $id): RedirectResponse
    {
        if (!session('user_data') || session('user_data')['email'] !== 'handynandaf@gmail.com') {
            return redirect()->route('dashboard');
        }

        $firebaseUrl = env('FIREBASE_DATABASE_URL') . '/GPS_TRACKING/AKUN/' . $id . '.json';
        Http::delete($firebaseUrl);

        return back()->with('status', 'User berhasil dihapus!');
    }
}