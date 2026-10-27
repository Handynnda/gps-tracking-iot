<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOwner
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('is_logged_in') || !session()->has('user_data')) {
            return redirect()->route('login');
        }

        $user = session('user_data');
        $email = $user['email'] ?? '';
        $role = strtolower($user['role'] ?? '');

        // Hak akses Owner: email handynandaf@gmail.com ATAU role owner
        $isOwner = ($email === 'handynandaf@gmail.com' || $role === 'owner');

        if (!$isOwner) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Fitur ini hanya dapat diakses oleh Owner.');
        }

        return $next($request);
    }
}
