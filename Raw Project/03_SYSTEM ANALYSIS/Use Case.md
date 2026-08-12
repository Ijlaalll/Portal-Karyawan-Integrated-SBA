# USE CASE

## Meeting Room Reservation System

Versi: 1.0
Status: Draft Final untuk Review

---

# 1. Purpose

Dokumen ini mendefinisikan aktor, fungsi utama, hak akses, dan batasan interaksi pengguna dengan Meeting Room Reservation System.

Dokumen ini menjadi dasar untuk Activity Diagram, Authorization, dan implementasi sistem.

---

# 2. Actors

## 2.1 Admin

Admin memiliki akses untuk mengelola sistem dan memproses booking.

Admin dapat:

* Login
* Melihat Admin Dashboard
* Mengelola Room
* Mengelola Facility
* Melihat User
* Melihat seluruh Booking
* Melihat Calendar seluruh Room
* Melihat Detail Booking
* Approve Booking
* Reject Booking
* Melihat Statistik Reservasi

## 2.2 User / Karyawan

User menggunakan sistem untuk mencari dan melakukan reservasi room.

User dapat:

* Login
* Melihat Dashboard
* Melihat daftar Room
* Melihat Detail Room
* Melihat Calendar Availability
* Mencari Room Tersedia
* Membuat Booking
* Melihat Booking miliknya
* Melihat Detail Booking miliknya
* Membatalkan Booking miliknya

User tidak dapat:

* Mengelola Room
* Mengelola Facility
* Mengelola User lain
* Approve Booking
* Reject Booking
* Melihat detail pribadi booking user lain

---

# 3. Use Case List

| ID    | Use Case                    | Actor       |
| ----- | --------------------------- | ----------- |
| UC-01 | Login                       | Admin, User |
| UC-02 | View User Dashboard         | User        |
| UC-03 | View Admin Dashboard        | Admin       |
| UC-04 | View Rooms                  | User        |
| UC-05 | View Room Detail            | User        |
| UC-06 | View Calendar Availability  | User        |
| UC-07 | Find Available Room         | User        |
| UC-08 | Create Booking              | User        |
| UC-09 | View My Bookings            | User        |
| UC-10 | View My Booking Detail      | User        |
| UC-11 | Cancel Booking              | User        |
| UC-12 | Manage Rooms                | Admin       |
| UC-13 | Manage Facilities           | Admin       |
| UC-14 | View Users                  | Admin       |
| UC-15 | View All Bookings           | Admin       |
| UC-16 | View All Room Calendar      | Admin       |
| UC-17 | View Booking Detail         | Admin       |
| UC-18 | Approve Booking             | Admin       |
| UC-19 | Reject Booking              | Admin       |
| UC-20 | View Reservation Statistics | Admin       |

---

# 4. Use Case Description

## UC-01 — Login

**Actor:** Admin, User

**Purpose:**
Melakukan authentication dan mengakses sistem sesuai role.

**Input:**

* Email
* Password

**Result:**

* User berhasil login.
* User diarahkan ke dashboard sesuai role.

**Rule:**

* User harus memiliki akun terdaftar.
* Akses sistem mengikuti role `admin` atau `user`.

---

## UC-02 — View User Dashboard

**Actor:** User

**Purpose:**
Melihat ringkasan informasi reservasi milik user.

**Result:**

* Summary booking.
* Upcoming booking.
* Informasi reservasi user.

User hanya melihat data booking miliknya sendiri.

---

## UC-03 — View Admin Dashboard

**Actor:** Admin

**Purpose:**
Melihat ringkasan kondisi reservasi dan aktivitas sistem.

**Result dapat mencakup:**

* Total rooms.
* Pending approvals.
* Today's bookings.
* Total users.
* Recent booking requests.
* Statistik reservasi.

Data statistik harus berasal dari database.

---

## UC-04 — View Rooms

**Actor:** User

**Purpose:**
Melihat daftar room yang tersedia untuk digunakan.

**Information:**

* Room name
* Location
* Capacity
* Status
* Informasi singkat room

Room dengan status `inactive` tidak dapat digunakan untuk booking baru.

---

## UC-05 — View Room Detail

**Actor:** User

**Purpose:**
Melihat informasi lengkap sebuah room.

**Information:**

* Name
* Location
* Capacity
* Description
* Facilities
* Status
* Image jika tersedia

Facility diambil melalui relationship Room dan Facility.

---

## UC-06 — View Calendar Availability

**Actor:** User

**Purpose:**
Melihat jadwal penggunaan dan availability seluruh room.

Calendar menggunakan konsep:

```text
Room × Date × Time
```

User dapat mengetahui:

* Room yang tersedia.
* Room yang sedang digunakan.
* Waktu yang telah terbooking.

Untuk booking user lain, sistem hanya menampilkan informasi availability, misalnya:

