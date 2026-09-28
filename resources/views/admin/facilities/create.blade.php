@extends('layouts.app')

@section('title', 'Add Facility - Campus Reserve')

@section('content')
<div class="min-h-screen bg-gray-50 py-10">
    <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ route('admin.dashboard') }}" class="mb-4 inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                &larr; Back to Admin Dashboard
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Add Facility</h1>
            <p class="mt-2 text-gray-600">Add a room or space to Campus Reserve.</p>
        </div>

        <form method="POST" action="{{ route('admin.facilities.store') }}" class="space-y-6 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            @csrf

            <div>
                <label for="name" class="mb-2 block text-sm font-medium text-gray-700">Facility name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="100" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="building" class="mb-2 block text-sm font-medium text-gray-700">Building</label>
                <input id="building" name="building" type="text" value="{{ old('building') }}" required maxlength="100" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                @error('building')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="type" class="mb-2 block text-sm font-medium text-gray-700">Facility type</label>
                    <input id="type" name="type" type="text" value="{{ old('type') }}" required maxlength="50" placeholder="e.g. Classroom, Laboratory" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                    @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="capacity" class="mb-2 block text-sm font-medium text-gray-700">Capacity</label>
                    <input id="capacity" name="capacity" type="number" value="{{ old('capacity') }}" required min="1" step="1" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                    @error('capacity')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="amenities" class="mb-2 block text-sm font-medium text-gray-700">Amenities</label>
                <textarea id="amenities" name="amenities" rows="3" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-black focus:outline-none focus:ring-1 focus:ring-black" placeholder="Separate amenities with commas">{{ old('amenities') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Optional. Separate each amenity with a comma.</p>
                @error('amenities')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-start gap-3 text-sm text-gray-700">
                <input type="checkbox" name="requires_approval" value="1" @checked(old('requires_approval')) class="mt-0.5 rounded border-gray-300 text-black focus:ring-black">
                <span>Require administrator approval for reservations at this facility.</span>
            </label>

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                <a href="{{ route('admin.dashboard') }}" class="rounded-full px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</a>
                <button type="submit" class="rounded-full bg-black px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-800">Save Facility</button>
            </div>
        </form>
    </div>
</div>
@endsection
