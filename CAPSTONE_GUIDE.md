# Campus Reserve Capstone Guide

## 1. Project Overview

Campus Reserve is a web application built with Laravel that enables users to reserve campus facilities. It features secure login with two-factor authentication, full reservation management, a visual calendar, real-time notifications, and an admin interface for reviewing bookings.

Main features:
- User authentication with 2FA
- Creating, editing, viewing, and canceling reservations
- Interactive calendar display
- Notification system for status updates
- Admin approval process
- Encrypted storage for user credentials
- Consistent UI design with Tailwind CSS

---

## 2. Core Files and Folders

#### Routes
- `routes/web.php`: Central routing file handling all web routes for authentication, reservations, notifications, and admin access.

#### Controllers
- `AuthController.php`: Manages user login, registration, logout, admin access, OAuth, 2FA, and profile updates.
- `CalendarController.php`: Provides calendar event data and handles new reservation submissions.
- `NotificationController.php`: Displays notifications, marks them as read, and handles deletions.
- `AdminController.php`: Admin dashboard, reservation approvals/rejections, user role management.
- `FacilityController.php`: Supplies facility data for forms.
- `CredentialController.php`: Manages saved user credentials.

#### Models
- `User.php`: Handles user data, encryption, and relationships.
- `Reservation.php`: Manages reservation records and status.
- `Facility.php`: Facility data with availability checks.
- `SavedCredential.php`: Encrypted credential storage.
- `AuditLog.php`: Logs admin actions.

#### Views
- `layouts/app.blade.php`: Main layout template.
- `reserve.blade.php`: User reservation list.
- `reservations/edit.blade.php`: Edit reservation form.
- `calendar.blade.php`: Calendar interface.
- `notifications/index.blade.php`: Notification management.
- `auth/*.blade.php`: Authentication pages.
- `admin/dashboard.blade.php`: Admin panel.

#### Configuration Files
- `.env.example`: Environment settings for database, cache, etc.
- `config/app.php`: Application name, timezone, locale, and service provider settings.
- `config/auth.php`: Authentication guard and password reset configurations.
- `config/cache.php`: Cache driver selection and expiration settings.
- `config/database.php`: Database connection settings and drivers.
- `config/filesystems.php`: Disk configuration for file storage (local, public, S3).
- `config/logging.php`: Logging channels and output configuration.
- `config/queue.php`: Queue driver and job settings.
- `config/services.php`: Third-party service credentials (Google OAuth, payment gateways, etc.).
- `config/session.php`: Session driver, lifetime, and cookie settings.
- `resources/js/bootstrap.js`: AJAX setup.
- `resources/js/app.js`: Frontend entry point.
- `resources/css/app.css`: Styles with Tailwind.
- `vite.config.js`: Build configuration.

#### Database
- `database/migrations/`: SQL migration files for creating and modifying tables.
- `database/seeders/`: Data seeders for populating initial test data.
  - `UserSeeder.php`: Creates default admin and test users.
  - `FacilitySeeder.php`: Populates facility database records.
  - `DummyReservationSeeder.php`: Creates sample reservations for testing.
- `database/factories/`: Factory classes for generating test data with Faker.
- `database.sqlite`: Local SQLite database file (development only).

#### Middleware
- `app/Http/Middleware/SecurityHeaders.php`: Adds security headers (CSP, X-Frame-Options, etc.) to protect against common web attacks.

#### Notifications
- `app/Notifications/ReservationPendingApproval.php`: Notifies admins when a new reservation requires approval.
- `app/Notifications/ReservationStatusChanged.php`: Notifies users when reservation status changes (approved/rejected).

#### Bootstrap
- `bootstrap/app.php`: Application initialization and service provider registration.
- `bootstrap/providers.php`: Bootstrap providers configuration.
- `bootstrap/cache/`: Cached configuration and service files.

#### Public
- `public/index.php`: Application entry point (front controller).
- `public/build/`: Compiled CSS/JS assets from Vite.
- `public/storage/`: Symbolic link to user uploads (profile pictures, credentials).
- `public/fullcalendar/`: FullCalendar library and assets.
- `public/js/`: Frontend JavaScript files.
- `public/robots.txt`: SEO directives for search engines.
- `public/.htaccess`: Apache rewrite rules for pretty URLs.

---

## 3. User Login Process

1. User accesses the login page.
2. Routes direct to AuthController for form display.
3. Submission validates credentials via AuthController@login.
4. If 2FA is active, an OTP is sent and session prepared.
5. User enters 2FA code or proceeds if disabled.
6. Successful login redirects to the reservation page.

