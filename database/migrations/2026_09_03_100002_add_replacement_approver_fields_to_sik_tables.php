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
        Schema::table('exit_permits', function (Blueprint $table) {
            $table->boolean('is_replacement_approver')->default(false)->after('approver_id');
        });

        Schema::table('sik_approval_histories', function (Blueprint $table) {
            $table->string('approver_type')->default('default')->after('approver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sik_approval_histories', function (Blueprint $table) {
            $table->dropColumn('approver_type');
        });

        Schema::table('exit_permits', function (Blueprint $table) {
            $table->dropColumn('is_replacement_approver');
        });
    }
};
