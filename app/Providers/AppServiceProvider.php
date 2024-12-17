<?php

namespace App\Providers;

use App\Services\AppointmentService;
use App\Services\MessageService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AppointmentService::class, function ($app) {
            return new AppointmentService();
        });
        $this->app->singleton(MessageService::class, function ($app) {
            return new MessageService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
