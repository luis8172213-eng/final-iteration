<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'facility_id',
        'reservation_date',
        'start_time',
        'end_time',
        'purpose',
        'notes',
        'status',
        'admin_remarks',
        'attendees_count',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'reservation_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Get the user that owns the reservation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the facility for the reservation.
     */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Get the status label with color class.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'pending' => ['label' => 'Pending', 'class' => 'bg-yellow-100 text-yellow-800'],
            'approved' => ['label' => 'Approved', 'class' => 'bg-green-100 text-green-800'],
            'rejected' => ['label' => 'Rejected', 'class' => 'bg-red-100 text-red-800'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-gray-100 text-gray-800'],
            'completed' => ['label' => 'Completed', 'class' => 'bg-blue-100 text-blue-800'],
            default => ['label' => ucfirst($this->status), 'class' => 'bg-gray-100 text-gray-800'],
        };
    }

    /**
     * Scope to get upcoming reservations.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('reservation_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('reservation_date')
            ->orderBy('start_time');
    }

    /**
     * Scope to get past/completed reservations.
     */
    public function scopeCompleted($query)
    {
        return $query->where(function ($q) {
            $q->where('reservation_date', '<', now()->toDateString())
                ->orWhere('status', 'completed');
        });
    }

    /**
     * Check if reservation can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'approved']) 
            && $this->reservation_date >= now()->toDateString();
    }
}
