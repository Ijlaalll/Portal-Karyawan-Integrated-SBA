# LARAVEL ARCHITECTURE

## Meeting Room Reservation System

**Version:** 1.0  
**Status:** Draft Final untuk Review  
**Framework:** Laravel 12  
**Database:** MySQL  
**Frontend:** Blade + HTML + CSS + JavaScript  
**Architecture:** MVC + Service Layer

---

# 1. Purpose

Dokumen ini mendefinisikan arsitektur aplikasi Laravel untuk Meeting Room Reservation System sebelum tahap coding dimulai.

Tujuan utama:

1. Menentukan pembagian tanggung jawab antar-layer.
2. Menentukan struktur folder Laravel.
3. Menentukan Controller, Service, Model, Form Request, Middleware, dan Policy yang diperlukan.
4. Menjaga business logic booking tidak menumpuk di Controller.
5. Menentukan lokasi logic availability dan double-booking prevention.
6. Menentukan authorization Admin dan User.
7. Menjaga privacy booking user lain.
8. Menyediakan struktur yang cukup sederhana untuk project magang dan tidak over-engineered.
9. Memudahkan perubahan temporary assumptions ketika requirement perusahaan sudah tersedia.

Dokumen ini adalah blueprint implementasi. Laravel coding belum dilakukan pada tahap ini.

---

# 2. Architecture Overview

Arsitektur utama:

```text
Browser
   |
   v
Route
   |
   v
Middleware / Authorization
   |
   v
Controller
   |
   +--> Form Request / Validation
   |
   v
Service Layer
   |
   +--> Eloquent Models
   |
   v
MySQL
```

Untuk proses booking:

```text
User
  |
  v
Booking Route
  |
  v
auth Middleware
  |
  v
BookingController
  |
  v
StoreBookingRequest
  |
  v
BookingService
  |
  +--> Validate business rules
  +--> Validate room status
  +--> Check availability
  +--> Transaction
  +--> Final availability check
  +--> Create booking
  |
  v
Booking Model
  |
  v
MySQL
```

Controller tidak menjadi tempat utama business logic.

---

# 3. Architectural Principles

## 3.1 Controller Thin

Controller bertanggung jawab untuk:

- menerima HTTP request;
- menggunakan Form Request untuk validasi input;
- memanggil Service;
- menentukan response/redirect;
- meneruskan data ke View.

Controller tidak bertanggung jawab untuk:

- menghitung overlap booking;
- menentukan apakah room tersedia;
- menentukan status booking;
- menjalankan approval workflow;
- menjalankan cancellation rules;
- mengelola transaction booking secara langsung.

---

## 3.2 Service Layer

Service digunakan untuk business logic yang memiliki aturan atau workflow.

Service utama:

```text
BookingService
```

Service tambahan hanya dibuat apabila kebutuhan implementasi benar-benar memerlukannya.

Availability dapat menjadi:

```text
AvailabilityService
```

jika logic availability digunakan oleh beberapa workflow dan sudah cukup kompleks.

Untuk tahap awal, hindari membuat terlalu banyak service kecil.

---

## 3.3 Model

Model bertanggung jawab terhadap:

- representasi entity;
- Eloquent relationship;
- casting;
- attribute/helper sederhana yang dekat dengan data.

Model tidak menjadi tempat utama workflow booking.

Contoh:

```text
Booking
```

memiliki relationship:

```text
user()
room()
approver()
```

tetapi proses:

```text
approve()
reject()
cancel()
```

yang memiliki business rules tetap dikendalikan oleh Service Layer.

---

## 3.4 Form Request

Form Request digunakan untuk input validation.

Contoh:

```text
StoreBookingRequest
UpdateRoomRequest
StoreRoomRequest
StoreFacilityRequest
UpdateFacilityRequest
```

Form Request menangani validasi format dan input dasar.

Business rule yang membutuhkan query/database atau workflow tetap berada di Service Layer.

---

# 4. Recommended Laravel Directory Structure

