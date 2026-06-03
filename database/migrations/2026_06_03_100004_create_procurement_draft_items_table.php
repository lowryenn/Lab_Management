<?php

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
        Schema::create('procurement_draft_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_draft_id')->constrained('procurement_drafts')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->string('link')->nullable();
            $table->foreignId('replaces_inventory_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->string('review_status')->default('pending'); // pending, approved, rejected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_draft_items');
    }
};
