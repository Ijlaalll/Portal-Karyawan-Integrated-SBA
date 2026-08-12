# MIGRATION DESIGN

## Meeting Room Reservation System

Versi: 1.0
Status: Final
Framework: Laravel 12
Database: MySQL

---

# 1. Purpose

Migration Design menerjemahkan struktur database yang telah ditetapkan pada ERD dan Database Design ke dalam struktur database Laravel.

Migration Design tidak mengubah entity, relationship, atau kebutuhan database yang telah disepakati.

Entity yang digunakan:

```text
users
rooms
facilities
room_facilities
bookings
```

Tidak ada tabel tambahan di luar scope tersebut.

---

# 2. Migration Order

Migration dibuat berdasarkan dependency foreign key.

Urutan:

```text
1. users
2. rooms
3. facilities
4. room_facilities
5. bookings
```

Dependency:

```text
users
 └── bookings

rooms
 ├── bookings
 └── room_facilities

facilities
 └── room_facilities
```

`room_facilities` membutuhkan tabel `rooms` dan `facilities`.

`bookings` membutuhkan tabel `users` dan `rooms`.

---

# 3. Users Migration

## Table

```text
users
```

## Structure

| Field      | Type      | Constraint       | Description                 |
| ---------- | --------- | ---------------- | --------------------------- |
| id         | BIGINT    | Primary Key      | ID user                     |
| name       | VARCHAR   | NOT NULL         | Nama user                   |
| email      | VARCHAR   | NOT NULL, UNIQUE | Email user                  |
| password   | VARCHAR   | NOT NULL         | Password yang telah di-hash |
| role       | ENUM      | NOT NULL         | `admin` / `user`            |
| created_at | TIMESTAMP |                  | Waktu pembuatan             |
| updated_at | TIMESTAMP |                  | Waktu perubahan             |

## Role

```text
admin
user
```

Email harus unique.

---

# 4. Rooms Migration

## Table

```text
rooms
```

## Structure

| Field       | Type             | Constraint  | Description           |
| ----------- | ---------------- | ----------- | --------------------- |
| id          | BIGINT           | Primary Key | ID room               |
| name        | VARCHAR          | NOT NULL    | Nama room             |
| location    | VARCHAR          | NOT NULL    | Lokasi room           |
| capacity    | UNSIGNED INTEGER | NOT NULL    | Kapasitas room        |
| description | TEXT             | NULLABLE    | Deskripsi room        |
| status      | ENUM             | NOT NULL    | `active` / `inactive` |
| image       | VARCHAR          | NULLABLE    | Path/nama gambar      |
| created_at  | TIMESTAMP        |             | Waktu pembuatan       |
| updated_at  | TIMESTAMP        |             | Waktu perubahan       |

## Room Status

```text
active
inactive
```

Room inactive tidak dapat digunakan untuk booking baru.

Availability tidak disimpan sebagai field pada room karena availability bergantung pada tanggal dan waktu booking.

---

# 5. Facilities Migration

## Table

```text
facilities
```

## Structure

| Field       | Type      | Constraint       | Description        |
| ----------- | --------- | ---------------- | ------------------ |
| id          | BIGINT    | Primary Key      | ID facility        |
| name        | VARCHAR   | NOT NULL, UNIQUE | Nama facility      |
| description | TEXT      | NULLABLE         | Deskripsi facility |
| created_at  | TIMESTAMP |                  | Waktu pembuatan    |
| updated_at  | TIMESTAMP |                  | Waktu perubahan    |

Nama facility harus unique.

---

# 6. Room Facilities Migration

## Table

```text
room_facilities
```

Tabel ini merupakan pivot table untuk relationship:

```text
Room N:M Facility
```

## Structure

| Field       | Type   | Constraint | Description |
| ----------- | ------ | ---------- | ----------- |
| room_id     | BIGINT | PK, FK     | ID room     |
| facility_id | BIGINT | PK, FK     | ID facility |

Primary key:

```text
(room_id, facility_id)
```

Tidak menggunakan `id` tambahan.

## Foreign Keys

```text
room_id
→ rooms.id

facility_id
→ facilities.id
```

Composite primary key mencegah pasangan room dan facility yang sama disimpan lebih dari satu kali.

---

# 7. Bookings Migration

## Table

```text
bookings
```

## Structure

| Field            | Type      | Constraint   | Description                   |
| ---------------- | --------- | ------------ | ----------------------------- |
| id               | BIGINT    | Primary Key  | ID booking                    |
| user_id          | BIGINT    | NOT NULL, FK | User pembuat booking          |
| room_id          | BIGINT    | NOT NULL, FK | Room yang dipesan             |
| approved_by      | BIGINT    | NULLABLE, FK | Admin yang melakukan approval |
| title            | VARCHAR   | NOT NULL     | Judul meeting                 |
| description      | TEXT      | NULLABLE     | Deskripsi meeting             |
| booking_date     | DATE      | NOT NULL     | Tanggal booking               |
| start_time       | TIME      | NOT NULL     | Waktu mulai                   |
| end_time         | TIME      | NOT NULL     | Waktu selesai                 |
| status           | ENUM      | NOT NULL     | Status booking                |
| approved_at      | TIMESTAMP | NULLABLE     | Waktu approval                |
| rejection_reason | TEXT      | NULLABLE     | Alasan rejection              |
| created_at       | TIMESTAMP |              | Waktu pembuatan               |
| updated_at       | TIMESTAMP |              | Waktu perubahan               |

---

# 8. Booking Status

Status booking:

```text
pending
approved
rejected
cancelled
completed
```

Booking yang memblokir availability:

```text
pending
approved
```

Booking yang tidak memblokir:

```text
rejected
cancelled
completed
```

Default status booking baru:

```text
pending
```

---

# 9. Foreign Key Relationships

## bookings.user_id

```text
bookings.user_id
→ users.id
```

Menunjukkan user yang membuat booking.

---

## bookings.room_id

```text
bookings.room_id
→ rooms.id
```

Menunjukkan room yang digunakan.

---

## bookings.approved_by

```text
bookings.approved_by
→ users.id
```

Menunjukkan Admin yang melakukan approval.

Field `approved_by` nullable karena booking baru belum memiliki approver.

Role Admin tidak divalidasi oleh foreign key. Validasi bahwa hanya Admin yang dapat melakukan approval berada pada application layer.

---

## room_facilities.room_id

```text
room_facilities.room_id
→ rooms.id
```

---

## room_facilities.facility_id

```text
room_facilities.facility_id
→ facilities.id
```

---

# 10. Foreign Key Delete Behavior

Untuk menjaga histori booking, data `bookings` tidak boleh ikut terhapus hanya karena perubahan data user atau room.

Relationship booking:

```text
users
  ↓
bookings

rooms
  ↓
bookings
```

menggunakan perilaku yang tidak menghapus booking secara otomatis.

`approved_by` bersifat nullable sehingga dapat menggunakan:

```text
ON DELETE SET NULL
```

untuk menjaga histori booking apabila approver dihapus pada masa mendatang.

Untuk pivot:

```text
room_facilities
```

foreign key dapat menggunakan:

```text
ON DELETE CASCADE
```

karena record pivot hanya merupakan relationship antara room dan facility.

---

# 11. Index

Index utama:

```text
bookings.user_id
bookings.room_id
bookings.booking_date
bookings.status
```

Index tersebut mendukung query berdasarkan user, room, tanggal, dan status booking.

Untuk kebutuhan availability checking, dapat digunakan composite index:

```text
(room_id, booking_date, status, start_time, end_time)
```

Index membantu pencarian data tetapi tidak menggantikan availability checking.

---

# 12. Availability Support

Availability ditentukan berdasarkan:

```text
room_id
booking_date
start_time
end_time
status
```

Hanya booking dengan status:

```text
pending
approved
```

yang dianggap memblokir slot.

Overlap menggunakan:

```text
existing.start_time < requested.end_time
AND
existing.end_time > requested.start_time
```

Boundary diperbolehkan.

Contoh:

```text
10:00–12:00
12:00–14:00
```

tidak dianggap overlap.

---

# 13. Double Booking

Tidak menggunakan UNIQUE constraint sederhana pada:

```text
room_id
booking_date
start_time
end_time
```

karena UNIQUE tidak dapat mencegah seluruh kemungkinan overlap.

Contoh:

```text
09:00–11:00
10:00–12:00
```

memiliki nilai yang berbeda tetapi tetap overlap.

Pencegahan double booking dilakukan pada application/service layer melalui availability checking.

Flow implementasi:

```text
Booking Request
↓
Availability Check
↓
Transaction / Locking
↓
Final Availability Check
↓
Create Booking
```

Business logic tersebut berada di:

```text
BookingService
```

bukan seluruhnya di `BookingController`.

---

# 14. Database Constraints

Constraint yang diterapkan pada database:

```text
Primary Key
Foreign Key
Unique
Composite Primary Key
NOT NULL
NULLABLE
ENUM
Index
```

Contoh:

```text
users.email
→ UNIQUE

facilities.name
→ UNIQUE

room_facilities
→ PRIMARY KEY (room_id, facility_id)
```

---

# 15. Application-Level Rules

Rules berikut tidak ditentukan melalui migration sebagai business logic:

```text
Booking date tidak boleh lampau
Start time harus lebih kecil dari end time
Minimum durasi 1 jam
Jam operasional 08:00–17:00
Room harus active
User harus authenticated
Hanya Admin yang dapat approve
Hanya Admin yang dapat reject
Rejection reason wajib saat reject
User hanya dapat cancel booking tertentu
Availability checking
Double booking prevention
```

Rules tersebut merupakan business rules dan sebagian merupakan temporary assumptions.

Implementasinya berada pada application/service layer.

---

# 16. Temporary Assumptions

Aturan berikut masih dapat berubah apabila perusahaan memberikan requirement resmi:

```text
Jam operasional:
08:00–17:00

Minimum durasi:
1 jam

Booking baru:
pending

Booking yang memblokir:
pending
approved
```

Temporary assumptions tidak dibuat terlalu kaku pada struktur database agar mudah diubah pada application layer.

---

# 17. Migration Dependency

Dependency:

```text
users
  │
  └──────────────┐
                 ↓
              bookings
                 ↑
                 │
rooms ───────────┘
  │
  ↓
room_facilities
  ↑
  │
facilities
```

Migration order:

```text
1. users
2. rooms
3. facilities
4. room_facilities
5. bookings
```

---

# 18. Implementation Boundary

Migration hanya bertanggung jawab terhadap struktur database.

Migration tidak menangani:

```text
availability checking
approval logic
rejection logic
cancellation logic
double booking prevention
authorization
```

Business logic tersebut akan ditentukan pada Laravel Architecture dan Service Layer.

---

# 19. Scope

Migration hanya membuat:

```text
users
rooms
facilities
room_facilities
bookings
```

Tidak membuat:

```text
notifications
payments
attendees
recurring_bookings
departments
buildings
floors
audit_logs
```

Tabel tambahan hanya dibuat apabila requirement resmi perusahaan nantinya membutuhkannya.
