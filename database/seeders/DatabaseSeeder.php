<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Room;
use App\Models\Facility;
use App\Models\Booking;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic dummy data.
     */
    public function run(): void
    {
        // 1. Create Users
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $user1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $user1->modules()->create(['module' => 'meeting_room']);

        $user2 = User::create([
            'name' => 'Siti Rahma',
            'email' => 'siti@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $user2->modules()->create(['module' => 'meeting_room']);

        $user3 = User::create([
            'name' => 'Andi Wijaya',
            'email' => 'andi@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $user3->modules()->create(['module' => 'meeting_room']);

        // Toilet Inspection Users
        $petugas = User::create([
            'name' => 'Petugas Toilet',
            'email' => 'petugas@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $petugas->modules()->create(['module' => 'toilet_inspection']);

        $spv = User::create([
            'name' => 'Supervisor Toilet',
            'email' => 'spv@example.com',
            'password' => Hash::make('password'),
            'role' => 'spv',
        ]);
        $spv->modules()->create(['module' => 'toilet_inspection']);

        // 2. Create Facilities
        $ac = Facility::create(['name' => 'Air Conditioner', 'description' => 'AC pendingin ruangan']);
        $projector = Facility::create(['name' => 'Projector', 'description' => 'Proyektor HD dengan kabel HDMI']);
        $whiteboard = Facility::create(['name' => 'Whiteboard', 'description' => 'Papan tulis spidol & penghapus']);
        $tv = Facility::create(['name' => 'Smart TV 55 Inch', 'description' => 'Layar display presentasi']);
        $vicon = Facility::create(['name' => 'Video Conference System', 'description' => 'Kamera dan mikrofon konferensi']);
        $wifi = Facility::create(['name' => 'High-Speed WiFi', 'description' => 'Koneksi internet dedicated']);
        $sound = Facility::create(['name' => 'Sound System', 'description' => 'Speaker dan mic nirkabel']);

        // 3. Create Rooms
        $roomA = Room::create([
            'name' => 'Room A - Innovation',
            'location' => 'Lantai 1',
            'capacity' => 10,
            'description' => 'Ruang meeting skala kecil cocok untuk diskusi tim.',
            'status' => 'active',
        ]);
        $roomA->facilities()->attach([$ac->id, $projector->id, $whiteboard->id, $wifi->id]);

        $roomB = Room::create([
            'name' => 'Room B - Executive',
            'location' => 'Lantai 2',
            'capacity' => 15,
            'description' => 'Ruang meeting eksekutif dengan fasilitas lengkap.',
            'status' => 'active',
        ]);
        $roomB->facilities()->attach([$ac->id, $tv->id, $vicon->id, $wifi->id]);

        $roomC = Room::create([
            'name' => 'Room C - Auditorium',
            'location' => 'Lantai 2',
            'capacity' => 25,
            'description' => 'Ruang presentasi besar untuk seminar internal.',
            'status' => 'active',
        ]);
        $roomC->facilities()->attach([$ac->id, $projector->id, $sound->id, $wifi->id, $whiteboard->id]);

        $roomD = Room::create([
            'name' => 'Room D - Focus',
            'location' => 'Lantai 1',
            'capacity' => 6,
            'description' => 'Ruang kerja fokus dan sprint rapat singkat.',
            'status' => 'active',
        ]);
        $roomD->facilities()->attach([$ac->id, $whiteboard->id, $wifi->id]);

        $roomE = Room::create([
            'name' => 'Room E - Maintenance',
            'location' => 'Lantai 3',
            'capacity' => 12,
            'description' => 'Ruangan sedang dalam pemeliharaan.',
            'status' => 'inactive',
        ]);
        $roomE->facilities()->attach([$ac->id]);

        // 4. Create Sample Bookings
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        // Pending Booking
        Booking::create([
            'user_id' => $user1->id,
            'room_id' => $roomA->id,
            'title' => 'Weekly Team Sprint',
            'description' => 'Pembahasan progress modul mingguan.',
            'booking_date' => $tomorrow,
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'status' => 'pending',
        ]);

        // Approved Booking
        Booking::create([
            'user_id' => $user2->id,
            'room_id' => $roomB->id,
            'approved_by' => $admin->id,
            'title' => 'Client Demo Presentation',
            'description' => 'Demonstrasi sistem aplikasi ke klien.',
            'booking_date' => $tomorrow,
            'start_time' => '13:00:00',
            'end_time' => '15:00:00',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Rejected Booking
        Booking::create([
            'user_id' => $user3->id,
            'room_id' => $roomA->id,
            'approved_by' => $admin->id,
            'title' => 'Department Q3 Planning',
            'description' => 'Perencanaan kegiatan kuartal 3.',
            'booking_date' => $tomorrow,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'rejected',
            'rejection_reason' => 'Jadwal bertabrakan dengan agenda prioritas manajemen.',
        ]);

        // Cancelled Booking
        Booking::create([
            'user_id' => $user1->id,
            'room_id' => $roomC->id,
            'title' => 'Internal Workshop',
            'description' => 'Pelatihan internal karyawan baru.',
            'booking_date' => $today,
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'status' => 'cancelled',
        ]);

        // Completed Booking (History)
        Booking::create([
            'user_id' => $user2->id,
            'room_id' => $roomA->id,
            'approved_by' => $admin->id,
            'title' => 'Morning Briefing',
            'description' => 'Briefing harian tim operasional.',
            'booking_date' => date('Y-m-d', strtotime('-1 day')),
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'status' => 'completed',
            'approved_at' => now()->subDay(),
        ]);

        // 5. Toilet Inspection Seeding
        \App\Models\ToiletLocation::firstOrCreate([
            'building' => 'Gedung A',
            'floor' => 'Lantai 1',
            'area' => 'Area Office Divisi Finance',
            'toilet_name' => 'Toilet Utama Pria',
            'gender' => 'pria',
        ], [
            'is_active' => true,
        ]);

        \App\Models\ToiletLocation::firstOrCreate([
            'building' => 'Gedung A',
            'floor' => 'Lantai 1',
            'area' => 'Area Office Divisi Finance',
            'toilet_name' => 'Toilet Utama Wanita',
            'gender' => 'wanita',
        ], [
            'is_active' => true,
        ]);

        $checklistItems = [
            ['name' => 'Bau', 'description' => 'Toilet bersih dan bebas bau tidak sedap'],
            ['name' => 'Lantai', 'description' => 'Lantai toilet bersih, kering, dan tidak licin'],
            ['name' => 'Dinding', 'description' => 'Dinding toilet bersih dan tidak terdapat noda atau kerusakan'],
            ['name' => 'Tempat Sampah', 'description' => 'Tempat sampah tersedia dan dalam kondisi bersih'],
            ['name' => 'Sabun', 'description' => 'Sabun cuci tangan tersedia'],
            ['name' => 'Tisu', 'description' => 'Tisu tersedia dan mencukupi'],
            ['name' => 'Cermin', 'description' => 'Cermin bersih dan dalam kondisi baik'],
            ['name' => 'Wastafel', 'description' => 'Wastafel bersih dan air mengalir dengan lancar'],
            ['name' => 'Kloset', 'description' => 'Kloset bersih dan berfungsi dengan baik'],
            ['name' => 'Saringan Pembuangan Air', 'description' => 'Saringan pembuangan air bersih dan tidak tersumbat'],
            ['name' => 'Bak / Ember', 'description' => 'Bak atau ember tersedia dan dalam kondisi bersih'],
            ['name' => 'Lampu', 'description' => 'Lampu menyala dengan baik'],
            ['name' => 'Exhaust Fan', 'description' => 'Exhaust fan berfungsi dengan baik'],
            ['name' => 'Air Bersih', 'description' => 'Air bersih tersedia dan dapat digunakan'],
        ];

        foreach ($checklistItems as $item) {
            \App\Models\ChecklistItem::firstOrCreate(['name' => $item['name']], [
                'description' => $item['description'],
                'is_active' => true,
            ]);
        }
    }
}