Key authentication methods in AuthController.php:
- login(), issueOtp(), showAdminLogin(), loginAdmin(), show2faVerify(), verify2fa().

Security highlights:
- Personal data (email, name, phone) is encrypted in the database.
- Uses hashed email for lookups to protect privacy.
- 2FA codes are securely hashed with expiration timestamps.

---

## 4. Reservation Workflow

### Creating a Reservation
1. User loads the calendar page.
2. Frontend fetches existing events via CalendarController@events.
3. User submits a new booking.
4. CalendarController@store validates and checks availability using Facility::isAvailable().
5. Reservation is saved as 'pending' and admins are notified.

### Editing or Deleting Reservations
- Edit: Access edit form, update via routes in web.php.
- Delete: Single or bulk deletion handled by route closures.

Core files:
- routes/web.php: Reservation routes.
- CalendarController.php: Event data and storage.
- Facility.php: Availability checks.
- Reservation.php: Status and date handling.

---

## 5. Calendar Integration

The calendar uses FullCalendar library. Events are fetched from CalendarController@events API endpoint.

Response includes:
- Title: Facility name
- Start/End: ISO datetime strings
- Color: Based on reservation status
- Extended properties: Purpose, status, room details

This connects backend data to the interactive frontend calendar.

---

## 6. Notifications and Admin Approval

### Notification Management
- View: NotificationController@index loads user notifications.
- Mark Read: NotificationController@markAllRead.
- Delete: NotificationController@destroySelected removes notifications and linked pending requests.

### Admin Approval Process
- Admins access /admin routes.
- AdminController methods approve/reject reservations, log actions in AuditLog, and send status notifications via ReservationStatusChanged.

### Notification System
- Uses Laravel's database notifications table.
- Key notifications: ReservationPendingApproval (for submissions) and ReservationStatusChanged (for decisions).

---

## 7. Admin Features

### Admin Access
- Login via hidden /admin-secret page.
- AuthController@loginAdmin() handles validation.
- Supports 2FA if enabled.

### Admin Functions
- Dashboard: AdminController@dashboard.
- Reservation management: Approve, reject, delete.
- User management: Role updates and deletions.

### Security Measures
- requireAdmin() and requireSuperAdmin() protect actions.
- Super admin role prevents unauthorized demotion.

---

## 8. Database Setup

### Configuration
- .env file: Database connection (host, port, credentials).
- config/database.php: Schema settings.

### Key Tables
- users, facilities, reservations, saved_credentials, notifications, audit_logs.

### Migrations
- User table with 2FA: add_profile_phone_and_2fa_settings_to_users_table.php
- Admin and audit logs: add_is_admin_to_users_and_create_audit_logs_table.php
- Notifications: create_notifications_table.php

### Data Handling
- Eloquent ORM for database interactions.
- Relationships: belongsTo/hasMany.
- Encryption for sensitive data using Laravel Crypt.

---

## 9. Key Files for Defense

### Authentication & Security
- AuthController.php, User.php, user table migrations.

### Reservation Logic
- routes/web.php, CalendarController.php, Reservation.php, Facility.php, reserve views.

### Admin Review
- AdminController.php, ReservationStatusChanged.php, ReservationPendingApproval.php, admin dashboard.

### Notifications
- NotificationController.php, notifications view.

### Data Security
- SavedCredential.php, User.php, saved_credentials migration.

---

## 10. Dependencies & Build

### PHP (composer.json)
- Core: Laravel 12.0, Socialite for OAuth.
- Dev: PHPUnit for testing, Pint for linting.
- Scripts: setup (full install), dev (servers), test (run tests).

### Frontend (package.json)
- Dev: Vite, Tailwind CSS.
- Prod: FullCalendar for calendar UI.
- Scripts: dev (hot reload), build (production).

Build process: Vite compiles JS/CSS, Laravel serves via @vite.

---

## 11. Quick run commands

```bash
php artisan migrate
php artisan db:seed
php artisan serve
```

If you are using a local environment with XAMPP, ensure the `.env` database settings match the local MySQL/MariaDB instance.

---

## 11. System Overview

### Reservation Creation
1. User logs in and accesses the calendar.
2. Existing bookings are loaded via API.
3. User submits a booking request.
4. System validates and checks facility availability.
5. Saves as pending and notifies admins.

### Admin Approval
1. Admin logs in to dashboard.
2. Reviews pending reservations.
3. Approves or rejects, logging the action.
4. User gets notified of the decision.

### Notifications
- Stored in database, managed via controller.
- Users can mark as read or delete.
- Linked to reservation status changes.

---

