<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Seat;
use App\Models\Venue;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    // Koltukları listeleme
    public function index()
    {
        $seats = Seat::with('venue')->get();  // Venue ilişkisini de dahil et
        return response()->json($seats);
    }

    public function showSeatsEvent($id)
    {
        $event = Event::with('seats')->find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        return response()->json($event->seats);
    }

    public function showSeatsVenue($id)
    {
        $venue = Venue::with('seats')->find($id);

        if (!$venue) {
            return response()->json(['message' => 'Venue not found'], 404);
        }

        return response()->json($venue->seats);
    }

    // Koltuk müsaitlik kontrolü
    public function checkAvailability($id)
    {
        $seat = Seat::find($id);  // Koltuğu ID'ye göre buluyoruz

        // Koltuk bulunamazsa hata mesajı döndürüyoruz
        if (!$seat) {
            return response()->json(['message' => 'Seat not found'], 404);
        }

        // Koltuğun durumu kontrol ediliyor
        if ($seat->status == 'available') {
            return response()->json([
                'status' => 'success',
                'message' => 'Seat is available'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Seat is not available'
        ]);
    }

    public function block(Request $request)
    {
        $seatId = $request->input('seat_id');
        $seat = Seat::find($seatId);

        if (!$seat) {
            return response()->json(['message' => 'Seat not found'], 404);
        }

        if ($seat->status === 'reserved') {
            return response()->json(['message' => 'Seat is already reserved and cannot be blocked'], 400);
        }

        if ($seat->status === 'blocked') {
            return response()->json(['message' => 'Seat is already blocked'], 400);
        }

        // Koltuğu blokla
        $seat->status = 'blocked';
        $seat->save();

        return response()->json(['message' => 'Seat blocked successfully']);
    }

    public function release(Request $request)
    {
        $seatId = $request->input('seat_id');
        $seat = Seat::find($seatId);

        if (!$seat) {
            return response()->json(['message' => 'Seat not found'], 404);
        }

        if ($seat->status !== 'blocked') {
            return response()->json(['message' => 'Seat is not blocked'], 400);
        }

        // Blok kaldırma işlemi
        $seat->status = 'available';
        $seat->save();

        return response()->json(['message' => 'Seat released successfully']);
    }
}
