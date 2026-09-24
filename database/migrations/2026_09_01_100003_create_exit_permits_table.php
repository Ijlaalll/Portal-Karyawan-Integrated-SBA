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
        Schema::create('exit_permits', function (Blueprint $table) {
            $table->id();
            $table->string('sik_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Snapshot columns at the time of creation
            $table->string('department');
            $table->string('position')->nullable();
            $table->string('employee_id')->nullable();

            // Request details
            $table->string('purpose_type')->default('dinas'); // 'dinas', 'pribadi'
            $table->string('purpose');
            $table->text('purpose_detail')->nullable();
            $table->date('permit_date');
            $table->string('expected_departure_time');
            $table->string('expected_return_time');
            $table->string('vehicle_type')->nullable(); // 'none', 'company_vehicle', 'private_vehicle'
            $table->string('vehicle_details')->nullable();

            // Lifecycle status
            $table->string('status')->default('draft')->index(); // draft, pending_approval, approved, rejected, revoked, outside, overdue, returned, closed_manually

            // Approval details
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_note')->nullable();
            $table->text('rejection_reason')->nullable();

            // QR Security Token (generated ONLY upon approval)
            $table->string('qr_token', 64)->nullable()->unique();

            // Security Gate Scan Info
            $table->timestamp('actual_departure_at')->nullable();
            $table->timestamp('actual_return_at')->nullable();
            $table->foreignId('exit_scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('return_scanned_by')->nullable()->constrained('users')->nullOnDelete();

            // Revocation Info
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->text('revoke_reason')->nullable();

            // Manual Close Info (for emergency / unreturned cases)
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->text('close_reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exit_permits');
    }
};
