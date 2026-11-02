<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAktif
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && ! $request->user()->aktif) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['username' => 'Akun Anda dinonaktifkan. Hubungi Superadmin.']);
        }

        return $next($request);
    }
}
