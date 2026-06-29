<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mengalihkan pengguna yang belum login ke halaman login.
 */
class EnsureAuthenticated
{
    /**
     * Menangani permintaan masuk.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('sistem.login');
        }

        return $next($request);
    }
}
