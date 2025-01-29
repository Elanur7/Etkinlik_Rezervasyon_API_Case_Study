<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\TicketController;

Route::group(['middleware' => ['api', 'auth.session'], 'prefix' => 'auth'], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:api'])->group(function () {
    Route::get('/events/{id}/seats', [SeatController::class, 'showSeatsEvent']);
    Route::get('/venues/{id}/seats', [SeatController::class, 'showSeatsVenue']);
    Route::post('/seats/block', [SeatController::class, 'block']);
    Route::delete('/seats/release', [SeatController::class, 'release']);
});

Route::middleware(['auth:api'])->group(function () {
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);
});

Route::middleware(['auth:api'])->group(function () {
    Route::get('/reservations', [ReservationController::class, 'index']);  
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);
    Route::post('/reservations', [ReservationController::class, 'store']); 
    Route::post('/reservations/{id}/confirm', [ReservationController::class, 'confirm']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
});

Route::middleware(['auth:api'])->group(function () {
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::get('/tickets/{id}/download', [TicketController::class, 'download']);
    Route::post('/tickets/{id}/transfer', [TicketController::class, 'transfer']);
    Route::post('/tickets', [TicketController::class, 'create']);
    Route::delete('/tickets/{id}/cancel', [TicketController::class, 'cancelTicket']);
});
