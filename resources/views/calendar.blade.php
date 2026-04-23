@extends('layouts.app')

@section('title', 'Reserve Calendar - Campus Reserve')

@section('content')
<!-- Calendar page showing reservation events and interaction controls for event details. -->
<div class="min-h-screen bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col gap-4 md:gap-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500">Campus Reserve</p>
                    <h1 class="text-3xl font-semibold text-slate-900">Calendar</h1>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="relative w-full sm:w-80">
                        <label for="eventSearch" class="sr-only">Search events</label>
                        <input id="eventSearch" type="search" placeholder="Search events, rooms, details..." class="w-full rounded-2xl border border-slate-200 bg-white py-3 px-4 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" autocomplete="off" />
                        <div id="searchSuggestions" class="absolute left-0 right-0 mt-2 hidden max-h-72 overflow-auto rounded-2xl border border-slate-200 bg-white shadow-lg z-20"></div>
                    </div>
                    <button id="todayBtn" type="button" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 transition">Today</button>
                </div>
            </div>
            <div class="gap-4 grid-cols-1" style="display:grid;grid-template-columns:240px minmax(680px,1fr);gap:1rem;overflow-x:auto;max-width:1040px;margin:0 auto;">
                <div class="space-y-6">
                    <aside class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                        <div class="rounded-3xl border border-slate-200 bg-slate-900 p-5 text-white shadow-sm">
                            <div class="flex items-center justify-between text-sm text-slate-300 mb-4">
                                <span id="miniCalendarHeader">August 2023</span>
                                <span id="miniCalendarView">Week</span>
                            </div>
                            <div class="grid grid-cols-7 gap-2 text-center text-[11px] uppercase text-slate-400 mb-3">
                                <span>Su</span>
                                <span>Mo</span>
                                <span>Tu</span>
                                <span>We</span>
                                <span>Th</span>
                                <span>Fr</span>
                                <span>Sa</span>
                            </div>
                            <div id="miniCalendarDays" class="grid grid-cols-7 gap-2 text-sm text-slate-100">
                                <!-- Filled by JavaScript -->
                            </div>
                        </div>
                    </aside>
                    <aside class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Pending</p>
                                <h2 class="text-xl font-semibold text-slate-900">Pending reservations</h2>
                            </div>
                            <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">Waiting approval</span>
                        </div>
                        <div id="pendingReservationsList" class="space-y-4">
                            <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-500">
                                Pending reservation requests will appear here.
                            </div>
                        </div>
                    </aside>
                </div>
                <main class="min-w-0 rounded-3xl bg-white p-6 shadow-sm border border-slate-200" style="min-width:680px;width:100%;max-width:100%;">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
                        <div class="space-y-2">
                            <p class="text-sm font-semibold text-slate-900">Weekly view</p>
                            <p class="text-sm text-slate-500">Browse existing reservations and see availability at a glance.</p>
                        </div>
                        <div class="flex flex-nowrap items-center gap-3 overflow-x-auto">
                            <button data-view="dayGridMonth" class="px-4 py-2 rounded-2xl border border-slate-200 bg-slate-100 text-slate-700 transition hover:bg-slate-200">Month</button>
                            <button data-view="timeGridWeek" class="px-4 py-2 rounded-2xl border border-slate-200 bg-slate-100 text-slate-700 transition hover:bg-slate-200">Week</button>
                            <button data-view="listWeek" class="px-4 py-2 rounded-2xl border border-slate-200 bg-slate-100 text-slate-700 transition hover:bg-slate-200">List</button>
                            <a href="/calendar/manage" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">Reserve Now</a>
                        </div>
                    </div>

                    <div id="calendar" class="rounded-3xl border border-slate-200 w-full" style="min-height:760px;"></div>
                </main>
            </div>
        </div>
    </div>
</div>

