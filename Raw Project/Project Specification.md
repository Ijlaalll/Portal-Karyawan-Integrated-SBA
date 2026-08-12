# PROJECT SPECIFICATION V1

## Meeting Room Reservation System

Versi: 1.0
Status: Draft untuk implementasi
Platform: Web Application
Framework: Laravel 12
Database: MySQL
Frontend: Blade + HTML + CSS + JavaScript
Design Reference: Google Stitch — ReserveIt

---

# 1. PROJECT OVERVIEW

Meeting Room Reservation System adalah aplikasi web internal perusahaan yang digunakan untuk mengelola reservasi ruangan meeting.

Sistem memungkinkan karyawan/user untuk melihat daftar ruangan, melihat fasilitas dan kapasitas ruangan, melihat jadwal penggunaan ruangan melalui kalender, mencari ruangan yang tersedia berdasarkan tanggal dan waktu, serta mengajukan reservasi.

Setiap reservasi yang dibuat user tidak langsung menjadi reservasi final. Reservasi terlebih dahulu memiliki status "Menunggu" dan harus mendapatkan persetujuan dari Admin.

Sistem harus mencegah terjadinya double booking pada ruangan dan waktu yang sama.

Data ruangan, fasilitas, user, dan booking untuk tahap awal menggunakan data dummy dan dapat dikembangkan menggunakan data perusahaan setelah requirement final tersedia.

---

# 2. TUJUAN SISTEM

Sistem dibuat untuk:

1. Mempermudah karyawan melakukan reservasi ruangan meeting.
2. Menampilkan ketersediaan ruangan secara jelas.
3. Menampilkan jadwal penggunaan ruangan dalam bentuk kalender.
4. Mencegah bentrokan jadwal atau double booking.
5. Mempermudah Admin mengelola ruangan dan fasilitas.
6. Mempermudah Admin memproses permintaan reservasi.
7. Menyediakan riwayat reservasi bagi user.
8. Menyediakan pengelolaan reservasi yang lebih terstruktur dibandingkan proses manual.

---

# 3. USER ROLE

Sistem memiliki dua role utama.

## 3.1 Admin

Admin merupakan pengguna yang memiliki akses untuk mengelola sistem dan memproses reservasi.

Admin dapat:

* Login.
* Melihat dashboard admin.
* Melihat seluruh ruangan.
* Menambah ruangan.
* Mengubah data ruangan.
* Mengaktifkan/nonaktifkan ruangan.
* Mengelola fasilitas.
* Melihat seluruh user.
* Melihat seluruh booking.
* Melihat kalender seluruh ruangan.
* Melihat detail booking.
* Menyetujui booking.
* Menolak booking.
* Memberikan alasan penolakan.
* Melihat statistik reservasi.

## 3.2 User/Karyawan

User merupakan karyawan yang menggunakan sistem untuk melakukan reservasi.

User dapat:

* Login.
* Melihat dashboard.
* Melihat daftar ruangan.
* Melihat detail ruangan.
* Melihat kapasitas ruangan.
* Melihat fasilitas ruangan.
* Melihat kalender ketersediaan.
* Melihat jadwal ruangan yang telah terbooking.
* Mencari ruangan berdasarkan tanggal dan waktu.
* Membuat reservasi.
* Melihat reservasi miliknya sendiri.
* Melihat status reservasi.
* Membatalkan reservasi sesuai aturan sistem.
* Melihat detail reservasi miliknya.

User tidak dapat:

* Mengelola ruangan.
* Mengelola fasilitas.
* Mengelola user lain.
* Menyetujui booking.
* Menolak booking.
* Melihat informasi pribadi/detail meeting milik user lain.

---

# 4. MAIN USER FLOW

Alur utama reservasi:

User Login
↓
Dashboard
↓
Pilih Calendar / Find Available Room
↓
Pilih tanggal dan waktu
↓
Sistem mengecek ketersediaan
↓
Menampilkan ruangan yang tersedia
↓
User memilih ruangan
↓
User mengisi informasi meeting
↓
Submit Booking
↓
Status = Menunggu
↓
Admin memproses
↓
┌───────────────────────┐
│                       │
▼                       ▼
Disetujui              Ditolak
│                       │
▼                       ▼
Booking aktif          Booking gagal

