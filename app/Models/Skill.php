<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Gets the users who have this skill.
     *
     * The skill_user pivot table connects users and skills through a
     * many-to-many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'skill_user');
    }

    /**
     * Gets the course progress records related to this skill.
     *
     * A skill can be linked to many completed or tracked course progress
     * records.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function courseProgress(): HasMany
    {
        return $this->hasMany(CourseProgress::class);
    }
}