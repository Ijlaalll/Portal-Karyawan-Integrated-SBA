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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('ga_status')->nullable();
            $table->string('it_status')->nullable();
            
            $table->foreignId('prepared_by_ga')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('prepared_by_it')->nullable()->constrained('users')->nullOnDelete();
            
            $table->boolean('ga_ready_popup_acknowledged')->default(false);
            $table->boolean('it_ready_popup_acknowledged')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['prepared_by_ga']);
                $table->dropForeign(['prepared_by_it']);
            }
            $table->dropColumn([
                'ga_status',
                'it_status',
                'prepared_by_ga',
                'prepared_by_it',
                'ga_ready_popup_acknowledged',
                'it_ready_popup_acknowledged',
            ]);
        });
    }
};
