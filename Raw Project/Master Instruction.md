# MASTER INSTRUCTION
# Meeting Room Reservation System

## 1. INSTRUCTION PURPOSE

Dokumen ini adalah MASTER INSTRUCTION untuk implementasi Meeting Room Reservation System menggunakan Antigravity.

Master Instruction ini menjadi pedoman utama implementasi.

Antigravity WAJIB membaca dan memahami seluruh dokumen project di dalam Raw Project sebelum melakukan coding.

Jika terjadi konflik antara dokumen, JANGAN langsung memilih salah satu. Identifikasi konflik, jelaskan, dan gunakan keputusan yang telah disepakati dalam Master Instruction ini sebagai baseline sementara.

Jangan mengubah requirement, database structure, business rule, architecture, atau UI direction tanpa alasan yang jelas dan tanpa meminta konfirmasi jika perubahan tersebut berdampak pada scope atau architecture.

---

# 2. PROJECT

Nama:

Meeting Room Reservation System

Tujuan:

Aplikasi web internal perusahaan untuk reservasi/booking meeting room.

Platform:

Web Application

Project ini merupakan project magang.

Data perusahaan asli belum tersedia.

Gunakan dummy data untuk development.

Company-specific rules yang belum diberikan mentor harus dianggap sebagai temporary assumptions.

Temporary assumptions harus dibuat mudah diubah dan tidak disebarkan secara tidak terkontrol ke banyak bagian aplikasi.

---

# 3. TECHNOLOGY STACK

Gunakan:

- Laravel 12
- PHP sesuai requirement Laravel 12
- MySQL
- Blade
- HTML
- CSS
- JavaScript
- Laravel Authentication
- Eloquent ORM

Architecture:

```text
Route
↓
Middleware
↓
Controller
↓
Form Request / Validation
↓
Service
↓
Eloquent Model
↓
MySQL
```

Gunakan MVC + Service Layer.

Jangan menggunakan architecture yang lebih kompleks tanpa kebutuhan nyata.

---

# 4. REQUIRED ARCHITECTURE

Business logic booking TIDAK BOLEH seluruhnya berada di Controller.

Core booking architecture:

```text
BookingController
↓
BookingService
↓
AvailabilityService
↓
Eloquent Models
↓
MySQL
```

BookingService bertanggung jawab terhadap booking workflow.

AvailabilityService bertanggung jawab terhadap reusable availability logic.

Controller harus tetap thin.

Controller bertanggung jawab terutama untuk:

- menerima request;
- memanggil Form Request;
- memanggil Service;
- menentukan response/redirect;
- mengirim data ke View.

---

# 5. DO NOT OVER-ENGINEER

Jangan menambahkan tanpa requirement:

- Repository Pattern
- DTO layer
- CQRS
- Event-driven architecture
- Microservices
- Domain-driven architecture
- API layer
- notification system
- attendee system
- recurring booking
- payment system
- audit log
- department
- building
- floor
- additional database tables

Gunakan struktur sederhana:

```text
Controller
↓
Service
↓
Model
↓
Database
```

Tambahkan abstraction hanya jika kebutuhan implementasi nyata membutuhkannya.

---

# 6. USER ROLES

Hanya ada dua role:

```text
admin
user
```

## Admin

Admin dapat:

- login;
- melihat dashboard;
- melihat seluruh room;
- menambah room;
- mengubah room;
- mengaktifkan/nonaktifkan room;
- mengelola facilities;
- melihat seluruh user;
- melihat seluruh booking;
- melihat kalender seluruh room;
- melihat detail booking;
- approve booking;
- reject booking;
- memberikan rejection reason;
- melihat statistik reservasi.

## User

User dapat:

