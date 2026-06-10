<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            // Room-based category
            $table->foreignId('item_type_id')
                ->nullable()
                ->after('room_id')
                ->constrained('item_types')
                ->nullOnDelete();

            // Extended condition (was: baik, kurang_baik, rusak_berat)
            $table->string('condition')->default('baik')->change();
            // New valid values: baik, rusak_ringan, rusak_berat, tidak_layak

            // QR Code fields
            $table->text('qr_internal')->nullable()->after('is_labeled'); // base64 PNG or path
            $table->string('qr_kampus')->nullable()->after('qr_internal');  // uploaded campus QR

            // Active/inactive status (auto-count based on this)
            $table->enum('status', ['active', 'inactive'])->default('active')->after('qr_kampus');

            // Replacement chain
            $table->unsignedBigInteger('replaced_from')->nullable()->after('status');
            $table->unsignedBigInteger('replaced_by')->nullable()->after('replaced_from');

            $table->foreign('replaced_from')->references('id')->on('inventory_items')->nullOnDelete();
            $table->foreign('replaced_by')->references('id')->on('inventory_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropForeign(['item_type_id']);
            $table->dropForeign(['replaced_from']);
            $table->dropForeign(['replaced_by']);
            $table->dropColumn(['item_type_id', 'qr_internal', 'qr_kampus', 'status', 'replaced_from', 'replaced_by']);
        });
    }
};
