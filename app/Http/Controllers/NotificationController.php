<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\NotificationResource;
use App\Events\NotificationMarkedAsRead;
use App\Events\AllNotificationsMarkedAsRead;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(10);

        return inertia('Notifications/Index', [
            'notifications' => NotificationResource::collection($notifications),
            'unreadCount' => $request->user()->unreadNotifications()->count()
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, string $notificationId)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->markAsRead();

            // Get the new unread count
            $newUnreadCount = $request->user()->unreadNotifications()->count();

            // Broadcast the update
            broadcast(new NotificationMarkedAsRead($request->user(), $notificationId, $newUnreadCount));
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->unreadNotifications
            ->markAsRead();

        // Broadcast the update
        broadcast(new AllNotificationsMarkedAsRead($request->user()));

        return response()->json(['success' => true]);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount(Request $request)
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count()
        ]);
    }

    /**
     * Get notifications list for API (dropdown/ajax requests)
     */
    public function list(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'notifications' => NotificationResource::collection($notifications)
        ]);
    }
}
