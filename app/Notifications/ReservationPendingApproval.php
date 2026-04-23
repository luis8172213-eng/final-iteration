<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Reservation;

class ReservationPendingApproval extends Notification
{
    use Queueable;

    // I keep the reservation data so I can include details in the notification
    public Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $reservation = $this->reservation;
        $userName = optional($reservation->user)->name ?? 'A user';
        $facilityName = optional($reservation->facility)->name ?? 'a facility';
        $date = optional($reservation->reservation_date)?->format('M d, Y') ?? 'a date';
        $start = optional($reservation->start_time)?->format('H:i') ?? '--:--';
        $end = optional($reservation->end_time)?->format('H:i') ?? '--:--';

        return [
            'reservation_id' => $reservation->id,
            'title' => 'Reservation change request',
            'message' => "{$userName} requested {$facilityName} on {$date} from {$start} to {$end}.",
            'action_url' => route('admin.dashboard'),
            'status' => $reservation->status,
        ];
    }
}