- login;
- melihat dashboard;
- melihat daftar room;
- melihat detail room;
- melihat kapasitas room;
- melihat fasilitas room;
- melihat calendar availability;
- melihat jadwal room yang sudah terbooking;
- mencari room berdasarkan tanggal dan waktu;
- mencari berdasarkan kapasitas;
- membuat booking;
- melihat booking miliknya sendiri;
- melihat status booking;
- melihat detail booking miliknya;
- membatalkan booking sesuai aturan.

User TIDAK dapat:

- mengelola room;
- mengelola facility;
- mengelola user lain;
- approve booking;
- reject booking;
- melihat detail pribadi booking user lain.

---

# 7. IMPORTANT USER STATUS DECISION

JANGAN menambahkan field `status` ke table `users`.

User Management hanya menggunakan:

- Name
- Email
- Role

Tidak ada:

```text
users.status
```

Tidak ada fitur activate/deactivate user pada scope saat ini.

Jika mentor memberikan requirement baru tentang active/inactive user, perlakukan sebagai change request baru dan jangan mengimplementasikannya secara asumtif.

---

# 8. DATABASE SCOPE

Database awal WAJIB hanya menggunakan:

```text
users
rooms
facilities
room_facilities
bookings
```

Jangan menambahkan table lain tanpa requirement.

---

# 9. USERS TABLE

Fields:

```text
id
name
email
password
role
created_at
updated_at
```

Constraints:

```text
email UNIQUE
role = admin/user
```

Relationship:

```text
User hasMany Booking
```

User juga dapat menjadi approver melalui:

```text
bookings.approved_by
```

---

# 10. ROOMS TABLE

Fields:

```text
id
name
location
capacity
description
status
image
created_at
updated_at
```

Status:

```text
active
inactive
```

Room inactive TIDAK boleh digunakan untuk booking baru.

Room availability bukan field tersimpan.

Availability ditentukan berdasarkan:

```text
room
date
time
booking status
```

Relationship:

```text
Room hasMany Booking
Room belongsToMany Facility
```

---

# 11. FACILITIES

Fields:

```text
id
name
description
created_at
updated_at
```

`name` UNIQUE.

Relationship:

```text
Facility belongsToMany Room
```

Pivot:

```text
room_facilities
```

Fields:

```text
room_id
facility_id
```

Composite primary key:

```text
(room_id, facility_id)
```

Tidak perlu model pivot khusus kecuali implementasi benar-benar memerlukannya.

---

# 12. BOOKINGS TABLE

Fields:

```text
id
user_id
room_id
approved_by
title
description
booking_date
start_time
end_time
status
approved_at
rejection_reason
created_at
updated_at
```

Foreign keys:

```text
user_id → users.id
room_id → rooms.id
approved_by → users.id
```

`approved_by` nullable.

Relationship:

```text
Booking belongsTo User
Booking belongsTo Room
Booking belongsTo User as Approver
```

Recommended relationship method names:

```text
user()
room()
approver()
```

---

# 13. BOOKING STATUS

Valid statuses:

```text
pending
approved
rejected
cancelled
completed
```

Blocking statuses:

```text
pending
approved
```

Non-blocking statuses:

```text
rejected
cancelled
completed
```

Do not introduce additional status values without requirement.

---

# 14. BOOKING FLOW

Main flow:

```text
User Login
↓
Dashboard
↓
Calendar / Find Available Room
↓
Select date
↓
Select time
↓
Availability checking
↓
Show available rooms
↓
Select room
↓
Booking form
↓
Submit
↓
Pending
↓
Admin processing
↓
Approve / Reject
```

Approval:

```text
pending → approved
```

Rejection:

```text
pending → rejected
```

Cancellation:

```text
pending → cancelled
approved → cancelled
```

Completion:

```text
approved → completed
```

Only implement status transitions allowed by the business rules.

---

# 15. AVAILABILITY RULE

A room is considered unavailable if it has an overlapping booking with status:

```text
pending
approved
```

Overlap formula:

```text
existing.start_time < requested.end_time
AND
existing.end_time > requested.start_time
```

Boundary adjacency is valid.

