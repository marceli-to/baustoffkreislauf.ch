<?php

namespace App\Providers;

use App\Auth\Protect\BlindsideProtector;
use App\Http\Controllers\Protect\PasswordProtectController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Statamic\Auth\Protect\ProtectorManager;
use Statamic\Auth\Protect\Protectors\Password\Controller as PasswordController;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Collection;

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

        // Submissions get their own CP section instead of sitting between the content collections
        Nav::extend(function ($nav) {
            $submissions = [
                'event_registrations' => 'calendar-date',
                'course_registrations' => 'content-book-open',
                'orders' => 'shopping-cart',
            ];

            if ($collections = $nav->find('Content', 'Collections')) {
                $children = $collections->children();

                $collections->children(fn () => collect(value($children))->reject(
                    fn ($item) => collect($submissions)->keys()->contains(fn ($handle) => str_ends_with($item->url(), '/collections/'.$handle))
                ));
            }

            foreach ($submissions as $handle => $icon) {
                if ($collection = Collection::find($handle)) {
                    $nav->create($collection->title())
                        ->section('Eingänge')
                        ->url($collection->showUrl())
                        ->icon($icon)
                        ->can('view', $collection);
                }
            }
        });
    }
}
