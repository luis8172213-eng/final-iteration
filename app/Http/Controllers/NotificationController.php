<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        // Get the logged-in user's notifications, showing the newest first, with 15 per page
        $notifications = auth()->user()->notifications()->latest()->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markAllRead(Request $request)
    {
        // Mark all the user's unread notifications as read
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    }

    public function destroySelected(Request $request)
    {
        // Delete selected notifications
        // If the notification is about a pending reservation, I also delete that reservation
        // This keeps everything in sync
        $request->validate([
            'selected_notifications' => 'array',
            'selected_notifications.*' => 'string',
        ]);

        $selectedIds = $request->input('selected_notifications', []);
        $notifications = auth()->user()->notifications()->whereIn('id', $selectedIds)->get();

        foreach ($notifications as $notification) {
            if ($notification->type === \App\Notifications\ReservationPendingApproval::class
                && ! empty($notification->data['reservation_id'])) {
                $reservation = Reservation::where('id', $notification->data['reservation_id'])
                    ->where('user_id', auth()->id())
                    ->first();

                if ($reservation && in_array($reservation->status, ['pending', 'approved', 'rejected'])) {
                    $reservation->delete();
                }
            }

            $notification->delete();
        }

        return back()->with('success', 'Selected notifications were removed successfully. Pending reservation requests were also cleared.');
    }
}
