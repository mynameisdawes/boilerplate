<?php

namespace Vektor\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    protected $fillable = [
        'product_id',
        'attribute_id',
        'value',
        'value_label',
        'configuration',
        'sort_order',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'product_id',
        'attribute_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'configuration' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'name',
        'name_label',
    ];

    public function getNameAttribute()
    {
        if ($this->attribute) {
            return $this->attribute->name;
        }

        return null;
    }

    public function getNameLabelAttribute()
    {
        if ($this->attribute) {
            return $this->attribute->name_label;
        }

        return null;
    }

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
     * Get the product for the product attribute.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the attribute for the product attribute.
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
