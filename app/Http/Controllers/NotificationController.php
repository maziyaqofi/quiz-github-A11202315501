<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAsRead(Request $request, string $id)
    {
        // Only retrieve notifications belonging to the authenticated user
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        // Mark the notification as read
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return back()->with(
            'success',
            'Notifikasi berhasil ditandai sebagai dibaca.'
        );
    }

    public function markAllAsRead(Request $request)
    {
        // Mark all unread notifications belonging to the authenticated user
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return back()->with(
            'success',
            'Semua notifikasi berhasil ditandai sebagai dibaca.'
        );
    }
}