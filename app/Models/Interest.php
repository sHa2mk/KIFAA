<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Interest extends Model
{
    protected $fillable = [
        'title',
    ];

    /**
     * Gets the users linked to this career interest.
     *
     * A single career interest can be reused by many users who share the same
     * target role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}