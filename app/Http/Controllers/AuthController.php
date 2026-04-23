<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Client;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        $pendingUserId = session('pending_2fa_user_id');
        if ($pendingUserId) {
            $otp = Otp::where('user_id', $pendingUserId)->latest()->first();

            if (! $otp || $otp->isExpired()) {
                session()->forget(['pending_2fa_user_id', 'pending_2fa_email', 'show_2fa_modal', 'time_left', 'pending_admin_2fa']);
            }
        }

        return view('auth.login');
    }

    /**
     * Show the hidden admin login page.
     */
    public function showAdminLogin()
    {
        session()->forget(['pending_2fa_user_id', 'pending_2fa_email', 'show_2fa_modal', 'time_left', 'pending_admin_2fa']);

        return view('auth.admin-login');
    }

    /**
     * Show admin 2FA verification page.
     */
    public function showAdmin2fa()
    {
        if (! session('pending_2fa_user_id') || ! session('pending_admin_2fa')) {
            return redirect()->route('admin.login');
        }

        return view('auth.admin-login');
    }

    /**
     * Handle admin login request.
     */
    public function loginAdmin(Request $request)
    {
        // Check admin email and password. This route is hidden from regular users.
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:5', 'max:30'],
        ]);

        $emailHash = hash('sha256', strtolower($request->email));
        $user = User::where('email_hash', $emailHash)->first();

        logger()->debug('Admin login request received', [
            'email' => $request->email,
            'remember_device' => $request->boolean('remember_device'),
        ]);

        if (! $user || ! $user->is_admin || ! Hash::check($request->password, $user->password)) {
            logger()->debug('Admin login request failed credentials', [
                'email' => $request->email,
                'user_exists' => $user !== null,
                'is_admin' => $user ? $user->is_admin : null,
            ]);

            return back()->withErrors([
                'email' => 'The provided admin credentials are invalid.',
            ])->onlyInput('email');
        }

        if (! $user->two_fa_enabled) {
            logger()->debug('Admin login user has 2FA disabled', ['user_id' => $user->id]);
            Auth::login($user);
            $request->session()->regenerate();
            return redirect('/admin/dashboard');
        }

        logger()->debug('Admin login user requires 2FA', ['user_id' => $user->id]);
        session()->put('remember_device', $request->boolean('remember_device'));

        try {
            $this->issueOtp($user);
        } catch (\Exception $e) {
            logger()->error('Admin OTP generation failed', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['email' => 'Unable to generate the 2FA code. Please try again.'])
                ->onlyInput('email');
        }

        session()->put([
            'pending_2fa_user_id' => $user->id,
            'pending_2fa_email' => $user->email,
            'pending_admin_2fa' => true,
            'time_left' => 30,
        ]);

        return redirect('/admin-2fa')->with('show_2fa_modal', true);
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Check the user's email and password
        // If they have 2FA enabled, I'll send them a code
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:5', 'max:30'],
        ]);

        logger()->debug('Login request received', [
            'route' => $request->path(),
            'email' => $request->email,
            'remember_device' => $request->boolean('remember_device'),
        ]);

        $emailHash = hash('sha256', strtolower($request->email));
        $user = User::where('email_hash', $emailHash)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            logger()->debug('Login request failed credentials', [
                'email' => $request->email,
                'user_exists' => $user !== null,
            ]);

            session()->forget(['pending_2fa_user_id', 'pending_2fa_email', 'show_2fa_modal', 'time_left', 'pending_admin_2fa']);

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        if ($this->hasValidRememberedDevice($request, $user)) {
            Auth::login($user);
            $request->session()->regenerate();

            return redirect('/reserve')->withCookie($this->refreshRememberDeviceCookie($user));
        }

        if (!$user->two_fa_enabled) {
            logger()->debug('Login user has 2FA disabled', ['user_id' => $user->id]);
            Auth::login($user);
            $request->session()->regenerate();
            return redirect('/reserve');
        }

        logger()->debug('Login user requires 2FA', ['user_id' => $user->id]);
        session()->put('remember_device', $request->boolean('remember_device'));

        try {
            $this->issueOtp($user);
        } catch (\Exception $e) {
            logger()->error('OTP generation failed', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['email' => 'Unable to generate the 2FA code. Please try again.'])
                ->onlyInput('email');
        }

        session()->put([
            'pending_2fa_user_id' => $user->id,
            'pending_2fa_email' => $user->email,
        ]);

        return back()->with([
            'show_2fa_modal' => true,
            'status' => 'A 6-digit code has been generated and logged.',
        ]);
    }

    private function issueOtp(User $user)
    {
        // Create a brand new one-time password (OTP) for 2FA
        // Hash it and set to expire in 30 seconds
        logger()->debug('issueOtp called', ['user_id' => $user->id, 'email' => $user->email]);
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete any existing active OTPs for this user
        Otp::where('user_id', $user->id)->delete();

        // Create new OTP record in database
        Otp::create([
            'user_id' => $user->id,
            'code' => Hash::make($otpCode),
            'expires_at' => now()->addSeconds(30),
            'attempts' => 0,
        ]);

        $this->logOtp($user, $otpCode);

        session()->put('time_left', 30);
    }

    private function logOtp(User $user, string $otp): void
    {
        // Save the OTP to a file for testing
        // In production, would be sent via email or SMS
        $logPath = storage_path('logs/otp.log');
        $expiresAt = now()->addSeconds(30)->toDateTimeString();

        $entry = sprintf(
            "Otp Code: %s\nEmail: %s\nTime of Expiration: %s\n\n---\n\n",
            $otp,
            $user->email,
            $expiresAt
        );

        File::append($logPath, $entry);
    }

    
    public function showProfile()
    {
        return view('profile');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country_code' => ['nullable', 'string', 'in:+1,+44,+63,+81,+91,+86,+33,+49,+39,+61'],
            'phone' => ['nullable', 'string', 'regex:/^[\+]?[0-9]{7,15}$/'],
            'two_fa_enabled' => ['sometimes', 'boolean'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = Auth::user();

        $data = [
            'name' => $request->name,
            'two_fa_enabled' => $request->boolean('two_fa_enabled'),
        ];

        if ($request->phone) {
            // If the phone number already has a country code (starts with +), keep it
            // Otherwise, I'll add the country code they selected
            if (str_starts_with($request->phone, '+')) {
                $data['phone'] = $request->phone;
            } else {
                $countryCode = $request->country_code ?? '+63';
                $data['phone'] = $countryCode . $request->phone;
            }
        }

        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $user->update($data);

        return back()->with('status', 'Profile updated successfully.');
    }

    /**
     * Update theme preference via AJAX
     */
    private function hasValidRememberedDevice(Request $request, User $user): bool
    {
        $token = $request->cookie('remember_device');

        if (! $token || ! $user->remember_device_token || ! $user->remember_device_expires_at) {
            return false;
        }

        if ($user->remember_device_expires_at->isPast()) {
            return false;
        }

        return Hash::check($token, $user->remember_device_token);
    }

    private function rememberDeviceCookie(User $user)
    {
        $token = Str::random(60);
        $expires = now()->addDays(30);

        $user->update([
            'remember_device_token' => Hash::make($token),
            'remember_device_expires_at' => $expires,
        ]);

        return cookie(
            'remember_device',
            $token,
            60 * 24 * 30,
            '/',
            null,
            app()->environment('production'),
            true,
            false,
            'Strict'
        );
    }

    private function refreshRememberDeviceCookie(User $user)
    {
        if (! $user->remember_device_token) {
            return null;
        }

        return $this->rememberDeviceCookie($user);
    }

    private function forgetRememberDeviceCookie()
    {
        return Cookie::forget('remember_device');
    }

    /**
     * Show the registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', 'min:5', 'max:30', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])[A-Za-z\d!@#$%^&*()_+]{5,30}$/'],
        ], [
            'password.regex' => 'Password must be 5-30 chars with uppercase, lowercase, number, and special character.',
        ]);

        $emailHash = hash('sha256', strtolower($request->email));
        if (User::where('email_hash', $emailHash)->exists()) {
            return back()->withErrors([
                'email' => 'This email is already registered.',
            ])->onlyInput('email', 'name');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile_picture' => 'profile_pictures/default-avatar.png', // Default avatar
        ]);

        Auth::login($user);

        return redirect('/reserve');
    }

    /**
     * Show 2FA verification form
     */
    public function show2faVerify(Request $request)
    {
        $userId = session('pending_2fa_user_id');
        if (!$userId) {
            return redirect('/login');
        }

        $otp = Otp::where('user_id', $userId)->latest()->first();

        if (! $otp || $otp->isExpired()) {
            $request->session()->forget(['pending_2fa_user_id', 'pending_2fa_email', 'show_2fa_modal', 'time_left']);
            return redirect('/login')->with('error', '2FA code expired. Please login again.');
        }

        $timeLeft = $otp->expires_at->diffInSeconds(now(), false);

        return view('auth.2fa-verify', compact('timeLeft'));
    }

    /**
     * Verify 2FA code
     */
    public function verify2fa(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $userId = session('pending_2fa_user_id');
        $user = User::findOrFail($userId);
        $otp = Otp::where('user_id', $userId)->latest()->first();

        if (! $otp || ! $otp->isValid()) {
            return back()->with([
                'show_2fa_modal' => true,
            ])->withErrors(['otp' => 'Code expired or too many attempts. Please resend a new code.']);
        }

        if (Hash::check($request->otp, $otp->code)) {
            $otp->delete();

            $request->session()->forget(['pending_2fa_user_id', 'pending_2fa_email', 'show_2fa_modal', 'time_left']);
            $isAdminLogin = session()->pull('pending_admin_2fa', false);

            Auth::login($user);
            $request->session()->regenerate();

            $response = redirect($isAdminLogin ? '/admin/dashboard' : '/reserve');
            if (session()->pull('remember_device', false)) {
                $response->withCookie($this->rememberDeviceCookie($user));
            }

            return $response;
        }

        $otp->incrementAttempts();
        return back()->with([
            'show_2fa_modal' => true,
        ])->withErrors(['otp' => 'Invalid code. Try again.']);
    }

    /**
     * Resend OTP
     */
    public function resend2fa(Request $request)
    {
        $userId = session('pending_2fa_user_id');
        $user = User::find($userId);

        if (! $user) {
            session()->forget(['pending_2fa_user_id', 'pending_2fa_email', 'show_2fa_modal', 'time_left', 'pending_admin_2fa']);
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Session expired. Login again.'], 440);
            }
            return redirect('/login')->with('error', 'Session expired. Login again.');
        }

        $this->issueOtp($user);

        session()->flash('show_2fa_modal', true);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'A new 6-digit code has been generated and logged.',
                'time_left' => 30,
            ]);
        }

        return back()->with([
            'show_2fa_modal' => true,
            'status' => 'A new 6-digit code has been generated and logged.',
        ]);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            Otp::where('user_id', $user->id)->delete();
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->forget(['pending_2fa_user_id', 'pending_2fa_email', 'show_2fa_modal', 'time_left', 'pending_admin_2fa']);

        return redirect('/')->withCookie($this->forgetRememberDeviceCookie());
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle(Request $request)
    {
        $redirectUrl = $request->getSchemeAndHttpHost() . '/auth/google/callback';
        return Socialite::driver('google')
            ->redirectUrl($redirectUrl)
            ->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            // Skip SSL checking for testing on localhost
            $httpClient = new Client(['verify' => false]);
            $googleUser = Socialite::driver('google')
                ->setHttpClient($httpClient)
                ->user();
        } catch (\Exception $e) {
            // Record the error for debugging
            \Log::error('Google OAuth Error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Failed to authenticate with Google.');
        }

        // Check if user exists by email hash
        $emailHash = hash('sha256', strtolower($googleUser->getEmail()));
        $user = User::where('email_hash', $emailHash)->first();

        // Create user if doesn't exist
        if (!$user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: 'User',
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(Str::random(16)), // They logged in with Google, so I create a random password for them
            ]);
        }

        // Sign them in right away
        Auth::login($user);
        $request = request();
        $request->session()->regenerate();

        return redirect('/reserve');
    }

}

