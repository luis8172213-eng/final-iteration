<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\ReservationPendingApproval;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class ChatbotController extends Controller
{
    public function reset(): JsonResponse
    {
        session()->forget([
            'chatbot_reservation',
            'chatbot_cancellation',
            'chatbot_last_action',
        ]);

        return response()->json(['success' => true]);
    }

    public function respond(Request $request): JsonResponse
    {
        $message = trim((string) $request->input('message'));
        $normalized = $this->normalizeMessage($message);

        if ($message === '') {
            return response()->json(['reply' => 'Ask me about available rooms, schedules, or reservations.'], 422);
        }

        [$safeMessage, $containsAbuse] = $this->moderateMessage($message);
        if ($containsAbuse) {
            return response()->json([
                'reply' => 'Please keep the conversation respectful. I can help with reservations, room availability, schedules, and your bookings.',
                'user_message' => $safeMessage,
                'moderated' => true,
            ], 422);
        }

        if (!$this->isSupportedMessage($normalized) && !$this->isFacilityName($normalized)) {
            return response()->json([
                'reply' => 'I can only help with Campus Reserve, room availability, schedules, reservations, cancellations, and your booking status.',
                'user_message' => $safeMessage,
            ]);
        }

        $state = session('chatbot_reservation', []);
        $cancellation = session('chatbot_cancellation', []);
        if (($cancellation['awaiting_confirmation'] ?? false) && $this->isConfirmation($normalized)) {
            $reservation = Reservation::with('facility')
                ->where('user_id', Auth::id())
                ->find($cancellation['reservation_id'] ?? null);

            if (!$reservation || !$reservation->canBeCancelled()) {
                session()->forget('chatbot_cancellation');
                return response()->json(['reply' => 'That reservation can no longer be cancelled. Please check your current reservations.']);
            }

            $facilityName = $reservation->facility?->name ?? 'the facility';
            $reservation->delete();
            session()->forget('chatbot_cancellation');

            return response()->json(['reply' => "Your reservation for {$facilityName} on " . $reservation->reservation_date->format('F j, Y') . ' was cancelled successfully.']);
        }

        if (($cancellation['awaiting_confirmation'] ?? false) && $this->isNegative($normalized)) {
            session()->forget('chatbot_cancellation');
            return response()->json(['reply' => 'Okay, I did not cancel the reservation.']);
        }

        if (($cancellation['awaiting_selection'] ?? false)) {
            $reservation = Reservation::with('facility')
                ->where('user_id', Auth::id())
                ->whereIn('status', ['pending', 'approved'])
                ->get()
                ->filter(fn (Reservation $reservation) => $reservation->canBeCancelled())
                ->first(function (Reservation $reservation) use ($normalized) {
                    $facilityName = preg_replace('/[^a-z0-9]/', '', strtolower($reservation->facility?->name ?? ''));
                    $question = preg_replace('/[^a-z0-9]/', '', $normalized);

                    return $facilityName !== '' && str_contains($question, $facilityName);
                });

            if ($reservation) {
                session(['chatbot_cancellation' => [
                    'reservation_id' => $reservation->id,
                    'awaiting_confirmation' => true,
                ]]);

                return response()->json(['reply' => "Do you want to cancel your {$reservation->facility->name} reservation on " . $reservation->reservation_date->format('F j, Y') . '? Reply yes to confirm cancellation.']);
            }
        }

        if (($state['awaiting_confirmation'] ?? false) && $this->isConfirmation($normalized)) {
            $facility = Facility::active()->find($state['facility_id'] ?? null);
            $date = $state['date'] ?? null;
            $startTime = $state['start_time'] ?? null;
            $endTime = $state['end_time'] ?? null;

            if (!$facility || !$date || !$startTime || !$endTime) {
                session()->forget('chatbot_reservation');
                return response()->json(['reply' => 'I need the room, date, and time again before I can submit the request.']);
            }

            if (!$facility->isAvailable($date, $startTime, $endTime)) {
                session()->forget('chatbot_reservation');
                return response()->json(['reply' => 'That time is no longer available. Please choose another time or room.']);
            }

            $reservation = Reservation::create([
                'user_id' => Auth::id(),
                'facility_id' => $facility->id,
                'reservation_date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'purpose' => 'Reservation requested through the reservation assistant',
                'status' => 'pending',
            ]);

            $admins = User::where('is_admin', true)
                ->orWhere('is_super_admin', true)
                ->get();
            Notification::send($admins, new ReservationPendingApproval($reservation));
            session()->forget('chatbot_reservation');
            session(['chatbot_last_action' => 'reservation_submitted']);

            return response()->json([
                'reply' => "Your reservation request for {$facility->name} on " . Carbon::parse($date)->format('F j, Y') . " from " . Carbon::createFromFormat('H:i', $startTime)->format('g:i A') . ' to ' . Carbon::createFromFormat('H:i', $endTime)->format('g:i A') . ' was sent to the administrators for approval.',
            ]);
        }

        if (session('chatbot_last_action') === 'reservation_submitted') {
            if ($this->isConfirmation($normalized)) {
                return response()->json(['reply' => 'Would you like to make another reservation? Tell me the room, date, and time, or reply “no” when you are finished.']);
            }

            if ($this->isNegative($normalized) || preg_match('/^(done|finished|that is all|that\'s all)\b/i', $normalized)) {
                session()->forget('chatbot_last_action');
                return response()->json(['reply' => 'Okay. Your reservation request is waiting for administrator approval.']);
            }

            session()->forget('chatbot_last_action');
        }

        if (preg_match('/\b(new|start over|reset|forget that|different)\b.*\b(reservation|booking|request|room|time)\b/i', $normalized)) {
            session()->forget('chatbot_reservation');
        }

        if (preg_match('/^\s*(hello|hi|hey)\s*[!.?]*\s*$/', $normalized)) {
            return response()->json(['reply' => 'Hi! I can check room availability and help you start a reservation. Try: “Is Room 101 available tomorrow from 9 AM to 11 AM?”']);
        }

        if (preg_match('/\b(help|what can you do|what do you do|website|site|account|login|profile)\b/', $normalized)) {
            return response()->json(['reply' => 'I can help with Campus Reserve, room availability, schedules, reservations, cancellations, and your booking status.']);
        }

        if ($this->isReservationStatusQuestion($normalized)) {
            $reservations = Reservation::with('facility')
                ->where('user_id', Auth::id())
                ->whereIn('status', ['pending', 'approved', 'rejected'])
                ->orderByDesc('reservation_date')
                ->orderByDesc('start_time')
                ->get();

            if ($reservations->isEmpty()) {
                return response()->json(['reply' => 'You do not have any pending, accepted, or declined reservations.']);
            }

            $summary = $reservations->map(function (Reservation $reservation) {
                $status = match ($reservation->status) {
                    'approved' => 'accepted',
                    'rejected' => 'declined',
                    default => $reservation->status,
                };
                $facility = $reservation->facility?->name ?? 'Unknown facility';
                $date = $reservation->reservation_date->format('F j, Y');
                $time = $reservation->start_time->format('g:i A') . ' to ' . $reservation->end_time->format('g:i A');

                return "{$facility} on {$date}, {$time} - {$status}";
            })->join("\n");

            return response()->json(['reply' => "Here are your reservations:\n{$summary}"]);
        }

        if (preg_match('/\b(cancel|remove|delete|get rid of)\b.*\b(reservation|booking|request|it|that)\b/i', $normalized)
            || preg_match('/\b(cancel|remove|delete)\b.*\b(my|the)\b/i', $normalized)) {
            $reservations = Reservation::with('facility')
                ->where('user_id', Auth::id())
                ->whereIn('status', ['pending', 'approved'])
                ->get()
                ->filter(fn (Reservation $reservation) => $reservation->canBeCancelled());

            $requestedReservation = $reservations->first(function (Reservation $reservation) use ($normalized) {
                $facilityName = preg_replace('/[^a-z0-9]/', '', strtolower($reservation->facility?->name ?? ''));
                $question = preg_replace('/[^a-z0-9]/', '', $normalized);

                return $facilityName !== '' && str_contains($question, $facilityName);
            });

            if ($requestedReservation) {
                $facilityName = $requestedReservation->facility?->name ?? 'the facility';
                session(['chatbot_cancellation' => [
                    'reservation_id' => $requestedReservation->id,
                    'awaiting_confirmation' => true,
                ]]);

                return response()->json(['reply' => "Do you want to cancel your {$facilityName} reservation on " . $requestedReservation->reservation_date->format('F j, Y') . '? Reply yes to confirm cancellation.']);
            }

            if ($reservations->isEmpty()) {
                return response()->json(['reply' => 'You do not have any pending or accepted reservations that can be cancelled.']);
            }

            $options = $reservations->map(function (Reservation $reservation) {
                return ($reservation->facility?->name ?? 'Unknown facility') . ' on ' . $reservation->reservation_date->format('F j, Y');
            })->join('; ');

            session(['chatbot_cancellation' => ['awaiting_selection' => true]]);
            return response()->json(['reply' => "Which reservation do you want to cancel? Your cancellable reservations are: {$options}. Reply with the room name, then I will ask you to confirm."]); 
        }

        $isFacilityListRequest = (
            preg_match('/\b(show|list|what|which|name)\b.*\b(rooms?|facilit(?:y|ies)|spaces?)\b/', $normalized)
            && preg_match('/\b(available|active|have|offer|there)\b/', $normalized)
        ) || preg_match('/\b(show|list|what) (rooms?|facilit(?:y|ies)|spaces?)\b/', $normalized);

        if ($isFacilityListRequest && !$this->hasDateOrTimeRequest($normalized)) {
            $facilities = Facility::active()->orderBy('name')->get();
            if ($facilities->isEmpty()) {
                return response()->json(['reply' => 'There are no active facilities available right now.']);
            }

            $rooms = $facilities->map(fn (Facility $facility) => $facility->name)->join(', ');
            return response()->json(['reply' => "Our active facilities are: {$rooms}. Ask me for a date and time to check availability."]); 
        }

        $date = $this->parseDate($normalized);
        [$startTime, $endTime] = $this->parseTimeRange($normalized);
        $facilities = Facility::active()->orderBy('name')->get();
        $requestedFacility = $facilities->first(function (Facility $facility) use ($normalized) {
            $facilityName = preg_replace('/[^a-z0-9]/', '', strtolower($facility->name));
            $roomNumber = preg_replace('/[^a-z0-9]/', '', strtolower((string) $facility->room_number));
            $question = preg_replace('/[^a-z0-9]/', '', $normalized);

            return ($facilityName !== '' && str_contains($question, $facilityName))
                || ($roomNumber !== '' && str_contains($question, $roomNumber));
        });

            $askingForAlternatives = (bool) preg_match('/\b(other|another|different|all)\s+(rooms?|facilit(?:y|ies))\b/i', $normalized);
            if ($askingForAlternatives) {
                $requestedFacility = null;
                $state['facility_id'] = null;
                $state['awaiting_confirmation'] = false;
            }

        $date ??= !empty($state['date']) ? Carbon::parse($state['date']) : null;
        $startTime ??= $state['start_time'] ?? null;
        $endTime ??= $state['end_time'] ?? null;
        $requestedFacility ??= $facilities->firstWhere('id', $state['facility_id'] ?? null);

        $state = [
            'date' => $date?->toDateString(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'facility_id' => $requestedFacility?->id,
        ];
        session(['chatbot_reservation' => $state]);

        if (!$date) {
            return response()->json(['reply' => 'What date should I check? You can say “today”, “tomorrow”, or use YYYY-MM-DD.']);
        }

        if (!$startTime || !$endTime) {
            if ($this->hasTimeRangeInput($normalized)) {
                return response()->json(['reply' => 'Please provide a valid time range where the end time is later than the start time, such as “9 to 10 AM” or “8 AM to 7 PM”.']);
            }

            return response()->json(['reply' => 'What time range should I check? For example: “from 9 AM to 11 AM”.']);
        }

        $matches = $requestedFacility ? collect([$requestedFacility]) : $facilities;
        $available = $matches->filter(fn (Facility $facility) => $facility->isAvailable($date->toDateString(), $startTime, $endTime));
        $dateLabel = $date->format('F j, Y');
        $timeLabel = Carbon::createFromFormat('H:i', $startTime)->format('g:i A') . ' to ' . Carbon::createFromFormat('H:i', $endTime)->format('g:i A');

        if ($available->isEmpty()) {
            $subject = $requestedFacility ? $requestedFacility->name : 'the requested rooms';
            session()->forget('chatbot_reservation');
            return response()->json(['reply' => "No availability was found for {$subject} on {$dateLabel} from {$timeLabel}. Try another time or date."]); 
        }

        $names = $available->map(fn (Facility $facility) => $facility->name)->join(', ');
        $isReservationRequest = (bool) preg_match('/\b(reserve|book|booking)\b/i', $normalized);

        if ($isReservationRequest && $requestedFacility) {
            session([
                'chatbot_reservation' => array_merge($state, [
                    'facility_id' => $requestedFacility->id,
                    'awaiting_confirmation' => true,
                ]),
            ]);

            return response()->json([
                'reply' => "{$requestedFacility->name} is available on {$dateLabel} from {$timeLabel}. Should I submit this reservation request to the administrators for approval? Reply yes to confirm reservation.",
            ]);
        }

        if ($requestedFacility) {
            session([
                'chatbot_reservation' => array_merge($state, [
                    'facility_id' => $requestedFacility->id,
                    'awaiting_confirmation' => true,
                ]),
            ]);

            return response()->json([
                'reply' => "Available on {$dateLabel} from {$timeLabel}: {$requestedFacility->name}. Would you like me to submit this reservation request to the administrators for approval? Reply yes to confirm reservation.",
            ]);
        }

        session([
            'chatbot_reservation' => array_merge($state, [
                'date' => $date->toDateString(),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'facility_id' => null,
                'awaiting_confirmation' => false,
            ]),
        ]);
        return response()->json([
            'reply' => ($isReservationRequest ? 'The requested time is available. ' : '') . "Available on {$dateLabel} from {$timeLabel}: {$names}. You can name a room if you want me to submit a reservation request.",
            'action_url' => route('reserve'),
            'action_label' => 'Open reservation form',
        ]);
    }

    private function parseDate(string $message): ?Carbon
    {
        if (preg_match('/\b(tomorrow|next day)\b/', $message)) {
            return now()->addDay()->startOfDay();
        }

        if (preg_match('/\b(today|this day)\b/', $message)) {
            return now()->startOfDay();
        }

        if (preg_match('/\b(20\d{2}-\d{2}-\d{2})\b/', $message, $matches)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $matches[1])->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        if (preg_match('/\b(January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{1,2}(?:st|nd|rd|th)?(?:,)?(?:\s+20\d{2})?\b/i', $message, $matches)) {
            try {
                $dateText = preg_replace('/(st|nd|rd|th)\b/i', '', $matches[0]);
                $date = Carbon::parse($dateText);
                if (!preg_match('/20\d{2}/', $dateText)) {
                    $date->year(now()->year);
                }

                return $date->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    private function parseTimeRange(string $message): array
    {
        $message = preg_replace('/\b(?:12\s*)?noon\b/i', '12 pm', $message);
        preg_match('/\b(?:from|between)?\s*(\d{1,2})(?::(\d{2}))?\s*(am|pm)?\s*(?:to|until|through|and|-)\s*(\d{1,2})(?::(\d{2}))?\s*(am|pm)?\b/i', $message, $range);

        if ($range) {
            $startPeriod = isset($range[3]) && $range[3] !== '' ? strtolower($range[3]) : null;
            $endPeriod = isset($range[6]) && $range[6] !== '' ? strtolower($range[6]) : $startPeriod;

            if (!$startPeriod && !$endPeriod) {
                $startPeriod = 'am';
                $endPeriod = 'am';
            }

            $times = [
                $this->formatTime($range[1], $range[2] ?? '', $startPeriod),
                $this->formatTime($range[4], $range[5] ?? '', $endPeriod),
            ];

            if ($times[0] === null || $times[1] === null || $times[0] >= $times[1]) {
                return [null, null];
            }

            return $times;
        }

        preg_match_all('/\b(\d{1,2})(?::(\d{2}))?\s*(am|pm)\b/i', $message, $matches, PREG_SET_ORDER);
        $times = [];

        foreach ($matches as $match) {
            $time = $this->formatTime($match[1], $match[2], strtolower($match[3]));

            if ($time !== null) {
                $times[] = $time;
            }
        }

        if (count($times) < 2 || $times[0] >= $times[1]) {
            return [null, null];
        }

        return [$times[0], $times[1]];
    }

    private function formatTime(string $hourText, string $minuteText, ?string $period): ?string
    {
        $hour = (int) $hourText;
        $minute = $minuteText !== '' ? (int) $minuteText : 0;

        if ($period === 'pm' && $hour < 12) {
            $hour += 12;
        } elseif ($period === 'am' && $hour === 12) {
            $hour = 0;
        }

        if ($hour > 23 || $minute > 59 || ($period !== null && $hourText > 12)) {
            return null;
        }

        return sprintf('%02d:%02d', $hour, $minute);
    }

    private function hasTimeRangeInput(string $message): bool
    {
        return (bool) preg_match('/\b\d{1,2}(?::\d{2})?\s*(?:am|pm)?\s*(?:to|until|through|-)\s*\d{1,2}(?::\d{2})?\s*(?:am|pm)?\b/i', $message);
    }

    private function hasDateOrTimeRequest(string $message): bool
    {
        return (bool) preg_match('/\b(today|tomorrow|20\d{2}-\d{2}-\d{2}|January|February|March|April|May|June|July|August|September|October|November|December|\d{1,2}(?::\d{2})?\s*(?:am|pm))\b/i', $message);
    }

    private function isReservationStatusQuestion(string $message): bool
    {
        if (preg_match('/\b(cancel|remove|delete)\b/i', $message)) {
            return false;
        }

        return (bool) preg_match('/\b(reservations?|bookings?)\b/i', $message)
            && (bool) preg_match('/\b(my|mine|do i have|what|show|list|status|pending|approved|accepted|declined|rejected)\b/i', $message);
    }

    private function isSupportedMessage(string $message): bool
    {
        return (bool) preg_match('/\b(hello|hi|hey|help|what can you do|website|site|account|login|profile|campus|room|rooms|facility|facilities|available|availability|schedule|reservation|reservations|reserve|reserved|book|booking|bookings|cancel|cancelled|delete|remove|pending|approved|accepted|declined|rejected|status|today|tomorrow|am|pm|noon|yes|yeah|yep|yup|sure|okay|ok|correct|right|confirm|submit|go ahead|do it|no|nope|nah|not now|done|finished|\d{4}-\d{2}-\d{2})\b/i', $message)
            || $this->hasTimeRangeInput($message);
    }

    private function isFacilityName(string $message): bool
    {
        $question = strtolower($message);
        $question = preg_replace('/\b(please|thanks|thank you|i want|i choose|choose|book|reserve|the)\b/', '', $question);
        $question = preg_replace('/[^a-z0-9]/', '', $question);

        if ($question === '') {
            return false;
        }

        return Facility::active()->get()->contains(function (Facility $facility) use ($question) {
            $facilityName = preg_replace('/[^a-z0-9]/', '', strtolower($facility->name));
            $roomNumber = preg_replace('/[^a-z0-9]/', '', strtolower((string) $facility->room_number));

            return ($facilityName !== '' && str_contains($question, $facilityName))
                || ($roomNumber !== '' && str_contains($question, $roomNumber));
        });
    }

    private function normalizeMessage(string $message): string
    {
        $normalized = strtolower(trim($message));
        $normalized = str_replace(['’', '`'], "'", $normalized);
        $normalized = preg_replace("/\bwhat's\b/", 'what is', $normalized);
        $normalized = preg_replace("/\bthat's\b/", 'that is', $normalized);
        $normalized = preg_replace('/\b(reservation request|booking request)\b/', 'reservation', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return $normalized;
    }

    private function isConfirmation(string $message): bool
    {
        return (bool) preg_match('/^(yes|yeah|yep|yup|sure|okay|ok|correct|right|that is right|that works|please do|go ahead|do it|submit|confirm)(?:\b|[!.?])/i', trim($message));
    }

    private function isNegative(string $message): bool
    {
        return (bool) preg_match('/^(no|nope|nah|not now|do not|don\'t|cancel)(?:\b|[!.?])/i', trim($message));
    }

    private function moderateMessage(string $message): array
    {
        $terms = [
            'fuck', 'fucker', 'fucking', 'shit', 'bitch', 'asshole', 'bastard',
            'damn', 'crap', 'dick', 'piss', 'nigger', 'nigga', 'faggot',
            'fag', 'dyke', 'homo', 'tranny', 'chink', 'spic', 'kike', 'gook',
        ];
        $normalized = strtolower($message);
        $normalized = strtr($normalized, [
            '0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a',
            '@' => 'a', '$' => 's', '5' => 's', '7' => 't',
        ]);
        $normalized = preg_replace('/[^a-z]/', '', $normalized);
        $normalized = preg_replace('/(.)\1+/', '$1', $normalized);
        $found = false;

        foreach ($terms as $term) {
            if (str_contains($normalized, $term)) {
                $found = true;
                break;
            }
        }

        if (!$found && preg_match('/(?:hate|kill|hurt|destroy|disgusting|dirty|stupid|gross)(?:all|the|those|these)?(?:gay|lesbian|lgbt|trans)\w*|(?:gay|lesbian|lgbt|trans)\w*(?:hate|kill|hurt|destroy|disgusting|dirty|stupid|gross)|(?:gay|lesbian|lgbt|trans)\w*(?:bad|wrong|inferior|unnatural|perverted|disgusting)/', $normalized)) {
            $found = true;
        }

        if ($found) {
            return ['[Censored]', true];
        }

        return [$message, false];
    }
}
