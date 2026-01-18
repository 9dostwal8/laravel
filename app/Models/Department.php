<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'name' => 'json',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
