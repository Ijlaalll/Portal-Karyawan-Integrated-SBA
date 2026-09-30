<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\UserModule;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Baseline operational modules for all regular users in SBA Portal
        $baselineModules = ['meeting_room', 'atk', 'sik', 'vehicle'];

        $users = User::where('role', '!=', 'admin')->get();

        foreach ($users as $user) {
            foreach ($baselineModules as $mod) {
                UserModule::firstOrCreate([
                    'user_id' => $user->id,
                    'module' => $mod,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep modules intact on rollback to prevent accidental loss
    }
};