```text
09:00 - 11:00
BOOKED
```

Sistem tidak menampilkan:

* Nama pemesan.
* Meeting title.
* Description.
* Informasi pribadi/internal booking lainnya.

### Booking yang memblokir slot

* `pending`
* `approved`

### Booking yang tidak memblokir slot

* `rejected`
* `cancelled`

Status `completed` digunakan sebagai histori.

---

## UC-07 — Find Available Room

**Actor:** User

**Purpose:**
Mencari room berdasarkan kebutuhan tertentu.

**Input:**

* Date
* Start time
* End time
* Minimum capacity

Room yang ditampilkan harus:

* Berstatus `active`.
* Memiliki kapasitas yang mencukupi.
* Tidak mengalami overlap dengan booking yang memblokir slot.

### Overlap Rule

```text
existing.start_time < requested.end_time
AND
existing.end_time > requested.start_time
```

Booking yang berstatus `pending` atau `approved` dianggap memblokir slot.

Booking yang tepat berdampingan pada boundary waktu tetap diperbolehkan.

Contoh:

```text
10:00 - 12:00
12:00 - 14:00
```

---

## UC-08 — Create Booking

**Actor:** User

**Purpose:**
Membuat reservasi room.

**Input:**

* Room
* Booking date
* Start time
* End time
* Meeting title
* Description

**Result:**

Booking berhasil dibuat dengan status:

```text
pending
```

### Rules

* User harus login.
* Room harus `active`.
* Tanggal tidak boleh berada di masa lalu.
* Start time harus lebih kecil dari end time.
* Durasi minimum 1 jam.
* Booking harus berada dalam jam operasional.
* Jam operasional sementara: `08:00–17:00`.
* Booking tidak boleh overlap.
* Availability harus diperiksa saat proses booking.
* Availability harus diperiksa kembali tepat sebelum booking disimpan.

Aturan jam operasional merupakan **temporary assumption** dan dapat berubah setelah requirement perusahaan tersedia.

---

## UC-09 — View My Bookings

**Actor:** User

**Purpose:**
Melihat seluruh booking milik user yang sedang login.

**Information:**

* Room
* Date
* Time
* Status
* Action

User hanya dapat melihat booking dengan `user_id` miliknya sendiri.

---

## UC-10 — View My Booking Detail

**Actor:** User

**Purpose:**
Melihat detail booking milik sendiri.

**Information:**

* Room
* Date
* Start time
* End time
* Title
* Description
* Status
* Approved at jika ada
* Rejection reason jika ada

User tidak dapat melihat detail booking milik user lain.

---

## UC-11 — Cancel Booking

**Actor:** User

**Purpose:**
Membatalkan booking miliknya.

User dapat membatalkan:

* `pending`
* `approved`

Setelah dibatalkan:

```text
status = cancelled
```

Booking yang berstatus `cancelled` tidak lagi memblokir slot.

User tidak dapat membatalkan:

* `rejected`
* `cancelled`
* `completed`

Aturan pembatalan merupakan temporary assumption dan dapat berubah sesuai kebijakan perusahaan.

---

## UC-12 — Manage Rooms

**Actor:** Admin

**Purpose:**
Mengelola data room.

**Functions:**

* View rooms
* Add room
* Edit room
* Activate room
* Deactivate room

**Room Data:**

* Name
* Location
* Capacity
* Description
* Status
* Image

Room dengan status `inactive` tidak dapat menerima booking baru.

---

## UC-13 — Manage Facilities

**Actor:** Admin

**Purpose:**
Mengelola facility dan assignment facility ke room.

**Functions:**

* View facilities
* Add facility
* Edit facility
* Delete facility
* Assign facility ke room
* Remove facility dari room

Relationship:

```text
rooms
   ↕
room_facilities
   ↕
facilities
```

Facility tidak disimpan sebagai string langsung pada tabel `rooms`.

---

## UC-14 — View Users

**Actor:** Admin

**Purpose:**
Melihat daftar user yang terdaftar dalam sistem.

Admin dapat melihat informasi user yang diperlukan untuk kebutuhan administrasi.

User tidak memiliki akses ke daftar user lain.

---

## UC-15 — View All Bookings

**Actor:** Admin

**Purpose:**
Melihat seluruh booking dalam sistem.

Admin dapat:

* Melihat daftar booking.
* Melihat status booking.
* Membuka detail booking.
* Memproses booking yang masih `pending`.

---

## UC-16 — View All Room Calendar

**Actor:** Admin

**Purpose:**
Melihat jadwal seluruh room untuk kebutuhan administrasi.

Admin dapat melihat:

* Room.
* Date.
* Time.
* Booking status.
* Detail booking sesuai hak akses Admin.

---

## UC-17 — View Booking Detail

**Actor:** Admin

