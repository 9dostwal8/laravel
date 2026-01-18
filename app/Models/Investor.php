<?php

namespace App\Models;

use App\Enums\GenderEnum;
use App\Models\InvestorProject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Investor extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'gender' => GenderEnum::class,
        'name' => 'json',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('organization', function (Builder $query) {
            /** @var User $user */
            $user = auth()->user();

//            if ($user && ! $user->hasRole('super_admin') && !$user->can_see) {
//                $query->where('organization_id', $user->organization_id);
//            }
        });

        static::creating(function (Investor $investor) {
            if (auth()->check()) {
                $investor->organization()->associate(auth()->user()->organization_id);
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'investor_project', 'investor_id', 'project_id')
            ->using(InvestorProject::class)
            ->withPivot('project_percentage')
            ->withTimestamps();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(InvestorDocuments::class);
    }

    public function representatives()
    {
        return $this->hasMany(InvestorsRepresentative::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
