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

namespace App\Http\Controllers;

use App\Http\Middleware\InstallOrUpdate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * The browser installer: checks the server, connects the database, runs the
 * migrations and sets up the first admin. Only reachable while APP_INSTALLED is
 * false (see InstallOrUpdate).
 */
class InstallController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.install', [
            'requirements' => $this->requirements(),
            'appUrl' => $request->root(),
        ]);
    }

    public function store(Request $request)
    {
        // Every value ends up on one line of .env, so no line breaks.
        $line = 'not_regex:/[\r\n]/';

        $data = $request->validate([
            'app_url' => ['required', 'url', 'max:255', $line],
            'db_host' => ['required', 'string', 'max:255', $line],
            'db_port' => 'required|integer|between:1,65535',
            'db_database' => ['required', 'string', 'max:64', $line],
            'db_username' => ['required', 'string', 'max:255', $line],
            'db_password' => ['nullable', 'string', 'max:255', $line],
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (in_array(false, $this->requirements(), true)) {
            return back()->withInput()->withErrors(['install' => __('install_requirements_missing')]);
        }

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $data['db_host'],
            'database.connections.mysql.port' => $data['db_port'],
            'database.connections.mysql.database' => $data['db_database'],
            'database.connections.mysql.username' => $data['db_username'],
            'database.connections.mysql.password' => (string) $data['db_password'],
        ]);
        DB::purge('mysql');

        try {
            DB::connection('mysql')->getPdo();
        } catch (Throwable $e) {
            // The driver's message names internal addresses and which logins exist; it goes to the log only.
            report($e);

            return back()->withInput()->withErrors(['db_host' => __('install_database_failed')]);
        }

        // The installer only ever sets up an empty database. Pointed at a database that already holds
        // an install, it would otherwise hand that install's admin account to whoever filled the form.
        if (Schema::connection('mysql')->hasTable('migrations')) {
            return back()->withInput()->withErrors(['db_database' => __('install_database_not_empty')]);
        }

        // Two submissions at once must not both install; the second one finds the flag and stops.
        $lock = fopen(storage_path('app/installed.lock'), 'c');
        flock($lock, LOCK_EX);

        try {
            if (InstallOrUpdate::migratedVersion() !== null) {
                abort(404);
            }

            $this->install($data);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        // No flash message: the session moves from files to the database here, so it would be lost.
        return redirect(rtrim($data['app_url'], '/') . '/login');
    }

    private function install(array $data): void
    {
        // A first migration takes a while; shared hosts often stop a request at 30 seconds.
        @set_time_limit(300);
        Artisan::call('migrate', ['--force' => true]);

        // The migrations create a default admin in the new, empty database; it becomes the one entered here.
        $admin = User::where('role', 'admin')->orderBy('id')->first() ?? new User(['role' => 'admin']);
        $admin->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ])->save();

        $this->writeEnvironment([
            'APP_URL' => rtrim($data['app_url'], '/'),
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $data['db_host'],
            'DB_PORT' => $data['db_port'],
            'DB_DATABASE' => $data['db_database'],
            'DB_USERNAME' => $data['db_username'],
            'DB_PASSWORD' => (string) $data['db_password'],
            'APP_INSTALLED' => 'true',
        ]);
        file_put_contents(storage_path('app/installed'), config('app.version'));
        Artisan::call('config:clear');
    }

    /**
     * What the server needs, each mapped to whether it has it.
     */
    private function requirements(): array
    {
        $requirements = ['PHP 8.2+' => version_compare(PHP_VERSION, '8.2.0', '>=')];

        foreach (['pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'fileinfo', 'curl', 'dom'] as $extension) {
            $requirements['PHP ' . $extension] = extension_loaded($extension);
        }

        $requirements[__('install_writable', ['path' => '.env'])] = is_writable(app()->environmentFilePath());
        $requirements[__('install_writable', ['path' => 'storage'])] = is_writable(storage_path());
        $requirements[__('install_writable', ['path' => 'bootstrap/cache'])] = is_writable(app()->bootstrapPath('cache'));

        return $requirements;
    }

    /**
     * Set these keys in .env, replacing a key's line or adding one.
     */
    private function writeEnvironment(array $values): void
    {
        $path = app()->environmentFilePath();
        $contents = (string) @file_get_contents($path);

        foreach ($values as $key => $value) {
            $line = $key . '=' . self::envValue((string) $value);
            $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

            $contents = preg_match($pattern, $contents)
                ? preg_replace_callback($pattern, fn () => $line, $contents)
                : rtrim($contents, "\n") . "\n" . $line . "\n";
        }

        file_put_contents($path, $contents);
    }

    /**
     * Quote a value so .env reads it back unchanged: bare when it is plain, in
     * double quotes otherwise, with the characters dotenv interprets escaped.
     */
    public static function envValue(string $value): string
    {
        if (preg_match('/^[A-Za-z0-9_.:\/@+-]*$/', $value)) {
            return $value;
        }

        return '"' . addcslashes($value, '\\"$') . '"';
    }
}
