<?php

namespace App\Filament\Auth;

use Filament\Pages\Auth\Login as BaseAuth;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseAuth
{
    public function getHeading(): string|Htmlable
    {
        return '';
    }
}
