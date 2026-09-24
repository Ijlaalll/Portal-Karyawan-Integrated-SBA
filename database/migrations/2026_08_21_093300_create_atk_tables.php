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
        // 1. Categories Table
        Schema::create('atk_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Items Table
        Schema::create('atk_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('atk_categories')->restrictOnDelete();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Variants Table
        Schema::create('atk_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('atk_items')->restrictOnDelete();
            $table->string('name')->index();
            $table->string('sku')->unique()->index();
            $table->string('unit');
            $table->integer('stock')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Requests Table
        Schema::create('atk_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique()->index();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['diajukan', 'disetujui', 'diproses', 'selesai', 'ditolak', 'dibatalkan'])->default('diajukan')->index();
            $table->text('note')->nullable();
            
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
        });

        // 5. Request Items Table
        Schema::create('atk_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('atk_requests')->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('atk_variants')->restrictOnDelete();
            $table->integer('quantity');
            $table->timestamps();
        });

        // 6. Stock Movements Table
        Schema::create('atk_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('atk_variants')->restrictOnDelete();
            $table->enum('type', ['adjustment', 'request', 'restock'])->index();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->integer('previous_stock');
            $table->integer('quantity');
            $table->integer('new_stock');
            $table->text('reason')->nullable();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        // 7. Request Histories Table
        Schema::create('atk_request_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('atk_requests')->cascadeOnDelete();
            $table->string('status')->index();
            $table->text('notes')->nullable();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atk_request_histories');
        Schema::dropIfExists('atk_stock_movements');
        Schema::dropIfExists('atk_request_items');
        Schema::dropIfExists('atk_requests');
        Schema::dropIfExists('atk_variants');
        Schema::dropIfExists('atk_items');
        Schema::dropIfExists('atk_categories');
    }
};
