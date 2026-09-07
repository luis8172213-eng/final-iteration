@extends('layouts.app')

@section('title', 'Forgot Password - Campus Reserve')

@section('content')
<main class="flex min-h-[calc(100vh-73px)] items-center justify-center px-6 py-12">
    <div class="w-full max-w-md">
        <h1 class="text-3xl font-bold text-gray-900">Forgot password?</h1>
        <p class="mt-2 text-sm text-gray-600">Enter your Campus Reserve email and we will send you a password reset link.</p>

        @if (session('status'))
            <div class="mt-6 rounded-xl bg-green-100 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
            @csrf
            <label class="block">
                <span class="text-sm text-gray-700">Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="mt-2 w-full rounded-full border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-black">
                @error('email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </label>
            <button type="submit" class="w-full rounded-md bg-black px-8 py-3 text-sm font-semibold text-white hover:bg-gray-800">Send reset link</button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600"><a href="{{ route('login') }}" class="text-blue-600 hover:underline">Back to login</a></p>
    </div>
</main>
@endsection
