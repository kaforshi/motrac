<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\MailManager;
use App\Mail\Transport\MailtrapTransport;
use Carbon\Carbon;

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
        // Set Carbon timezone based on authenticated user's timezone or default
        Carbon::setLocale(config('app.locale', 'id'));
        
        // Set timezone based on authenticated user
        if (auth()->check() && auth()->user()->timezone) {
            $timezone = auth()->user()->timezone;
        } else {
            $timezone = config('app.timezone', 'Asia/Jakarta');
        }
        
        date_default_timezone_set($timezone);
        
        // Register Mailtrap API transport
        $this->app->resolving(MailManager::class, function (MailManager $manager) {
            $manager->extend('mailtrap', function (array $config) {
                return new MailtrapTransport(
                    $config['api_token'] ?? env('MAILTRAP_API_TOKEN'),
                    $config['inbox_id'] ?? env('MAILTRAP_INBOX_ID')
                );
            });
        });
    }
}

