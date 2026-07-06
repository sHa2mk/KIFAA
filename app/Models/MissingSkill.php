<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissingSkill extends Model
{
    protected $fillable = [
        'user_id',
        'skill_id',
        'source',
        'detected_at',
        'priority',
        'priority_reason',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
    ];

    /**
     * Gets the user who owns this missing skill.
     *
     * Each missing skill record belongs to one user and represents a gap in
     * that user's career profile.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Gets the skill details for this missing skill record.
     *
     * The related skill record stores the readable skill name used in the
     * dashboard and course recommendation pages.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}