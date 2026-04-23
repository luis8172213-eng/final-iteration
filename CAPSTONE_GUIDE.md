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

### Development Setup
If the app doesn't load properly in browsers (e.g., Chrome), run these in separate terminals simultaneously:
1. First terminal: `npm run dev` (compiles front-end assets like JS and CSS)
2. Second terminal: `php artisan serve` (starts the Laravel development server)

Both are needed because Laravel uses Vite for asset compilation, requiring separate processes for front-end builds and back-end serving.

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

## 10. Encryption & Hashing Guide

### Password Hashing
Passwords are hashed using Laravel's `Hash` facade with **bcrypt** algorithm:
```php
// In AuthController@login
$user = User::where('email', $request->email)->first();
if (Hash::check($request->password, $user->password)) {
    // Password matches
}
```
- Passwords are **never** stored in plain text
- Each password is salted and hashed during registration
- `Hash::check()` safely compares plain password against hashed version
- Cannot be decrypted—only verified via comparison

### OTP Hashing & Encryption
OTP (One-Time Password) storage in the `otps` table:
```php
// OTP is hashed before saving
'otp_hash' => Hash::make($otp),  // Bcrypt hashed
'expires_at' => now()->addMinutes(5),  // 5-minute expiry
'attempts' => 0  // Track failed attempts
```
- OTPs are **hashed**, not encrypted (cannot be reversed)
- Verification uses `Hash::check()` like passwords
- Automatically cleaned up after expiration via cleanup command
- Limited to 3 failed attempts before lockout

### Encrypted User Data
Sensitive fields in the `users` table are encrypted using Laravel's **AES-256 encryption**:
```php
// In User model
protected $casts = [
    'phone_number' => 'encrypted',
    'id_number' => 'encrypted',
    'additional_info' => 'encrypted'
];
```
- These fields are encrypted at rest in the database
- Automatically decrypted when accessed as model attributes
- Cannot read encrypted value directly from database without Laravel app

---

## 11. Decrypting Data with PHP Tinker

**PHP Tinker** is Laravel's interactive shell for testing and debugging. Use it to decrypt or test encrypted data:

### Start Tinker
```bash
php artisan tinker
```

### Decrypt User Encrypted Fields
```php
// Get a user by ID
$user = App\Models\User::find(1);

// Encrypted fields auto-decrypt when accessed
echo $user->phone_number;     // Shows decrypted value
echo $user->id_number;        // Shows decrypted value
```

### Verify OTP Hash
```php
// Get a specific OTP record
$otp = DB::table('otps')->where('user_id', 1)->first();

// Check if a plain OTP matches the hash
$plainOtp = "123456"; // The OTP user entered
Hash::check($plainOtp, $otp->otp_hash) // Returns true/false
```

### Verify Password Hash
```php
// Get a user
$user = App\Models\User::find(1);

// Check if a password matches
Hash::check("plainPassword123", $user->password) // Returns true/false
```

### View Encrypted Database Values Directly
```php
// View raw encrypted value (unreadable)
DB::table('users')->where('id', 1)->first();
// Shows encrypted string in phone_number field

// To get the actual decrypted value
$user = App\Models\User::find(1);
echo $user->phone_number; // Returns plain text
```

### Exit Tinker
```php
exit
```

---

## 12. Quick Reference
- OTP stored in `otps` table, hashed with bcrypt
- OTP lifetime is short and cleanup is automated
- Passwords are bcrypt hashed—never stored in plain text
- Encrypted user fields (phone, ID) use AES-256 and auto-decrypt via Laravel models
- Use `Hash::check()` to verify passwords and OTPs without decryption
- Use PHP Tinker to test and decrypt data during development
- Pending bookings are not visible on the main calendar until approved
- Admin approval updates reservation status and notifies users

Good luck with your capstone defense!
