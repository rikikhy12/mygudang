<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->hasRole('admin') || $user->hasPermission($permission)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki hak akses untuk tindakan atau halaman ini.');
    }
}
