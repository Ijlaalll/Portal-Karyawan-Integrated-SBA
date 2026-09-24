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
        Schema::table('atk_variants', function (Blueprint $table) {
            $table->decimal('purchase_price', 15, 2)->default(0.00)->after('minimum_stock');
        });

        Schema::table('atk_request_items', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)->default(0.00)->after('quantity');
            $table->decimal('subtotal', 15, 2)->default(0.00)->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atk_variants', function (Blueprint $table) {
            $table->dropColumn('purchase_price');
        });

        Schema::table('atk_request_items', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'subtotal']);
        });
    }
};
