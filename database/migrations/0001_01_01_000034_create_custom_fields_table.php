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

return new class extends Migration
{
    private const RECORDS = ['customers', 'contacts', 'projects', 'sales', 'contracts'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            // The table whose records carry the field.
            $table->string('applies_to', 20)->index();
            $table->string('label');
            $table->string('type', 10);
            // The choices of a "select" field.
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        // Values live with the record, keyed by field id; a deleted field's value is simply ignored.
        foreach (self::RECORDS as $records) {
            Schema::table($records, fn (Blueprint $table) => $table->json('custom_values')->nullable());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::RECORDS as $records) {
            Schema::table($records, fn (Blueprint $table) => $table->dropColumn('custom_values'));
        }

        Schema::dropIfExists('custom_fields');
    }
};
