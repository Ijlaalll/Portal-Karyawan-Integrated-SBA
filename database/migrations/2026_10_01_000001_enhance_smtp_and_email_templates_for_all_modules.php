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
        // 1. Support Port 25 without password (internal unauthenticated relay)
        Schema::table('smtp_settings', function (Blueprint $table) {
            $table->string('username')->nullable()->change();
            $table->text('password')->nullable()->change();
        });

        Schema::table('smtp_histories', function (Blueprint $table) {
            $table->string('username')->nullable()->change();
        });

        $now = now();

        // 2. New Email Templates across ALL modules
        $templates = [
            // --- ROOM MEETING (HYBRID & GA ADMIN) ---
            [
                'template_key' => 'meeting_hybrid_persiapan_ga',
                'name' => 'Persiapan Ruangan Hybrid (GA)',
                'subject' => '[Tugas GA] Persiapan Ruangan Meeting Hybrid: {title}',
                'body' => "Halo Tim GA,\n\nAda jadwal meeting hybrid baru yang memerlukan persiapan ruangan fisik:\n\nJudul: {title}\nRuangan: {room_name}\nTanggal: {date}\nWaktu: {start_time} - {end_time} WIB\nPemohon: {user_name} ({user_department})\nCatatan / Kebutuhan Tambahan: {additional_requirements}\n\nSilakan lakukan persiapan tata ruang, kebersihan, dan perlengkapan ruangan sebelum meeting dimulai.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'meeting_hybrid_persiapan_it',
                'name' => 'Persiapan Teknis Hybrid (IT)',
                'subject' => '[Tugas IT] Persiapan Teknis & Vicon Meeting Hybrid: {title}',
                'body' => "Halo Tim IT,\n\nAda jadwal meeting hybrid baru yang memerlukan dukungan teknis dan video conference:\n\nJudul: {title}\nRuangan: {room_name}\nTanggal: {date}\nWaktu: {start_time} - {end_time} WIB\nPemohon: {user_name} ({user_department})\nPlatform: Microsoft Teams / Video Conference\nKebutuhan Tambahan: {additional_requirements}\n\nSilakan lakukan pengecekan mikrofon, layar display/proyektor, kamera vicon, dan koneksi internet di ruangan tersebut.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'reservasi_baru_admin',
                'name' => 'Notifikasi Reservasi Masuk (Admin GA)',
                'subject' => 'Notifikasi Reservasi Ruangan Baru #{reservation_id} - {room_name}',
                'body' => "Halo Admin GA,\n\nReservasi ruangan baru telah dibuat oleh {user_name} ({user_department}):\n\nRuangan: {room_name}\nJudul: {title}\nTanggal: {date}\nWaktu: {start_time} - {end_time} WIB\nTipe: {meeting_type}\nJumlah Peserta: {participant_count} orang\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // --- ATK (ALAT TULIS KANTOR) ---
            [
                'template_key' => 'atk_request_submitted_requester',
                'name' => 'Pengajuan ATK Diterima (Pemohon)',
                'subject' => 'Konfirmasi Pengajuan Permintaan ATK #{request_number}',
                'body' => "Halo {user_name},\n\nPermintaan ATK Anda nomor referensi #{request_number} telah berhasil diajukan dan sedang menunggu verifikasi Tim GA Logistik.\n\nTanggal: {date}\nCatatan: {note}\nJumlah Item: {item_count} macam barang\n\nAnda akan menerima pemberitahuan email berikutnya setelah permintaan diproses.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'atk_request_submitted_ga',
                'name' => 'Pengajuan ATK Masuk (Tim GA)',
                'subject' => '[ATK Baru] Permintaan ATK #{request_number} dari {user_name}',
                'body' => "Halo Tim GA Logistik,\n\nAda pengajuan permintaan ATK baru yang menunggu peninjauan Anda:\n\nNomor Permintaan: #{request_number}\nPemohon: {user_name} ({user_department})\nTanggal: {date}\nCatatan: {note}\nRincian Item:\n{items_detail}\n\nSilakan buka Portal SBA untuk meninjau dan memproses permintaan ini.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'atk_request_approved',
                'name' => 'Permintaan ATK Disetujui (Pemohon)',
                'subject' => 'Permintaan ATK #{request_number} Disetujui',
                'body' => "Halo {user_name},\n\nPermintaan ATK Anda nomor #{request_number} telah DISETUJUI oleh Tim General Affairs.\n\nBarang sedang dipersiapkan oleh petugas. Anda akan menerima notifikasi saat barang siap diambil.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'atk_request_rejected',
                'name' => 'Permintaan ATK Ditolak (Pemohon)',
                'subject' => 'Permintaan ATK #{request_number} Ditolak',
                'body' => "Halo {user_name},\n\nPermintaan ATK Anda nomor #{request_number} DITOLAK oleh General Affairs.\n\nAlasan Penolakan: {rejection_reason}\n\nSilakan hubungi bagian logistik GA jika membutuhkan informasi lebih lanjut.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'atk_request_ready',
                'name' => 'Barang ATK Siap Diambil (Pemohon)',
                'subject' => 'Barang ATK #{request_number} Siap Diambil di Ruang GA',
                'body' => "Halo {user_name},\n\nBarang ATK untuk permohonan #{request_number} telah selesai diproses dan SIAP DIAMBIL.\n\nLokasi Pengambilan: Ruang Logistik / General Affairs (GA) SBA\nNomor Referensi: #{request_number}\n\nHarap mengambil barang tepat waktu.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // --- SIK (SURAT IZIN KELUAR) ---
            [
                'template_key' => 'sik_submitted_requester',
                'name' => 'Pengajuan SIK Diterima (Pemohon)',
                'subject' => 'Pengajuan SIK #{sik_number} Berhasil Diajukan',
                'body' => "Halo {user_name},\n\nPengajuan Surat Izin Keluar (SIK) nomor #{sik_number} telah berhasil dikirimkan ke atasan ({approver_name}).\n\nTanggal Izin: {permit_date}\nRencana Waktu: {departure_time} - {return_time} WIB\nJenis Keperluan: {purpose_type}\nKeperluan: {purpose}\n\nStatus saat ini: Menunggu Persetujuan HOD / Atasan.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'sik_submitted_approver',
                'name' => 'Permohonan Persetujuan SIK (Approver/HOD)',
                'subject' => '[Approval SIK] Permohonan Izin Keluar #{sik_number} dari {user_name}',
                'body' => "Halo {approver_name},\n\nKaryawan berikut mengajukan permohonan izin keluar kantor dan memerlukan persetujuan Anda:\n\nNomor SIK: #{sik_number}\nNama: {user_name} ({user_department} / {user_position})\nTanggal: {permit_date}\nWaktu: {departure_time} - {return_time} WIB\nJenis Keperluan: {purpose_type}\nKeperluan: {purpose}\nDetail: {purpose_detail}\nKendaraan: {vehicle_info}\n\nSilakan buka Portal Karyawan SBA untuk memberikan persetujuan atau penolakan.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'sik_approved',
                'name' => 'SIK Disetujui (Pemohon)',
                'subject' => 'Surat Izin Keluar (SIK) #{sik_number} Disetujui',
                'body' => "Halo {user_name},\n\nPengajuan SIK nomor #{sik_number} telah DISETUJUI oleh {approver_name}.\n\nTanggal: {permit_date}\nWaktu: {departure_time} - {return_time} WIB\nKeperluan: {purpose}\nCatatan: {approval_note}\n\nQR Code izin keluar resmi telah aktif. Silakan buka aplikasi Portal SBA dan tunjukkan QR Code ke petugas Security saat melewati pos pemeriksaan keluar dan masuk kembali.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'sik_rejected',
                'name' => 'SIK Ditolak (Pemohon)',
                'subject' => 'Surat Izin Keluar (SIK) #{sik_number} Ditolak',
                'body' => "Halo {user_name},\n\nPengajuan SIK nomor #{sik_number} DITOLAK oleh {approver_name}.\n\nTanggal: {permit_date}\nKeperluan: {purpose}\nAlasan Penolakan: {rejection_reason}\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'sik_approved_security',
                'name' => 'Notifikasi SIK Aktif (Pos Security)',
                'subject' => '[Security Info] Izin Keluar Sah #{sik_number} - {user_name}',
                'body' => "Halo Tim Security SBA,\n\nTelah diterbitkan izin keluar kantor resmi untuk karyawan:\n\nNomor SIK: #{sik_number}\nNama: {user_name} ({user_department})\nTanggal: {permit_date}\nWaktu Izin: {departure_time} - {return_time} WIB\nJenis: {purpose_type}\nKendaraan: {vehicle_info}\nDisetujui Oleh: {approver_name}\n\nHarap lakukan verifikasi pemindaian QR Code saat karyawan keluar dan kembali ke plant.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // --- KENDARAAN OPERASIONAL ---
            [
                'template_key' => 'vehicle_request_submitted_requester',
                'name' => 'Permohonan Kendaraan Diterima (Pemohon)',
                'subject' => 'Konfirmasi Permohonan Kendaraan Dinas #{request_number}',
                'body' => "Halo {user_name},\n\nPermohonan penggunaan kendaraan dinas nomor #{request_number} telah berhasil diajukan ke Tim General Affairs.\n\nTujuan: {destination}\nKeperluan: {purpose}\nJadwal: {start_datetime} s/d {end_datetime} WIB\nPreferensi Unit: {preferred_vehicle}\n\nPermohonan Anda sedang dalam antrean alokasi armada dan supir oleh GA.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'vehicle_request_submitted_ga',
                'name' => 'Permohonan Kendaraan Masuk (GA Fleet)',
                'subject' => '[Permohonan Mobil] Permintaan #{request_number} dari {user_name}',
                'body' => "Halo Tim GA Transport,\n\nAda permohonan kendaraan dinas baru yang membutuhkan alokasi armada:\n\nNomor: #{request_number}\nPemohon: {user_name} ({user_department})\nTujuan: {destination}\nKeperluan: {purpose}\nJadwal: {start_datetime} s/d {end_datetime} WIB\nCatatan: {notes}\n\nSilakan tentukan unit kendaraan dan supir melalui Portal SBA.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'vehicle_request_approved_requester',
                'name' => 'Permohonan Kendaraan Disetujui (Pemohon)',
                'subject' => 'Permohonan Kendaraan #{request_number} Disetujui',
                'body' => "Halo {user_name},\n\nPermohonan kendaraan dinas #{request_number} telah DISETUJUI oleh General Affairs.\n\nRincian Penugasan Armada:\n- Unit Kendaraan: {vehicle_name} ({plate_number})\n- Supir: {driver_name} ({driver_phone})\n- Jadwal: {start_datetime} s/d {end_datetime} WIB\n- Tujuan: {destination}\n\nSilakan berkoordinasi langsung dengan supir yang ditugaskan sebelum waktu keberangkatan.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'vehicle_request_approved_driver',
                'name' => 'Penugasan Perjalanan Kendaraan (Driver)',
                'subject' => '[Tugas Driver] Penugasan Mengantar Karyawan #{request_number}',
                'body' => "Halo {driver_name},\n\nAnda telah ditugaskan untuk mengantar perjalanan dinas karyawan SBA:\n\nNomor Permohonan: #{request_number}\nKaryawan: {user_name} ({user_department})\nNo. Telp Karyawan: {user_phone}\nTujuan: {destination}\nKeperluan: {purpose}\nWaktu Berangkat: {start_datetime} WIB\nRencana Kembali: {end_datetime} WIB\nUnit Kendaraan: {vehicle_name} ({plate_number})\n\nHarap mempersiapkan kondisi armada dan hadir tepat waktu.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'vehicle_request_rejected',
                'name' => 'Permohonan Kendaraan Ditolak (Pemohon)',
                'subject' => 'Permohonan Kendaraan #{request_number} Ditolak',
                'body' => "Halo {user_name},\n\nPermohonan kendaraan dinas nomor #{request_number} DITOLAK oleh General Affairs.\n\nAlasan Penolakan: {rejection_reason}\n\nSilakan hubungi bagian GA Transport jika memerlukan alternatif armada.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // --- INSPEKSI TOILET ---
            [
                'template_key' => 'toilet_inspection_submitted_inspector',
                'name' => 'Laporan Inspeksi Toilet Tercatat (Inspector)',
                'subject' => 'Laporan Inspeksi Toilet {location_name} Berhasil Tercatat',
                'body' => "Halo {inspector_name},\n\nLaporan hasil inspeksi toilet telah berhasil disimpan ke sistem:\n\nLokasi: {location_name} (Gedung {building}, Lantai {floor})\nWaktu: {inspected_at}\nStatus: {status}\nRingkasan: {summary}\nCatatan: {notes}\n\nTerima kasih atas dedikasi Anda menjaga kebersihan dan kenyamanan lingkungan SBA.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'toilet_inspection_issue_reported',
                'name' => 'Temuan Masalah Inspeksi Toilet (SPV/GA)',
                'subject' => '[Perhatian] Temuan Fasilitas Rusak/Kotor di Toilet {location_name}',
                'body' => "Halo Supervisor Kebersihan & GA,\n\nDitemukan fasilitas toilet yang TIDAK MEMENUHI STANDAR / RUSAK pada inspeksi terbaru:\n\nLokasi: {location_name} (Gedung {building}, Lantai {floor})\nPetugas: {inspector_name}\nWaktu Inspeksi: {inspected_at}\n\nDaftar Temuan Masalah:\n{issues_list}\n\nCatatan Petugas: {notes}\n\nMohon segera ditindaklanjuti untuk perbaikan fasilitas atau pembersihan ulang.\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'toilet_inspection_status_updated',
                'name' => 'Status Hasil Inspeksi Diperbarui (Inspector)',
                'subject' => 'Hasil Inspeksi Toilet di {location_name} Telah Diverifikasi',
                'body' => "Halo {inspector_name},\n\nHasil inspeksi toilet Anda di {location_name} telah diverifikasi oleh Supervisor ({spv_name}).\n\nStatus Verifikasi: {status}\nCatatan: {spv_notes}\nWaktu: {actioned_at}\n\nTerima kasih.",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($templates as $tpl) {
            DB::table('email_templates')->updateOrInsert(
                ['template_key' => $tpl['template_key']],
                $tpl
            );

            // 3. Register corresponding notification setting toggle
            DB::table('email_notification_settings')->updateOrInsert(
                ['event_key' => $tpl['template_key']],
                [
                    'event_key' => $tpl['template_key'],
                    'event_name' => $tpl['name'],
                    'is_active' => true,
                    'reminder_time' => 15,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert templates added
        $keys = [
            'meeting_hybrid_persiapan_ga',
            'meeting_hybrid_persiapan_it',
            'reservasi_baru_admin',
            'atk_request_submitted_requester',
            'atk_request_submitted_ga',
            'atk_request_approved',
            'atk_request_rejected',
            'atk_request_ready',
            'sik_submitted_requester',
            'sik_submitted_approver',
            'sik_approved',
            'sik_rejected',
            'sik_approved_security',
            'vehicle_request_submitted_requester',
            'vehicle_request_submitted_ga',
            'vehicle_request_approved_requester',
            'vehicle_request_approved_driver',
            'vehicle_request_rejected',
            'toilet_inspection_submitted_inspector',
            'toilet_inspection_issue_reported',
            'toilet_inspection_status_updated',
        ];

        DB::table('email_templates')->whereIn('template_key', $keys)->delete();
        DB::table('email_notification_settings')->whereIn('event_key', $keys)->delete();

        // Note: keeping username & password nullable is backward compatible
    }
};
