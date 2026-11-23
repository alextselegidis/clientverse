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

namespace Tests\Feature;

use App\Http\Controllers\InstallController;
use App\Http\Middleware\InstallOrUpdate;
use Dotenv\Dotenv;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_fresh_upload_is_sent_to_the_installer(): void
    {
        config(['app.installed' => false]);

        $this->withoutInstalledFlag(function () {
            $this->get(route('login'))->assertRedirect(route('install'));
            $this->get(route('install'))->assertOk()->assertSee(__('install_requirements'));
        });

        // Once the flag file exists, a stray APP_INSTALLED=false no longer opens the installer.
        file_put_contents(storage_path('app/installed'), config('app.version'));
        $this->get(route('install'))->assertNotFound();
    }

    private function withoutInstalledFlag(callable $callback): void
    {
        $flag = storage_path('app/installed');
        $before = is_file($flag) ? file_get_contents($flag) : null;
        @unlink($flag);

        try {
            $callback();
        } finally {
            $before === null ? @unlink($flag) : file_put_contents($flag, $before);
        }
    }

    public function test_the_installer_is_gone_once_installed(): void
    {
        $this->get(route('install'))->assertNotFound();
        $this->post(route('install.store'))->assertNotFound();
    }

    public function test_a_new_version_migrates_once(): void
    {
        $flag = storage_path('app/installed');
        $before = is_file($flag) ? file_get_contents($flag) : null;
        file_put_contents($flag, '0.0.0');

        try {
            InstallOrUpdate::migrate();
            $this->assertSame(config('app.version'), trim(file_get_contents($flag)));
        } finally {
            $before === null ? @unlink($flag) : file_put_contents($flag, $before);
        }
    }

    public function test_env_values_read_back_unchanged(): void
    {
        foreach (['plain', 'p@ss w"o\\rd$X#1', 'a#b', 'x${HOME}y', ''] as $value) {
            $this->assertSame($value, Dotenv::parse('K=' . InstallController::envValue($value))['K']);
        }
    }
}
