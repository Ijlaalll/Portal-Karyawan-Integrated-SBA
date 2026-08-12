# ACTIVITY DIAGRAM — USER BOOKING

## Meeting Room Reservation System

Versi: 1.0
Status: Draft Final

---

# 1. Purpose

Dokumen ini menjelaskan alur proses User ketika melakukan booking meeting room.

Activity Diagram ini menjadi dasar untuk implementasi proses booking dan `BookingService` pada Laravel.

---

# 2. Actor

**User / Karyawan**

User harus sudah login sebelum dapat membuat booking.

---

# 3. Main Flow

```text
START
  │
  ▼
User Login
  │
  ▼
Open Find Available Room
  │
  ▼
Input:
- Date
- Start Time
- End Time
- Minimum Capacity
  │
  ▼
Validate Search Input
  │
  ├── Invalid ──► Show Validation Error
  │                    │
  │                    └──► Input Again
  │
  ▼ Valid
Find Active Rooms
  │
  ▼
Check Capacity
  │
  ▼
Check Availability
  │
  ▼
Display Available Rooms
  │
  ▼
User Selects Room
  │
  ▼
Open Booking Form
  │
  ▼
Input:
- Meeting Title
- Description
  │
  ▼
Submit Booking
  │
  ▼
Validate Booking
  │
  ├── Invalid ──► Show Validation Error
  │                    │
  │                    └──► Return to Form
  │
  ▼ Valid
Check Room Status
  │
  ├── Inactive ──► Booking Failed
  │
  ▼ Active
Check Availability
  │
  ├── Not Available ──► Show Room Unavailable
  │                         │
  │                         └──► Select Another Room
  │
  ▼ Available
Final Availability Check
  │
  ├── Conflict ──► Booking Failed
  │
  ▼ No Conflict
Create Booking
  │
  ▼
Set Status = pending
  │
  ▼
Show Booking Confirmation
  │
  ▼
END
```

---

# 4. Activity Details

## 4.1 Find Available Room

User memasukkan:

* Date
* Start time
* End time
* Minimum capacity

Sistem melakukan:

1. Validasi tanggal.
2. Validasi waktu.
3. Memastikan start time lebih kecil dari end time.
4. Memastikan durasi memenuhi minimum 1 jam.
5. Memastikan waktu berada dalam jam operasional.
6. Mencari room dengan status `active`.
7. Memeriksa kapasitas room.
8. Memeriksa availability.

Room yang memenuhi seluruh kondisi ditampilkan kepada User.

---

## 4.2 Select Room

User memilih salah satu room dari hasil pencarian.

Sistem membuka booking form dengan room dan waktu yang dipilih.

Informasi booking:

* Room
* Date
* Start time
* End time
* Meeting title
* Description

---

## 4.3 Submit Booking

Saat User melakukan submit, sistem melakukan validasi ulang.

Validasi meliputi:

* User authenticated.
* Room tersedia.
* Room berstatus `active`.
* Date valid.
* Start time < end time.
* Durasi minimum 1 jam.
* Waktu berada dalam jam operasional.

---

# 5. Availability Checking

Availability diperiksa pada beberapa tahap.

### Check 1 — Find Available Room

Digunakan saat User mencari room.

Tujuan:

```text
Menampilkan room yang kemungkinan tersedia.
```

### Check 2 — Booking Submission

Dilakukan saat User mengirim booking.

Tujuan:

```text
Memastikan room masih tersedia sebelum proses dilanjutkan.
```

### Check 3 — Final Check

Dilakukan tepat sebelum booking disimpan.

Tujuan:

```text
Mengurangi risiko double booking ketika
dua User melakukan booking pada waktu yang hampir bersamaan.
```

---

# 6. Double Booking Rule

Booking dianggap overlap apabila:

```text
existing.start_time < requested.end_time
AND
existing.end_time > requested.start_time
```

Hanya booking dengan status berikut yang dianggap memblokir:

```text
pending
approved
```

Booking dengan status berikut tidak memblokir:

```text
rejected
cancelled
```

`completed` digunakan sebagai histori.

### Boundary Time

Booking yang berakhir tepat ketika booking lain dimulai diperbolehkan.

Contoh:

```text
Booking A:
10:00 - 12:00

Booking B:
12:00 - 14:00

Result:
VALID
```

---

# 7. Successful Booking

Jika seluruh validasi berhasil:

```text
Create Booking
      ↓
Save to Database
      ↓
status = pending
      ↓
Show Confirmation
```

Booking belum dianggap disetujui sampai Admin melakukan approval.

---

# 8. Failed Booking

Booking tidak dibuat jika:

* Input tidak valid.
* Room inactive.
* Room tidak memenuhi kapasitas.
* Room sudah memiliki booking yang overlap.
* Final availability check menemukan conflict.

Sistem menampilkan informasi yang sesuai dan User dapat memilih room atau waktu lain.

---

# 9. Result

Jika berhasil:

```text
Booking Created
status = pending
```

Booking selanjutnya masuk ke proses:

```text
Admin Approval
```

yang akan dijelaskan pada:

```text
04_ACTIVITY_DIAGRAM/Admin Approval.md
```

---

# 10. Business Rules

1. User harus login.
2. Room harus active.
3. Booking tidak boleh dilakukan pada tanggal lampau.
4. Start time harus lebih kecil dari end time.
5. Durasi minimum adalah 1 jam.
6. Jam operasional sementara adalah 08:00–17:00.
7. Booking `pending` dan `approved` memblokir slot.
8. Booking `rejected` dan `cancelled` tidak memblokir slot.
9. Booking tidak boleh overlap.
10. Boundary time diperbolehkan.
11. Availability harus diperiksa saat pencarian.
12. Availability harus diperiksa saat submit booking.
13. Availability harus diperiksa kembali tepat sebelum penyimpanan.
14. Booking yang berhasil dibuat memiliki status `pending`.
15. Business rules yang belum ditentukan perusahaan merupakan temporary assumption.
