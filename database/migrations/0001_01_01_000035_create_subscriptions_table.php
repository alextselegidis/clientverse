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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('currency', 3);
            // Net price per period; tax is added on top, as on invoices.
            $table->decimal('amount', 12, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->enum('interval', ['week', 'month', 'year'])->default('month');
            $table->enum('status', ['draft', 'pending', 'active', 'past_due', 'canceled'])->default('draft');
            $table->string('stripe_subscription_id')->nullable()->unique();
            $table->string('stripe_customer_id')->nullable();
            $table->date('current_period_end')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->timestamps();
        });

        // Every period Stripe collects becomes a paid invoice linked back here.
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('subscription_id')->nullable()->after('recurring_parent_id')
                ->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', fn (Blueprint $table) => $table->dropConstrainedForeignId('subscription_id'));
        Schema::dropIfExists('subscriptions');
    }
};
