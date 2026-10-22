<?php

namespace App\Providers;

use App\Auth\Protect\BlindsideProtector;
use App\Http\Controllers\Cp\GuideController;
use App\Http\Controllers\Protect\PasswordProtectController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Statamic\Auth\Protect\ProtectorManager;
use Statamic\Auth\Protect\Protectors\Password\Controller as PasswordController;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Collection;
use Statamic\Statamic;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Render the password form in the site of the protected page
        $this->app->bind(PasswordController::class, PasswordProtectController::class);
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

        Statamic::vite('app', [
            'resources/css/cp.css',
        ]);

        // Editor guide, see resources/redaktion
        Statamic::pushCpRoutes(function () {
            Route::get('anleitung', [GuideController::class, 'show'])->name('guide');
            Route::get('anleitung/bilder/{file}', [GuideController::class, 'image'])
                ->where('file', '[a-z0-9-]+\.(png|jpg)')
                ->name('guide.image');
        });

        // Submissions get their own CP section instead of sitting between the content collections
        Nav::extend(function ($nav) {
            $submissions = [
                'event_registrations' => ['Anlässe', 'calendar-date'],
                'course_registrations' => ['Kurse', 'content-book-open'],
                'orders' => ['Bestellungen', 'shopping-cart'],
            ];

            if ($collections = $nav->find('Content', 'Collections')) {
                $children = $collections->children();

                $collections->children(fn () => collect(value($children))->reject(
                    fn ($item) => collect($submissions)->keys()->contains(fn ($handle) => str_ends_with($item->url(), '/collections/'.$handle))
                ));
            }

            foreach ($submissions as $handle => [$label, $icon]) {
                if ($collection = Collection::find($handle)) {
                    $nav->create($label)
                        ->section('Eingänge')
                        ->url($collection->showUrl())
                        ->icon($icon)
                        ->can('view', $collection);
                }
            }

            $nav->create('Anleitung')
                ->section('Hilfe')
                ->route('guide')
                ->icon('docs')
                ->attributes(['target' => '_blank']);
        });
    }
}
