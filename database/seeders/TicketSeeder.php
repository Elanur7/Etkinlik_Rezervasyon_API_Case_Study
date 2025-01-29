<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Örnek olarak 10 adet bilet oluşturalım
        for ($i = 0; $i < 10; $i++) {
            do {
                $ticketCode = Str::random(10); // 10 karakterli rastgele bir kod üret
            } while (Ticket::where('ticket_code', $ticketCode)->exists()); // Kod daha önce kullanılmışsa tekrar üret

            Ticket::create([
                'reservation_id' => 1, // Örnek bir reservation_id, gerçek senaryoda dinamik olarak atanabilir
                'seat_id' => $i + 1,
                'ticket_code' => $ticketCode,
                'status' => 'unused',
            ]);
        }
    }
}
