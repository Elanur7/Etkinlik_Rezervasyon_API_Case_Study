<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('reservations')->insert([
            [
                'id' => 1,
                'user_id' => 1,
                'event_id' => 1,
                'status' => 'confirmed',
                'total_amount' => 100.00,
                'expires_at' => now()->addDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
