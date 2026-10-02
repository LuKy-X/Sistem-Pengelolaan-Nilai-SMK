<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $notifications = $user->notifications()
            ->paginate(15)
            ->withQueryString();

        $unreadCount = $user->unreadNotifications()->count();

        // Tandai semua sudah dibaca setelah halaman dibuka, kecuali bila siswa
        // sedang melihat notifikasi tertentu lewat penanda ?read=<id>.
        if ($request->query('read') === null && $request->boolean('mark_read', true)) {
            $user->unreadNotifications->markAsRead();
        }

        return view('student.notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Menandai satu notifikasi sudah dibaca lalu mengarahkan ke tujuan di dalamnya.
     */
    public function read(string $notification): RedirectResponse
    {
        $user = Auth::user();

        $item = $user->notifications()->findOrFail($notification);
        $item->markAsRead();

        $target = $item->data['url'] ?? route('student.notifications.index');

        return redirect()->to($target);
    }

    public function markAllRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return redirect()
            ->route('student.notifications.index')
            ->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