## 12. Defense Talking Points
- Clean MVC architecture with separated concerns.
- Robust security: 2FA, encrypted data, hashed lookups.
- Conflict-free reservations via availability checks.
- Integrated notification system using Laravel's framework.
- Audited admin actions for accountability.
- Encrypted sensitive data at the model level.

---

## 13. Q&A Defense Preparation

Professors typically test code block knowledge and logical flow. Prepare for these areas:

### Security & Authentication (High Priority)
- **OTP Flow**: Explain end-to-end (generation → hashing → database storage → verification → deletion).
- **Why hash OTP?** Prevents exposure if database is compromised. Even if someone accesses DB, hashed OTP is useless.
- **Email encryption + hashing**: Why both? Email is encrypted for storage privacy. Hash enables lookups without decrypting entire database.
- **Mid-2FA scenarios**: What happens if user closes browser after OTP sent? Session persists pending_2fa_user_id but expires if code expires.
- **Brute force protection**: Maximum 3 attempts in 30 seconds. User must resend if exhausted.

### Database & Data Integrity
- **Separate OTP table**: Why not store in users table? Separation of concerns. OTPs are temporary, transient data. Keeps users table clean.
- **Foreign key cascade**: DELETE on CASCADE ensures OTPs are removed when user is deleted. No orphaned records.
- **Index on expires_at**: Speeds up cleanup queries that find records where expires_at < NOW().
- **Race conditions**: Cleanup command runs every minute. What if two processes delete same OTP? Database handles with atomic operations.

### Edge Cases They Will Ask
- **Multiple OTP requests**: Each new issueOtp() deletes old OTPs for that user first (via Otp::where('user_id', $user->id)->delete()).
- **Refresh page 5 times during 2FA**: Session state persists pending_2fa_user_id. Each refresh checks if OTP expired. If yes, redirect to login.
- **Scheduler failure**: Cleanup never runs. Database bloats with expired records. Solution: Add cleanup to logout process or add cron alerts.
- **Device remember cookie expires**: User must re-authenticate with 2FA. Cookie is separate from OTP mechanism.

### Code Quality & Design Decisions
- **Why delete() after verify vs NULL fields**: Cleaner approach. Deleted record means OTP consumed. No need to store null references.
- **Error handling**: verify2fa() checks if Otp exists and is valid before Hash::check(). Prevents exceptions.
- **Logging OTP to file**: Development convenience. In production, would send via email/SMS provider (e.g., Twilio, SendGrid).
- **Why removed 2FA columns from users table**: Moved to Otp model for better design. Users table was getting bloated with temporary data.

### Tracing the Flow (Whiteboard Practice)
Walk through this scenario: "A user enters wrong OTP 3 times"
1. First attempt: incrementAttempts() → attempts = 1. Error message.
2. Second attempt: incrementAttempts() → attempts = 2. Error message.
3. Third attempt: incrementAttempts() → attempts = 3. Check fails: !$otp->isValid() because attempts >= 3.
4. Response: "Code expired or too many attempts. Please resend a new code."
5. Resend endpoint: Deletes old OTP, creates new one, resets attempts to 0.

### Improvements & Follow-up Questions
Be ready to discuss what you'd improve:
- **TOTP support**: Time-based one-time passwords (authenticator apps like Google Authenticator).
- **Recovery codes**: If user loses phone, backup codes for account access.
- **Rate limiting per IP**: Prevent brute force from same IP address.
- **Email/SMS integration**: Currently logs to file. How would you implement actual delivery?
- **Audit logging**: Currently logs OTP generation. Could log verification attempts too.

### Quick Reference Answers
| Question | Answer |
|----------|--------|
| Where is OTP stored? | Database `otps` table. Hashed with bcrypt. |
| How long does OTP last? | 30 seconds from generation. |
| Where is it sent to user? | Currently logged to `storage/logs/otp.log` for development. |
| How many attempts allowed? | Maximum 3 within the 30-second window. |
| What happens after expiry? | Automatic deletion via `php artisan otps:cleanup` command (runs every minute). |
| Why separate table? | Keeps data model clean. OTPs are temporary. Enables efficient cleanup. |
| What if user never uses OTP? | Cleaned up automatically after expiration. |

### Practice Session Tips
1. **Know your migrations by date**: They show your development timeline and thought process.
2. **Trace login flow on whiteboard**: Draw boxes for Auth, OTP Model, Database. Show data flow.
3. **Explain encryption strategy**: Why email is encrypted but email_hash is not.
4. **Be confident about limitations**: Don't pretend OTP is sent via email if it isn't. Mention it's logged for testing.
5. **Have backup answers**: "That's a great point. In production, we'd use X service for Y reason."

Good luck with your capstone defense!
