# ACTIVITY DIAGRAM — ADMIN APPROVAL

## Meeting Room Reservation System

Versi: 1.0
Status: Draft Final

---

# 1. Purpose

Dokumen ini menjelaskan alur proses Admin ketika memproses booking yang berstatus `pending`.

Admin dapat:

* Approve booking
* Reject booking

Proses approval wajib melakukan pengecekan availability kembali sebelum booking disetujui.

---

# 2. Actor

**Admin**

Admin harus sudah login dan memiliki role `admin`.

---

# 3. Main Flow

```text
START
  │
  ▼
Admin Login
  │
  ▼
Open Booking Management
  │
  ▼
View Pending Bookings
  │
  ▼
Select Booking
  │
  ▼
View Booking Detail
  │
  ▼
Check Booking Status
  │
  ├── Not Pending ──► Cannot Process
  │
  ▼ Pending
Choose Action
  │
  ├───────────────────────┐
  │                       │
  ▼                       ▼
Approve                  Reject
  │                       │
  ▼                       ▼
Check Availability       Input Rejection Reason
  │                       │
  ├── Conflict             ▼
  │                       Validate Reason
  │                       │
  │                       ▼
  │                   Update Status
  │                       │
  │                       ▼
  │                  status = rejected
  │                       │
  │                       ▼
  │                    END
  │
  ▼ Available
Update Booking
  │
  ├── status = approved
  ├── approved_by = Admin
  └── approved_at = current time
  │
  ▼
Show Approval Result
  │
  ▼
END
```

---

# 4. Open Pending Booking

Admin membuka halaman Booking Management.

Sistem menampilkan booking yang tersedia untuk kebutuhan administrasi.

Admin memilih booking yang ingin diproses dan membuka detail booking.

Informasi booking dapat mencakup:

* User
* Room
* Booking date
* Start time
* End time
* Meeting title
* Description
* Status
* Created time

---

# 5. Booking Status Validation

Sebelum Admin melakukan approval atau rejection, sistem memeriksa status booking.

Booking hanya dapat diproses apabila:

```text
status = pending
```

Jika booking sudah:

* `approved`
* `rejected`
* `cancelled`
* `completed`

maka booking tidak dapat diproses kembali melalui proses approval.

---

# 6. Approve Booking

Jika Admin memilih **Approve**, sistem melakukan availability check kembali.

## Process

```text
Admin Approve
      ↓
Validate Status
      ↓
Check Room Status
      ↓
Check Availability
      ↓
Available?
   /       \
 Yes        No
  |          |
  ▼          ▼
Approve    Reject Approval
  |
  ▼
Save Approval Data
```

### Approval Result

Jika room masih tersedia:

```text
status = approved
approved_by = current admin
approved_at = current timestamp
```

Booking menjadi booking yang disetujui dan tetap memblokir slot.

---

# 7. Approval Availability Check

Availability harus diperiksa kembali ketika Admin melakukan approval.

Hal ini diperlukan karena kondisi room dapat berubah setelah User membuat booking.

Contoh:

```text
User A
10:00 - 12:00
status = pending
```

Kemudian terdapat booking lain yang menyebabkan slot menjadi tidak tersedia.

Saat Admin mencoba approve:

```text
Check Availability
        ↓
Conflict Found
        ↓
Approval Failed
```

Sistem tidak boleh mengubah status menjadi `approved` apabila terjadi conflict.

---

# 8. Reject Booking

Jika Admin memilih **Reject**, sistem meminta alasan penolakan.

Admin harus mengisi:

```text
rejection_reason
```

Setelah validasi berhasil:

```text
status = rejected
```

Sistem menyimpan alasan penolakan.

### Result

Booking tidak lagi memblokir slot.

---

# 9. Rejection Flow

```text
Admin Select Reject
        ↓
Input Rejection Reason
        ↓
Validate Reason
        │
        ├── Invalid
        │     ↓
        │  Show Error
        │     ↓
        │  Input Again
        │
        ▼ Valid
Update Booking
        ↓
status = rejected
        ↓
Save rejection_reason
        ↓
Show Result
```

---

# 10. Approval Data

Ketika booking berhasil di-approve, sistem menyimpan:

| Field         | Value             |
| ------------- | ----------------- |
| `status`      | `approved`        |
| `approved_by` | ID Admin          |
| `approved_at` | Current timestamp |

---

# 11. Rejection Data

Ketika booking di-reject, sistem menyimpan:

| Field              | Value             |
| ------------------ | ----------------- |
| `status`           | `rejected`        |
| `rejection_reason` | Alasan dari Admin |

`approved_by` dan `approved_at` tidak perlu diisi ketika booking ditolak.

---

# 12. Booking Status Transition

Status transition yang diperbolehkan:

```text
pending
   │
   ├──────────────► approved
   │
   └──────────────► rejected

approved ─────────► cancelled
pending  ─────────► cancelled
```

Status berikut tidak dapat diproses kembali:

```text
rejected
cancelled
completed
```

---

# 13. Business Rules

1. Hanya Admin yang dapat approve atau reject booking.
2. Booking yang dapat diproses harus berstatus `pending`.
3. Approval wajib melakukan availability check kembali.
4. Room harus tetap `active` saat approval.
5. Booking tidak boleh di-approve jika terjadi overlap.
6. Booking yang berhasil di-approve menjadi `approved`.
7. Approval menyimpan `approved_by`.
8. Approval menyimpan `approved_at`.
9. Rejection wajib memiliki `rejection_reason`.
10. Booking yang ditolak menjadi `rejected`.
11. Booking `rejected` tidak memblokir availability.
12. Booking yang sudah `approved`, `rejected`, `cancelled`, atau `completed` tidak dapat diproses ulang sebagai pending booking.
13. Temporary business rules dapat berubah setelah requirement resmi perusahaan tersedia.

---

# 14. Result

### Jika Approve berhasil

```text
Booking
status = approved
```

Booking tetap memblokir slot room.

### Jika Reject berhasil

```text
Booking
status = rejected
```

Booking tidak lagi memblokir slot.

---

# 15. Next Process

Setelah Admin Approval selesai:

```text
User Booking
     ↓
Booking Pending
     ↓
Admin Approval
     ↓
Approved / Rejected
```

Dokumen ini menjadi dasar untuk implementasi proses approval pada Laravel dan `BookingService`.
