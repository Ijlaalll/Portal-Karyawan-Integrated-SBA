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
        Schema::create('sik_approval_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exit_permit_id')->constrained('exit_permits')->cascadeOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status'); // APPROVED, REJECTED, REVOKED, CLOSED_MANUALLY
            $table->text('note')->nullable();
            $table->timestamp('actioned_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sik_approval_histories');
    }
};
