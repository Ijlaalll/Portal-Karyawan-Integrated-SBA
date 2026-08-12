# Database Design

## Meeting Room Reservation System

## 1. Database Overview

Database Meeting Room Reservation System menggunakan MySQL.

Database awal terdiri dari lima tabel utama:

1. `users`
2. `rooms`
3. `facilities`
4. `room_facilities`
5. `bookings`

Database dirancang untuk mendukung:

* Authentication dan role user.
* Pengelolaan meeting room.
* Pengelolaan facilities.
* Hubungan many-to-many antara room dan facility.
* Booking meeting room.
* Approval dan rejection booking.
* Cancellation booking.
* Pengecekan availability.
* Pencegahan double booking.
* Histori booking.

Database tidak menggunakan tabel tambahan seperti:

* `notifications`
* `payments`
* `attendees`
* `recurring_bookings`
* `departments`
* `buildings`
* `floors`
* `audit_logs`

Tabel tambahan hanya dapat ditambahkan apabila terdapat requirement resmi yang membutuhkannya.

## 2. users

| Field        | Type            | Constraint         | Description                    |
| ------------ | --------------- | ------------------ | ------------------------------ |
| `id`         | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key user               |
| `name`       | VARCHAR(255)    | NOT NULL           | Nama user                      |
| `email`      | VARCHAR(255)    | NOT NULL, UNIQUE   | Email user                     |
| `password`   | VARCHAR(255)    | NOT NULL           | Password yang telah di-hash    |
| `role`       | ENUM            | NOT NULL           | Role user: `admin` atau `user` |
| `created_at` | TIMESTAMP       | NULL               | Waktu pembuatan record         |
| `updated_at` | TIMESTAMP       | NULL               | Waktu perubahan record         |

Role yang diperbolehkan:

* `admin`
* `user`

Tidak terdapat field `status` pada `users`.

User Status seperti active/inactive belum termasuk scope.

## 3. rooms

| Field         | Type            | Constraint         | Description                           |
| ------------- | --------------- | ------------------ | ------------------------------------- |
| `id`          | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key room                      |
| `name`        | VARCHAR(255)    | NOT NULL           | Nama meeting room                     |
| `location`    | VARCHAR(255)    | NOT NULL           | Lokasi room                           |
| `capacity`    | INT UNSIGNED    | NOT NULL           | Kapasitas maksimum room               |
| `description` | TEXT            | NULL               | Deskripsi room                        |
| `status`      | ENUM            | NOT NULL           | Status room: `active` atau `inactive` |
| `image`       | VARCHAR(255)    | NULL               | Path/nama file gambar room            |
| `created_at`  | TIMESTAMP       | NULL               | Waktu pembuatan record                |
| `updated_at`  | TIMESTAMP       | NULL               | Waktu perubahan record                |

Room status:

* `active`
* `inactive`

Room dengan status `inactive` tidak dapat digunakan untuk booking baru.

Room status tidak sama dengan availability.

Availability ditentukan berdasarkan tanggal, waktu, dan booking yang memblokir slot.

## 4. facilities

| Field         | Type            | Constraint         | Description            |
| ------------- | --------------- | ------------------ | ---------------------- |
| `id`          | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key facility   |
| `name`        | VARCHAR(255)    | NOT NULL, UNIQUE   | Nama facility          |
| `description` | TEXT            | NULL               | Deskripsi facility     |
| `created_at`  | TIMESTAMP       | NULL               | Waktu pembuatan record |
| `updated_at`  | TIMESTAMP       | NULL               | Waktu perubahan record |

Contoh facility:

* Air Conditioner
* Projector
* Whiteboard
* TV / Display
* Video Conference
* Speaker

## 5. room_facilities

`room_facilities` merupakan pivot table untuk hubungan many-to-many antara `rooms` dan `facilities`.

| Field         | Type            | Constraint | Description                  |
| ------------- | --------------- | ---------- | ---------------------------- |
| `room_id`     | BIGINT UNSIGNED | PK, FK     | Referensi ke `rooms.id`      |
| `facility_id` | BIGINT UNSIGNED | PK, FK     | Referensi ke `facilities.id` |

