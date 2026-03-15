<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'building',
        'room_number',
        'type',
        'capacity',
        'description',
        'amenities',
        'image',
        'is_active',
        'requires_approval',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amenities' => 'array',
        'is_active' => 'boolean',
        'requires_approval' => 'boolean',
    ];

    /**
     * Get the reservations for the facility.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get the type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'classroom' => 'Classroom',
            'laboratory' => 'Laboratory',
            'conference_room' => 'Conference Room',
            'auditorium' => 'Auditorium',
            'sports_hall' => 'Sports Hall',
            'study_room' => 'Study Room',
            'other' => 'Other',
            default => ucfirst($this->type),
        };
    }

    /**
     * Scope to get only active facilities.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if facility is available at a given date and time.
     */
    public function isAvailable($date, $startTime, $endTime, $excludeReservationId = null): bool
    {
        $query = $this->reservations()
            ->where('reservation_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return $query->count() === 0;
    }
}
