<?php

namespace App\Traits;

use Illuminate\Support\Arr;

trait LangSwitcher
{
    public static function getTranslation(?array $translations): ?string
    {
        if (empty($translations)) {
            return '';
        }

        $translations = Arr::whereNotNull($translations);

        return Arr::has($translations, app()->getLocale()) ?
            Arr::get($translations, app()->getLocale()) :
            Arr::first($translations);
    }

    public static function getModelName(): string
    {
        return 'name->'.app()->getLocale();
    }

    public static function getModelNameDotPoint(): string
    {
        return 'name.'.app()->getLocale();
    }

    public static function getDirection(): string
    {
        return in_array(app()->getLocale(), ['ckb', 'ar']) ? 'rtl' : 'ltr';
    }

    public static function isRtl(): bool
    {
        return self::getDirection() === 'rtl';
    }
}
