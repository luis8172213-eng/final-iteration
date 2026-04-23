<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Facility;
use App\Models\User;
use App\Notifications\ReservationPendingApproval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class CalendarController extends Controller
{
    // Get all reservations and format them as calendar events
    public function events(Request $request)
    {
        // I return JSON data that the calendar can display
        // If someone asks for a specific facility, I filter by that
        $facilityName = $request->query('facility');
        $query = Reservation::with('facility');
        if ($facilityName) {
            $query->whereHas('facility', function ($q) use ($facilityName) {
                $q->where('name', $facilityName);
            });
        }
        $reservations = $query->get();
        $events = $reservations->map(function ($reservation) {
            // Format the dates and times so the calendar can understand them
            $dateStr = $reservation->reservation_date->format('Y-m-d');
            // Make sure times are in the right format (hours:minutes:seconds)
            $startTime = $reservation->start_time->format('H:i:00');
            $endTime = $reservation->end_time->format('H:i:00');
            $start = $dateStr . 'T' . $startTime;
            $end = $dateStr . 'T' . $endTime;
            return [
                'id' => $reservation->id,
                'title' => $reservation->facility->name,
                'start' => $start,
                'end' => $end,
                'color' => $this->getReservationColor($reservation->id),
                'extendedProps' => [
                    'purpose' => $reservation->purpose,
                    'status' => $reservation->status,
                    'room' => $reservation->facility->name,
                    'date' => $dateStr,
                ],
            ];
        });
        return response()->json($events);
    }

    // Save a new reservation to the database
    public function store(Request $request)
    {
        // First, I validate that all the required info is provided
        // Then I check if the room is actually available at that time
        $request->validate([
            'facility' => 'required|string',
            'purpose' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);
        $facility = Facility::where('name', $request->facility)->firstOrFail();
        if (!$facility->isAvailable($request->date, $request->start_time, $request->end_time)) {
            return response()->json(['error' => 'Time slot not available'], 409);
        }
        $reservation = Reservation::create([
            'user_id' => Auth::id(),
            'facility_id' => $facility->id,
            'reservation_date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'purpose' => $request->purpose,
            'status' => 'pending',
        ]);

        $admins = User::where('is_admin', true)
            ->orWhere('is_super_admin', true)
            ->get();

        Notification::send($admins, new ReservationPendingApproval($reservation));

        return response()->json(['success' => true, 'reservation' => $reservation]);
    }

    // Pick a color for each reservation so they look different on the calendar
    private function getReservationColor($reservationId)
    {
        // I have 24 nice colors that all look good together
        $colors = [
            '#FF6B6B',  // Vibrant Red
            '#4ECDC4',  // Turquoise
            '#45B7D1',  // Sky Blue
            '#FFA07A',  // Light Salmon
            '#98D8C8',  // Mint
            '#F7DC6F',  // Golden Yellow
            '#BB8FCE',  // Purple
            '#85C1E2',  // Cornflower Blue
            '#F8B88B',  // Peach
            '#A8E6CF',  // Light Green
            '#FFD3B6',  // Light Orange
            '#FF8B94',  // Rose
            '#A8D8EA',  // Powder Blue
            '#AA96DA',  // Lavender
            '#FCBAD3',  // Pink
            '#B4A7D6',  // Soft Purple
            '#73A580',  // Sage Green
            '#F0A202',  // Deep Gold
            '#E76F51',  // Burnt Orange
            '#2A9D8F',  // Teal
            '#E9C46A',  // Sandy Yellow
            '#F4A261',  // Orange
            '#D62828',  // Deep Red
            '#1D3557',  // Navy Blue
        ];
        
        // I cycle through the colors based on the reservation ID so each one gets a different color
        $colorIndex = (intval($reservationId) % count($colors));
        return $colors[$colorIndex];
    }
}

