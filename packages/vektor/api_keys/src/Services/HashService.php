<?php

namespace Vektor\ApiKeys\Services;

use Vektor\ApiKeys\Models\ApiKey;

class HashService
{
    public static function api_key($length = null)
    {
        if (null === $length) {
            $length = config('api_keys.key_length');
        }

        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $key = '';
        for ($i = 0; $i < $length; ++$i) {
            $key .= $characters[rand(0, strlen($characters) - 1)];
        }
        $entry = ApiKey::where('key', $key)->first();
        if ($entry) {
            return self::api_key($length);
        }

        return $key;
    }
}