Struktur utama:

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── BookingApprovalController.php
│   │   │   ├── RoomController.php
│   │   │   ├── FacilityController.php
│   │   │   └── UserController.php
│   │   │
│   │   ├── BookingController.php
│   │   ├── CalendarController.php
│   │   ├── DashboardController.php
│   │   └── RoomSearchController.php
│   │
│   ├── Requests/
│   │   ├── StoreBookingRequest.php
│   │   ├── StoreRoomRequest.php
│   │   ├── UpdateRoomRequest.php
│   │   ├── StoreFacilityRequest.php
│   │   └── UpdateFacilityRequest.php
│   │
│   └── Middleware/
│       └── AdminMiddleware.php
│
├── Models/
│   ├── User.php
│   ├── Room.php
│   ├── Facility.php
│   └── Booking.php
│
├── Policies/
│   ├── BookingPolicy.php
│   ├── RoomPolicy.php
│   ├── FacilityPolicy.php
│   └── UserPolicy.php
│
└── Services/
    ├── BookingService.php
    └── AvailabilityService.php
```

Tambahan standard Laravel tetap mengikuti struktur Laravel 12.

Tidak perlu membuat folder atau class tambahan sebelum benar-benar diperlukan.

---

# 5. Models

Entity utama sesuai database:

```text
User
Room
Facility
Booking
```

Pivot:

```text
room_facilities
```

tidak membutuhkan model khusus pada tahap awal karena dapat menggunakan Eloquent `belongsToMany()`.

---

# 6. User Model

Model:

```text
App\Models\User
```

Relationship:

```text
User hasMany Booking
```

User juga direferensikan oleh:

```text
Booking.approved_by
```

sebagai approver.

User memiliki role:

```text
admin
user
```

Role digunakan untuk authorization.

Role tidak digunakan untuk menggantikan authentication.

Authentication memastikan user sudah login.

Authorization menentukan apa yang boleh dilakukan user.

---

# 7. Room Model

Model:

```text
App\Models\Room
```

Relationship:

```text
Room hasMany Booking
Room belongsToMany Facility
```

Room memiliki status:

```text
active
inactive
```

Room inactive tidak dapat digunakan untuk booking baru.

Availability tidak disimpan sebagai field Room karena availability bergantung pada:

```text
room
date
start_time
end_time
booking status
```

---

# 8. Facility Model

Model:

```text
App\Models\Facility
```

Relationship:

```text
Facility belongsToMany Room
```

Facility memiliki:

```text
name
description
```

`name` unique.

---

# 9. Booking Model

Model:

```text
App\Models\Booking
```

Relationship:

```text
Booking belongsTo User
Booking belongsTo Room
Booking belongsTo User as approver
```

Konsep relationship:

```text
user()
room()
approver()
```

Booking status:

```text
pending
approved
rejected
cancelled
completed
```

Status yang memblokir availability:

```text
pending
approved
```

Status yang tidak memblokir:

```text
rejected
cancelled
completed
```

---

# 10. Eloquent Relationship Summary

```text
User
 └── hasMany Booking

Room
 ├── hasMany Booking
 └── belongsToMany Facility

Facility
 └── belongsToMany Room

Booking
 ├── belongsTo User
 ├── belongsTo Room
 └── belongsTo User as Approver
```

Pivot:

```text
room_facilities
```

menggunakan:

```text
belongsToMany()
```

dengan composite primary key pada database.

---

# 11. Authentication

Laravel authentication digunakan untuk seluruh user.

Requirement:

```text
User harus login untuk membuat booking.
```

Route yang membutuhkan login menggunakan:

```text
auth
```

middleware.

Public access hanya diberikan untuk halaman yang memang ditentukan sebagai public, misalnya login.

---

# 12. Authorization

Authorization dibagi menjadi dua level utama.

## 12.1 Authentication

Memastikan:

```text
user sudah login
```

menggunakan:

```text
auth middleware
```

## 12.2 Admin Authorization

Memastikan:

```text
user.role === admin
```

untuk fitur administrasi.

Gunakan middleware khusus:

```text
admin
```

untuk route group Admin.

---

# 13. Admin Route Boundary

Semua fitur administrasi ditempatkan dalam route group:

```text
auth
+
admin
```

Contoh logical structure:

```text
/authenticated
    |
    +-- user routes
    |
    +-- admin routes
          |
          +-- dashboard
          +-- rooms
          +-- facilities
          +-- users
          +-- bookings/approvals
          +-- calendar
