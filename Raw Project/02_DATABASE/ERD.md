# ERD — Meeting Room Reservation System

Versi: 1.0
Status: Draft Final untuk Review
Database: MySQL

---

# 1. Entity Relationship Diagram

```mermaid
erDiagram

    USERS ||--o{ BOOKINGS : creates
    USERS ||--o{ BOOKINGS : approves
    ROOMS ||--o{ BOOKINGS : has
    ROOMS ||--o{ ROOM_FACILITIES : has
    FACILITIES ||--o{ ROOM_FACILITIES : assigned_to

    USERS {
        bigint id PK
        varchar name
        varchar email UK
        varchar password
        enum role
        timestamp created_at
        timestamp updated_at
    }

    ROOMS {
        bigint id PK
        varchar name
        varchar location
        int capacity
        text description
        enum status
        varchar image
        timestamp created_at
        timestamp updated_at
    }

    FACILITIES {
        bigint id PK
        varchar name UK
        text description
        timestamp created_at
        timestamp updated_at
    }

    ROOM_FACILITIES {
        bigint room_id PK, FK
        bigint facility_id PK, FK
    }

    BOOKINGS {
        bigint id PK
        bigint user_id FK
        bigint room_id FK
        bigint approved_by FK
        varchar title
        text description
        date booking_date
        time start_time
        time end_time
        enum status
        timestamp approved_at
        text rejection_reason
        timestamp created_at
        timestamp updated_at
    }