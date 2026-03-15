<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DummyReservationSeeder extends Seeder
{
    public function run()
    {
        // Get a user (first user)
        $user = User::first();
        if (!$user) return;

        // Get facilities by name
        $facilities = Facility::whereIn('name', [
            'Conference Room A',
            'Computer Lab',
            'Auditorium',
        ])->get()->keyBy('name');

        // Insert dummy reservations
        $dummyReservations = [
            [
                'facility' => 'Conference Room A',
                'date' => '2026-03-20',
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'purpose' => 'Team Meeting',
            ],
            [
                'facility' => 'Computer Lab',
                'date' => '2026-03-22',
                'start_time' => '13:00:00',
                'end_time' => '15:00:00',
                'purpose' => 'Lab Session',
            ],
            [
                'facility' => 'Auditorium',
                'date' => '2026-03-25',
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'purpose' => 'Presentation',
            ],
        ];

        foreach ($dummyReservations as $dummy) {
            $facility = $facilities[$dummy['facility']] ?? null;
            if (!$facility) continue;
            Reservation::firstOrCreate([
                'user_id' => $user->id,
                'facility_id' => $facility->id,
                'reservation_date' => $dummy['date'],
                'start_time' => $dummy['start_time'],
                'end_time' => $dummy['end_time'],
                'purpose' => $dummy['purpose'],
            ], [
                'status' => 'pending',
            ]);
        }
    }
}
