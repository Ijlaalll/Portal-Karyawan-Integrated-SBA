<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Make room_id nullable in bookings table
        Schema::table('bookings', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['room_id']);
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->change();
            
            if (DB::getDriverName() !== 'sqlite') {
                $table->foreign('room_id')->references('id')->on('rooms')->restrictOnDelete();
            }
        });

        // 2. Add new columns
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('meeting_type')->default('offline');
            $table->integer('participant_count')->nullable();
            $table->json('additional_requirements')->nullable();
            $table->string('online_platform')->nullable();
            $table->string('online_meeting_link')->nullable();
            $table->string('preparation_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'meeting_type',
                'participant_count',
                'additional_requirements',
                'online_platform',
                'online_meeting_link',
                'preparation_status',
            ]);
            
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['room_id']);
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('room_id')->nullable(false)->change();
            
            if (DB::getDriverName() !== 'sqlite') {
                $table->foreign('room_id')->references('id')->on('rooms')->restrictOnDelete();
            }
        });
    }
};
