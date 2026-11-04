<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Pastikan user ada di database SQL lokal; jika belum ada tapi terdaftar di Firebase, sinkronkan otomatis:
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            $baseFirebase = rtrim(config('services.firebase.database_url', env('FIREBASE_DATABASE_URL', 'https://gps-tracking-6ba6f-default-rtdb.asia-southeast1.firebasedatabase.app')), '/');
            $response = Http::withoutVerifying()->get($baseFirebase . '/GPS_TRACKING/AKUN.json');
            $fbUsers = $response->json();
            if ($fbUsers && is_array($fbUsers)) {
                foreach ($fbUsers as $fbUser) {
                    if (isset($fbUser['email']) && strtolower(trim($fbUser['email'])) === strtolower(trim($request->email))) {
                        User::create([
                            'name' => $fbUser['name'] ?? 'User',
                            'email' => $fbUser['email'],
                            'password' => $fbUser['password'] ?? Hash::make(Str::random(16)),
                            'role' => $fbUser['role'] ?? 'admin',
                        ]);
                        break;
                    }
                }
            }
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
