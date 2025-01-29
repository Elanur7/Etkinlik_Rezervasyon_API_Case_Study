<?php

namespace App\Http;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use App\Jobs\ExpireReservation;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class Kernel extends HttpKernel
{

    protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        Log::info('Schedule run started');

        $reservations = Reservation::with('items.seat')
            ->where('status', 'confirmed')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($reservations as $reservation) {
            ExpireReservation::dispatch($reservation->id);
        }

        Log::info('Schedule run completed');
    })->everyFifteenMinutes();
}
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * These middleware can be assigned to groups to make them easier to apply.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware are assigned to individual routes.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth.session' => \App\Http\Middleware\AuthenticateSession::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}
