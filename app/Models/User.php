<?php

namespace App\Models;

use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Mockery\Generator\StringManipulation\Pass\Pass;
use Spatie\Permission\Traits\HasRoles;
use Stephenjude\FilamentTwoFactorAuthentication\TwoFactorAuthenticatable;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Concerns\InteractsWithPasskeys;

class User extends Authenticatable implements FilamentUser, HasPasskeys
{
    use HasApiTokens;
    use HasFactory;
    use HasPanelShield;
    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable, InteractsWithPasskeys;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
        'activated',
        'can_see',
        'can_edit',
        'can_insert_progress',
        'activity_area_limit',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'activity_area_limit' => 'json',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->activated;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }
}
