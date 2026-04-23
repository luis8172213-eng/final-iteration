<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Reservation;

class ReservationStatusChanged extends Notification
{
    use Queueable;

    // Reservation object used to create the status change notification details.
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
        $facilityName = optional($reservation->facility)->name ?? 'a facility';
        $date = optional($reservation->reservation_date)?->format('M d, Y') ?? 'a date';
        $start = optional($reservation->start_time)?->format('H:i') ?? '--:--';
        $end = optional($reservation->end_time)?->format('H:i') ?? '--:--';
        $status = ucfirst($reservation->status);

        return [
            'reservation_id' => $reservation->id,
            'title' => "Reservation {$status}",
            'message' => "Your reservation for {$facilityName} on {$date} from {$start} to {$end} has been {$reservation->status}.",
            'action_url' => route('reserve'),
            'status' => $reservation->status,
        ];
    }
}