Example:

```text
10:00–12:00
12:00–14:00
```

is VALID.

These are invalid:

```text
09:00–11:00
10:00–11:00
11:00–13:00
09:00–13:00
```

when an existing booking is:

```text
10:00–12:00
```

---

# 16. AVAILABILITY MUST BE CHECKED THREE TIMES

Availability must be checked at:

## Stage 1

When user searches for available rooms.

## Stage 2

When user opens/submits booking form.

## Stage 3

Immediately before booking is inserted into database.

Stage 3 is mandatory.

Never rely only on the availability result shown earlier in the UI.

---

# 17. DOUBLE BOOKING PROTECTION

Do NOT rely on a simple UNIQUE constraint to prevent time overlap.

Final booking creation must use a transaction.

Recommended logical flow:

```text
BEGIN TRANSACTION
↓
Lock relevant room/resource row
↓
Final availability check
↓
If unavailable:
    rollback / return conflict
↓
If available:
    create booking
↓
COMMIT
```

Use an appropriate MySQL row-locking strategy through Laravel/Eloquent.

The exact implementation must preserve the requirement that two concurrent requests cannot both successfully reserve the same overlapping room slot.

Do not claim concurrency protection merely by checking availability outside a transaction.

---

# 18. BOOKING SERVICE

Create:

```text
App\Services\BookingService
```

Responsibilities:

```text
create booking
approve booking
reject booking
cancel booking
```

BookingService must enforce business rules.

Do not put booking workflow directly into:

- Blade
- Controller
- route closure
- Model-only logic

---

# 19. AVAILABILITY SERVICE

Create:

```text
App\Services\AvailabilityService
```

Responsibilities:

```text
check room availability
find available rooms
detect blocking overlap
```

It should be reusable by:

- Find Available Room
- Calendar
- Booking creation
- Booking form validation
- Admin approval

Do not duplicate the overlap query in multiple Controllers.

---

# 20. TEMPORARY BOOKING RULES

Until official company requirements are available:

```text
Booking date cannot be in the past.
Start time must be before end time.
Minimum duration = 1 hour.
Operating hours = 08:00–17:00.
Room must be active.
User must be authenticated to create booking.
```

These are TEMPORARY ASSUMPTIONS.

Do not treat them as immutable company policy.

Keep their implementation easy to change.

---

# 21. CANCELLATION RULES

Temporary rules:

```text
pending:
    user can cancel

approved:
    user can cancel

rejected:
    cannot cancel

cancelled:
    cannot cancel

completed:
    cannot cancel
```

User ownership must be checked before cancellation.

A User must not be able to cancel another User's booking by changing an ID in the request.

---

# 22. APPROVAL

Admin approval flow:

```text
Admin
↓
Booking Approval
↓
BookingService::approve()
↓
Validate status transition
↓
Check relevant business rules
↓
Final availability check
↓
Set:
    status = approved
    approved_by = admin.id
    approved_at = current timestamp
```

Approval must not blindly change status without validation.

Because pending bookings block availability, approval should still perform a final consistency/availability check.

If the booking can no longer be approved, return a clear business error.

---

# 23. REJECTION

Admin rejection requires a rejection reason.

Flow:

```text
BookingService::reject()
↓
Validate status
↓
Validate rejection reason
↓
status = rejected
rejection_reason = supplied reason
```

Rejected booking no longer blocks availability.

---

# 24. PRIVACY

User may know that a room/time slot is occupied.

User MUST NOT see private details of another User's booking.

For calendar/availability data, do not expose to ordinary User:

```text
other user's name
other user's user_id
meeting title
meeting description
approved_by
rejection_reason
other private booking details
```

The User-facing calendar should expose only safe availability information such as:

```text
room
date
start_time
end_time
available/unavailable
```

Privacy must be enforced server-side, not only by hiding fields in Blade/JavaScript.

---

# 25. OWNERSHIP

