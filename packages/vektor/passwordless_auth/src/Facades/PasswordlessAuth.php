<?php

namespace Vektor\PasswordlessAuth\Facades;

use Illuminate\Support\Facades\Facade;

class PasswordlessAuth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'passwordless_auth';
    }
}
