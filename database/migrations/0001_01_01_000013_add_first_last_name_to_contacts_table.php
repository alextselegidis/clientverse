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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('first_name')->after('customer_id')->default('');
            $table->string('last_name')->nullable()->after('first_name');
        });

        // Migrate existing name data to first_name
        DB::table('contacts')->update([
            'first_name' => DB::raw('name'),
        ]);

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('name')->after('customer_id')->default('');
        });

        // Migrate first_name back to name
        DB::table('contacts')->update([
            'name' => DB::raw("CONCAT(first_name, COALESCE(CONCAT(' ', last_name), ''))"),
        ]);

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
