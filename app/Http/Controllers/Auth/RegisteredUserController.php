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
    private function isOwner(): bool
    {
        $user = session('user_data');
        if (!$user) {
            return false;
        }
        return ($user['email'] ?? '') === 'handynandaf@gmail.com' || strtolower($user['role'] ?? '') === 'owner';
    }

    public function create(): View|RedirectResponse
    {
        if (!$this->isOwner()) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Fitur ini hanya dapat diakses oleh Owner.');
        }

        $firebaseUrl = env('FIREBASE_DATABASE_URL') . '/GPS_TRACKING/AKUN.json';
        $response = Http::withoutVerifying()->get($firebaseUrl);
        
        $users = $response->json() ?? []; 

        return view('auth.register', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (!$this->isOwner()) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Fitur ini hanya dapat diakses oleh Owner.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:owner,admin'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $userId = uniqid('user_');
        $role = strtolower($request->role);

        $userData = [
            'id' => $userId,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'created_at' => now()->toDateTimeString()
        ];

        $firebaseUrl = env('FIREBASE_DATABASE_URL') . '/GPS_TRACKING/AKUN/' . $userId . '.json';
        Http::withoutVerifying()->put($firebaseUrl, $userData);

        return back()->with('status', 'Akun ' . ucfirst($role) . ' berhasil didaftarkan!');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        if (!$this->isOwner()) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Fitur ini hanya dapat diakses oleh Owner.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:owner,admin'], // Validasi role: owner atau admin
            'password' => ['nullable', Rules\Password::defaults()],
        ]);

        $firebaseUrl = env('FIREBASE_DATABASE_URL') . '/GPS_TRACKING/AKUN/' . $id . '.json';
        $role = strtolower($request->role);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $role, // Simpan perubahan role ke Firebase
            'updated_at' => now()->toDateTimeString(),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        Http::withoutVerifying()->patch($firebaseUrl, $updateData);

        return back()->with('status', 'Data akun & role berhasil diperbarui!');
    }

    public function destroy(string $id): RedirectResponse
    {
        if (!$this->isOwner()) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Fitur ini hanya dapat diakses oleh Owner.');
        }

        $firebaseUrl = env('FIREBASE_DATABASE_URL') . '/GPS_TRACKING/AKUN/' . $id . '.json';
        Http::withoutVerifying()->delete($firebaseUrl);

        return back()->with('status', 'User berhasil dihapus!');
    }
}