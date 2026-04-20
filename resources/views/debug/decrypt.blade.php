@extends('layouts.app')

@section('title', 'Decrypt User Data - Campus Reserve')

@section('content')
<main class="min-h-[calc(100vh-73px)] bg-slate-50 py-10 px-4 md:px-8 xl:px-16">
    <div class="mx-auto max-w-3xl rounded-3xl bg-white p-8 shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Decrypt User Data</h1>
                <p class="mt-2 text-sm text-slate-600">Select a registered member to view and decrypt their stored information.</p>
            </div>
            <a href="/admin/dashboard" class="text-sm text-gray-600 hover:underline">← Back to Admin</a>
        </div>

        <form method="POST" action="{{ route('debug.decrypt') }}" class="mt-8 space-y-6">
            @csrf

            <!-- Member Selection Dropdown -->
            <div>
                <label for="member" class="block text-sm font-medium text-slate-700">1. Select Registered Member</label>
                <select id="member" name="user_id" required class="mt-1 block w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-black focus:ring-black">
                    <option value="">-- Choose a member --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ (isset($selectedUserId) && $selectedUserId == $user->id) ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                @error('user_id')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Field (Auto-filled, Read-only) -->
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">2. Email</label>
                <input type="email" id="email" readonly class="mt-1 block w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm text-slate-600 cursor-not-allowed">
            </div>

            <!-- Password Field (Auto-filled, Read-only) -->
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">3. Password (Encrypted)</label>
                <input type="text" id="password" readonly class="mt-1 block w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm text-slate-600 cursor-not-allowed" placeholder="(Hashed - Not encrypted)">
            </div>

            <!-- Phone Number Field (Auto-filled, Read-only) -->
            <div>
                <label for="phone" class="block text-sm font-medium text-slate-700">4. Phone Number</label>
                <input type="text" id="phone" readonly class="mt-1 block w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm text-slate-600 cursor-not-allowed">
            </div>

            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-black px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">Decrypt & View</button>
        </form>

        <!-- Decrypted Results Display -->
        @if(isset($decrypted))
            <div class="mt-10 rounded-3xl border-2 border-green-200 bg-green-50 p-6">
                <h2 class="text-lg font-semibold text-green-900 mb-4">✓ Decrypted User Information</h2>
                <pre class="text-sm text-slate-800 whitespace-pre-wrap wrap-break-word leading-relaxed">User: {{ $decrypted['user'] }}
Email: {{ $decrypted['email'] }}
Password: (Hashed - Not decryptable)
Phone Number: {{ $decrypted['phone'] }}</pre>
            </div>
        @endif

        <div class="mt-6 text-xs text-slate-500">
            <p><strong>🔒 Session Status:</strong> OTP verified for this session. Safe to decrypt multiple users.</p>
            <p class="mt-2"><strong>Note:</strong> Password fields are bcrypt-hashed and cannot be decrypted. Email and phone are AES-encrypted and shown above when decrypted.</p>
        </div>
    </div>
</main>

<script>
    // Auto-fill fields when member is selected
    document.getElementById('member').addEventListener('change', async function(e) {
        const userId = e.target.value;
        if (!userId) {
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            document.getElementById('phone').value = '';
            return;
        }

        try {
            const response = await fetch(`{{ route('debug.decrypt.user-details') }}?user_id=${userId}`);
            const data = await response.json();
            document.getElementById('email').value = data.email;
            document.getElementById('password').value = data.email_encrypted ? '[Encrypted]' : '(None)';
            document.getElementById('phone').value = data.phone;
        } catch (error) {
            console.error('Error fetching user details:', error);
        }
    });

    // Trigger auto-fill on page load if user is already selected
    if (document.getElementById('member').value) {
        document.getElementById('member').dispatchEvent(new Event('change'));
    }
</script>
@endsection
