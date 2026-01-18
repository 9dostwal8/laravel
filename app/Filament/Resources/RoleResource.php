<?php

namespace App\Filament\Resources;

use BezhanSalleh\FilamentShield\Resources\RoleResource as BaseRoleResourceAlias;

class RoleResource extends BaseRoleResourceAlias
{
    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('navigation.settings');
    }
}
