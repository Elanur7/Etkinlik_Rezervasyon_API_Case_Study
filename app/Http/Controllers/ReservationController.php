<?php

namespace App\Http\Controllers;

use App\Jobs\ExpireReservation;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\Seat;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'seats' => 'required|array',
            'total_amount' => 'required|numeric',
        ]);

        $seats = $request->seats;

        DB::beginTransaction();

        try {
            // event_id'ye ait bir Event kaydı olup olmadığını kontrol et
            $event = Event::find($request->event_id);
            if (!$event) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid event_id. Event not found.'
                ], 404);
            }

            // Rezervasyonu oluştur
            $reservation = Reservation::create([
                'user_id' => auth('api')->id(),
                'event_id' => $request->event_id,
                'total_amount' => $request->total_amount,
                'expires_at' => now()->addMinutes(1),
                'status' => 'confirmed',
            ]);

            foreach ($seats as $seatId) {
                $seat = Seat::where('id', $seatId)->where('status', 'available')->first();
                if (!$seat) {
                    throw new \Exception("Seat {$seatId} is already reserved or doesn't exist.");
                }

                $seat->update(['status' => 'reserved']);

                $reservation->items()->create([
                    'seat_id' => $seatId,
                    'price' => $seat->price ?? 0,
                ]);
            }

            // Job'ı dispatch et
            ExpireReservation::dispatch($reservation->id)->delay(now()->addMinutes(1));

            DB::commit();

            return response()->json([
                'status' => 'success',
                'reservation' => $reservation,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Rezervasyonları listeleme
    public function index()
    {
        $reservations = Reservation::with('user', 'event', 'items.seat')->get();

        return response()->json([
            'status' => 'success',
            'reservations' => $reservations,
        ]);
    }

    public function show($id)
    {
        // Kullanıcıya ait belirli bir rezervasyonu ve ilişkili verileri (user, event, items, seat) almak için
        $reservation = Reservation::with('items')
            ->where('id', $id)          // Rezervasyon ID'sine göre filtreleme yapıyoruz
            ->first();                  // İlk bulduğumuzu alıyoruz (id'siyle tek bir kayıt dönecek)

        // Eğer rezervasyon bulunamazsa hata döndürüyoruz
        if (!$reservation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reservation not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'reservation' => $reservation,  // Bulunan rezervasyonu döndürüyoruz
        ]);
    }

    public function confirm($id)
    {
        // Kullanıcının yaptığı rezervasyonu buluyoruz
        $reservation = Reservation::where('id', $id)
            ->where('user_id', auth('api')->id()) // Yalnızca oturum açan kullanıcının rezervasyonunu onaylayabilmesi için kontrol
            ->first();

        // Eğer rezervasyon bulunamazsa veya kullanıcıya ait değilse, hata döndürüyoruz
        if (!$reservation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reservation not found or not authorized'
            ], 404);
        }

        // Eğer rezervasyon zaten onaylıysa, hata döndürüyoruz
        if ($reservation->status == 'confirmed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Reservation already confirmed'
            ], 400);
        }

        // Rezervasyonun durumunu 'confirmed' olarak güncelliyoruz
        $reservation->status = 'confirmed';
        $reservation->save(); // Değişiklikleri kaydediyoruz

        return response()->json([
            'status' => 'success',
            'message' => 'Reservation confirmed successfully',
            'reservation' => $reservation
        ]);
    }
    public function destroy($id)
    {
        // Kullanıcıya ait olan belirli bir rezervasyonu buluyoruz
        $reservation = Reservation::where('id', $id)
            ->where('user_id', auth('api')->id()) // Yalnızca oturum açan kullanıcının rezervasyonlarını silmelerini sağlıyoruz
            ->first(); // İlk bulduğumuzu alıyoruz

        // Eğer rezervasyon bulunamazsa hata döndürüyoruz
        if (!$reservation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reservation not found or not authorized'
            ], 404);
        }

        // Rezervasyonu siliyoruz
        $reservation->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Reservation deleted successfully'
        ]);
    }
}
