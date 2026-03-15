<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Campus Reserve')</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-white">
    <!-- Header -->
    <header class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200">
        <a href="/" class="flex items-center">
            <div class="flex items-center border border-black px-2 py-1">
                <span class="text-xs font-medium tracking-wide">CAMPUS</span>
                <span class="bg-black text-white text-xs font-medium px-1 ml-1">RESERVE</span>
            </div>
        </a>

        <nav class="hidden md:flex items-center gap-8">
            <a href="/" class="text-sm font-medium hover:text-gray-600 transition-colors {{ request()->is('/') ? 'text-black' : 'text-gray-700' }}">HOME</a>
            <a href="/reserve" class="text-sm font-medium hover:text-gray-600 transition-colors {{ request()->is('reserve') ? 'text-black' : 'text-gray-700' }}">RESERVE</a>
            <a href="/calendar" class="text-sm font-medium hover:text-gray-600 transition-colors {{ request()->is('calendar') ? 'text-black' : 'text-gray-700' }}">CALENDAR</a>
            <a href="/about" class="text-sm font-medium hover:text-gray-600 transition-colors {{ request()->is('about') ? 'text-black' : 'text-gray-700' }}">ABOUT US</a>
            <a href="/contact" class="text-sm font-medium hover:text-gray-600 transition-colors {{ request()->is('contact') ? 'text-black' : 'text-gray-700' }}">CONTACT</a>
        </nav>

        <div class="flex items-center gap-3">
            @auth
                <a href="/dashboard" class="text-sm font-medium text-gray-700 hover:text-gray-900">Dashboard</a>
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
