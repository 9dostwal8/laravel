<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectAmenity extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function amenity(): BelongsTo
    {
        return $this->belongsTo(Amenity::class);
    }
}
