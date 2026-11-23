<?php

/* ----------------------------------------------------------------------------
 * Clientverse - Self-Hosted CRM
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://clientverse.org
 * ---------------------------------------------------------------------------- */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends a fresh upload to the installer, and brings an existing install's database
 * up to date the first time a new version is served - so uploading new files is the
 * whole update, as long as the web server may run the migrations.
 */
class InstallOrUpdate
{
    public function handle(Request $request, Closure $next): Response
    {
        // The flag file is written by the installer and by every update, so its presence outranks a
        // stray APP_INSTALLED=false: an install that ran once never shows the installer again.
        if (!config('app.installed') && self::migratedVersion() === null) {
            // No database yet, so the session and cache cannot live there.
            config(['session.driver' => 'file', 'cache.default' => 'file']);

            return $request->routeIs('install', 'install.*') ? $next($request) : redirect()->route('install');
        }

        if ($request->routeIs('install', 'install.*')) {
            abort(404);
        }

        // Tests share the developer's storage folder; they must not mark the real database as migrated.
        if (!app()->runningUnitTests() && self::migratedVersion() !== config('app.version')) {
            self::migrate();
        }

        return $next($request);
    }

    /**
     * The version whose migrations last ran, from storage/app/installed.
     */
    public static function migratedVersion(): ?string
    {
        $path = storage_path('app/installed');

        return is_file($path) ? trim((string) file_get_contents($path)) : null;
    }

    /**
     * Run any pending migrations once per version. The file lock makes parallel
     * requests wait for the one that is migrating instead of migrating again.
     */
    public static function migrate(): void
    {
        // With storage/app read-only the app keeps serving; the log says what to run by hand.
        $lock = @fopen(storage_path('app/installed.lock'), 'c');

        if (!$lock) {
            report(new \RuntimeException('storage/app is not writable, so the database was not updated; run php artisan migrate.'));

            return;
        }

        flock($lock, LOCK_EX);

        try {
            if (self::migratedVersion() !== config('app.version')) {
                Artisan::call('migrate', ['--force' => true]);
                file_put_contents(storage_path('app/installed'), config('app.version'));
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
