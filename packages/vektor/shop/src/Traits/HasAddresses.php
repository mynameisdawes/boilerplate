<?php

namespace Vektor\Shop\Traits;

use Vektor\Shop\Models\UserAddress;

trait HasAddresses
{
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }
}
