<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ActivityType extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'name' => 'json',
    ];

    public function activityArea(): BelongsTo
    {
        return $this->belongsTo(ActivityArea::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_variants', 'activity_type_id', 'project_id');
    }
}
