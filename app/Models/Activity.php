<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\Models\Activity as BaseActivity;

class Activity extends BaseActivity
{
    public function command(): BelongsTo
    {
        return $this->belongsTo(Command::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo('subject');
    }

    public function parentSubject(): MorphTo
    {
        return $this->morphTo( 'parent_subject');
    }
}
