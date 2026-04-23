<header class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200">
    <a href="/" class="flex items-center">
        <div class="flex items-center border border-black px-2 py-1">
            <span class="text-xs font-medium tracking-wide">CAMPUS</span>
            <span class="bg-black text-white text-xs font-medium px-1 ml-1">RESERVE</span>
        </div>
    </a>

    <nav class="hidden md:flex items-center gap-8">
        <a href="/" class="text-sm font-medium text-black hover:text-slate-700 transition-colors">HOME</a>
        <a href="/reserve" class="text-sm font-medium text-black hover:text-slate-700 transition-colors">RESERVE</a>
        <a href="/calendar" class="text-sm font-medium text-black hover:text-slate-700 transition-colors">CALENDAR</a>
        <a href="/about" class="text-sm font-medium text-black hover:text-slate-700 transition-colors">ABOUT US</a>
        <a href="/contact" class="text-sm font-medium text-black hover:text-slate-700 transition-colors">CONTACT</a>
    </nav>

    <div class="flex items-center gap-3">
        @auth
            <a href="{{ route('credentials.index') }}" class="text-sm font-medium text-black hover:text-slate-700">Passwords</a>
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
