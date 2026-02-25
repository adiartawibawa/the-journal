<?php

namespace App\Traits;

use App\Models\Scopes\UserDataScope;

trait HasUserScope
{
    public static function bootHasUserScope(): void
    {
        static::addGlobalScope(new UserDataScope);
    }
}
