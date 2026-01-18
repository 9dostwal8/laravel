<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityArea extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'name' => 'json',
    ];

    public function activityTypes(): HasMany
    {
        return $this->hasMany(ActivityType::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_variants', 'activity_area_id', 'project_id');
    }

    public function amenity()
    {
        return $this->belongsTo(Amenity::class, 'id', 'activity_area_id');
    }

}
