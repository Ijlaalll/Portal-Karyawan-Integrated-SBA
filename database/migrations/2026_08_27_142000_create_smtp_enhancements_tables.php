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
        // 1. Add connection status columns to smtp_settings
        Schema::table('smtp_settings', function (Blueprint $table) {
            $table->string('status')->default('not_tested')->after('from_name');
            $table->timestamp('last_success_at')->nullable()->after('status');
            $table->timestamp('last_failure_at')->nullable()->after('last_success_at');
            $table->text('last_error_message')->nullable()->after('last_failure_at');
        });

        // 2. Create smtp_histories table
        Schema::create('smtp_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('host');
            $table->integer('port');
            $table->string('encryption')->nullable();
            $table->string('username');
            $table->string('from_address');
            $table->string('from_name');
            $table->string('action'); // e.g., 'created', 'updated'
            $table->timestamp('created_at')->useCurrent();
        });

        // 3. Create email_logs table
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient');
            $table->string('subject');
            $table->string('email_type'); // e.g., 'test_email', 'reservation_created', etc.
            $table->string('status'); // e.g., 'sent', 'failed'
            $table->text('error_detail')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 4. Create email_templates table
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_key')->unique();
            $table->string('name');
            $table->string('subject');
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Create email_notification_settings table
        Schema::create('email_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique();
            $table->string('event_name');
            $table->boolean('is_active')->default(true);
            $table->integer('reminder_time')->default(15); // in minutes
            $table->timestamps();
        });

        // 6. Seed initial templates
        $now = now();
        DB::table('email_templates')->insert([
            [
                'template_key' => 'reservasi_baru',
                'name' => 'Reservasi Baru',
                'subject' => 'Reservasi Ruang Meeting Baru #{reservation_id}',
                'body' => "Halo {user_name},\n\nReservasi Anda untuk ruangan {room_name} pada tanggal {date} pukul {start_time} - {end_time} telah berhasil dibuat.\n\nStatus saat ini: {status}.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'reservasi_disetujui',
                'name' => 'Reservasi Disetujui',
                'subject' => 'Reservasi Ruang Meeting Disetujui #{reservation_id}',
                'body' => "Halo {user_name},\n\nReservasi Anda untuk ruangan {room_name} pada tanggal {date} pukul {start_time} - {end_time} telah DISETUJUI oleh General Affairs (GA).\n\nSilakan gunakan ruangan sesuai waktu tersebut.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'reservasi_ditolak',
                'name' => 'Reservasi Ditolak',
                'subject' => 'Reservasi Ruang Meeting Ditolak #{reservation_id}',
                'body' => "Halo {user_name},\n\nKami menginformasikan bahwa reservasi Anda untuk ruangan {room_name} pada tanggal {date} pukul {start_time} - {end_time} telah DITOLAK.\n\nAlasan penolakan: {cancellation_note}.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'reservasi_dibatalkan',
                'name' => 'Reservasi Dibatalkan',
                'subject' => 'Reservasi Ruang Meeting Dibatalkan #{reservation_id}',
                'body' => "Halo {user_name},\n\nReservasi Anda untuk ruangan {room_name} pada tanggal {date} pukul {start_time} - {end_time} telah DIBATALKAN.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'pengingat_meeting',
                'name' => 'Pengingat Meeting',
                'subject' => 'Pengingat: Meeting Anda di {room_name} akan segera dimulai',
                'body' => "Halo {user_name},\n\nIni adalah pengingat bahwa meeting Anda di ruangan {room_name} akan dimulai dalam waktu {reminder_time} menit lagi.\n\nWaktu: {start_time} - {end_time} WIB.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'akun_dibuat',
                'name' => 'Akun Dibuat',
                'subject' => 'Akun SBA Anda telah berhasil dibuat',
                'body' => "Halo {user_name},\n\nAkun Anda untuk sistem SBA telah berhasil dibuat oleh Administrator.\n\nUsername/Email: {user_email}\nPeran: {role}\n\nSilakan login menggunakan kredensial Anda.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'reset_password',
                'name' => 'Reset Password',
                'subject' => 'Permintaan Reset Password Akun SBA',
                'body' => "Halo {user_name},\n\nKami menerima permintaan untuk merestart password akun Anda.\n\nSilakan gunakan tautan berikut untuk memperbarui password Anda: {reset_link}\n\nJika Anda tidak meminta ini, abaikan email ini.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ]);

        // 7. Seed initial notification settings
        DB::table('email_notification_settings')->insert([
            [
                'event_key' => 'reservasi_baru',
                'event_name' => 'Reservasi Baru',
                'is_active' => true,
                'reminder_time' => 15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'event_key' => 'reservasi_disetujui',
                'event_name' => 'Reservasi Disetujui',
                'is_active' => true,
                'reminder_time' => 15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'event_key' => 'reservasi_ditolak',
                'event_name' => 'Reservasi Ditolak',
                'is_active' => true,
                'reminder_time' => 15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'event_key' => 'reservasi_dibatalkan',
                'event_name' => 'Reservasi Dibatalkan',
                'is_active' => true,
                'reminder_time' => 15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'event_key' => 'pengingat_meeting',
                'event_name' => 'Pengingat Meeting',
                'is_active' => true,
                'reminder_time' => 30, // Default 30 minutes reminder
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_notification_settings');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('smtp_histories');
        
        Schema::table('smtp_settings', function (Blueprint $table) {
            $table->dropColumn(['status', 'last_success_at', 'last_failure_at', 'last_error_message']);
        });
    }
};
