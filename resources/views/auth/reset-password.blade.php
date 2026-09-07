@extends('layouts.app')

@section('title', 'Reset Password - Campus Reserve')

@section('content')
<main class="flex min-h-[calc(100vh-73px)] items-center justify-center px-6 py-12">
    <div class="w-full max-w-md">
        <h1 class="text-3xl font-bold text-gray-900">Reset password</h1>
        <p class="mt-2 text-sm text-gray-600">Choose a new password for your Campus Reserve account.</p>

        <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <label class="block">
                <span class="text-sm text-gray-700">Email</span>
                <input type="email" name="email" value="{{ old('email', $email) }}" required class="mt-2 w-full rounded-full border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-black">
                @error('email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </label>
            <label class="block">
                <span class="text-sm text-gray-700">New password</span>
                <input type="password" name="password" required class="mt-2 w-full rounded-full border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-black">
                <p class="mt-2 text-xs leading-5 text-gray-500">Use 5-30 characters with at least one uppercase letter, one lowercase letter, one number, and one special character: <span class="font-medium text-gray-700">! @ # $ % ^ &amp; * ( ) _ +</span>. For better security, use 12 or more characters.</p>
            </label>
            <label class="block">
                <span class="text-sm text-gray-700">Confirm new password</span>
                <input type="password" name="password_confirmation" required class="mt-2 w-full rounded-full border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-black">
                @error('password')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </label>
            <button type="submit" class="w-full rounded-md bg-black px-8 py-3 text-sm font-semibold text-white hover:bg-gray-800">Reset password</button>
        </form>
    </div>
</main>
@endsection
