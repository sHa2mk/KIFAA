<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Casts user attributes into the correct data types.
     *
     * The email verification date is handled as a datetime, and the password
     * is automatically hashed by Laravel when it is assigned.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Builds short initials from the user's name.
     *
     * This is used for profile avatars or navigation UI.
     *
     * @return string
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Gets the selected career interest for the user.
     *
     * The users table stores the selected target role through interest_id.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function interest(): BelongsTo
    {
        return $this->belongsTo(Interest::class);
    }

    /**
     * Gets the user's current skills.
     *
     * The skill_user pivot table connects users and skills through a
     * many-to-many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'skill_user');
    }

    /**
     * Gets the missing skills generated for the user.
     *
     * These records represent the user's current skill gaps and are used by
     * the dashboard and course recommendation flow.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function missingSkills(): HasMany
    {
        return $this->hasMany(MissingSkill::class);
    }

    /**
     * Gets the user's Digital Twin record.
     *
     * Each user should have one active Digital Twin profile containing the
     * latest readiness score.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function digitalTwin(): HasOne
    {
        return $this->hasOne(DigitalTwin::class);
    }

    /**
     * Gets the user's course progress records.
     *
     * These records track completed learning steps connected to skills.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function courseProgress(): HasMany
    {
        return $this->hasMany(CourseProgress::class);
    }
}