<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // API: Get recent notifications (for bell dropdown)
    public function recent()
    {
        $userId = auth()->id();
        $notifs = AdminNotification::forUser($userId)->orderByDesc('id')->limit(15)->get();
        $unread = AdminNotification::forUser($userId)->where('read', false)->count();

        return response()->json([
            'notifications' => $notifs,
            'unread_count'  => $unread,
        ]);
    }

    // API: Mark all as read
    public function markAllRead()
    {
        $userId = auth()->id();

        AdminNotification::forUser($userId)
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json([
            'ok' => true,
            'message' => 'Semua notifikasi sudah dibaca.',
        ]);
    }

    // API: Mark one as read
    public function markRead(AdminNotification $notification)
    {
        $userId = auth()->id();
        $notification = AdminNotification::forUser($userId)->findOrFail($notification->id);
        $notification->update(['read' => true]);

        return response()->json([
            'ok' => true,
            'message' => 'Notifikasi ditandai sudah dibaca.',
        ]);
    }

    // Full activity log page
    public function activityLog(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->orderByDesc('id')
            ->paginate(50);

        return view('admin.activity-log', compact('logs'));
    }
}
