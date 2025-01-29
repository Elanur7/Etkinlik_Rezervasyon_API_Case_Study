<?php

namespace Database\Seeders;

use App\Models\Venue;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $venues = [
            [
                'name' => 'Grand Event Hall',
                'address' => '123 Main Street, Cityville',
                'capacity' => 500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Downtown Conference Center',
                'address' => '456 Elm Street, Metropolis',
                'capacity' => 300,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'The Open Air Theater',
                'address' => '789 Oak Street, Springfield',
                'capacity' => 1000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($venues as $venue) {
            Venue::create($venue);
        }
    }
}