```

Admin route tidak boleh hanya dilindungi oleh UI.

Authorization harus dilakukan pada server side.

---

# 14. Policies

Policy digunakan ketika authorization bergantung pada ownership atau resource tertentu.

Policy utama:

```text
BookingPolicy
```

Contoh rule:

```text
User hanya dapat melihat detail booking miliknya sendiri.
User hanya dapat membatalkan booking yang menjadi miliknya.
```

Admin dapat melihat booking yang diperlukan untuk administrasi.

Untuk Room, Facility, dan User, authorization Admin dapat ditangani melalui Admin Middleware jika seluruh resource hanya tersedia untuk Admin.

Policy tidak dibuat berlebihan.

---

# 15. Controller Responsibilities

## 15.1 DashboardController

User dashboard.

Tugas:

- mengambil data ringkasan user;
- mengambil upcoming bookings milik user;
- mengembalikan view dashboard.

Tidak melakukan booking business logic.

---

## 15.2 Admin/DashboardController

Admin dashboard.

Tugas:

- mengambil statistik;
- mengambil pending booking;
- mengambil ringkasan room/user;
- mengembalikan view admin dashboard.

---

## 15.3 RoomSearchController

Digunakan untuk:

```text
Find Available Room
```

Input:

```text
date
start_time
end_time
capacity
```

Controller memanggil availability logic.

Controller tidak menulis query overlap secara langsung.

---

## 15.4 CalendarController

Menampilkan availability/calendar.

User dapat melihat:

```text
room
date
time
availability
```

Informasi booking user lain harus disanitasi/minimal.

Calendar tidak boleh mengirim:

```text
user name
title
description
```

milik booking user lain kepada User.

---

## 15.5 BookingController

Digunakan untuk workflow User:

```text
create
store
show
index
cancel
```

Controller:

1. menerima request;
2. melakukan request validation;
3. memanggil BookingService;
4. menangani hasil;
5. redirect/view.

Business logic tetap berada di BookingService.

---

## 15.6 Admin/BookingApprovalController

Digunakan untuk:

```text
pending bookings
show booking
approve
reject
```

Workflow approval/rejection diproses oleh BookingService.

Controller tidak langsung mengubah status booking melalui Eloquent.

---

## 15.7 Admin/RoomController

CRUD Room:

```text
index
create
store
edit
update
```

Aktivasi/nonaktifasi room juga dapat ditangani di controller/service yang sama pada tahap awal.

Tidak perlu RoomService kecuali logic room management berkembang signifikan.

---

## 15.8 Admin/FacilityController

CRUD Facility:

```text
index
create
store
edit
update
destroy
```

Assignment facility ke room dapat dilakukan dari Room form.

---

## 15.9 Admin/UserController

Admin dapat melihat daftar user.

User management tetap sederhana sesuai requirement.

Tidak membuat fitur user management kompleks sebelum requirement perusahaan memerlukannya.

---

# 16. BookingService

Class:

```text
App\Services\BookingService
```

Ini merupakan service utama sistem.

Tanggung jawab:

```text
create booking
approve booking
reject booking
cancel booking
```

Service memastikan business rules diterapkan secara konsisten.

---

# 17. Create Booking Flow

Logical flow:

```text
StoreBookingRequest
        |
        v
BookingService::create()
        |
        v
Validate business rules
        |
        +--> booking date valid?
        +--> time valid?
        +--> minimum duration?
        +--> operating hours?
        +--> room active?
        |
        v
Transaction
        |
        v
Final availability check
        |
        v
Create booking with status=pending
        |
        v
Commit
```

Availability check harus dilakukan lagi tepat sebelum insert.

---

# 18. AvailabilityService

Class:

```text
App\Services\AvailabilityService
```

Service ini bertanggung jawab untuk logic availability yang reusable.

Tanggung jawab utama:

```text
check room availability
find available rooms
detect booking overlap
```

Jika implementasi awal masih sangat sederhana, logic availability tetap dapat berada di BookingService. Namun apabila digunakan oleh Calendar, Find Room, dan Booking creation, memisahkannya menjadi AvailabilityService lebih bersih.

Recommended architecture:

```text
BookingService
      |
      v
