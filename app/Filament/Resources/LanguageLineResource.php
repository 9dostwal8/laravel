<?php

namespace App\Filament\Resources;

use Kenepa\TranslationManager\Resources\LanguageLineResource as BaseLanguageLineResourceAlias;

class LanguageLineResource extends BaseLanguageLineResourceAlias
{
    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('navigation.settings');
    }
}
