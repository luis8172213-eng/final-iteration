# Campus Reserve Capstone Guide

## 1. Project Overview
Campus Reserve is a Laravel app for reserving campus facilities. It includes user login, optional 2FA, reservation requests, admin approval, a visual calendar, notifications, and encrypted user data.

**Tech Stack:**
- **Framework**: Laravel (PHP web framework)
- **Back End**: PHP with Laravel for server-side logic, routing, and API endpoints
- **Front End**: Blade templates with Tailwind CSS for styling, JavaScript for interactivity (FullCalendar library)
- **Database**: SQLite for development (local file-based), configured for MySQL/MariaDB in production via XAMPP

Main features:
- User authentication with 2FA
- Create/edit/cancel reservations
- Pending approval workflow
- FullCalendar booking view
- Admin dashboard and audit logging
- Database notifications
- Tailwind CSS UI

---

## 2. Core Components

### Controllers
- `AuthController.php`: login, 2FA, admin login, profile actions
- `CalendarController.php`: fetch calendar events, store reservations
- `AdminController.php`: review and approve/reject bookings
- `NotificationController.php`: user notifications

### Models
- `Facility.php`: availability checks
- `Reservation.php`: booking status and timestamps
- `User.php`: encrypted user fields and relationships
- `SavedCredential.php`: encrypted saved credentials

### Routes & Views
- `routes/web.php`: all web route definitions
- `calendar.blade.php`: main calendar interface
- `calendar-manage.blade.php`: reservation form/modal
- `reserve.blade.php`: user reservation list
- `admin/dashboard.blade.php`: admin actions
- `notifications/index.blade.php`: notification list

---

## 3. Login & Security
Flow:
1. User opens login page
2. AuthController validates credentials
3. If 2FA enabled, issue OTP and show verification page
4. Verify OTP and redirect to calendar/reserve

Security:
- Sensitive fields encrypted in DB
- Email hash used for lookups
- OTPs are hashed and expire quickly
- Admin routes protected with role checks

---

## 4. Reservation Flow
- Users load calendar and see approved bookings
- New requests are saved as `pending`
- Availability is checked with `Facility::isAvailable()` before save
- Pending bookings appear in the sidebar, not on the main calendar
- Admin approves/rejects and users get notified

Validation:
- Reservation end time must be after start time
- Default booking window loads as 06:00–07:00

---

## 5. Calendar & Notifications
- FullCalendar fetches events from `CalendarController@events`
- Events include title, start/end, status color, and details
- Notifications use Laravel database notifications
- Key notification types: pending approval, status changed

---

## 6. Admin Workflow
- Admin login through hidden `/admin-secret`
- Admin dashboard lists pending reservations
- Approve/reject actions update status and trigger notifications
- Actions are recorded in `AuditLog`

---

## 7. Simple System Flows
- **Reservation flow**: login → open calendar → choose day → fill form → validate time → save as pending → notify admins.
- **Pending flow**: pending items load via `/api/calendar/pending` → show left sidebar → remain off calendar until approved.
- **Approval flow**: admin reviews pending requests → checks conflicts → approves/rejects → user notified and status updated.
- **Authentication flow**: login → optional 2FA → access reserve/calendar → submit reservation requests.

---

## 8. Database & Deployment
- Uses Laravel migrations and Eloquent models
- Tables: `users`, `facilities`, `reservations`, `saved_credentials`, `notifications`, `audit_logs`
- Encrypted fields handled by model casts

Run commands:
```bash
php artisan migrate
php artisan db:seed
php artisan serve
```

---

## 9. Defense Essentials
Talk about:
- MVC separation: routes, controllers, models, views
- 2FA and OTP design: hashed, expiring, limited attempts
- Pending approval workflow and conflict checks
- Notification delivery through Laravel
- Encrypted sensitive data and privacy-aware lookup design

Recent UI updates:
- Pending bookings shown separately
- Light blur modal overlay
- Inline reservation validation
- Reserve button aligned with list controls

---

## 10. Quick Reference
- OTP stored in `otps` table, hashed
- OTP lifetime is short and cleanup is automated
- Pending bookings are not visible on the main calendar until approved
- Admin approval updates reservation status and notifies users

Good luck with your capstone defense!
