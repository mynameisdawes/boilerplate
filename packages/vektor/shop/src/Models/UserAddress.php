<?php

namespace Vektor\Shop\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'address_line_1',
        'address_line_2',
        'city',
        'county',
        'postcode',
        'country',
        'is_default_billing',
        'is_default_shipping',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_default_billing' => 'boolean',
        'is_default_shipping' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
