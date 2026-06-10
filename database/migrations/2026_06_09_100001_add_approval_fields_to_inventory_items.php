<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            // Approval workflow fields
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->after('status');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');

            // Category: BHP or Inventaris
            $table->enum('category', ['inventaris', 'bhp'])->default('inventaris')->after('name');

            // Description for richer data
            $table->text('description')->nullable()->after('category');

            // Brand / Merk
            $table->string('brand')->nullable()->after('description');

            // Year of acquisition
            $table->year('acquisition_year')->nullable()->after('purchase_date');

            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'approval_status', 'approved_by', 'approved_at', 'rejection_reason',
                'category', 'description', 'brand', 'acquisition_year',
            ]);
        });
    }
};
