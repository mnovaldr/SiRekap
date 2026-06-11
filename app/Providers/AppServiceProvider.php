<?php

namespace App\Providers;

use App\Models\Event;
use App\Observers\EventObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            \Filament\Http\Responses\Auth\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );
        $this->app->singleton(
            \Filament\Http\Responses\Auth\Contracts\LogoutResponse::class,
            \App\Http\Responses\LogoutResponse::class
        );
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Event::observe(EventObserver::class);

        // Log Login Sukses
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function ($event) {
                \App\Models\ActivityLog::log(
                    \App\Models\ActivityLog::ACTION_LOGIN,
                    $event->user,
                    "User login sukses: {$event->user->name} ({$event->user->email})"
                );
            }
        );

        // Log Login Gagal
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Failed::class,
            function ($event) {
                $email = $event->credentials['email'] ?? 'unknown';
                \App\Models\ActivityLog::log(
                    'login_failed',
                    null,
                    "Percobaan login gagal: mencoba email '{$email}'"
                );
            }
        );

        // Log Logout
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Logout::class,
            function ($event) {
                if ($event->user) {
                    \App\Models\ActivityLog::log(
                        \App\Models\ActivityLog::ACTION_LOGOUT,
                        $event->user,
                        "User logout: {$event->user->name} ({$event->user->email})"
                    );
                }
            }
        );
    }
}
