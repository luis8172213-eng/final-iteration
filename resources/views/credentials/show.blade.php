@extends('layouts.app')

@section('title', $credential->site_name . ' - Campus Reserve')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('credentials.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Credentials
            </a>
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900">{{ $credential->site_name }}</h1>
                <div class="flex items-center gap-2">
                    <a href="{{ route('credentials.edit', $credential) }}" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Edit
                    </a>
                    <form method="POST" action="{{ route('credentials.destroy', $credential) }}" onsubmit="return confirm('Are you sure you want to delete this credential?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 text-red-600 border border-red-300 rounded-lg hover:bg-red-50 transition-colors">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Credential Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Site URL -->
            @if($credential->site_url)
            <div class="p-6 border-b border-gray-200">
                <label class="block text-sm font-medium text-gray-500 mb-1">Site URL</label>
                <a href="{{ $credential->site_url }}" target="_blank" class="text-blue-600 hover:underline flex items-center">
                    {{ $credential->site_url }}
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                </a>
            </div>
            @endif

            <!-- Username -->
            <div class="p-6 border-b border-gray-200">
                <label class="block text-sm font-medium text-gray-500 mb-1">Username / Email</label>
                <div class="flex items-center justify-between">
                    <span class="text-gray-900 font-mono">{{ $credential->username }}</span>
                    <button onclick="copyToClipboard('{{ $credential->username }}')" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors" title="Copy">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Password -->
            <div class="p-6 border-b border-gray-200">
                <label class="block text-sm font-medium text-gray-500 mb-1">Password</label>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span id="password-display" class="text-gray-900 font-mono">••••••••••••</span>
                        <button onclick="togglePasswordVisibility()" class="text-sm text-blue-600 hover:text-blue-800" id="toggle-btn">
                            Show
                        </button>
                    </div>
                    <button onclick="copyPassword()" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors" title="Copy Password">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Notes -->
            @if($credential->notes)
            <div class="p-6">
                <label class="block text-sm font-medium text-gray-500 mb-1">Notes</label>
                <p class="text-gray-900 whitespace-pre-wrap">{{ $credential->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Metadata -->
        <div class="mt-6 p-4 bg-gray-100 rounded-lg">
            <div class="flex items-center justify-between text-sm text-gray-500">
                <span>Created: {{ $credential->created_at->format('M d, Y h:i A') }}</span>
                <span>Updated: {{ $credential->updated_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <div>
                    <h4 class="text-sm font-semibold text-blue-900">Encrypted Storage</h4>
                    <p class="text-sm text-blue-700 mt-1">This credential is stored with AES-256-CBC encryption. The password is decrypted only when you view or copy it.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let passwordVisible = false;
let cachedPassword = null;

async function fetchPassword() {
    if (cachedPassword) return cachedPassword;
    
    try {
        const response = await fetch('/credentials/{{ $credential->id }}/password');
        const data = await response.json();
        cachedPassword = data.password;
        return cachedPassword;
    } catch (error) {
        console.error('Error fetching password:', error);
        return null;
    }
}

async function togglePasswordVisibility() {
    const display = document.getElementById('password-display');
    const btn = document.getElementById('toggle-btn');
    
    if (!passwordVisible) {
        const password = await fetchPassword();
        if (password) {
            display.textContent = password;
            btn.textContent = 'Hide';
            passwordVisible = true;
        }
    } else {
        display.textContent = '••••••••••••';
        btn.textContent = 'Show';
        passwordVisible = false;
    }
}

async function copyPassword() {
    const password = await fetchPassword();
    if (password) {
        await navigator.clipboard.writeText(password);
        alert('Password copied to clipboard!');
    } else {
        alert('Failed to copy password');
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text);
    alert('Copied to clipboard!');
}
</script>
@endsection
