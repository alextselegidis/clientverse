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
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // A recurring invoice is a template: invoices:recur copies it on next_recurring_date.
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('recurring_interval', 10)->nullable()->after('due_date');
            $table->date('next_recurring_date')->nullable()->after('recurring_interval');
            $table->foreignId('recurring_parent_id')->nullable()->after('next_recurring_date')
                ->constrained('invoices')->nullOnDelete();
        });

        // When reminders:send last emailed the customer about this record.
        foreach (['invoices', 'estimates', 'proposals', 'contracts'] as $table) {
            Schema::table($table, fn (Blueprint $table) => $table->dateTime('reminded_at')->nullable());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['invoices', 'estimates', 'proposals', 'contracts'] as $table) {
            Schema::table($table, fn (Blueprint $table) => $table->dropColumn('reminded_at'));
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recurring_parent_id');
            $table->dropColumn(['recurring_interval', 'next_recurring_date']);
        });
    }
};
