<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/** Keluarkan pengguna saat ini dari aplikasi */
class Logout
{
    /** Logout, invalidasi sesi, dan redirect ke halaman utama */
    public function __invoke()
    {
        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        return redirect('/');
    }
}
