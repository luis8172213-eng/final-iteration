@extends('layouts.app')

@section('title', 'Login - Campus Reserve')

@section('content')
<main class="flex min-h-[calc(100vh-73px)] items-stretch">
    <!-- Left side - Form -->
    <!-- Login form area with email/password input and optional 2FA modal. -->
    <div class="w-full md:w-1/2 flex flex-col items-center justify-center px-8 py-12">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <div class="flex items-center border border-black px-2 py-1">
                    <span class="text-xs font-medium tracking-wide">CAMPUS</span>
                    <span class="bg-black text-white text-xs font-medium px-1 ml-1">RESERVE</span>
                </div>
            </div>
            
            <h1 class="text-3xl font-bold text-center text-gray-900 mb-8">
                WELCOME BACK!
            </h1>
            
            <!-- Status Messages -->
            @if (session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('status') }}
                </div>
            @endif
            
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-6" id="loginForm">
                @csrf
                <div id="loginFormError" class="hidden rounded bg-red-100 px-4 py-3 text-sm text-red-700"></div>
                
                <div class="space-y-2">
                    <label for="email" class="block text-sm text-gray-700">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        class="w-full px-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"
                        value="{{ old('email') }}"
                    >
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="space-y-2">
                    <label for="password" class="block text-sm text-gray-700">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        class="w-full px-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"
                    >
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <div class="text-right">
                        <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
                    </div>
                </div>
                
                <!-- Device Remember & Submit -->
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember_device" class="rounded border-gray-300 text-black focus:ring-black">
                        <span class="ml-2 text-sm text-gray-600">Remember this device for 30 days</span>
                    </label>
                </div>
                
                <div class="flex justify-center pt-4">
                    <button
                        type="submit"
                        class="px-8 py-2 bg-black text-white rounded-md hover:bg-gray-800 transition-colors w-full"
                    >
                        Log In
                    </button>
                </div>
            </form>

            <!-- 2FA Modal -->
