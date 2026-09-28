@extends('layouts.app')

@section('title', '2FA Verification - Campus Reserve')

@section('content')
<!-- Standalone 2FA verification page for the one-time code validation step. -->
<main class="flex min-h-[calc(100vh-73px)]">
    <div class="w-full md:w-1/2 flex flex-col items-center justify-center px-8 py-12">
        <div class="w-full max-w-md">
            <div class="flex justify-center mb-6">
                <div class="flex items-center border border-black px-2 py-1">
                    <span class="text-xs font-medium tracking-wide">CAMPUS</span>
                    <span class="bg-black text-white text-xs font-medium px-1 ml-1">RESERVE</span>
                </div>
            </div>
            
            <h1 class="text-3xl font-bold text-center text-gray-900 mb-8">
                VERIFY YOUR LOGIN
            </h1>
            
            <div class="text-center mb-8">
                @if($method === 'authenticator')
                    <p class="text-gray-600 mb-2">
                        @if($provider === 'google')
                            Google sign-in succeeded. Enter the current code from your Campus Reserve authenticator app.
                        @else
                            Enter the current code from your authenticator app.
                        @endif
                    </p>
                @else
                    <p class="text-gray-600 mb-2">
                        @if($provider === 'google')
                            Google sign-in succeeded. We sent a 6-digit verification code to your email.
                        @else
                            We sent a 6-digit verification code to your email.
                        @endif
                    </p>
                @endif
            </div>
            
            @if (session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('status') }}
                </div>
            @endif

            <p class="mb-6 text-center text-sm font-medium text-amber-700">
                You have {{ $attemptsRemaining }} attempts remaining.
            </p>
            
            <form method="POST" action="{{ route('2fa.verify') }}" class="space-y-6">
                @csrf
                
                <div class="space-y-2">
                    <label for="otp" class="block text-sm text-gray-700">Verification Code</label>
                    <input
                        id="otp"
                        name="otp"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        maxlength="6"
                        autocomplete="one-time-code"
                        required
                        class="w-full px-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent text-center text-lg tracking-widest"
                        placeholder="000000"
                    >
                    @error('otp')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3">
                    <button
                        type="submit"
                        class="flex-1 px-8 py-2 bg-black text-white rounded-md hover:bg-gray-800 transition-colors"
                    >
                        Verify
                    </button>
                    <button
                        type="button"
                        onclick="document.querySelector('input[name=otp]').value = ''"
                        class="flex-1 px-8 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors"
                    >
                        Clear
                    </button>
                </div>
            </form>
            
            <div class="mt-6 text-center">
                @if($method !== 'authenticator')
                    <form method="POST" action="{{ route('2fa.resend') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-blue-600 hover:underline">
                            Resend Code
                        </button>
                    </form>
                    <p class="text-xs text-gray-500 mt-2">
                        Didn't receive it? Check your spam folder.
                    </p>
                @endif
                <p class="mt-4">
                    <a href="/login" class="text-sm text-red-600 hover:underline">Back to Login</a>
                </p>
            </div>
        </div>
    </div>
    
    <div class="hidden md:block w-1/2 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-300 via-purple-200 to-pink-200"></div>
    </div>
</main>

@endsection
