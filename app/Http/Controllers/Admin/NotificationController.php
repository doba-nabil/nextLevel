<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $notifications = AdminNotification::where('admin_id', $admin->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('dashboard.notifications.index', compact('notifications'));
    }

    public function unreadCount()
    {
        $admin = Auth::guard('admin')->user();
        $count = AdminNotification::where('admin_id', $admin->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markAsRead($id)
    {
        $admin = Auth::guard('admin')->user();
        $notification = AdminNotification::where('admin_id', $admin->id)
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $admin = Auth::guard('admin')->user();
        AdminNotification::where('admin_id', $admin->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json(['success' => true]);
    }

    public function getLatest()
    {
        $admin = Auth::guard('admin')->user();
        $notifications = AdminNotification::where('admin_id', $admin->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($notifications);
    }
}