<div id="2faModal" class="fixed inset-0 {{ session('show_2fa_modal') && session('pending_2fa_user_id') ? 'flex' : 'hidden' }} items-center justify-center z-50 p-4 animate-in fade-in zoom-in duration-200" style="background: rgba(0, 0, 0, 0.18); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
                <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl p-8 max-w-sm w-full border border-gray-200 animate-in slide-in-from-bottom-4 duration-300">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Verify Your Login</h2>
                        <button onclick="close2faModal()" class="text-gray-500 hover:text-gray-900 text-xl p-1 rounded-full hover:bg-gray-200 transition-all">&times;</button>
                    </div>
                    
                    <div id="two-factor-instructions">
                        @if (session('pending_2fa_method') === 'authenticator')
                            <p class="text-gray-700 mb-2 text-lg font-medium">Enter your authenticator code.</p>
                            <p class="text-sm text-gray-500 mb-4">Open Google Authenticator or Microsoft Authenticator and enter the current 6-digit code.</p>
                        @else
                            <p class="text-gray-700 mb-2 text-lg font-medium">Enter the code sent to your email.</p>
                            <p class="text-sm text-gray-500 mb-4">This code expires in 30 seconds.</p>
                        @endif
                    </div>
                    
                    <div class="text-center mb-8">
                        <div class="text-lg font-mono bg-gray-100 rounded-lg p-4 mb-2">{{ session('pending_2fa_email') }}</div>
                        <p id="two-factor-attempts-warning" class="text-sm font-medium text-amber-700">You have {{ session('two_fa_attempts_remaining', 3) }} attempts remaining.</p>
                    </div>
                    
                    <form id="verify2faForm" method="POST" action="{{ route('2fa.verify') }}" class="space-y-6">
                        @csrf
                        
                        <div>
                            <input
                                id="verify_otp"
                                name="otp"
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]{6}"
                                maxlength="6"
                                autocomplete="one-time-code"
                                required
                                class="w-full px-6 py-4 rounded-2xl border-2 border-gray-200 focus:border-black focus:outline-none text-center text-2xl font-bold tracking-widest uppercase bg-gray-50 hover:bg-white transition-all"
                                placeholder="000000"
                            >
                            <p id="two-factor-code-error" class="{{ $errors->has('otp') ? '' : 'hidden' }} text-red-500 text-xs mt-2 text-center">{{ $errors->first('otp') }}</p>
                        </div>
                        
                        <div class="flex gap-3 pt-2">
                            <button
                                type="button"
                                onclick="clearOtp()"
                                class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all font-medium border border-gray-200"
                            >
                                Clear
                            </button>
                            <button
                                type="submit"
                                class="flex-1 px-6 py-3 bg-black text-white rounded-xl hover:bg-gray-900 transition-all font-bold shadow-lg hover:shadow-xl"
                            >
                                Confirm
                            </button>
                        </div>
                    </form>
                    
                    <div class="flex items-center justify-center gap-2 mt-6 pt-6 border-t border-gray-200">
                        <div id="two-factor-resend" class="{{ session('pending_2fa_method') === 'authenticator' ? 'hidden' : '' }}">
                            <form id="resendOtpForm" method="POST" action="{{ route('2fa.resend') }}" class="inline-flex items-center">
                                @csrf
                                <button type="submit" class="text-sm text-blue-600 hover:text-blue-700 font-medium hover:underline transition-colors">
                                    Resend Code
                                </button>
                            </form>
                            <span class="text-xs text-gray-500">| Code expires quickly.</span>
                        </div>
                        <span id="authenticator-refresh-message" class="{{ session('pending_2fa_method') === 'authenticator' ? '' : 'hidden' }} text-xs text-gray-500">Your authenticator code refreshes automatically.</span>
                    </div>
                    <div id="otpStatusMessage" class="text-center text-sm text-green-700 mt-3"></div>
                    
                    <button onclick="location.href='{{ route('login') }}'" class="w-full mt-6 py-2 px-4 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all font-medium text-sm">
                        Cancel
                    </button>
                </div>
            </div>
            
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600 mb-4">Or use below to login to your account</p>

                <div class="flex justify-center gap-4 mb-4">
                    <!-- Google Login -->
                    <a href="{{ route('auth.google') }}" class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors" title="Login with Google">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                    </a>
                    <!-- Facebook and LinkedIn similar -->
                </div>

                <div id="adminHint" class="hidden mt-6 text-center bg-gray-900 text-white rounded-3xl p-4 border border-gray-800">
                    <p class="text-sm font-semibold">Hidden admin portal available.</p>
                    <a href="{{ route('admin.login') }}" class="inline-flex mt-3 items-center justify-center rounded-full border border-white px-4 py-2 text-sm font-medium text-white hover:bg-white hover:text-black transition-colors">Go to Admin Login</a>
                </div>

                <p class="mx-auto inline-block mt-6 text-sm text-gray-600">
                    Don't have an account?
                    <a href="/signup" class="text-blue-600 hover:underline">Sign up here</a>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Right side - Gradient -->
    <div class="hidden md:block w-1/2 relative overflow-hidden h-full min-h-[calc(100vh-73px)]">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, #c4b5fd 0%, #fbcfe8 45%, #fb923c 100%);"></div>
    </div>
</main>

