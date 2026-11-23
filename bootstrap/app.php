<?php

use App\Http\Middleware\ExtendRememberSession;
use App\Http\Middleware\InstallOrUpdate;
use App\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: explode(',', (string) env('TRUSTED_PROXIES', '*')));
        $middleware->statefulApi();

        // The client portal has its own login page and home.
        $middleware->redirectGuestsTo(fn ($request) => $request->is('portal*') ? route('portal.login') : route('login'));
        $middleware->redirectUsersTo(fn ($request) => $request->is('portal*') ? route('portal') : route('dashboard'));
        // Before the session starts: an install with no database yet keeps its session in files.
        $middleware->web(prepend: [InstallOrUpdate::class], append: [
            ExtendRememberSession::class,
        ]);
        $middleware->api(prepend: [InstallOrUpdate::class]);

        // Namespace the CSRF cookie so installs sharing a domain do not clobber
        // each other's; see cookie_suffix() in helpers.php.
        $middleware->replaceInGroup('web', \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, ValidateCsrfToken::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
