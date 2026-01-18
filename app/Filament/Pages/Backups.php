<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use ShuvroRoy\FilamentSpatieLaravelBackup\Pages\Backups as BaseBackups;

class Backups extends BaseBackups
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?int $navigationSort = 10;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return trans('resources.backups');
    }

    /**
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return trans('resources.backups');
    }

    /**
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return trans('navigation.settings');
    }
}