A normal User may access detailed booking information only for their own booking.

Conceptual authorization:

```text
booking.user_id === authenticated_user.id
```

Use Policy/authorization for ownership-sensitive actions.

Do not rely only on frontend route visibility.

---

# 26. AUTHENTICATION

All booking actions require authentication.

Use Laravel authentication and:

```text
auth
```

middleware.

Do not implement custom authentication unless required.

---

# 27. ADMIN AUTHORIZATION

Admin functionality must be protected server-side.

Use:

```text
auth
+
admin authorization
```

for Admin routes.

Admin authorization must not rely on:

```text
hidden buttons
hidden menu
Blade condition only
JavaScript condition
```

---

# 28. ROUTE STRUCTURE

Logical groups:

```text
Public
Authenticated User
Admin
```

Authenticated User features:

```text
dashboard
rooms
calendar
room search
bookings
my bookings
```

Admin features:

```text
dashboard
rooms
facilities
users
booking approvals
admin calendar
```

Use route names and resource-style routes consistently.

Admin routes should be grouped under an appropriate admin prefix/name structure.

---

# 29. ADMIN FIND ROOM DECISION

Do not create an additional dedicated Admin "Find Available Room" page unless a requirement explicitly demands it.

Admin availability needs can be handled through:

```text
Admin Calendar
Booking Approval
```

However, `AvailabilityService` must remain reusable for both User and Admin workflows.

---

# 30. FORM REQUESTS

Use Laravel Form Requests for input validation where appropriate.

Examples:

```text
StoreBookingRequest
StoreRoomRequest
UpdateRoomRequest
StoreFacilityRequest
UpdateFacilityRequest
```

Form Requests handle input validation.

Business rules involving database state/workflow belong to Services.

Do not place the complete booking workflow inside Form Request rules.

---

# 31. MODELS

Main models:

```text
App\Models\User
App\Models\Room
App\Models\Facility
App\Models\Booking
```

Do not create unnecessary models for pivot tables.

Recommended relationships:

```text
User:
    hasMany(Booking::class)

Room:
    hasMany(Booking::class)
    belongsToMany(Facility::class)

Facility:
    belongsToMany(Room::class)

Booking:
    belongsTo(User::class)
    belongsTo(Room::class)
    belongsTo(User::class, 'approved_by')
```

Relationship names should remain clear and consistent.

---

# 32. MODEL RESPONSIBILITIES

Models handle:

- Eloquent relationships;
- casts;
- attributes;
- simple data-level helpers.

Models should not become the main location for the complete booking workflow.

Complex workflow remains in Services.

---

# 33. VALIDATION BOUNDARY

Separate:

## Input validation

Examples:

```text
required
string
date
time
integer
minimum values
```

Handled by Form Requests.

## Business validation

Examples:

```text
date not in past
operating hours
minimum duration
room active
availability
status transition
ownership
```

Handled by Service/authorization layer.

---

# 34. CALENDAR

Calendar concept:

```text
Room × Date × Time
```

User can see occupied time slots.

User cannot see private booking details.

Calendar data must be transformed into a safe presentation structure before being returned to the frontend.

---

# 35. FIND AVAILABLE ROOM

Search criteria:

```text
date
start_time
end_time
minimum capacity
```

Returned rooms must satisfy:

```text
room.status = active
room.capacity >= requested capacity
no blocking overlap
```

Use AvailabilityService.

Do not duplicate availability logic in Blade or Controller.

---

# 36. UI

Google Stitch is the primary visual reference.

Style:

```text
Modern Corporate
Clean
Professional
Enterprise internal application
Light theme
Minimal decoration
No gradients
No glassmorphism
No excessive shadows
No unnecessary illustrations
```

Design values:

```text
Primary: #2563EB
Background: #F9FAFB
Surface: #FFFFFF
Border: #E5E7EB
Primary text: #0F172A
Secondary text: #475569
Font: Inter
```

