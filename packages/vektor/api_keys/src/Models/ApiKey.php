<?php

namespace Vektor\ApiKeys\Models;

use Illuminate\Database\Eloquent\Model;
use Vektor\ApiKeys\Services\HashService;

class ApiKey extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'name',
        'is_active',
    ];

    /**
     * The attributes that are to be casted.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (ApiKey $api_key) {
            try {
                $api_key->key = HashService::api_key();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        });
    }
}
