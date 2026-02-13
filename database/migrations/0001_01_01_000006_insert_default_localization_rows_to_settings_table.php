<?php
/* ----------------------------------------------------------------------------
 * Clientverse - Simple Bookmark Manager
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://github.com/alextselegidis/clientverse
 * ---------------------------------------------------------------------------- */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $table = DB::table('settings');

        if (!$table->where('name', 'default_locale')->exists()) {
            $table->insert([
                'name' => 'default_locale',
                'value' => config('app.locale', 'en'),
            ]);
        }

        if (!$table->where('name', 'default_timezone')->exists()) {
            $table->insert([
                'name' => 'default_timezone',
                'value' => config('app.timezone', 'UTC'),
            ]);
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table = DB::table('settings');

        $table->whereIn('name', ['default_locale', 'default_timezone'])->delete();
    }
};
