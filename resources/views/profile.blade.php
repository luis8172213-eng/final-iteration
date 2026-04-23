@extends('layouts.app')

@section('title', 'Profile Settings - Campus Reserve')

@section('content')
<!-- Profile settings page for updating user account details, profile picture, and phone information. -->
<main class="min-h-[calc(100vh-73px)] bg-slate-50 py-10 px-4 md:px-8 xl:px-16">
    <div class="mx-auto grid w-full max-w-7xl gap-8 xl:grid-cols-[280px_1fr]">
        <aside class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900">Settings</h2>
                <p class="mt-2 text-sm text-gray-500">Manage your account settings from one place.</p>
            </div>

            <div class="space-y-3">
                <div class="rounded-3xl border border-gray-200 bg-slate-50 px-4 py-4 text-sm font-semibold text-slate-900">Account</div>
                <a href="{{ route('profile.show') }}" class="flex items-center justify-between rounded-3xl border border-gray-200 bg-white px-4 py-4 text-sm font-medium text-gray-700 shadow-sm transition hover:border-black hover:text-black">
                    <span>Account</span>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Active</span>
                </a>
            </div>

            <div class="mt-10 border-t border-gray-200 pt-8">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">System</p>
                <div class="mt-4 space-y-3">
                    <a href="{{ route('notifications.index') }}" class="w-full block rounded-3xl border border-gray-200 bg-white px-4 py-4 text-left text-sm text-gray-600 transition hover:border-black hover:text-black">Notifications</a>
                    <button type="button" class="w-full rounded-3xl border border-gray-200 bg-white px-4 py-4 text-left text-sm text-gray-600 transition hover:border-black hover:text-black">Preferences</button>
                </div>
            </div>
        </aside>

        <section class="space-y-8">
            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
                <div class="bg-linear-to-r from-slate-900 via-slate-800 to-black px-8 py-8 text-white">
                    <h1 class="text-3xl font-semibold">Account</h1>
                    <p class="mt-2 max-w-2xl text-sm text-slate-300">Update your profile, security preferences, and manage how people see your account.</p>
                </div>

                <div class="p-8 space-y-8">
                    @if(session('status'))
                        <div class="rounded-3xl bg-emerald-50 border border-emerald-200 px-5 py-4 text-sm text-emerald-800">{{ session('status') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="rounded-3xl bg-red-50 border border-red-200 px-5 py-4 text-sm text-red-800">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-8">
                        @csrf
                        
                        <!-- Hidden inputs for phone and country code submission -->
                        <input type="hidden" name="phone" id="form_phone_input" value="{{ old('phone', auth()->user()->phone ?? '') }}" />
                        <input type="hidden" name="country_code" id="form_country_code_input" value="" />

                        <div class="grid gap-6 xl:grid-cols-[280px_1fr]">
                            <div class="rounded-3xl border border-gray-200 bg-slate-50 p-6">
                                <p class="text-sm font-semibold text-gray-900">Profile</p>
                                <p class="mt-2 text-sm text-gray-500">Your avatar, name, and contact details.</p>
                            </div>

                            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                    <div class="relative h-24 w-24 overflow-hidden rounded-3xl border border-gray-200 bg-gray-100">
                                        @if(auth()->user()->profile_picture)
                                            <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" alt="Profile picture" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-gray-400">No photo</div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <label class="block text-sm font-semibold text-gray-900">Profile picture</label>
                                        <p class="mt-1 text-sm text-gray-500">Square image works best.</p>
                                        <input type="file" name="profile_picture" accept="image/png,image/jpeg,image/webp" class="mt-3 block w-full text-sm text-gray-700" />
                                        @error('profile_picture')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div class="grid gap-6 md:grid-cols-2">
                                    <label class="block">
                                        <span class="text-sm font-semibold text-gray-900">Full name</span>
                                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required class="mt-2 w-full rounded-3xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-black" />
                                        @error('name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </label>

                                    <label class="block">
                                        <span class="text-sm font-semibold text-gray-900">Email address</span>
                                        <input type="email" value="{{ auth()->user()->email }}" readonly class="mt-2 w-full rounded-3xl border border-gray-200 bg-slate-100 px-4 py-3 text-sm text-gray-600" />
                                    </label>
                                </div>

                                <label class="block">
                                    <span class="text-sm font-semibold text-gray-900">Phone number</span>
                                    
                                    <!-- Display Mode -->
                                    <div id="phone_display_mode" class="mt-2">
                                        <div class="flex items-center justify-between rounded-3xl border border-gray-300 bg-slate-50 px-4 py-3">
                                            <span class="text-sm text-gray-900" id="phone_display_text">
                                                @if(auth()->user()->phone)
                                                    {{ auth()->user()->phone }}
                                                @else
                                                    <span class="text-gray-500">Not set</span>
                                                @endif
                                            </span>
                                            <button type="button" id="phone_edit_btn" class="text-gray-500 hover:text-gray-900 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-10l7-7m0 0l-7 7m7-7v12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Edit Mode -->
                                    <div id="phone_edit_mode" class="mt-2 space-y-3 hidden">
                                        <div class="grid gap-3 sm:grid-cols-[150px_1fr]">
                                            <label class="block">
                                                <span class="text-xs font-semibold text-gray-700">Country Code</span>
                                                <select id="country_code_select" class="mt-1 w-full rounded-3xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-black">
                                                    <option value="+1">+1 (USA/Canada)</option>
                                                    <option value="+44">+44 (UK)</option>
                                                    <option value="+63">+63 (Philippines)</option>
                                                    <option value="+81">+81 (Japan)</option>
                                                    <option value="+91">+91 (India)</option>
                                                    <option value="+86">+86 (China)</option>
                                                    <option value="+33">+33 (France)</option>
                                                    <option value="+49">+49 (Germany)</option>
                                                    <option value="+39">+39 (Italy)</option>
                                                    <option value="+61">+61 (Australia)</option>
                                                </select>
                                            </label>
                                            <label class="block">
                                                <span class="text-xs font-semibold text-gray-700">Phone Number</span>
                                                <input type="tel" id="phone_number_input" placeholder="9123456789" class="mt-1 w-full rounded-3xl border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-black" />
                                            </label>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" id="phone_save_btn" class="flex-1 rounded-3xl bg-black px-4 py-2 text-sm font-semibold text-white hover:bg-gray-900 transition">Save</button>
                                            <button type="button" id="phone_cancel_btn" class="flex-1 rounded-3xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition">Cancel</button>
                                        </div>
                                    </div>
                                    @error('phone')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </label>
                            </div>
                        </div>

                        <div class="grid gap-6 xl:grid-cols-[280px_1fr]">
                            <div class="rounded-3xl border border-gray-200 bg-slate-50 p-6">
                                <p class="text-sm font-semibold text-gray-900">Security</p>
                                <p class="mt-2 text-sm text-gray-500">Control password access and two-factor authentication.</p>
                            </div>

                            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                                <div class="grid gap-6 sm:grid-cols-2">
                                    <label class="block">
                                        <span class="text-sm font-semibold text-gray-900">Current password</span>
                                        <input type="password" name="current_password" class="mt-2 w-full rounded-3xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-black" placeholder="••••••••" />
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-semibold text-gray-900">New password</span>
                                        <input type="password" name="password" class="mt-2 w-full rounded-3xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-black" placeholder="••••••••" />
                                    </label>
                                </div>

                                <label class="mt-4 block">
                                    <span class="text-sm font-semibold text-gray-900">Two-factor authentication</span>
                                    <div class="mt-3 inline-flex items-center gap-4 rounded-3xl border border-gray-300 bg-slate-50 p-4">
                                        <input type="hidden" name="two_fa_enabled" value="0">
                                        <input type="checkbox" name="two_fa_enabled" value="1" class="h-5 w-5 rounded border-gray-300 text-black focus:ring-black" {{ old('two_fa_enabled', auth()->user()->two_fa_enabled) ? 'checked' : '' }}>
                                        <span class="text-sm text-gray-700">Enable two-factor authentication</span>
                                    </div>
                                    @error('two_fa_enabled')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </label>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row justify-end">
                            <a href="{{ route('reserve') }}" class="inline-flex items-center justify-center rounded-full border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-all">Cancel</a>
                            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-black px-6 py-3 text-sm font-semibold text-white hover:bg-gray-900 transition-all">Save settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Phone number edit functionality
        const displayMode = document.getElementById('phone_display_mode');
        const editMode = document.getElementById('phone_edit_mode');
        const editBtn = document.getElementById('phone_edit_btn');
        const saveBtn = document.getElementById('phone_save_btn');
        const cancelBtn = document.getElementById('phone_cancel_btn');
        const countryCodeSelect = document.getElementById('country_code_select');
        const phoneNumberInput = document.getElementById('phone_number_input');
        const displayText = document.getElementById('phone_display_text');

        // Parse current phone value to extract country code and number
        function parsePhone(phone) {
            if (!phone) {
                return { countryCode: '+63', number: '' };
            }

            const countryCodes = ['+1', '+44', '+63', '+81', '+91', '+86', '+33', '+49', '+39', '+61'];
            
            for (const code of countryCodes) {
                if (phone.startsWith(code)) {
                    return {
                        countryCode: code,
                        number: phone.substring(code.length)
                    };
                }
            }
            
            return { countryCode: '+63', number: phone };
        }

        // Format phone for display
        function formatPhoneDisplay(countryCode, number) {
            if (!number) return 'Not set';
            return countryCode + ' ' + number;
        }

        // Initialize with current phone data
        const currentPhone = '{{ auth()->user()->phone }}';
        const parsed = parsePhone(currentPhone);
        countryCodeSelect.value = parsed.countryCode;
        phoneNumberInput.value = parsed.number;

        // Edit button click
        editBtn.addEventListener('click', function(e) {
            e.preventDefault();
            displayMode.classList.add('hidden');
            editMode.classList.remove('hidden');
            phoneNumberInput.focus();
        });

        // Save button click
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const countryCode = countryCodeSelect.value;
            const phoneNumber = phoneNumberInput.value.trim();

            if (!phoneNumber) {
                alert('Please enter a phone number');
                return;
            }

            // Update the hidden form fields for submission
            const phoneHiddenInput = document.getElementById('form_phone_input');
            const countryCodeHiddenInput = document.getElementById('form_country_code_input');
            
            // Update display
            const formattedPhone = formatPhoneDisplay(countryCode, phoneNumber);
            displayText.textContent = formattedPhone;
            
            // Store the combined phone value in hidden inputs
            phoneHiddenInput.value = countryCode + phoneNumber;
            countryCodeHiddenInput.value = countryCode;

            // Switch back to display mode
            displayMode.classList.remove('hidden');
            editMode.classList.add('hidden');
        });

        // Cancel button click
        cancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Reset to current values
            countryCodeSelect.value = parsed.countryCode;
            phoneNumberInput.value = parsed.number;
            
            // Switch back to display mode
            displayMode.classList.remove('hidden');
            editMode.classList.add('hidden');
        });

        // Allow Enter key to save
        phoneNumberInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveBtn.click();
            }
        });
    });
</script>
@endsection
