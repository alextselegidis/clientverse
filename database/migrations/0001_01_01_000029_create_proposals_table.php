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
        // A proposal is a priced pitch: an estimate with a subject and a free-text body,
        // usually tied to a sale. Once accepted it can become an estimate.
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('estimate_id')->nullable()->constrained()->onDelete('set null');
            $table->string('number')->nullable()->unique();
            $table->string('subject');
            $table->longText('content')->nullable();
            $table->enum('status', ['draft', 'sent', 'accepted', 'declined'])->default('draft');
            $table->date('issue_date')->nullable();
            $table->date('open_till')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->decimal('total', 12, 2)->default(0);
            $table->longText('notes')->nullable();
            $table->longText('terms')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('proposal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_items');
        Schema::dropIfExists('proposals');
    }
};
