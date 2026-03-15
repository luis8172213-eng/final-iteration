<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Facility;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    // Fetch reservations as events for FullCalendar
    public function events(Request $request)
    {
        $facilityName = $request->query('facility');
        $query = Reservation::with('facility');
        if ($facilityName) {
            $query->whereHas('facility', function ($q) use ($facilityName) {
                $q->where('name', $facilityName);
            });
        }
        $reservations = $query->get();
        $events = $reservations->map(function ($reservation) {
            // Ensure proper ISO 8601 format for FullCalendar
            $dateStr = $reservation->reservation_date->format('Y-m-d');
            $start = $dateStr . 'T' . $reservation->start_time;
            $end = $dateStr . 'T' . $reservation->end_time;
            if (strlen($reservation->start_time) === 5) {
                $start .= ':00';
            }
            if (strlen($reservation->end_time) === 5) {
                $end .= ':00';
            }
            return [
                'id' => $reservation->id,
                'title' => $reservation->facility->name,
                'start' => $start,
                'end' => $end,
                'color' => $this->getFacilityColor($reservation->facility->name),
            ];
        });
        return response()->json($events);
    }

    // Store a new reservation
    public function store(Request $request)
    {
        $request->validate([
            'facility' => 'required|string',
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
            'status' => 'pending',
        ]);
        return response()->json(['success' => true, 'reservation' => $reservation]);
    }

    // Assign a color to each facility
    private function getFacilityColor($facilityName)
    {
        $colors = [
            'Conference Room A' => '#6366f1',
            'Computer Lab' => '#10b981',
            'Study Room B' => '#f59e42',
            'Auditorium' => '#f43f5e',
            'Sports Hall' => '#3b82f6',
            'Science Lab' => '#a21caf',
        ];
        return $colors[$facilityName] ?? '#6366f1';
    }
}
