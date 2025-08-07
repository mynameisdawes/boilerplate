<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Vektor\PasswordlessAuth\Traits\HasPasswordlessAuth;

// class User extends Authenticatable implements MustVerifyEmail
class User extends Authenticatable
{
    use Notifiable;
    use HasPasswordlessAuth;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'configuration',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'configuration' => 'array',
        'is_admin' => 'boolean',
    ];

    public function setConfigurationAttribute($value)
    {
        $configuration = [];

        if (!empty($value) && is_array($value)) {
            foreach ($value as $array_key => $array_item) {
                if (!is_null($array_item)) {
                    $configuration[$array_key] = $array_item;
                }
            }
        }

        if (!empty($configuration)) {
            ksort($configuration);
            $this->attributes['configuration'] = json_encode($configuration);
        } else {
            $this->attributes['configuration'] = null;
        }
    }
}
