@extends('layouts.app')

@section('title', 'Secure Your Account - Campus Reserve')

@section('content')
<main class="min-h-[calc(100vh-73px)] bg-slate-50 px-4 py-12 md:px-8">
    <section class="mx-auto max-w-2xl rounded-3xl border border-red-200 bg-white p-8 shadow-sm">
        <div class="mb-6 inline-flex rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-800">Account security</div>
        <h1 class="text-3xl font-semibold text-gray-900">Secure your account</h1>
        <p class="mt-3 text-gray-600">If you did not change your Campus Reserve password, take these steps immediately.</p>
        <ol class="mt-8 space-y-4 text-sm text-gray-700">
            <li><strong>1.</strong> Change your password from Account Settings using a new password you do not use elsewhere.</li>
            <li><strong>2.</strong> Confirm that authenticator-based two-factor authentication is enabled.</li>
            <li><strong>3.</strong> Sign out of unfamiliar sessions and review your account activity.</li>
            <li><strong>4.</strong> Contact your campus administrator if you cannot access your account.</li>
        </ol>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('profile.show') }}" class="rounded-md bg-black px-5 py-3 text-sm font-semibold text-white">Open account settings</a>
            <a href="{{ route('home') }}" class="rounded-md border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700">Return home</a>
        </div>
    </section>
</main>
@endsection
