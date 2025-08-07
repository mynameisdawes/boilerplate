<?php

namespace Vektor\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = [
        'name',
        'name_label',
        'configuration',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'configuration' => 'array',
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

        $this->attributes['configuration'] = json_encode($configuration);
    }

    /**
     * Get the product attributes for the attribute.
     */
    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    // Define the relationship with Product through ProductAttribute
    public function products()
    {
        return $this->hasManyThrough(Product::class, ProductAttribute::class, 'attribute_id', 'id', 'id', 'product_id');
    }
}
