<?php

namespace App\Models;

use App\Enums\LetterRecipientTypeEnum;
use App\Enums\LetterTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Letter extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'letter_type' => LetterTypeEnum::class,
        'recipient_type' => LetterRecipientTypeEnum::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
