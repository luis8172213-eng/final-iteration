<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Campus Reserve')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" crossorigin="anonymous">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js" defer></script>
    @stack('styles')
</head>
<body class="min-h-screen bg-white text-slate-900 transition-colors duration-200">
    <!-- Application header and global navigation bar -->
    <header class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200 transition-colors duration-200">
        <a href="/" class="flex items-center">
            <div class="flex items-center border border-black px-2 py-1">
                <span class="text-xs font-medium tracking-wide">CAMPUS</span>
                <span class="bg-black text-white text-xs font-medium px-1 ml-1">RESERVE</span>
            </div>
        </a>

        <nav class="hidden md:flex items-center gap-8">
            <!-- Main site navigation links. The active route is highlighted dynamically. -->
            <a href="/" class="text-sm font-medium hover:text-gray-600 transition-colors {{ request()->is('/') ? 'text-black' : 'text-gray-700' }}">HOME</a>
            <a href="/reserve" class="text-sm font-medium hover:text-gray-600 transition-colors {{ request()->is('reserve') ? 'text-black' : 'text-gray-700' }}">RESERVE</a>
            <a href="/calendar" class="text-sm font-medium hover:text-gray-600 transition-colors {{ request()->is('calendar') ? 'text-black' : 'text-gray-700' }}">CALENDAR</a>
            <a href="/about" class="text-sm font-medium hover:text-gray-600 transition-colors {{ request()->is('about') ? 'text-black' : 'text-gray-700' }}">ABOUT US</a>
            <a href="/contact" class="text-sm font-medium hover:text-gray-600 transition-colors {{ request()->is('contact') ? 'text-black' : 'text-gray-700' }}">CONTACT</a>
        </nav>

        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('notifications.index') }}" class="relative text-gray-700 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if(auth()->user()->unreadNotifications->count())
                        <span class="absolute -top-1 -right-1 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-600 px-1.5 text-[10px] font-semibold text-white">
                            {{ auth()->user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </a>
                <a href="/profile" class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                    <img src="{{ auth()->user()->profile_picture ? asset('storage/' . auth()->user()->profile_picture) : 'https://via.placeholder.com/32x32/cccccc/000000?text=?' }}" alt="Profile Picture" class="w-8 h-8 rounded-full object-cover">
                    Profile
                </a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">Admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-1.5 text-sm font-medium bg-black text-white rounded-full hover:bg-gray-800 transition-colors">
                        Log Out
                    </button>
                </form>
            @else
                <a href="/signup" class="px-4 py-1.5 text-sm font-medium bg-black text-white rounded-full hover:bg-gray-800 transition-colors">Sign Up</a>
                <a href="/login" class="px-4 py-1.5 text-sm font-medium bg-black text-white rounded-full hover:bg-gray-800 transition-colors">Log In</a>
            @endauth
        </div>
    </header>
    
    <main>
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
