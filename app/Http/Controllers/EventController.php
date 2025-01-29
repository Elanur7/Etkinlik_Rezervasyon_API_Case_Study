<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Info(
 *     title="Event API",
 *     version="1.0.0",
 *     description="API for managing events",
 *     @OA\Contact(
 *         email="contact@example.com"
 *     )
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

class EventController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/events",
     *     summary="Get all events",
     *     tags={"Events"},
     *     @OA\Response(
     *         response=200,
     *         description="List of events",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="Event ID"),
     *                 @OA\Property(property="name", type="string", description="Event name"),
     *                 @OA\Property(property="description", type="string", description="Event description"),
     *                 @OA\Property(property="start_date", type="string", format="date-time", description="Event start date"),
     *                 @OA\Property(property="end_date", type="string", format="date-time", description="Event end date")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    // Etkinliklerin listelenmesi
    public function index()
    {
        $events = Event::all();
        return response()->json($events);
    }

    /**
     * @OA\Get(
     *     path="/api/events/{id}",
     *     summary="Get a single event by ID",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the event",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Details of the event",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", description="Event ID"),
     *             @OA\Property(property="name", type="string", description="Event name"),
     *             @OA\Property(property="description", type="string", description="Event description"),
     *             @OA\Property(property="start_date", type="string", format="date-time", description="Event start date"),
     *             @OA\Property(property="end_date", type="string", format="date-time", description="Event end date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */

    // Tek bir etkinliği görüntüleme
    public function show($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        return response()->json($event);
    }

    // Etkinlik oluşturma (Admin)
    public function store(Request $request)
    {
        // Bearer token ile oturum açmış kullanıcıyı al
        $user = Auth::user();

        // Eğer kullanıcı oturum açmamışsa veya admin değilse hata döndür
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Not admin'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'venue_id' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // venue_id'ye ait bir venue kaydı olup olmadığını kontrol et
        $venue = Venue::find($request->venue_id);
        if (!$venue) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid venue_id. Venue not found.'
            ], 404);
        }

        $event = Event::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Event created successfully',
            'event' => $event
        ], 201);
    }

    // Etkinlik güncelleme (Admin)
    public function update(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $event->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Event updated successfully',
            'event' => $event
        ]);
    }

    // Etkinlik silme (Admin)
    public function destroy($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        $event->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Event deleted successfully'
        ]);
    }
}