Primary Key:

```text
(room_id, facility_id)
```

Foreign Keys:

```text
room_id
→ rooms.id

facility_id
→ facilities.id
```

Relationship:

```text
Room N : M Facility
```

Satu room dapat memiliki banyak facility.

Satu facility dapat digunakan oleh banyak room.

Pivot table tidak menggunakan field `id` terpisah.

## 6. bookings

| Field              | Type            | Constraint         | Description                  |
| ------------------ | --------------- | ------------------ | ---------------------------- |
| `id`               | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key booking          |
| `user_id`          | BIGINT UNSIGNED | NOT NULL, FK       | User yang membuat booking    |
| `room_id`          | BIGINT UNSIGNED | NOT NULL, FK       | Room yang dibooking          |
| `approved_by`      | BIGINT UNSIGNED | NULL, FK           | Admin yang memproses booking |
| `title`            | VARCHAR(255)    | NOT NULL           | Judul meeting                |
| `description`      | TEXT            | NULL               | Deskripsi meeting            |
| `booking_date`     | DATE            | NOT NULL           | Tanggal booking              |
| `start_time`       | TIME            | NOT NULL           | Waktu mulai booking          |
| `end_time`         | TIME            | NOT NULL           | Waktu selesai booking        |
| `status`           | ENUM            | NOT NULL           | Status booking               |
| `approved_at`      | TIMESTAMP       | NULL               | Waktu booking disetujui      |
| `rejection_reason` | TEXT            | NULL               | Alasan booking ditolak       |
| `created_at`       | TIMESTAMP       | NULL               | Waktu pembuatan record       |
| `updated_at`       | TIMESTAMP       | NULL               | Waktu perubahan record       |

Booking status yang diperbolehkan:

* `pending`
* `approved`
* `rejected`
* `cancelled`
* `completed`

Foreign Keys:

```text
user_id
→ users.id

room_id
→ rooms.id

approved_by
→ users.id
```

`approved_by` bersifat nullable karena booking dengan status `pending` belum memiliki approver.

`approved_at` bersifat nullable karena booking belum tentu disetujui.

`rejection_reason` digunakan untuk menyimpan alasan ketika Admin menolak booking.

## 7. Relationships

### User → Booking

Relationship:

```text
User 1 : N Booking
```

Satu user dapat memiliki banyak booking.

Eloquent:

```text
User
└── hasMany(Booking)

Booking
└── belongsTo(User)
```

### Room → Booking

Relationship:

```text
Room 1 : N Booking
```

Satu room dapat memiliki banyak booking pada tanggal dan waktu yang berbeda.

Eloquent:

```text
Room
└── hasMany(Booking)

Booking
└── belongsTo(Room)
```

### Room ↔ Facility

Relationship:

```text
Room N : M Facility
```

Relationship menggunakan pivot table:

```text
room_facilities
```

Eloquent:

```text
Room
└── belongsToMany(Facility)

Facility
└── belongsToMany(Room)
```

### Approver → Booking

Admin yang melakukan approval atau rejection direferensikan melalui:

```text
bookings.approved_by
→ users.id
```

Secara konseptual:

```text
User/Admin 1 : N Booking
```

Pada model `Booking`, relationship approver mengarah ke model `User`.

```text
Booking
└── belongsTo(User, 'approved_by')
```

`approved_by` bersifat nullable.

## 8. Booking Status

Booking memiliki lima status:

```text
pending
approved
rejected
cancelled
completed
```

### pending

Booking telah dibuat oleh user dan menunggu proses Admin.

Booking dengan status `pending` memblokir availability room.

### approved

Booking telah disetujui oleh Admin.

Booking dengan status `approved` memblokir availability room.

### rejected

Booking telah ditolak oleh Admin.

Booking dengan status `rejected` tidak memblokir availability room.

### cancelled

Booking telah dibatalkan.