<script>
document.getElementById('loginForm')?.addEventListener('submit', async function(event) {
    event.preventDefault();
    const form = this;
    const submitButton = form.querySelector('button[type="submit"]');
    const error = document.getElementById('loginFormError');
    submitButton.disabled = true;
    submitButton.textContent = 'Checking...';
    error.classList.add('hidden');

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
            },
            body: new FormData(form),
            credentials: 'same-origin',
        });

        const contentType = response.headers.get('content-type') || '';
        const data = contentType.includes('application/json')
            ? await response.json()
            : {};
        if (!response.ok) {
            throw new Error(data.message || Object.values(data.errors || {}).flat()[0] || 'The provided credentials do not match our records.');
        }

        if (data.redirect) {
            window.location.href = data.redirect;
            return;
        }

        if (data.two_factor_required) {
            const modal = document.getElementById('2faModal');
            const instructions = document.getElementById('two-factor-instructions');
            const resend = document.getElementById('two-factor-resend');
            const refreshMessage = document.getElementById('authenticator-refresh-message');
            document.getElementById('2faModal').querySelector('.text-lg.font-mono').textContent = data.email;
            instructions.innerHTML = data.method === 'authenticator'
                ? '<p class="text-gray-700 mb-2 text-lg font-medium">Enter your authenticator code.</p><p class="text-sm text-gray-500 mb-4">Open Google Authenticator or Microsoft Authenticator and enter the current 6-digit code.</p>'
                : '<p class="text-gray-700 mb-2 text-lg font-medium">Enter the code sent to your email.</p><p class="text-sm text-gray-500 mb-4">This code expires in 30 seconds.</p>';
            resend.classList.toggle('hidden', data.method === 'authenticator');
            refreshMessage.classList.toggle('hidden', data.method !== 'authenticator');
            document.getElementById('two-factor-attempts-warning').textContent = 'You have 3 attempts remaining.';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('verify_otp').focus();
        }
    } catch (requestError) {
        error.textContent = requestError.message;
        error.classList.remove('hidden');
    } finally {
        submitButton.disabled = false;
        submitButton.textContent = 'Log In';
    }
});

document.getElementById('verify2faForm')?.addEventListener('submit', async function(event) {
    event.preventDefault();
    const form = this;
    const button = form.querySelector('button[type="submit"]');
    const error = document.getElementById('two-factor-code-error');
    const warning = document.getElementById('two-factor-attempts-warning');
    button.disabled = true;
    button.textContent = 'Checking...';
    error.classList.add('hidden');

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
            },
            body: new FormData(form),
            credentials: 'same-origin',
        });
        const data = await response.json();

        if (response.status === 429) {
            window.location.href = data.redirect || '{{ route('login') }}';
            return;
        }

        if (!response.ok) {
            error.textContent = data.message || 'Invalid Authenticator Code';
            error.classList.remove('hidden');
            if (data.attempts_remaining) {
                warning.textContent = `You have ${data.attempts_remaining} attempts remaining.`;
            }
            document.getElementById('verify_otp').value = '';
            document.getElementById('verify_otp').focus();
            return;
        }

        if (data.redirect) {
            window.location.href = data.redirect;
        }
    } catch (requestError) {
        error.textContent = 'Unable to verify the code. Please try again.';
        error.classList.remove('hidden');
    } finally {
        button.disabled = false;
        button.textContent = 'Confirm';
    }
});

function close2faModal() {
    document.getElementById('2faModal').style.display = 'none';
}

function clearOtp() {
    document.getElementById('verify_otp').value = '';
}

async function handleResendOtp(event) {
    event.preventDefault();
    const form = document.getElementById('resendOtpForm');
    const status = document.getElementById('otpStatusMessage');

    if (! form || ! status) {
        return;
    }

    status.textContent = 'Resending code...';
    status.classList.remove('text-red-700');
    status.classList.add('text-green-700');

    const token = form.querySelector('input[name="_token"]').value;

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify({}),
            credentials: 'same-origin',
        });

        const data = await response.json();
        if (! response.ok) {
            status.classList.remove('text-green-700');
            status.classList.add('text-red-700');
            status.textContent = data.error || 'Unable to resend code. Try again.';
            return;
        }

        status.classList.remove('text-red-700');
        status.classList.add('text-green-700');
        status.textContent = data.status || 'A new code has been generated and logged.';
        
    } catch (error) {
        status.classList.remove('text-green-700');
        status.classList.add('text-red-700');
        status.textContent = 'Unable to resend code. Try again.';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const resendForm = document.getElementById('resendOtpForm');
    if (resendForm) {
        resendForm.addEventListener('submit', handleResendOtp);
    }
});

window.addEventListener('keydown', function (event) {
    if (event.ctrlKey && event.shiftKey && event.key.toLowerCase() === 'a') {
        const adminHint = document.getElementById('adminHint');
        if (adminHint) {
            adminHint.classList.remove('hidden');
            adminHint.classList.add('block');
        }
    }
});
</script>
@endsection