AvailabilityService
```

---

# 19. Availability Rule

Booking yang memblokir:

```text
pending
approved
```

Query logical condition:

```text
existing.start_time < requested.end_time
AND
existing.end_time > requested.start_time
```

dengan filter:

```text
room_id = requested room
booking_date = requested date
status IN (pending, approved)
```

Boundary diperbolehkan.

Contoh:

```text
10:00–12:00
12:00–14:00
```

tidak overlap.

---

# 20. Find Available Room

AvailabilityService menerima:

```text
date
start_time
end_time
minimum_capacity
```

Room yang dapat dikembalikan harus memenuhi:

```text
status = active
capacity >= minimum_capacity
```

dan:

```text
tidak memiliki booking pending/approved
yang overlap dengan requested time.
```

Logical flow:

```text
Find active rooms
      |
      v
Filter capacity
      |
      v
Exclude rooms with blocking overlap
      |
      v
Return available rooms
```

---

# 21. Availability Checking — Three Stages

Availability diperiksa pada tiga tahap sesuai requirement.

## Stage 1 — Search

Saat user melakukan Find Available Room.

Tujuan:

```text
memberikan hasil availability.
```

## Stage 2 — Booking Form

Saat user membuka/mengirim booking form.

Tujuan:

```text
menghindari form menggunakan slot
yang sudah tidak tersedia.
```

## Stage 3 — Final Save

Tepat sebelum booking disimpan.

Tujuan:

```text
mengurangi risiko race condition.
```

Stage 3 adalah pengecekan paling penting.

---

# 22. Double Booking Protection

Tidak menggunakan UNIQUE constraint sederhana untuk mencegah overlap.

Alasan:

```text
09:00–11:00
10:00–12:00
```

memiliki start/end berbeda tetapi tetap overlap.

Protection berada di application/service layer dengan transaction dan locking strategy.

Logical flow:

```text
BEGIN TRANSACTION
        |
        v
Lock relevant room row
        |
        v
Final availability check
        |
        +---- unavailable ---> rollback / validation error
        |
        v
Create booking
        |
        v
COMMIT
```

Locking digunakan untuk mengurangi kemungkinan dua transaction memproses room yang sama secara bersamaan.

Implementasi detail locking akan ditentukan saat coding setelah migration/model siap.

---

# 23. Booking Transaction Boundary

Transaction digunakan untuk operasi yang membutuhkan atomicity.

Minimal create booking:

```text
DB::transaction(...)
```

Di dalam transaction:

```text
lock relevant room
final availability check
create booking
```

Jika availability gagal:

```text
rollback
```

Jika create berhasil:

```text
commit
```

Jangan melakukan external side effect yang tidak transactional di dalam transaction pada tahap awal.

---

# 24. Approval Flow

Admin melakukan:

```text
Approve
```

Flow:

```text
Admin
  |
  v
BookingApprovalController
  |
  v
BookingService::approve()
  |
  v
Check booking status
  |
  v
Check room/status/business rule
  |
  v
Final availability check
  |
  v
Set:
status = approved
approved_by = admin.id
approved_at = now()
```

Approval harus kembali melakukan availability check.

Alasannya:

Booking berstatus pending memang sudah memblokir slot, tetapi kondisi database dapat berubah sebelum Admin melakukan approval.

Jika slot sudah tidak dapat digunakan, approval harus gagal dengan jelas.

---

# 25. Rejection Flow

Admin melakukan:

```text
Reject
```

Rejection reason wajib.

Flow:

```text
Admin
  |
  v
BookingService::reject()
  |
  +--> verify admin
  +--> verify booking can be rejected
  +--> validate rejection reason
  |
  v
status = rejected
rejection_reason = reason
```

Booking rejected tidak lagi memblokir availability.

---

# 26. Cancellation Flow

Cancellation diproses melalui:

```text
BookingService::cancel()
```

Rule sementara:

```text
pending    -> can cancel
approved   -> can cancel
rejected   -> cannot cancel
cancelled  -> cannot cancel
completed  -> cannot cancel
```

Untuk User:

```text
booking.user_id == authenticated user id
```

harus dipastikan sebelum cancellation.

Admin dapat memiliki kemampuan administratif yang berbeda apabila requirement final membutuhkannya, tetapi jangan ditambahkan sebelum diperlukan.

---

# 27. Booking Lifecycle

Lifecycle logical:

```text
                 +------------+
                 |   pending  |
                 +------------+
                  /          \
                 /            \
                v              v
        +------------+   +------------+
        |  approved  |   |  rejected  |
        +------------+   +------------+
              |
              v
        +------------+
        |  completed |
        +------------+

