<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LicensingAuthority extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'name' => 'array',
    ];

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)
            ->withTimestamps();
    }
}
