<?php

namespace App\Providers;

use App\Auth\Protect\BlindsideProtector;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Statamic\Auth\Protect\ProtectorManager;
use Statamic\Statamic;

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
        if (!app()->environment('production')) {
            Mail::alwaysTo('m@marceli.to');
        }

        // Protection scheme for the hidden Baustofftage pages, see config/blindside.php
        app(ProtectorManager::class)->extend('blindside', function () {
            return new BlindsideProtector;
        });

        // Statamic::vite('app', [
        //     'resources/js/cp.js',
        //     'resources/css/cp.css',
        // ]);
    }
}