Existing Stitch screens should be followed rather than redesigned unnecessarily.

Possible existing screens include:

```text
Login
Employee Dashboard
Calendar
My Bookings
Find Room
Book Room
Admin Dashboard
Room Management
Booking Approvals
```

Additional screens may be implemented only when required by the agreed scope.

---

# 37. BLADE

Use Blade for presentation.

Recommended logical structure:

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

Blade must not contain booking business logic.

---

# 38. DUMMY DATA

Development uses dummy data because production company data is not available.

Minimum:

```text
Users:
1 Admin
3–5 Users

Rooms:
4–6 Rooms

Facilities:
6–8 Facilities

Bookings:
Pending
Approved
Rejected
Cancelled
Completed
```

Dummy data should make Dashboard and Calendar look realistic.

Use seeders/factories as appropriate without changing production schema.

---

# 39. IMPLEMENTATION ORDER

Antigravity MUST implement incrementally.

Recommended order:

```text
1. Inspect project and documents
2. Confirm Laravel project structure
3. Configure environment / MySQL
4. Implement migrations
5. Implement models + relationships
6. Implement authentication
7. Implement admin authorization
8. Implement Form Requests
9. Implement AvailabilityService
10. Implement BookingService
11. Implement routes/controllers
12. Implement Blade layouts/components
13. Implement User pages
14. Implement Admin pages
15. Implement Calendar
16. Implement seeders/dummy data
17. Implement tests
18. UI refinement
19. Final consistency review
```

Do not skip architecture by jumping directly into UI.

---

# 40. IMPLEMENTATION DISCIPLINE

Before implementing a feature:

1. Identify the corresponding requirement.
2. Identify affected database entities.
3. Identify the appropriate layer.
4. Implement the smallest correct solution.
5. Verify authorization.
6. Verify privacy.
7. Verify business rules.
8. Test the affected workflow.
9. Check that no unrelated scope was introduced.

---

# 41. DOCUMENT CONSISTENCY

The following project documents are authoritative project references:

```text
Requirement.md
Project Specification.md

02_DATABASE/
    ERD.md
    Database Design.md

03_SYSTEM ANALYSIS/
    Use Case.md

04_ACTIVITY DIAGRAM/
    User Booking.md
    Admin Approval.md

05_IMPLEMENTATION/
    Migration Design.md
    Laravel Architecture.md
```

Read these before coding.

If another document conflicts with them, identify the conflict instead of silently overriding the established design.

---

# 42. CHANGE CONTROL

Antigravity MUST NOT silently:

- add database tables;
- add database columns;
- add new roles;
- change booking statuses;
- change availability rules;
- change cancellation rules;
- expose private booking data;
- add external services;
- redesign the UI substantially;
- replace Blade with another frontend framework;
- introduce an API architecture;
- introduce complex architectural patterns.

If such a change appears necessary, stop and request confirmation.

---

# 43. ERROR HANDLING

Business rule errors must be handled cleanly.

Examples:

```text
Room is no longer available for the selected time.
Booking cannot be cancelled in its current status.
You are not authorized to access this booking.
```

Do not expose raw SQL/database exceptions to users.

Use appropriate validation/business error responses and preserve the UI flow.

---

# 44. SECURITY

At minimum:

- use Laravel authentication;
- use authorization middleware/policies;
- validate all user input;
- enforce ownership server-side;
- protect against mass assignment;
- use Eloquent/query builder safely;
- use CSRF protection for web forms;
- never trust hidden form fields for authorization;
- never rely on frontend hiding for privacy/security;
- protect booking creation against concurrent double booking.

---

# 45. TESTING PRIORITY

Business-critical tests should include:

