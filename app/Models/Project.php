<?php

namespace App\Models;

use App\Enums\InvestmentTypeEnum;
use App\Enums\ProjectStatus;
use App\Models\InvestorProject;
use App\Traits\LangSwitcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Project extends Model
{
    use HasFactory, LogsActivity, LangSwitcher;

    protected $guarded = [
        'performance_rate'
    ];

    protected $casts = [
        'status' => ProjectStatus::class,
        'investment_type' => InvestmentTypeEnum::class,
        'land_number' => 'array',
        'project_name' => 'json',
        'company_name' => 'json',

    ];

    protected $appends = [
        'location',
        'performance_rate'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('organization', function (Builder $query) {
            /** @var User $user */
            $user = auth()->user();

            if ($user && ! $user->hasRole('super_admin') && !$user->can_see) {
                $query->where('organization_id', $user->organization_id);
            }
        });

        static::creating(function (Project $project) {
            if (auth()->check()) {
                $project->organization_id = auth()->user()->organization_id;
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function projectVariants(): HasMany
    {
        return $this->hasMany(ProjectVariant::class);
    }

    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class);
    }

    public function letters(): HasMany
    {
        return $this->hasMany(Letter::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    public function lastProgress(): HasOne
    {
        return $this->hasOne(Progress::class)
            ->orderByDesc('progress_percentage')
            ->latest();
    }

    public function commands(): HasMany
    {
        return $this->hasMany(Command::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function investors(): BelongsToMany
    {
        return $this->belongsToMany(Investor::class, 'investor_project', 'project_id', 'investor_id')
            ->using(InvestorProject::class)
            ->withPivot('project_percentage')
            ->withTimestamps();
    }

   public function amenities(): HasMany
    {
        return $this->hasMany(ProjectAmenity::class);
    }

    public function scopeIgnoreOrganizationForSuperAdmin(Builder $query)
    {
        $query->when(
            auth()->user()->hasRole('super-admin'),
            fn (Builder $query) => $query->withoutGlobalScope('organization')
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty();
    }

    public function getLocationAttribute(): array
    {
        return [
            "lat" => (float)$this->lat,
            "lng" => (float)$this->lng,
        ];
    }

    public function setLocationAttribute(?array $location): void
    {
        if (is_array($location))
        {
            $this->attributes['lat'] = $location['lat'];
            $this->attributes['lng'] = $location['lng'];
            unset($this->attributes['location']);
        }
    }

    public static function getLatLngAttributes(): array
    {
        return [
            'lat' => 'lat',
            'lng' => 'lng',
        ];
    }

    public static function getComputedLocation(): string
    {
        return 'location';
    }

    public function getPerformanceRateAttribute()
    {
        $progress_percentage = 0;

        foreach ($this->progresses as $progress) {
            $progress_percentage += $progress->progress_percentage;
        }

        return "$progress_percentage%";
    }

    public function countries(): HasMany
    {
        return $this->hasMany(ProjectCountry::class);
    }

    public function originalCountries(): BelongsToMany {
        return $this->belongsToMany(Country::class, 'project_countries', 'project_id', 'country_id');
    }
}
