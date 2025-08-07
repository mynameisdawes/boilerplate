<?php

namespace Vektor\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shipping_method_id',
        'code',
        'price',
        'configuration',
        'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'configuration' => 'array',
        'is_active' => 'boolean',
    ];

    public function setConfigurationAttribute($value)
    {
        $array = [];

        if (!empty($value) && is_array($value)) {
            foreach ($value as $array_key => $array_item) {
                if (!is_null($array_item)) {
                    $array[$array_key] = $array_item;
                }
            }
        }

        if (!empty($array)) {
            $this->attributes['configuration'] = json_encode($array);
        } else {
            $this->attributes['configuration'] = null;
        }
    }

    public function method()
    {
        return $this->belongsTo(ShippingMethod::class);
    }
}
