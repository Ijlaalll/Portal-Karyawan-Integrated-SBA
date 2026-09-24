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
        // 1. Add status and name columns to cost_centers table
        Schema::table('cost_centers', function (Blueprint $table) {
            $table->string('name')->after('cost_center_code')->default('');
            $table->string('status')->after('company_code')->default('active')->index();
        });

        // 2. Add price_per_hour and is_pricing_active to rooms table
        Schema::table('rooms', function (Blueprint $table) {
            $table->decimal('price_per_hour', 15, 2)->default(0.00)->after('description');
            $table->boolean('is_pricing_active')->default(true)->after('price_per_hour');
        });

        // 3. Add department and cost_center_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('department')->nullable()->after('role');
            $table->foreignId('cost_center_id')->nullable()->after('department')->constrained('cost_centers')->nullOnDelete();
        });

        // 4. Create cost_center_transactions table
        Schema::create('cost_center_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_center_id')->constrained('cost_centers')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('activity_type')->index(); // ROOM_BOOKING, ATK_USAGE
            $table->unsignedBigInteger('reference_id')->index(); // ID of Booking or AtkRequest
            $table->unsignedBigInteger('reference_item_id')->default(0)->index(); // 0 for room booking, AtkRequestItem ID for ATK
            $table->string('description');
            $table->string('item_name'); // Room Name or Atk Variant Name
            $table->decimal('quantity', 15, 2); // Hours or units count
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_amount', 15, 2); // quantity * unit_price (negative for reversal)
            $table->string('status')->default('posted')->index(); // posted, reversed
            $table->timestamps();

            // To prevent duplicate cost center transactions for the same source activity & item
            $table->unique(['activity_type', 'reference_id', 'reference_item_id', 'status'], 'cc_tx_unique_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_center_transactions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['cost_center_id']);
            $table->dropColumn(['department', 'cost_center_id']);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['price_per_hour', 'is_pricing_active']);
        });

        Schema::table('cost_centers', function (Blueprint $table) {
            $table->dropColumn(['name', 'status']);
        });
    }
};
