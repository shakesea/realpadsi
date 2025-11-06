<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Session::get('user');

        if (!$user) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userRole = strtolower(trim($user['type'])); // normalize role
        $allowedRoles = array_map('strtolower', $roles);

        // Semua role boleh ke dashboard
        if ($request->is('dashboard')) {
            return $next($request);
        }

        if ($userRole === 'manager') {
            return $next($request); 
        }

        // Cek apakah role user termasuk role yang diizinkan
        if (!in_array(strtolower($user['type']), array_map('strtolower', $roles))) {
    abort(403, 'Anda tidak memiliki akses ke halaman ini.');
}

        return $next($request);
    }
}
