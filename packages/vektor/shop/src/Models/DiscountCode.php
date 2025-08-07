<?php

namespace Vektor\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'discount_id',
        'code',
        'is_used',
    ];

    protected $casts = [
        'is_used' => 'boolean',
    ];

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }
}