1. Past booking date rejected.
2. Start time before end time.
3. Minimum one-hour duration.
4. Operating hours.
5. Inactive room cannot be booked.
6. Pending booking blocks slot.
7. Approved booking blocks slot.
8. Rejected booking does not block.
9. Cancelled booking does not block.
10. Completed booking does not block.
11. Exact boundary booking is allowed.
12. Overlapping booking is rejected.
13. User cannot access another user's booking detail.
14. User cannot cancel another user's booking.
15. Admin can approve.
16. Admin can reject.
17. Rejection reason is required.
18. Approval respects availability/business rules.
19. Concurrent booking attempts cannot create conflicting bookings.

---

# 46. TESTING FOCUS

Prioritize tests around:

```text
BookingService
AvailabilityService
Authorization
Booking lifecycle
```

Do not spend disproportionate effort testing trivial presentation markup before core booking correctness is established.

---

# 47. PRODUCTION DATA

Production/company data is NOT available.

Do not fabricate real company data.

Use clearly dummy development data.

Company-specific values must remain replaceable later.

---

# 48. ANTIGRAVITY WORKING RULE

Antigravity should act primarily as an IMPLEMENTER of the approved design.

It should NOT act as an independent product architect unless explicitly asked.

Before making a structural decision not already specified:

```text
1. Check existing documents.
2. Check Laravel Architecture.
3. Check this Master Instruction.
4. Prefer the smallest change.
5. If the change affects scope/schema/architecture, ask for confirmation.
```

---

# 49. PHASED EXECUTION

Do not implement the entire application blindly in one step.

Work in phases:

```text
Phase 1:
Project + Database + Models

Phase 2:
Authentication + Authorization

Phase 3:
Availability + Booking Service

Phase 4:
Routes + Controllers

Phase 5:
Blade/UI

Phase 6:
Admin Features

Phase 7:
Calendar

Phase 8:
Dummy Data

Phase 9:
Testing

Phase 10:
Final Review
```

After each major phase:

- verify the implementation;
- report what changed;
- report files created/modified;
- report tests/checks performed;
- identify remaining work;
- do not silently proceed into unrelated scope.

---

# 50. FINAL ACCEPTANCE CRITERIA

The implementation is considered aligned only if:

```text
✓ Laravel 12
✓ MySQL
✓ Blade
✓ MVC + Service Layer
✓ Two roles only
✓ Five initial database tables
✓ Booking lifecycle implemented
✓ Pending/Approved block availability
✓ Rejected/Cancelled/Completed do not block
✓ Correct overlap formula
✓ Boundary time allowed
✓ Three-stage availability checking
✓ Transaction + concurrency protection
✓ Admin approval/rejection
✓ Rejection reason
✓ User cancellation rules
✓ Ownership authorization
✓ Booking privacy
✓ Active/inactive room
✓ Room/facility many-to-many
✓ Google Stitch visual direction
✓ Dummy data
✓ No unnecessary features
✓ No unnecessary architecture
✓ Tests for critical booking rules
```

---

# 51. FINAL INSTRUCTION

The most important rule:

> DO NOT INVENT REQUIREMENTS.

Implement the agreed system faithfully.

When a requirement is ambiguous:

- use the documented temporary assumption if one exists;
- otherwise ask for clarification if the ambiguity affects schema, business logic, security, authorization, or architecture.

Do not silently make a major architectural decision.

Preserve consistency between:

```text
Requirement
Project Specification
Database Design
Use Case
Activity Diagram
Migration Design
Laravel Architecture
Implementation
```

The final application must be understandable, maintainable, appropriate for a project internship, and simple enough for future company-specific requirements to be incorporated without unnecessary rewrites.

---

# 52. CURRENT STARTING POINT

At the time this Master Instruction is issued:

```text
Requirements:
READY

Database Design:
READY

System Analysis:
READY

Activity Diagrams:
READY

Migration Design:
READY

Laravel Architecture:
FINAL APPROVED

Laravel Coding:
NOT STARTED
```

Antigravity should begin by inspecting the complete Raw Project and verifying that the implementation environment matches the approved documentation before writing application code.

Do not start by redesigning the system.
