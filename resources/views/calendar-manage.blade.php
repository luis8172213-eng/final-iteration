@extends('layouts.app')

@section('title', 'Reserve a Facility')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Reserve a Facility</h1>
        <div class="flex items-center gap-4 mb-4">
            <select id="monthDropdown" class="border rounded px-2 py-1">
                <option value="0">January</option>
                <option value="1">February</option>
                <option value="2">March</option>
                <option value="3">April</option>
                <option value="4">May</option>
                <option value="5">June</option>
                <option value="6">July</option>
                <option value="7">August</option>
                <option value="8">September</option>
                <option value="9">October</option>
                <option value="10">November</option>
                <option value="11">December</option>
            </select>
            <select id="yearDropdown" class="border rounded px-2 py-1"></select>
        </div>
        <div id="calendar"></div>

        <!-- Reservation Modal -->
        <div id="reservationModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 hidden">
            <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">Reserve a facility on <span id="modalDate"></span></h2>
                <form id="reservationForm">
                    <div class="mb-4">
                        <label for="facilitySelect" class="block text-gray-700">Facility</label>
                        <select id="facilitySelect" name="facility" class="mt-1 block w-full border-gray-300 rounded-md" required></select>
                    </div>
                    <div class="mb-4">
                        <label for="startTime" class="block text-gray-700">Start Time</label>
                        <input type="time" id="startTime" name="startTime" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-4">
                        <label for="endTime" class="block text-gray-700">End Time</label>
                        <input type="time" id="endTime" name="endTime" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" id="closeModalBtn" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Reserve</button>
                    </div>
                </form>
            </div>
        </div>
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
            var monthDropdown = document.getElementById('monthDropdown');
            var yearDropdown = document.getElementById('yearDropdown');
            // Populate year dropdown (current year +/- 5 years)
            var currentYear = new Date().getFullYear();
            for (var y = currentYear - 5; y <= currentYear + 5; y++) {
                var opt = document.createElement('option');
                opt.value = y;
                opt.textContent = y;
                if (y === currentYear) opt.selected = true;
                yearDropdown.appendChild(opt);
            }

            // Populate facility dropdown
            function populateFacilities(selected) {
                fetch('/api/facilities')
                    .then(response => response.json())
                    .then(facilities => {
                        var select = document.getElementById('facilitySelect');
                        select.innerHTML = '';
                        facilities.forEach(function(fac) {
                            var opt = document.createElement('option');
                            opt.value = fac.name;
                            opt.textContent = fac.name;
                            if (selected && fac.name === selected) opt.selected = true;
                            select.appendChild(opt);
                        });
                    });
            }
            var selectedDate = null;
            var calendar = new window.FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                selectable: true,
                editable: false,
                events: function(fetchInfo, successCallback, failureCallback) {
                    fetch('/api/calendar/events')
                        .then(response => response.json())
                        .then(events => successCallback(events))
                        .catch(failureCallback);
                },
                dateClick: function(info) {
                    selectedDate = info.dateStr;
                    document.getElementById('modalDate').textContent = info.dateStr;
                    populateFacilities();
                    document.getElementById('reservationModal').classList.remove('hidden');
                },
            });
            calendar.render();

            // Set dropdowns to current calendar date
            function syncDropdowns() {
                var date = calendar.getDate();
                monthDropdown.value = date.getMonth();
                yearDropdown.value = date.getFullYear();
            }
            calendar.on('datesSet', syncDropdowns);
            syncDropdowns();

            // Change calendar when dropdowns change
            monthDropdown.addEventListener('change', function() {
                var year = parseInt(yearDropdown.value);
                var month = parseInt(monthDropdown.value);
                calendar.gotoDate(new Date(year, month, 1));
            });
            yearDropdown.addEventListener('change', function() {
                var year = parseInt(yearDropdown.value);
                var month = parseInt(monthDropdown.value);
                calendar.gotoDate(new Date(year, month, 1));
            });

            document.getElementById('closeModalBtn').onclick = function() {
                document.getElementById('reservationModal').classList.add('hidden');
            };
            document.getElementById('reservationForm').onsubmit = function(e) {
                e.preventDefault();
                const facility = document.getElementById('facilitySelect').value;
                const startTime = document.getElementById('startTime').value;
                const endTime = document.getElementById('endTime').value;
                fetch('/api/calendar/reserve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        facility: facility,
                        date: selectedDate,
                        start_time: startTime,
                        end_time: endTime
                    })
                })
                .then(response => {
                    if (!response.ok) return response.json().then(err => Promise.reject(err));
                    return response.json();
                })
                .then(data => {
                    calendar.refetchEvents();
                    document.getElementById('reservationModal').classList.add('hidden');
                    document.getElementById('reservationForm').reset();
                })
                .catch(err => {
                    alert(err.error || 'Reservation failed.');
                });
            };
        });
    </script>
@endpush
