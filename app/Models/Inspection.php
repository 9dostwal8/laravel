<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Inspection extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::addGlobalScope('organization', function (Builder $query) {
            /** @var User $user */
            $user = auth()->user();

            if ($user && ! $user->hasRole('super_admin')) {
                $query->where('organization_id', $user->organization_id);
            }
        });

        static::creating(function (Inspection $inspection) {
            if (auth()->check()) {
                $inspection->organization_id = auth()->user()->organization_id;
            }
        });
    }

    public function inspectors(): BelongsToMany
    {
        return $this->belongsToMany(Inspector::class)
            ->withTimestamps();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
