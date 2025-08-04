<?php

namespace App\Http\Controllers;

use App\Events\NotificationMarkedAsRead;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

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
        ]);
    }

    /**
     * Mark notification(s) as read.
     * If notificationId is 'all', marks all notifications as read.
     * Otherwise, marks the specific notification as read.
     */
    public function markAsRead(Request $request)
    {
        $user = $request->user();
        $notificationId = $request->input('notification_id');

        if ($request->input('mark_all') === false && $request->has('notification_id')) {
            // Mark specific notification as read
            $notification = $user->notifications()
                ->where('id', $notificationId)
                ->first();

            if ($notification) {
                $notification->markAsRead();

                // Broadcast the update for specific notification
                broadcast(new NotificationMarkedAsRead($user, $notificationId));
            }

        }

        if ($request->input('mark_all') === true) {
            // Mark all unread notifications as read
            $user->unreadNotifications->markAsRead();

            // Broadcast the update for all notifications (null means all)
            broadcast(new NotificationMarkedAsRead($user, null));
        }

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
            'notifications' => NotificationResource::collection($notifications),
            'unreadCount'   => $request->user()->unreadNotifications()->count(),
        ]);
    }
}
