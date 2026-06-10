<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->string('qr_code')->index(); // The QR code string that was scanned
            $table->enum('action', ['generate', 'scan'])->default('scan');
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('location')->nullable(); // Optional: location context of scan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_logs');
    }
};
