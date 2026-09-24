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
        // 1. Alter users role enum to support 'spv'
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'spv', 'ga', 'it') NOT NULL DEFAULT 'user'");
        }

        // 2. Create user_modules table
        Schema::create('user_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('module'); // 'meeting_room', 'toilet_inspection'
            $table->timestamps();

            $table->unique(['user_id', 'module']);
        });

        // 3. Backfill existing non-admin users with 'meeting_room' module access
        $users = DB::table('users')->where('role', '!=', 'admin')->get();
        foreach ($users as $user) {
            DB::table('user_modules')->insert([
                'user_id' => $user->id,
                'module' => 'meeting_room',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop user_modules table
        Schema::dropIfExists('user_modules');

        // 2. Revert users role enum to admin/user
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user') NOT NULL DEFAULT 'user'");
        }
    }
};
