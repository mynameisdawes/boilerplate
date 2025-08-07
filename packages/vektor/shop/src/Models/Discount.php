<?php

namespace Vektor\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'blurb',
        'amount',
        'type',
        'cta_url',
        'cta_text',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'amount' => 'float',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function discount_codes()
    {
        return $this->hasMany(DiscountCode::class);
    }
}