pending
   |
   v
cancelled

approved
   |
   v
cancelled
```

Transition harus divalidasi oleh Service Layer.

---

# 28. Completion

`completed` merupakan status histori.

Pada tahap awal, completion tidak membutuhkan scheduler khusus.

Status completed dapat ditangani melalui mekanisme yang sederhana setelah requirement final tersedia.

Jangan membuat queue, scheduler, atau background job khusus hanya untuk completion sebelum memang diperlukan.

---

# 29. Privacy Architecture

Privacy diterapkan di application layer.

Untuk User:

```text
Booking::show()
```

hanya boleh mengakses booking miliknya sendiri.

Untuk calendar:

User hanya menerima data:

```text
room
date
start_time
end_time
availability/status
```

Jangan expose:

```text
user_id
user name
title
description
rejection_reason
approved_by
```

milik booking user lain.

Admin dapat melihat detail booking sesuai kebutuhan administrasi.

---

# 30. Calendar Data Boundary

Calendar User menggunakan dua jenis data:

## Available slot

```text
available
```

## Occupied slot

```text
booked / unavailable
```

Detail internal booking tidak ikut dikirim.

Contoh response concept:

```text
Room A
10:00
12:00
unavailable
```

Bukan:

```text
Room A
10:00
12:00
Muhammad
Weekly Director Meeting
Confidential discussion
```

---

# 31. Route Architecture

Route dibagi menjadi:

```text
Public
Authenticated User
Admin
```

Logical grouping:

```text
Public
├── login
└── authentication endpoints

Authenticated
├── dashboard
├── rooms
├── calendar
├── room search
├── bookings
└── my bookings

Admin
├── dashboard
├── rooms
├── facilities
├── users
├── booking approvals
└── admin calendar
```

Semua route User dan Admin yang membutuhkan account harus menggunakan `auth`.

Admin route menggunakan:

```text
auth + admin
```

---

# 32. Route Naming Convention

Gunakan Laravel resource-style naming jika sesuai.

Contoh:

```text
rooms.index
rooms.show

bookings.index
bookings.create
bookings.store
bookings.show

admin.rooms.index
admin.rooms.create
admin.rooms.store
admin.rooms.edit
admin.rooms.update

admin.facilities.index

admin.bookings.index
admin.bookings.show
admin.bookings.approve
admin.bookings.reject
```

Nama route final ditentukan saat route design/coding.

---

# 33. Validation Boundary

Validation dibagi menjadi dua.

## Input Validation

Dilakukan oleh Form Request.

Contoh:

```text
booking_date required
start_time required
end_time required
title required
```

## Business Validation

Dilakukan Service.

Contoh:

```text
booking date tidak lampau
minimum duration 1 jam
operating hours
room active
availability
status transition
ownership
```

Alasannya: business rules membutuhkan context dan dapat berubah.

---

# 34. Temporary Assumptions

Aturan berikut merupakan temporary assumptions:

```text
Operating hours:
08:00–17:00

Minimum duration:
1 hour

New booking:
pending

Blocking status:
pending + approved
```

Aturan tersebut sebaiknya tidak tersebar hard-coded di banyak Controller.

Tempat yang direkomendasikan:

```text
Service / dedicated configuration
```

Jika jumlah configurable rule bertambah, dapat dipindahkan ke:

```text
config/booking.php
```

Namun config tersebut tidak perlu dibuat sebelum coding membutuhkannya.

---

# 35. Database Boundary

Laravel application menggunakan lima table sesuai design:

```text
users
rooms
facilities
room_facilities
bookings
```

Tidak menambahkan:

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

kecuali requirement resmi nantinya membutuhkan.

---

# 36. No Over-Engineering Rule

Jangan membuat:

```text
Repository Pattern
DTO Layer
Command Bus
Event-driven architecture
CQRS
Microservices
Domain-driven architecture
```

pada tahap awal.

Untuk project ini:

```text
Controller
    ↓
Service
    ↓
Eloquent Model
    ↓