```

---

# 5. ROOM MANAGEMENT

Setiap ruangan memiliki informasi:

- Nama ruangan.
- Lokasi.
- Kapasitas.
- Deskripsi.
- Status.
- Fasilitas.
- Foto ruangan jika diperlukan.

Contoh data dummy:

Room A
- Lokasi: Lantai 1
- Kapasitas: 10 orang
- Status: Aktif
- Fasilitas: AC, Projector, Whiteboard

Room B
- Lokasi: Lantai 2
- Kapasitas: 15 orang
- Status: Aktif
- Fasilitas: AC, TV, WiFi

Room C
- Lokasi: Lantai 2
- Kapasitas: 20 orang
- Status: Aktif
- Fasilitas: AC, Projector, Conference Camera

Data dummy hanya digunakan untuk pengembangan awal dan dapat diganti dengan data perusahaan.

---

# 6. FACILITY MANAGEMENT

Fasilitas ruangan harus dikelola sebagai data terpisah, bukan hanya disimpan sebagai string di tabel rooms.

Contoh fasilitas:

- AC
- Projector
- Whiteboard
- TV
- WiFi
- Conference Camera
- Sound System

Admin dapat:

- Menambah fasilitas.
- Mengubah fasilitas.
- Menghapus fasilitas.
- Menghubungkan fasilitas dengan ruangan.

Satu ruangan dapat memiliki banyak fasilitas dan satu fasilitas dapat digunakan oleh banyak ruangan.

Relasi:

rooms
    ↕
room_facilities
    ↕
facilities

---

# 7. ROOM STATUS

Status ruangan hanya menunjukkan apakah ruangan secara umum dapat digunakan.

Status:

- Aktif
- Tidak Aktif

Room dengan status Tidak Aktif tidak dapat digunakan untuk booking baru.

Status room berbeda dengan status booking.

Contoh:

Room A:
Status = Aktif

Tetapi:
10:00–12:00 = Terbooking

Room tetap aktif, tetapi slot tersebut tidak tersedia.

---

# 8. CALENDAR & AVAILABILITY

Calendar merupakan salah satu fitur utama sistem.

User dapat melihat jadwal seluruh ruangan.

Calendar menggunakan konsep:

Room × Date × Time

Contoh:

                08  09  10  11  12  13  14  15  16  17

Room A          AVAILABLE
                    BOOKED
                            AVAILABLE

Room B          AVAILABLE
                        BOOKED
                                AVAILABLE

Room C          AVAILABLE

User dapat mengetahui:

- Ruangan yang tersedia.
- Ruangan yang sedang terbooking.
- Waktu booking.
- Status slot.

User tidak dapat melihat:

- Nama orang yang melakukan booking.
- Judul meeting milik user lain.
- Deskripsi meeting.
- Informasi internal booking user lain.

Informasi booking user lain ditampilkan secara minimal sebagai status ketersediaan.

Contoh:

09:00–11:00
BOOKED

Bukan:

09:00–11:00
Muhammad — Meeting Direktur

---

# 9. FIND AVAILABLE ROOM

Selain calendar, sistem menyediakan fitur pencarian ruangan berdasarkan kebutuhan user.

Input:

- Tanggal.
- Waktu mulai.
- Waktu selesai.
- Kapasitas minimum.

Contoh:

Tanggal:
15 Agustus 2026

Mulai:
10:00

Selesai:
12:00

Kapasitas:
10 orang

Sistem kemudian mencari ruangan yang memenuhi seluruh kondisi.

Hasil:

Room A
- Kapasitas: 10
- Available
- AC
- Projector
- Whiteboard

Room C
- Kapasitas: 20
- Available
- AC
- Projector
- WiFi

User dapat memilih salah satu ruangan.

---

# 10. BOOKING FORM

Setelah memilih ruangan, user mengisi:

- Ruangan.
- Tanggal.
- Waktu mulai.
- Waktu selesai.
- Judul meeting.
- Deskripsi/tujuan meeting.

Contoh:

Room:
Meeting Room A

Date:
15 August 2026

Start:
10:00

End:
12:00

Meeting Title:
Weekly Team Meeting

Description:
Pembahasan progress pekerjaan tim.

---

# 11. BOOKING STATUS

Booking memiliki status:

- Menunggu
- Disetujui
- Ditolak
- Dibatalkan
- Selesai

## Menunggu

Booking telah dibuat user tetapi belum diproses Admin.

## Disetujui

Booking telah disetujui Admin dan slot ruangan dianggap terpakai.

## Ditolak

Booking ditolak Admin.

Booking dengan status Ditolak tidak lagi memblokir slot.

## Dibatalkan

Booking dibatalkan oleh user atau sistem sesuai aturan.

Booking yang dibatalkan tidak lagi memblokir slot.

## Selesai

Waktu booking telah selesai.

Booking dengan status Selesai hanya digunakan untuk histori.

---

# 12. PENDING BOOKING DAN AVAILABILITY

Booking dengan status Menunggu tetap dianggap mengambil slot sementara.

Contoh:

User A:
Room A
10:00–12:00
Status: Menunggu

User B melihat:

Room A
10:00–12:00
BOOKED / UNAVAILABLE

Tujuannya adalah mencegah dua user mengajukan reservasi pada slot yang sama ketika salah satunya masih menunggu approval.

Status yang memblokir slot:

- Menunggu
- Disetujui

Status yang tidak memblokir slot:

- Ditolak
- Dibatalkan

---

# 13. DOUBLE BOOKING PREVENTION

Sistem harus mencegah booking yang waktunya bertabrakan.

Contoh booking pertama:

Room A
10:00–12:00

Booking berikut tidak diperbolehkan:

09:00–11:00
10:00–11:00
11:00–13:00
09:00–13:00

Booking berikut diperbolehkan:

08:00–10:00
12:00–14:00

Dengan kata lain, dua booking boleh berdampingan tepat pada batas waktu:

10:00–12:00
12:00–14:00

Tidak boleh terjadi overlap.

Pengecekan availability harus dilakukan:

1. Saat user mencari ruangan.
2. Saat user membuka/mengirim form booking.
3. Tepat sebelum booking disimpan ke database.

Pengecekan terakhir tetap wajib dilakukan untuk mencegah race condition ketika dua user melakukan booking hampir bersamaan.

---

# 14. BOOKING DATE & TIME RULES

Untuk versi dummy, digunakan aturan:

- Hari booking tidak boleh berada di masa lalu.
- Start time harus lebih kecil daripada end time.
- Durasi minimum booking: 1 jam.
- Booking hanya dapat dilakukan dalam jam operasional.
- Jam operasional dummy: 08:00–17:00.
- Booking di luar jam operasional ditolak.
- Room harus berstatus Aktif.
- User harus login untuk membuat booking.

Aturan jam operasional merupakan aturan sementara dan dapat diubah setelah mentor memberikan requirement perusahaan.

---

# 15. BOOKING CANCELLATION

Untuk versi awal:

Booking Menunggu:
→ User dapat membatalkan.

Booking Disetujui:
→ User dapat membatalkan.

Booking Ditolak:
→ Tidak perlu dibatalkan.

Booking Dibatalkan:
→ Tidak dapat dibatalkan kembali.

Booking Selesai:
→ Tidak dapat dibatalkan.

Aturan pembatalan dapat diperketat kemudian apabila perusahaan memberikan kebijakan khusus.

---

# 16. ADMIN APPROVAL

Admin memiliki halaman Booking Approvals.

Admin dapat melihat:

- Nama user.
- Nama ruangan.
- Tanggal.
- Waktu.
- Judul meeting.
- Deskripsi.
- Status.
- Waktu pengajuan.

Admin memiliki dua tindakan:

Approve
Reject

Jika Reject, sistem sebaiknya menyediakan:

Rejection Reason

Contoh:

"Ruangan sedang digunakan untuk kegiatan perusahaan."

Alasan penolakan disimpan di database.

---

# 17. USER BOOKING PRIVACY

User hanya dapat melihat detail lengkap booking miliknya sendiri.

User A dapat melihat:

Booking milik User A
- Room
- Date
- Time
- Title
- Description
- Status

User A tidak dapat melihat:

Booking milik User B
- Nama User B
- Judul meeting User B
- Deskripsi User B
- Informasi internal User B

Namun User A tetap dapat mengetahui bahwa slot tersebut tidak tersedia.

---

# 18. DASHBOARD USER

Dashboard user berdasarkan desain Stitch memiliki:

- Greeting.
- Summary My Bookings.
- Pending.
- Approved.
- Upcoming Bookings.
- Upcoming booking table.
- Room overview.
- Shortcut menuju reservasi baru.

Catatan:
Desain Stitch menggunakan teks "Welcome back, Admin" pada contoh dashboard. Pada implementasi final, greeting harus bersifat dinamis:

"Welcome back, [Nama User]"

Bukan hard-coded "Admin".

---

# 19. DASHBOARD ADMIN

Admin Dashboard menampilkan:

- Total Rooms.
- Pending Approvals.
- Today's Bookings.
- Total Users.
- Recent Booking Requests.
- Shortcut ke pengelolaan room.
- Shortcut ke booking approval.

Admin dapat langsung memproses request dari dashboard atau menuju halaman approval.

---

# 20. MY BOOKINGS

User memiliki halaman My Bookings.

Data ditampilkan dalam tabel:

- Room.
- Date.
- Time.
- Status.
- Action.

Action dapat berupa:

- View Details.
- Cancel.

User hanya melihat booking miliknya sendiri.

---

# 21. ADMIN ROOM MANAGEMENT

Admin dapat:

- Melihat daftar ruangan.
- Mencari ruangan.
- Menambah ruangan.
- Mengedit ruangan.
- Mengaktifkan ruangan.
- Menonaktifkan ruangan.

Form room:

- Room Name.
- Location.
- Capacity.
- Description.
- Status.
- Facilities.
- Image (opsional).

Facilities menggunakan multiple selection.

---

# 22. USER MANAGEMENT

Admin memiliki akses melihat daftar user.

Informasi minimum:

- Name.
- Email.
- Role.

Untuk versi awal, Admin tidak harus memiliki fungsi kompleks seperti permission builder.

Role hanya:

- Admin
- User

---

# 23. NOTIFICATION

Notifikasi belum menjadi requirement wajib.

Untuk versi pertama:

Tidak perlu membuat sistem email notification atau WhatsApp notification.

Status booking dapat diketahui melalui:

- Dashboard.
- My Bookings.
- Booking detail.
- Admin approval page.

Sistem notifikasi dapat ditambahkan pada tahap pengembangan berikutnya jika mentor meminta.

---

# 24. AUTHENTICATION

Sistem menggunakan authentication Laravel.

Fitur minimum:

- Login.
- Logout.
- Password hashing.
- Session authentication.

Role-based authorization digunakan untuk membedakan akses Admin dan User.

User tidak boleh mengakses route Admin secara langsung.

Admin dapat mengakses halaman Admin.

---

# 25. DATABASE INITIAL DESIGN

Database awal:

users
rooms
facilities
room_facilities
bookings

## users

Fields:

- id
- name
- email
- password
- role
- created_at
- updated_at

## rooms

Fields:

- id
- name
- location
- capacity
- description
- status
- image
- created_at
- updated_at

## facilities

Fields:

- id
- name
- description
- created_at
- updated_at

## room_facilities

Fields:

- room_id
- facilities_id

Catatan:
room_id dan facilities_id digunakan sebagai composite key/unique pair untuk mencegah satu fasilitas yang sama terhubung dua kali ke ruangan yang sama.

## bookings

Fields:

- id
- user_id
- room_id
- title
- description
- booking_date
- start_time
- end_time
- status
- approved_by
- approved_at
- rejection_reason
- created_at
- updated_at

Catatan:
Struktur database ini merupakan initial design. ERD final akan dibuat setelah specification ini direview.

---

# 26. DATABASE RELATIONSHIPS

User:

User hasMany Bookings.

Booking:

Booking belongsTo User.
Booking belongsTo Room.
Booking optionally belongsTo Approver/Admin.

Room:

Room hasMany Bookings.
Room belongsToMany Facilities.

Facility:

Facility belongsToMany Rooms.

Relasi utama:

User
  │
  │ 1:N
  ▼
Booking
  │
  │ N:1
  ▼
Room
  │
  │ N:M
  ▼
Facility

---

# 27. AUTHORIZATION RULES

User:

- Read active rooms.
- Read room facilities.
- Read public availability.
- Create own booking.
- Read own bookings.
- Cancel own eligible bookings.

Admin:

- Full room management.
- Full facility management.
- Read all bookings.
- Approve booking.
- Reject booking.
- Read users.
- Read all room schedules.

User tidak boleh mengubah atau menghapus booking user lain.

User tidak boleh melakukan approval/rejection.

---

# 28. UI DESIGN SYSTEM

Implementasi UI mengikuti Google Stitch "Core Enterprise Reserve".

Visual direction:

- Modern Corporate.
- Clean.
- Professional.
- Enterprise internal application.
- Light theme.
- Minimal decoration.
- No gradients.
- No glassmorphism.
- No excessive shadows.
- No unnecessary illustrations.

Primary color:

#2563EB

Background:

#F9FAFB

Surface:

#FFFFFF

Border:

#E5E7EB

Primary text:

#0F172A

Secondary text:

#475569

Font:

Inter

Spacing:

8px base spacing system.

Sidebar:

240–280px.

Border radius:

4px untuk komponen umum.
8px untuk container/card.
12px hanya jika dibutuhkan.

---

# 29. UI PAGES

## User

1. Login
2. Dashboard
3. Room Availability Calendar
4. Find Available Room
5. Room Detail
6. Booking Form
7. My Bookings
8. Booking Detail

## Admin

1. Login
2. Admin Dashboard
3. Room Management
4. Facility Management
5. User Management
6. Booking Approvals
7. Booking Detail
8. Admin Calendar
9. Reports jika dibutuhkan pada tahap berikutnya

---

# 30. GOOGLE STITCH DESIGN REFERENCE

Design yang telah dibuat menggunakan Google Stitch menjadi referensi visual utama.

Existing Stitch pages:

- Login
- Employee Dashboard
- Calendar
- My Bookings
- Find Room
- Book Room
- Admin Dashboard
- Room Management
- Booking Approvals

Desain Google Stitch telah selesai dan digunakan sebagai referensi visual utama. Beberapa halaman atau fitur tambahan perlu dibuat pada tahap implementasi karena belum tersedia pada desain Stitch:

- Facility Management.
- User Management.
- Booking Detail jika belum cukup tersedia.
- Admin Calendar bila membutuhkan view khusus.

UI yang telah dibuat Stitch tidak perlu didesain ulang secara besar-besaran.

---

# 31. LARAVEL ARCHITECTURE

Aplikasi menggunakan arsitektur MVC Laravel dengan pemisahan business logic.

Alur:

Route
    ↓
Middleware
    ↓
Controller
    ↓
Service
    ↓
Model / Eloquent
    ↓
Database

Contoh:

BookingController
    ↓
BookingService
    ↓
Availability checking
    ↓
Booking Model
    ↓
Database

Business logic booking tidak sebaiknya seluruhnya diletakkan di Controller.

---

# 32. EXPECTED LARAVEL STRUCTURE

Struktur awal:

app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   └── User/
│   └── Middleware/
├── Models/
└── Services/
    └── BookingService.php

resources/
└── views/
    ├── layouts/
    ├── components/
    ├── auth/
    ├── user/
    └── admin/

database/
├── migrations/
└── seeders/

routes/
└── web.php

Business logic yang berhubungan dengan availability dan booking ditempatkan pada service layer agar mudah diuji dan dikembangkan.

---

# 33. DATA DUMMY

Development awal menggunakan data dummy.

Minimum:

Users:
- 1 Admin.
- 3–5 User.

Rooms:
- 4–6 rooms.

Facilities:
- 6–8 facilities.

Bookings:
- Beberapa booking dengan status Menunggu.
- Beberapa booking Disetujui.
- Beberapa booking Ditolak.
- Beberapa booking Dibatalkan.
- Beberapa booking historis.

Dummy data harus cukup untuk membuat Calendar dan Dashboard terlihat realistis ketika aplikasi dijalankan.

---

# 34. NON-FUNCTIONAL REQUIREMENTS

Sistem harus:

1. Responsive.
2. Memiliki validasi form.
3. Menggunakan authentication.
4. Menggunakan authorization berdasarkan role.
5. Menggunakan password hashing.
6. Tidak mengekspos informasi booking pribadi user lain.
7. Mencegah double booking.
8. Menggunakan database relational.
9. Memiliki struktur kode yang mudah dipelihara.
10. Memiliki UI yang konsisten dengan desain Stitch.

---

# 35. DEVELOPMENT PHASE

Phase 1 — Project Setup

- Laravel setup.
- Database connection.
- Authentication.
- Role middleware.
- Basic layout.

Phase 2 — Master Data

- User.
- Room.
- Facility.
- Room-facility relationship.

Phase 3 — Availability

- Calendar.
- Room schedule.
- Availability checking.
- Time conflict detection.

Phase 4 — Booking

- Create booking.
- Booking validation.
- Pending status.
- My bookings.
- Booking detail.
- Cancellation.

Phase 5 — Admin

- Admin dashboard.
- Booking approval.
- Reject reason.
- Room management.
- Facility management.
- User management.

Phase 6 — UI Integration

- Implement Google Stitch design.
- Responsive behavior.
- Component cleanup.
- Form validation.
- Error states.
- Empty states.

Phase 7 — Testing

Test:

- Login.
- Role authorization.
- Room CRUD.
- Facility CRUD.
- Booking.
- Double booking.
- Time overlap.
- Approval.
- Rejection.
- Cancellation.
- Calendar.
- User privacy.

Phase 8 — Finalization

- Bug fixing.
- Database cleanup.
- Dummy data cleanup/adjustment.
- Deployment preparation.
- Documentation.
- Project presentation.

---

# 36. IMPORTANT BUSINESS RULES

Rules berikut menjadi dasar implementasi awal:

1. Hanya authenticated user yang dapat membuat booking.
2. Hanya Admin yang dapat approve/reject booking.
3. Room tidak aktif tidak dapat dibooking.
4. Booking tanggal lampau tidak diperbolehkan.
5. Start time harus lebih kecil dari end time.
6. Durasi minimum booking adalah 1 jam.
7. Booking harus berada dalam jam operasional 08:00–17:00.
8. Booking Pending dan Approved memblokir slot.
9. Booking Rejected dan Cancelled tidak memblokir slot.
10. Booking tidak boleh overlap dengan booking aktif/pending lainnya.
11. User dapat melihat availability seluruh ruangan.
12. User tidak dapat melihat detail pribadi booking user lain.
13. User hanya dapat melihat dan mengelola booking miliknya sendiri.
14. Admin dapat melihat seluruh booking.
15. Admin dapat mengelola room dan facility.
16. Fasilitas disimpan sebagai entity terpisah.
17. Availability harus dicek kembali tepat sebelum booking disimpan.
18. Notifikasi eksternal belum diperlukan.
19. Jam operasional dan aturan booking merupakan konfigurasi sementara sampai mentor memberikan aturan perusahaan.
20. Data dummy digunakan selama tahap pengembangan.

---

# 37. CURRENT PROJECT STATUS

Requirement:
READY — Draft v1

UI Design:
READY — Google Stitch

Business Rules:
READY — Initial version

Database:
ERD & Database Design — DRAFT READY

Laravel:
NOT STARTED

Antigravity:
NOT STARTED

Production Data:
NOT AVAILABLE

Company-specific rules:
NOT AVAILABLE

---

# 38. NEXT STEP

Setelah dokumen ini disetujui, langkah berikutnya adalah:

1. Finalisasi Use Case.
2. Membuat Activity Diagram User Booking.
3. Membuat Activity Diagram Admin Approval.
4. Finalisasi ERD dan Database Design.
5. Menentukan migration.
6. Menentukan Model dan relationship Laravel.
7. Menentukan route dan authorization.
8. Menentukan BookingService dan availability logic.
9. Membuat struktur project Laravel.
10. Menyusun Master Instruction untuk Antigravity.
11. Baru memulai coding.
