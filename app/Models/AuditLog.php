<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    // Fields I can assign when logging an admin action
    protected $fillable = [
        'user_id',
        'reservation_id',
        'action',
        'details',
    ];

    // Link to the admin who performed the action
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Link to the reservation that was modified (if applicable)
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