**Purpose:**
Melihat informasi lengkap booking untuk kebutuhan administrasi.

**Information:**

* User
* Room
* Date
* Start time
* End time
* Meeting title
* Description
* Status
* Approved by
* Approved at
* Rejection reason

---

## UC-18 — Approve Booking

**Actor:** Admin

**Purpose:**
Menyetujui booking yang berstatus `pending`.

**Result:**

```text
status = approved
```

Sistem menyimpan:

* `approved_by`
* `approved_at`

### Rule

Availability harus diperiksa kembali saat approval.

Jika slot sudah tidak tersedia, approval tidak boleh dilakukan.

---

## UC-19 — Reject Booking

**Actor:** Admin

**Purpose:**
Menolak booking yang berstatus `pending`.

Admin wajib memberikan:

```text
rejection_reason
```

**Result:**

```text
status = rejected
```

Booking yang ditolak tidak lagi memblokir slot.

---

## UC-20 — View Reservation Statistics

**Actor:** Admin

**Purpose:**
Melihat statistik reservasi berdasarkan data booking.

Statistik dapat mencakup:

* Total booking
* Pending
* Approved
* Rejected
* Cancelled
* Completed
* Penggunaan room

Statistik harus dihitung berdasarkan data database.

---

# 5. Booking Status

| Status      | Memblokir Slot | Keterangan                         |
| ----------- | :------------: | ---------------------------------- |
| `pending`   |       Ya       | Menunggu approval Admin            |
| `approved`  |       Ya       | Booking disetujui                  |
| `rejected`  |      Tidak     | Booking ditolak                    |
| `cancelled` |      Tidak     | Booking dibatalkan                 |
| `completed` |      Tidak     | Histori booking yang telah selesai |

---

# 6. Access Control Matrix

| Feature                     | User | Admin |
| --------------------------- | :--: | :---: |
| Login                       |   ✓  |   ✓   |
| User Dashboard              |   ✓  |   -   |
| Admin Dashboard             |   -  |   ✓   |
| View Rooms                  |   ✓  |   ✓   |
| View Room Detail            |   ✓  |   ✓   |
| View Calendar Availability  |   ✓  |   ✓   |
| Find Available Room         |   ✓  |   ✓   |
| Create Booking              |   ✓  |   -   |
| View My Bookings            |   ✓  |   -   |
| View My Booking Detail      |   ✓  |   -   |
| Cancel Own Booking          |   ✓  |   -   |
| Manage Rooms                |   -  |   ✓   |
| Manage Facilities           |   -  |   ✓   |
| View Users                  |   -  |   ✓   |
| View All Bookings           |   -  |   ✓   |
| View All Room Calendar      |   -  |   ✓   |
| View Booking Detail         |   -  |   ✓   |
| Approve Booking             |   -  |   ✓   |
| Reject Booking              |   -  |   ✓   |
| View Reservation Statistics |   -  |   ✓   |

---

# 7. Main Booking Rules

Aturan berikut menjadi dasar untuk proses booking:

1. User harus login untuk membuat booking.
2. Room harus berstatus `active`.
3. Booking tidak boleh dilakukan pada tanggal lampau.
4. Start time harus lebih kecil dari end time.
5. Durasi minimum booking adalah 1 jam.
6. Jam operasional sementara adalah `08:00–17:00`.
7. Booking tidak boleh mengalami overlap.
8. Booking `pending` dan `approved` memblokir slot.
9. Booking `rejected` dan `cancelled` tidak memblokir slot.
10. Availability diperiksa saat pencarian room.
11. Availability diperiksa saat proses booking.
12. Availability diperiksa kembali sebelum booking disimpan.
13. Availability diperiksa kembali saat Admin melakukan approval.
14. User hanya dapat melihat detail booking miliknya sendiri.
15. Calendar User tidak boleh membocorkan detail pribadi booking user lain.
16. Rejection membutuhkan `rejection_reason`.
17. Approval menyimpan `approved_by` dan `approved_at`.
18. User hanya dapat membatalkan booking `pending` atau `approved`.

Aturan yang belum diberikan oleh perusahaan merupakan **temporary assumption** dan dapat diubah setelah requirement resmi tersedia.

---

# 8. Scope Boundary

Use Case ini tidak mencakup:

* Notifications
* Payments
* Attendees
* Recurring bookings
* Departments
* Buildings
* Floors
* Audit logs

Fitur tambahan hanya akan dibuat jika requirement perusahaan nantinya membutuhkannya.

---

# 9. Relationship dengan Dokumen Berikutnya

Use Case ini menjadi dasar untuk:

```text
Use Case
   ↓
Activity Diagram
   ↓
Migration Design
   ↓
Laravel Architecture
   ↓
Implementation
```

Detail alur proses utama akan dijelaskan pada Activity Diagram, terutama:

1. User Booking
2. Admin Approval
