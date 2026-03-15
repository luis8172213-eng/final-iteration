<?php
// ...existing code...
use App\Models\Reservation;
// DEBUG: Dump all reservations with facility as JSON
Route::get('/debug/reservations', function () {
    return Reservation::with('facility')->get();
});
// Calendar management subpage (not in navbar)
Route::middleware('auth')->get('/calendar/manage', function () {
    return view('calendar-manage');
});
use App\Http\Controllers\FacilityController;
// API endpoint for facility dropdown
Route::get('/api/facilities', [FacilityController::class, 'list']);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CredentialController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home page
Route::get('/', function () {
    return view('welcome');
})->name('home');


use App\Http\Controllers\CalendarController;
// Static pages
Route::get('/calendar', function () {
    return view('calendar');
})->name('calendar');


// Calendar API endpoints
Route::get('/api/calendar/events', [CalendarController::class, 'events']);
Route::middleware('auth')->post('/api/calendar/reserve', [CalendarController::class, 'store']);

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

/*
|--------------------------------------------------------------------------
| Authentication Routes (Guest only)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Register
    Route::get('/signup', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/signup', [AuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Auth required)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Reserve page (Dashboard) - requires login
    Route::get('/reserve', function () {
        return view('reserve');
    })->name('reserve');

    // Saved Credentials (Password Manager)
    Route::resource('credentials', CredentialController::class);
    Route::get('/credentials/{credential}/password', [CredentialController::class, 'getPassword'])
        ->name('credentials.password');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
