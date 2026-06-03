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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('label_code')->unique()->nullable(); // e.g. INV-KOM-012
            $table->string('name');
            $table->string('condition')->default('baik'); // baik, kurang_baik, rusak_berat
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->decimal('price', 15, 2)->default(0);
            $table->date('purchase_date')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_labeled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