Booking dengan status `cancelled` tidak memblokir availability room.

### completed

Booking telah selesai dan digunakan sebagai histori.

Booking dengan status `completed` tidak memblokir availability room.

## 9. Database Constraints

### 9.1 Primary Key

Primary key pada tabel utama:

```text
users.id
rooms.id
facilities.id
bookings.id
```

Pivot table menggunakan composite primary key:

```text
(room_facilities.room_id, room_facilities.facility_id)
```

### 9.2 Unique Constraint

Field berikut harus unique:

```text
users.email
facilities.name
```

### 9.3 Foreign Key Constraint

Foreign key yang digunakan:

```text
bookings.user_id
→ users.id

bookings.room_id
→ rooms.id

bookings.approved_by
→ users.id

room_facilities.room_id
→ rooms.id

room_facilities.facility_id
→ facilities.id
```

### 9.4 Role Constraint

`users.role` hanya dapat memiliki nilai:

```text
admin
user
```

### 9.5 Room Status Constraint

`rooms.status` hanya dapat memiliki nilai:

```text
active
inactive
```

### 9.6 Booking Status Constraint

`bookings.status` hanya dapat memiliki nilai:

```text
pending
approved
rejected
cancelled
completed
```

### 9.7 Booking Integrity

Sistem harus menjaga aturan berikut:

* Booking harus memiliki user.
* Booking harus memiliki room.
* Start time harus lebih kecil daripada end time.
* Booking tidak boleh menggunakan room dengan status `inactive`.
* Booking tidak boleh berada pada tanggal lampau.
* Durasi booking minimum adalah 1 jam.
* Booking harus berada dalam jam operasional yang ditentukan.
* Booking tidak boleh overlap dengan booking yang sedang memblokir slot.
* Booking status harus merupakan salah satu status yang valid.

### 9.8 Availability Constraint

Booking dengan status berikut dianggap memblokir room:

```text
pending
approved
```

Booking dengan status berikut tidak memblokir room:

```text
rejected
cancelled
completed
```

### 9.9 Overlap Constraint

Pengecekan overlap menggunakan formula:

```text
existing.start_time < requested.end_time
AND
existing.end_time > requested.start_time
```

Contoh existing booking:

```text
10:00–12:00
```

Booking berikut tidak diperbolehkan:

```text
09:00–11:00
10:00–11:00
11:00–13:00
09:00–13:00
```

Booking berikut diperbolehkan:

```text
08:00–10:00
12:00–14:00
```

Booking yang tepat berada pada boundary waktu diperbolehkan.

Contoh:

```text
10:00–12:00
12:00–14:00
```

Valid.

### 9.10 Concurrency and Double Booking

Pencegahan double booking tidak hanya bergantung pada application-level availability check.

Final availability check harus dilakukan tepat sebelum booking disimpan.

Proses penyimpanan booking harus menggunakan database transaction dan locking yang sesuai untuk mengurangi risiko race condition ketika dua user mencoba melakukan booking pada room dan waktu yang sama.

Alur konseptual:

```text
Begin Transaction
↓
Lock relevant room / booking data
↓
Final Availability Check
↓
Create Booking
↓
Commit Transaction
```

## 10. Scope

Database design ini hanya mencakup kebutuhan awal Meeting Room Reservation System.

### Included

* User
* Role
* Room
* Facility
* Room-Facility relationship
* Booking
* Booking status
* Booking approval
* Booking rejection
* Booking cancellation
* Availability
* Double booking prevention
* Booking history

### Not Included

* User status / active-inactive user
* Notifications
* Payments
* Attendees
* Recurring bookings
* Departments
* Buildings
* Floors
* Audit logs
* Rebook
* Additional external integrations

Fitur di luar scope hanya boleh ditambahkan apabila terdapat requirement resmi baru dari perusahaan atau mentor.

Aturan sementara yang belum ditetapkan oleh perusahaan tetap dianggap sebagai temporary assumption dan harus mudah diubah apabila requirement resmi diberikan.
