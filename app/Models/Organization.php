<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'name' => 'json',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function investors(): HasMany
    {
        return $this->hasMany(Investor::class);
    }

    public function letters(): HasMany
    {
        return $this->hasMany(Investor::class);
    }

    public function licensingAuthorities(): BelongsToMany
    {
        return $this->belongsToMany(LicensingAuthority::class)
            ->withTimestamps();
    }
}
