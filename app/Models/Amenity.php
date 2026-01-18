<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Amenity extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'name' => 'array',
    ];

    public function activityArea(): BelongsTo
    {
        return $this->belongsTo(ActivityArea::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_amenities');
    }
}