<div id="eventInfoModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40 p-4">
    <div class="w-full max-w-xl rounded-3xl bg-white p-8 shadow-2xl">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <p class="text-sm text-slate-500">Reservation details</p>
                <h2 id="eventInfoTitle" class="text-2xl font-semibold text-slate-900 mt-2">Reservation</h2>
            </div>
            <button id="closeEventInfoBtn" type="button" class="rounded-full bg-slate-100 p-3 text-slate-600 hover:bg-slate-200">×</button>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="space-y-2">
                <p class="text-sm font-semibold text-slate-700">Room</p>
                <p id="eventInfoRoom" class="text-slate-900">—</p>
            </div>
            <div class="space-y-2">
                <p class="text-sm font-semibold text-slate-700">Status</p>
                <p id="eventInfoStatus" class="inline-flex rounded-full px-3 py-1 text-sm font-semibold bg-yellow-100 text-yellow-800">Waiting Approval</p>
            </div>
            <div class="space-y-2">
                <p class="text-sm font-semibold text-slate-700">Date</p>
                <p id="eventInfoDate" class="text-slate-900">—</p>
            </div>
            <div class="space-y-2">
                <p class="text-sm font-semibold text-slate-700">Time</p>
                <p id="eventInfoTime" class="text-slate-900">—</p>
            </div>
            <div class="sm:col-span-2 space-y-2">
                <p class="text-sm font-semibold text-slate-700">Purpose</p>
                <p id="eventInfoPurpose" class="text-slate-900">—</p>
            </div>
        </div>
        <div class="mt-8 flex flex-wrap items-center gap-3">
            <a id="eventInfoEditBtn" href="#" class="hidden items-center justify-center rounded-full border border-slate-200 bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-200 transition">Edit reservation</a>
            <form id="eventInfoDeleteForm" method="POST" action="#" class="hidden" onsubmit="return confirm('Remove this reservation? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-rose-600 px-5 py-3 text-sm font-semibold text-white hover:bg-rose-700 transition">Remove</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('fullcalendar/main.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<!-- FullCalendar scripts and page event handling logic for loading events and opening modals. -->
<script src="{{ asset('fullcalendar/index.global.min.js') }}" crossorigin="anonymous"></script>
<script src="{{ asset('fullcalendar/locales-all.global.min.js') }}" crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var searchInput = document.getElementById('eventSearch');
        var pendingReservationsList = document.getElementById('pendingReservationsList');
        var todayBtn = document.getElementById('todayBtn');
        var viewButtons = document.querySelectorAll('[data-view]');
        var eventInfoModal = document.getElementById('eventInfoModal');
        var eventInfoTitle = document.getElementById('eventInfoTitle');
        var eventInfoRoom = document.getElementById('eventInfoRoom');
        var eventInfoStatus = document.getElementById('eventInfoStatus');
        var eventInfoDate = document.getElementById('eventInfoDate');
        var eventInfoTime = document.getElementById('eventInfoTime');
        var eventInfoPurpose = document.getElementById('eventInfoPurpose');
        var eventInfoEditBtn = document.getElementById('eventInfoEditBtn');
        var eventInfoDeleteForm = document.getElementById('eventInfoDeleteForm');
        var eventInfoClose = document.getElementById('closeEventInfoBtn');
        var currentSearch = '';
        var cachedEvents = [];
            var suggestionsEl = document.getElementById('searchSuggestions');

            function closeEventInfoModal() {
                if (eventInfoModal) {
                    eventInfoModal.classList.add('hidden');
                    eventInfoModal.classList.remove('flex');
                }
            }

            function renderSearchSuggestions(filteredEvents) {
                if (!suggestionsEl) return;
                if (!filteredEvents.length || !currentSearch) {
                    suggestionsEl.innerHTML = '';
                    suggestionsEl.classList.add('hidden');
                    return;
                }

                var listHtml = filteredEvents.map(function(item) {
                    var date = new Date(item.start);
                    var time = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    return '<button type="button" class="w-full text-left px-4 py-3 hover:bg-slate-100 transition-colors" data-event-id="' + item.id + '">'
                        + '<div class="font-semibold text-slate-900">' + item.title + '</div>'
                        + '<div class="text-xs text-slate-500">' + item.extendedProps.room + ' • ' + item.extendedProps.purpose + '</div>'
                        + '<div class="text-xs text-slate-500 mt-1">' + date.toLocaleDateString() + ' • ' + time + '</div>'
                        + '</button>';
                }).join('');

                suggestionsEl.innerHTML = listHtml;
                suggestionsEl.classList.remove('hidden');
            }

            function getSearchMatches(query) {
                if (!query) return [];
                query = query.toLowerCase();

                return cachedEvents.filter(function(event) {
                    var title = (event.title || '').toLowerCase();
                    var room = (event.extendedProps.room || '').toLowerCase();
                    var purpose = (event.extendedProps.purpose || '').toLowerCase();
                    return title.includes(query) || room.includes(query) || purpose.includes(query);
                }).slice(0, 6);
            }

            function openEventFromSuggestion(eventId) {
                var event = calendar.getEventById(eventId);
                var sourceEvent = cachedEvents.find(function(item) {
                    return String(item.id) === String(eventId);
                });

                if (!sourceEvent) return;
                if (sourceEvent.start) {
                    calendar.gotoDate(new Date(sourceEvent.start));
                }

                if (event) {
                    event.setProp('backgroundColor', event.backgroundColor || event.color);
                }

                // Show details modal for the selected suggestion
                var props = sourceEvent.extendedProps || {};
                var date = new Date(sourceEvent.start);
                var startTime = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                var endTime = sourceEvent.end ? new Date(sourceEvent.end).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
                var status = props.status || 'pending';
                var statusLabel = status === 'approved' ? 'Approved' : status === 'rejected' ? 'Rejected' : 'Waiting Approval';
                var statusClasses = status === 'approved' ? 'inline-flex rounded-full px-3 py-1 text-sm font-semibold bg-emerald-100 text-emerald-700' :
                                    status === 'rejected' ? 'inline-flex rounded-full px-3 py-1 text-sm font-semibold bg-red-100 text-red-700' :
                                    'inline-flex rounded-full px-3 py-1 text-sm font-semibold bg-yellow-100 text-yellow-800';

                eventInfoTitle.textContent = props.room || sourceEvent.title || 'Reservation';
                eventInfoRoom.textContent = props.room || sourceEvent.title || '—';
                eventInfoStatus.textContent = statusLabel;
                eventInfoStatus.className = statusClasses;
                eventInfoDate.textContent = props.date || date.toLocaleDateString();
                eventInfoTime.textContent = startTime + ' - ' + endTime;
                eventInfoPurpose.textContent = props.purpose || '—';
                if (eventInfoEditBtn) {
                    eventInfoEditBtn.href = '/reserve/reservations/' + sourceEvent.id + '/edit';
                    // Since we now only fetch current user's reservations, show edit for pending/approved/rejected
                    if (['approved', 'rejected', 'pending'].includes(props.status)) {
                        eventInfoEditBtn.classList.remove('hidden');
                    } else {
                        eventInfoEditBtn.classList.add('hidden');
                    }
                }
                if (eventInfoDeleteForm) {
                    eventInfoDeleteForm.action = '/reserve/reservations/' + sourceEvent.id;
                    if (status === 'rejected' || status === 'pending') {
                        eventInfoDeleteForm.classList.remove('hidden');
                    } else {
                        eventInfoDeleteForm.classList.add('hidden');
                    }
                }
                eventInfoModal.classList.remove('hidden');
                eventInfoModal.classList.add('flex');
            }

        var calendar = new window.FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: false,
            navLinks: true,
            selectable: false,
            editable: false,
            events: function(info, successCallback, failureCallback) {
                fetch('/api/calendar/events', { credentials: 'include' })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(events) {
                        cachedEvents = events.map(function(item) {
                            return {
                                id: item.id,
                                title: item.title,
                                start: item.start,
                                end: item.end,
                                extendedProps: item.extendedProps || {},
                                color: item.color || null
                            };
                        });
                        successCallback(events);
                    })
                    .catch(function(err) {
                        failureCallback(err);
                    });
            },
            eventClick: function(info) {
                var event = info.event;
                var props = event.extendedProps || {};
                var startTime = event.start ? event.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
                var endTime = event.end ? event.end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
                var status = props.status || 'pending';
                var statusLabel = status === 'approved' ? 'Approved' : status === 'rejected' ? 'Rejected' : 'Waiting Approval';
                var statusClasses = status === 'approved' ? 'inline-flex rounded-full px-3 py-1 text-sm font-semibold bg-emerald-100 text-emerald-700' :
                                    status === 'rejected' ? 'inline-flex rounded-full px-3 py-1 text-sm font-semibold bg-red-100 text-red-700' :
                                    'inline-flex rounded-full px-3 py-1 text-sm font-semibold bg-yellow-100 text-yellow-800';

                eventInfoTitle.textContent = props.room || event.title || 'Reservation';
                eventInfoRoom.textContent = props.room || event.title || '—';
                eventInfoStatus.textContent = statusLabel;
                eventInfoStatus.className = statusClasses;
                eventInfoDate.textContent = props.date || (event.start ? event.start.toLocaleDateString() : '—');
                eventInfoTime.textContent = startTime + ' - ' + endTime;
                eventInfoPurpose.textContent = props.purpose || '—';
                if (eventInfoEditBtn) {
                    eventInfoEditBtn.href = '/reserve/reservations/' + event.id + '/edit';
                    // Since we now only fetch current user's reservations, show edit for pending/approved/rejected
                    if (['approved', 'rejected', 'pending'].includes(props.status)) {
                        eventInfoEditBtn.classList.remove('hidden');
                    } else {
                        eventInfoEditBtn.classList.add('hidden');
                    }
                }
                if (eventInfoDeleteForm) {
                    eventInfoDeleteForm.action = '/reserve/reservations/' + event.id;
                    if (status === 'rejected' || status === 'pending') {
                        eventInfoDeleteForm.classList.remove('hidden');
                    } else {
                        eventInfoDeleteForm.classList.add('hidden');
                    }
                }
                eventInfoModal.classList.remove('hidden');
                eventInfoModal.classList.add('flex');
            }
        });

        calendar.render();
        fetchPendingReservations();

        function fetchPendingReservations() {
            if (!pendingReservationsList) return;
            fetch('/api/calendar/pending', { credentials: 'include' })
                .then(function(response) { return response.json(); })
                .then(function(pending) {
                    if (!pending.length) {
                        pendingReservationsList.innerHTML = '<div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-500">No pending reservations at the moment.</div>';
                        return;
                    }

                    pendingReservationsList.innerHTML = pending.map(function(item) {
                        return '<div class="rounded-3xl bg-slate-50 p-4">'
                            + '<div class="flex items-center justify-between">'
                            + '<div>'
                            + '<p class="text-sm font-semibold text-slate-900">' + item.facility + '</p>'
                            + '<p class="text-sm text-slate-500">' + item.date + ' • ' + item.start_time + ' - ' + item.end_time + '</p>'
                            + '</div>'
                            + '<span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">Pending</span>'
                            + '</div>'
                            + '<p class="mt-3 text-sm text-slate-600">' + (item.purpose || 'No purpose provided.') + '</p>'
                            + '</div>';
                    }).join('');
                })
                .catch(function() {
                    if (pendingReservationsList) {
                        pendingReservationsList.innerHTML = '<div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-500">Unable to load pending reservations.</div>';
                    }
                });
        }

        // Mini calendar update function
        function updateMiniCalendar() {
            var currentDate = calendar.getDate();
            var miniHeader = document.getElementById('miniCalendarHeader');
            var miniView = document.getElementById('miniCalendarView');
            var miniDays = document.getElementById('miniCalendarDays');

            if (!miniHeader || !miniDays) return;

            // Update header with current month/year
            var monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                            'July', 'August', 'September', 'October', 'November', 'December'];
            miniHeader.textContent = monthNames[currentDate.getMonth()] + ' ' + currentDate.getFullYear();

            // Get first day of month and number of days
            var firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            var lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
            var startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay());

            // Clear and populate days
            miniDays.innerHTML = '';
            var today = new Date();
            today.setHours(0, 0, 0, 0);

            for (var i = 0; i < 42; i++) {
                var dayDate = new Date(startDate);
                dayDate.setDate(dayDate.getDate() + i);
                var span = document.createElement('span');
                span.textContent = dayDate.getDate();
                span.className = 'py-2';

                // Highlight today
                if (dayDate.toDateString() === today.toDateString()) {
                    span.className = 'rounded-2xl bg-slate-700 py-2';
                }

                // Dim days outside current month
                if (dayDate.getMonth() !== currentDate.getMonth()) {
                    span.className += ' opacity-40';
                }

                miniDays.appendChild(span);
            }

            // Update view type
            if (miniView && calendar.view) {
                var viewType = calendar.view.type;
                if (viewType.includes('Week')) miniView.textContent = 'Week';
                else if (viewType.includes('Month')) miniView.textContent = 'Month';
                else if (viewType.includes('List')) miniView.textContent = 'List';
            }
        }

        updateMiniCalendar();

        // Update mini calendar when main calendar changes
        calendar.on('datesSet', updateMiniCalendar);

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                currentSearch = this.value.trim();
                var matches = getSearchMatches(currentSearch);
                renderSearchSuggestions(matches);
            });

            searchInput.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    currentSearch = '';
                    this.value = '';
                    renderSearchSuggestions([]);
                }
            });

            searchInput.addEventListener('blur', function() {
                setTimeout(function() {
                    renderSearchSuggestions([]);
                }, 150);
            });
        }

        if (suggestionsEl) {
            suggestionsEl.addEventListener('click', function(event) {
                var button = event.target.closest('button[data-event-id]');
                if (!button) return;
                var eventId = button.getAttribute('data-event-id');
                openEventFromSuggestion(eventId);
                renderSearchSuggestions([]);
                searchInput.value = '';
                currentSearch = '';
            });
        }

        if (todayBtn) {
            todayBtn.addEventListener('click', function() {
                calendar.today();
                updateMiniCalendar();
            });
        }

        viewButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var viewName = button.getAttribute('data-view');
                if (viewName) {
                    calendar.changeView(viewName);
                    updateMiniCalendar();
                }
            });
        });

        if (eventInfoClose) {
            eventInfoClose.addEventListener('click', closeEventInfoModal);
        }

        if (eventInfoModal) {
            eventInfoModal.addEventListener('click', function(event) {
                if (event.target === eventInfoModal) {
                    closeEventInfoModal();
                }
            });
        }
    });
</script>
@endpush

