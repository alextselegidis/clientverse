<?php

namespace App\Providers;

use App\Auth\AppSessionGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

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
        Auth::extend('session', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider'] ?? null);

            $guard = new AppSessionGuard(
                $name,
                $provider,
                $app['session.store'],
                $app['request']
            );

            if (method_exists($guard, 'setCookieJar')) {
                $guard->setCookieJar($app['cookie']);
            }

            if (method_exists($guard, 'setDispatcher')) {
                $guard->setDispatcher($app['events']);
            }

            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($app->refresh('request', $guard, 'setRequest'));
            }

            return $guard;
        });

        // Deactivated users must lose API access too, not just the web session.
        Sanctum::authenticateAccessTokensUsing(
            fn ($accessToken, bool $isValid) => $isValid && (bool) $accessToken->tokenable?->is_active
        );
    }
}
