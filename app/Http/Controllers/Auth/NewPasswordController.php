<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                
                $newPasswordHash = Hash::make($request->password);

                $user->forceFill([
                    'password' => $newPasswordHash,
                    'remember_token' => Str::random(60),
                ])->save();

                $firebaseUrl = env('FIREBASE_DATABASE_URL') . '/GPS_TRACKING/AKUN.json';
                $response = Http::get($firebaseUrl);
                $users = $response->json();

                if ($users) {
                    foreach ($users as $id => $firebaseUser) {
                        if (isset($firebaseUser['email']) && $firebaseUser['email'] === $request->email) {
                            
                            $updateUrl = env('FIREBASE_DATABASE_URL') . '/GPS_TRACKING/AKUN/' . $id . '.json';
                            Http::patch($updateUrl, [
                                'password' => $newPasswordHash
                            ]);
                            break;
                        }
                    }
                }

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}