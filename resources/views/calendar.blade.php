@extends('layouts.app')

@section('title', 'Reserve Calendar - Campus Reserve')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Calendar</h1>
        <!-- Color Legend -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-2">Facility Color Legend</h2>
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-2"><span class="inline-block w-4 h-4 rounded" style="background:#6366f1"></span> Conference Room A</div>
                <div class="flex items-center gap-2"><span class="inline-block w-4 h-4 rounded" style="background:#10b981"></span> Computer Lab</div>
                <div class="flex items-center gap-2"><span class="inline-block w-4 h-4 rounded" style="background:#f59e42"></span> Study Room B</div>
                <div class="flex items-center gap-2"><span class="inline-block w-4 h-4 rounded" style="background:#f43f5e"></span> Auditorium</div>
                <div class="flex items-center gap-2"><span class="inline-block w-4 h-4 rounded" style="background:#3b82f6"></span> Sports Hall</div>
                <div class="flex items-center gap-2"><span class="inline-block w-4 h-4 rounded" style="background:#a21caf"></span> Science Lab</div>
            </div>
        </div>
        <div id="calendar"></div>
        <!-- Floating Reserve Button -->
        <a href="/calendar/manage" class="fixed bottom-8 right-8 bg-blue-600 text-white px-6 py-3 rounded-full shadow-lg hover:bg-blue-700 transition-all z-50 text-lg font-semibold">Reserve</a>
    </div>
</div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet" />
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales-all.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new window.FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                editable: false,
                selectable: false,
                headerToolbar: {
                    left: 'prev',
                    center: 'title',
                    right: 'next'
                },
                events: '/api/calendar/events',
            });
            calendar.render();
        });
    </script>
@endpush
