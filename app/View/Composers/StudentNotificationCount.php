<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Menyuntikkan jumlah notifikasi belum dibaca ke layout portal siswa supaya
 * lencana pada menu Navigasi terisi tanpa query di dalam Blade.
 */
class StudentNotificationCount
{
    public function compose(View $view): void
    {
        $user = Auth::user();

        $count = 0;

        if ($user !== null && $user->isStudent()) {
            $count = $user->unreadNotifications()->count();
        }

        $view->with('unreadNotificationCount', $count);
    }
}