Database
```

sudah mencukupi.

Repository baru dipertimbangkan jika query/data access menjadi kompleks dan berulang secara nyata.

---

# 37. Error Handling

Business rule failure harus menghasilkan response yang dapat dipahami user.

Contoh:

```text
Room is no longer available for the selected time.
```

atau versi UI yang sesuai desain.

Untuk conflict availability:

```text
HTTP 422
```

dapat digunakan sebagai validation/business rule response pada request web.

User harus diarahkan kembali ke form/search dengan pesan yang jelas.

Jangan menampilkan exception database mentah kepada user.

---

# 38. Controller-Service Responsibility Matrix

| Responsibility | Controller | Form Request | Service | Model |
|---|---:|---:|---:|---:|
| Receive HTTP request | ✓ | | | |
| Basic input validation | | ✓ | | |
| Business validation | | | ✓ | |
| Availability checking | | | ✓ | |
| Double booking prevention | | | ✓ | |
| Transaction | | | ✓ | |
| Create booking | | | ✓ | ✓ |
| Approve booking | | | ✓ | |
| Reject booking | | | ✓ | |
| Cancel booking | | | ✓ | |
| Eloquent relationship | | | | ✓ |
| Return View/Redirect | ✓ | | | |

---

# 39. Core Booking Architecture

```text
                    USER
                      |
                      v
                  Route
                      |
                      v
                 auth middleware
                      |
                      v
              BookingController
                      |
                      v
             StoreBookingRequest
                      |
                      v
                BookingService
                      |
          +-----------+-----------+
          |                       |
          v                       v
  AvailabilityService       Business Rules
          |                       |
          +-----------+-----------+
                      |
                      v
                DB Transaction
                      |
                      v
              Final Availability
                      |
                      v
                 Booking Model
                      |
                      v
                    MySQL
```

---

# 40. Admin Approval Architecture

```text
                    ADMIN
                      |
                      v
                    Route
                      |
                      v
             auth + admin middleware
                      |
                      v
       BookingApprovalController
                      |
                      v
                BookingService
                      |
          +-----------+-----------+
          |                       |
          v                       v
  Validate transition     Availability check
                                  |
                                  v
                           Database Transaction
                                  |
                                  v
                         Update Booking status
```

---

# 41. Find Room Architecture

```text
User
 |
 v
RoomSearchController
 |
 v
Request validation
 |
 v
AvailabilityService
 |
 +--> active rooms
 +--> capacity
 +--> booking date
 +--> overlap
 +--> blocking status
 |
 v
Available Rooms
 |
 v
Blade View
```

---

# 42. Calendar Architecture

```text
User
 |
 v
CalendarController
 |
 v
Query rooms + relevant bookings
 |
 v
Transform into calendar-safe data
 |
 +--> available
 +--> unavailable
 |
 v
Blade / JavaScript Calendar UI
```

Privacy filtering harus dilakukan sebelum data diberikan ke frontend.

---

# 43. Blade Architecture

Blade digunakan sebagai presentation layer.

Logical view structure:

```text
resources/views/
├── layouts/
├── components/
├── auth/
├── dashboard/
├── rooms/
├── bookings/
├── calendar/
└── admin/
    ├── dashboard/
    ├── rooms/
    ├── facilities/
    ├── users/
    └── bookings/
```

Blade tidak menjalankan business logic booking.

Blade hanya:

```text
display data
conditional presentation
form
links
UI state
```

---

# 44. UI Integration

Google Stitch menjadi visual reference utama.

Implementasi Blade harus mengikuti:

```text
Modern Corporate
Clean
Professional
Light Theme
Minimal Decoration
No Gradients
No Glassmorphism
No Excessive Shadows
```

Primary color:

```text
#2563EB
```

Font:

```text
Inter
```

UI architecture tidak mengubah business architecture.

---

# 45. Service Dependency Direction

Dependency harus mengarah ke bawah:

```text
Controller
   ↓
Service
   ↓
Model
   ↓
