<?php

namespace Database\Seeders;

use App\Models\Seat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Örnek Koltuk Verileri
        $seats = [
            [
                'venue_id' => 1,
                'section' => 'A',
                'row' => '1',
                'number' => 1,
                'status' => 'available',
                'price' => 50.00
            ],
            [
                'venue_id' => 2,
                'section' => 'B',
                'row' => '1',
                'number' => 2,
                'status' => 'reserved',
                'price' => 50.00
            ],
            [
                'venue_id' => 3,
                'section' => 'C',
                'row' => '1',
                'number' => 3,
                'status' => 'available',
                'price' => 50.00
            ],
        ];

        // Koltukları veritabanına ekliyoruz
        foreach ($seats as $seat) {
            Seat::create($seat);
        }
    }
}
