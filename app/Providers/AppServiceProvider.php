<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;
use Symfony\Component\HttpClient\HttpClient;

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
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        
        \App\Models\Role::observe(\App\Observers\RoleObserver::class);

        // Register Brevo Mail Transport
        Mail::extend('brevo', function (array $config) {
            return new BrevoApiTransport(
                $config['key'],
                HttpClient::create()
            );
        });
    }
}