Database
```

Controller tidak memanggil Controller lain.

Service tidak mengembalikan Blade View.

Model tidak mengetahui HTTP request.

Form Request tidak menjalankan workflow booking.

---

# 46. Testing Boundary

Testing akan dilakukan setelah implementasi dasar.

Prioritas testing:

1. Booking overlap.
2. Boundary time.
3. Pending blocking.
4. Approved blocking.
5. Rejected not blocking.
6. Cancelled not blocking.
7. Room inactive.
8. Minimum duration.
9. Operating hours.
10. Past date.
11. User ownership.
12. Admin approval.
13. Admin rejection.
14. Rejection reason.
15. Double booking under concurrent requests.

Business-critical tests harus berfokus pada BookingService dan AvailabilityService.

---

# 47. Future Change Strategy

Jika mentor memberikan rule baru, perubahan dilakukan pada layer yang sesuai.

Contoh:

## Operating hours berubah

Dari:

```text
08:00–17:00
```

menjadi:

```text
07:30–18:00
```

Tidak perlu mengubah database schema.

---

## Cancellation policy berubah

Misalnya:

```text
Approved booking hanya dapat dibatalkan
minimal 2 jam sebelum booking.
```

Rule diubah pada:

```text
BookingService
```

atau configuration jika dibuat configurable.

---

## New room attribute

Jika perusahaan meminta:

```text
floor
```

barulah database design dan migration dapat diperbarui.

Jangan menambahkan field hanya berdasarkan kemungkinan.

---

# 48. Implementation Order

Setelah dokumen arsitektur ini disetujui, implementasi Laravel dilakukan bertahap:

```text
1. Laravel project setup
        ↓
2. Environment + MySQL
        ↓
3. Migrations
        ↓
4. Models + relationships
        ↓
5. Authentication
        ↓
6. Admin middleware / authorization
        ↓
7. Form Requests
        ↓
8. AvailabilityService
        ↓
9. BookingService
        ↓
10. Controllers + routes
        ↓
11. Blade layouts/components
        ↓
12. User pages
        ↓
13. Admin pages
        ↓
14. Calendar UI
        ↓
15. Dummy seeders
        ↓
16. Feature / business tests
        ↓
17. Final UI refinement
```

Urutan ini menjaga business logic dibuat sebelum UI bergantung terlalu jauh pada implementasi.

---

# 49. Architecture Decision Summary

Keputusan arsitektur final sementara:

| Area | Decision |
|---|---|
| Framework | Laravel 12 |
| Database | MySQL |
| Frontend | Blade + HTML + CSS + JavaScript |
| Pattern | MVC |
| Business logic | Service Layer |
| Main booking service | BookingService |
| Availability | AvailabilityService |
| ORM | Eloquent |
| Authentication | Laravel Authentication |
| Authorization | Middleware + Policy |
| Booking privacy | Server-side authorization/filtering |
| Double booking | Service + transaction + locking |
| Validation | Form Request + Service |
| UI reference | Google Stitch |
| Database tables | 5 tables |
| Repository pattern | Not used initially |
| DTO layer | Not used initially |
| API layer | Not required initially |
| Notifications | Not implemented initially |
| Recurring booking | Not implemented |
| Attendees | Not implemented |

---

# 50. Review Against Previous Documents

Arsitektur ini konsisten dengan:

```text
Requirement.md
Project Specification.md
ERD.md
Database Design.md
Use Case.md
User Booking.md
Admin Approval.md
Migration Design.md
```

Tidak mengubah:

- role;
- booking status;
- availability rule;
- double booking formula;
- room/facility relationship;
- database table scope;
- privacy rule;
- temporary booking assumptions.

Catatan arsitektur yang perlu diperhatikan:

1. `approved_by` tetap nullable dan mereferensikan `users`.
2. Pending dan approved tetap memblokir availability.
3. Rejected, cancelled, dan completed tidak memblokir availability.
4. Availability checking tetap dilakukan pada tiga tahap.
5. Final availability check dilakukan sebelum create booking.
6. Admin approval tetap melakukan availability validation.
7. User tidak menerima detail booking user lain melalui calendar.
8. Controller tidak menjadi tempat utama business logic.
9. Tidak ada fitur tambahan di luar scope.

---

# 51. Architecture Status

Status dokumen:

```text
Laravel Architecture
= DRAFT FINAL FOR REVIEW
```

Laravel coding:

```text
NOT STARTED
```

Tahap berikutnya setelah dokumen ini disetujui:

```text
Laravel Project Setup
↓
Migration Implementation
↓
Model & Eloquent Relationship
```

Model dan migration implementation tetap belum dilakukan sampai arsitektur ini disetujui.
