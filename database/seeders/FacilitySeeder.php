<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Facility;
use Illuminate\Support\Facades\DB;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear old facilities first - disable foreign key checks for truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Facility::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $facilities = [
            // Computer Labs
            [
                'name' => 'Com LAB 1',
                'building' => 'Technology Building',
                'type' => 'Computer Lab',
                'capacity' => 30,
                'amenities' => 'Desktop computers, Projector, Whiteboard, Air conditioning',
                'requires_approval' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Com LAB 2',
                'building' => 'Technology Building',
                'type' => 'Computer Lab',
                'capacity' => 30,
                'amenities' => 'Desktop computers, Projector, Whiteboard, Air conditioning',
                'requires_approval' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Com LAB 3',
                'building' => 'Technology Building',
                'type' => 'Computer Lab',
                'capacity' => 30,
                'amenities' => 'Desktop computers, Projector, Whiteboard, Air conditioning',
                'requires_approval' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Com LAB 4',
                'building' => 'Technology Building',
                'type' => 'Computer Lab',
                'capacity' => 30,
                'amenities' => 'Desktop computers, Projector, Whiteboard, Air conditioning',
                'requires_approval' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Mac LAB 4',
                'building' => 'Technology Building',
                'type' => 'Computer Lab',
                'capacity' => 20,
                'amenities' => 'Mac computers, Projector, Whiteboard, Air conditioning',
                'requires_approval' => false,
                'is_active' => true,
            ],

            // Classrooms
            [
                'name' => 'Classroom A',
                'building' => 'Academic Building',
                'type' => 'Classroom',
                'capacity' => 50,
                'amenities' => 'Projector, Whiteboard, Tables and chairs, Air conditioning',
                'requires_approval' => false,
            ],
            [
                'name' => 'Classroom B',
                'building' => 'Academic Building',
                'type' => 'Classroom',
                'capacity' => 50,
                'amenities' => 'Projector, Whiteboard, Tables and chairs, Air conditioning',
                'requires_approval' => false,
            ],

            // Meeting Rooms
            [
                'name' => 'Meeting Room 1',
                'building' => 'Administrative Building',
                'type' => 'Meeting Room',
                'capacity' => 15,
                'amenities' => 'Conference table, Video conferencing, Whiteboard',
                'requires_approval' => false,
            ],
            [
                'name' => 'Meeting Room 2',
                'building' => 'Administrative Building',
                'type' => 'Meeting Room',
                'capacity' => 20,
                'amenities' => 'Conference table, Video conferencing, Projector',
                'requires_approval' => false,
            ],

            // Kitchen Classroom
            [
                'name' => 'Kitchen Classroom',
                'building' => 'Culinary Building',
                'type' => 'Kitchen',
                'capacity' => 25,
                'amenities' => 'Kitchen equipment, Cooking stations, Ventilation system',
                'requires_approval' => true,
            ],

            // Science Lab
            [
                'name' => 'Science Lab',
                'building' => 'Science Building',
                'type' => 'Laboratory',
                'capacity' => 40,
                'amenities' => 'Lab equipment, Safety gear, Fume hoods, Storage cabinets',
                'requires_approval' => true,
            ],

            // Library
            [
                'name' => 'Library',
                'building' => 'Library Building',
                'type' => 'Study Space',
                'capacity' => 100,
                'amenities' => 'Books, Computers, Quiet study areas, Collaboration zones',
                'requires_approval' => false,
            ],

            // Conference Rooms
            [
                'name' => 'Conference Room A',
                'building' => 'Administrative Building',
                'type' => 'Meeting Room',
                'capacity' => 30,
                'amenities' => 'Conference table, Video conferencing, Projector, Whiteboard',
                'requires_approval' => false,
            ],
            [
                'name' => 'Conference Room B',
                'building' => 'Administrative Building',
                'type' => 'Meeting Room',
                'capacity' => 30,
                'amenities' => 'Conference table, Video conferencing, Projector, Whiteboard',
                'requires_approval' => false,
            ],

            // Gym
            [
                'name' => 'GYM',
                'building' => 'Sports Complex',
                'type' => 'Sports Facility',
                'capacity' => 80,
                'amenities' => 'Exercise equipment, Mats, Changing rooms, Showers',
                'requires_approval' => false,
            ],
        ];

        foreach ($facilities as $facility) {
            Facility::create($facility);
        }
    }
}
