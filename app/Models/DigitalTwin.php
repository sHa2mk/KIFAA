<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalTwin extends Model
{
    protected $fillable = [
        'user_id',
        'readiness_score',
        'status',
        'last_updated_at',
    ];

    protected $casts = [
        'last_updated_at' => 'datetime',
    ];

    /**
     * Gets the user connected to this Digital Twin.
     *
     * The Digital Twin score is calculated from the user's current skills and
     * missing skills.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}