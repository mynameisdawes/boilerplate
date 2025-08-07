<?php

namespace Vektor\Shop\Services;

use Vektor\Shop\Models\DiscountCode;

class HashService
{
    public static function discount_code($length = 10)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < $length; ++$i) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        $entry = DiscountCode::where('code', $code)->first();
        if ($entry) {
            return self::discount_code($length);
        }

        return $code;
    }
}
