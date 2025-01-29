<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ExpireReservation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reservationId;

    public function __construct($reservationId)
    {
        $this->reservationId = $reservationId;
    }

   public function handle()
{
    try {
        Log::info('ExpireReservation job started for reservation ID: ' . $this->reservationId);

        $reservation = Reservation::with('items.seat')->find($this->reservationId);

        if ($reservation) {
            Log::info('Reservation found. Status: ' . $reservation->status);

            if ($reservation->status === 'confirmed' && now()->greaterThanOrEqualTo($reservation->expires_at)) {
                DB::beginTransaction(); // Add transaction

                $reservation->update(['status' => 'cancelled']);

                foreach ($reservation->items as $item) {
                    $item->seat->update(['status' => 'available']);
                }

                DB::commit(); // Commit transaction

                Log::info('Reservation expired and seats updated.');
            } else {
                Log::info('Reservation not expired or not confirmed.');
            }
        } else {
            Log::error('Reservation not found for ID: ' . $this->reservationId);
        }
    } catch (\Exception $e) {
        Log::error('Error in ExpireReservation job: ' . $e->getMessage());
    }
}
}
