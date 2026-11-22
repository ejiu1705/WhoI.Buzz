<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleScopeMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        if (! in_array($user->role_scope, $roles, true)) {
            abort(403, 'Akses ditolak oleh Zero-Trust RBAC: peran Anda ('.$user->roleLabel().') tidak diizinkan membuka modul ini.');
        }

        return $next($request);
    }
}
