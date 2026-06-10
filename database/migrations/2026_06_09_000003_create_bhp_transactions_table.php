<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bhp_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bhp_item_id')->constrained('bhp_items')->cascadeOnDelete();
            $table->enum('type', ['in', 'out'])->default('out'); // stok masuk / stok keluar
            $table->integer('quantity');
            $table->integer('stock_before')->default(0);
            $table->integer('stock_after')->default(0);
            $table->string('description')->nullable();
            $table->string('batch_id')->nullable()->index(); // groups bulk transactions together
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bhp_transactions');
    }
};
