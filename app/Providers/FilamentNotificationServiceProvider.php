<?php

namespace App\Providers;

use App\Filament\Notifications\DatabaseNotifications;
use Illuminate\Support\ServiceProvider;

class FilamentNotificationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        DatabaseNotifications::register();
    }
}