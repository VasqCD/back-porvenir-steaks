<?php

namespace App\Providers;

use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\Notificacion;
use App\Observers\NotificacionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Notificacion::observe(NotificacionObserver::class);
    }
}
