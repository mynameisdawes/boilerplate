<?php

namespace Vektor\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Vektor\Shop\CanBeBought;
use Vektor\Shop\Contracts\Buyable;
use Vektor\Shop\Utilities as ShopUtilities;

class Product extends Model implements Buyable
{
    use CanBeBought;
    use HasSlug;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'is_enabled',
        'slug',
        'name',
        'name_label',
        'sku',
        'supplier_sku',
        'price',
        'weight',
        'images',
        'configuration',
        'metadata',
        'sort_order',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that are to be casted.
     *
     * @var array
     */
    protected $casts = [
        'is_enabled' => 'boolean',
        'price' => 'float',
        'images' => 'array',
        'configuration' => 'array',
        'metadata' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'display_price',
        'tax',
    ];

    public function getDisplayPriceAttribute()
    {
        $display_price = $this->attributes['price'];

        if (isset($this->attributes['configuration'])) {
            $configuration = json_decode($this->attributes['configuration'], true);

            if (isset($configuration['tax_percentage'])) {
                $display_price = ShopUtilities::addPercentage($this->attributes['price'], $configuration['tax_percentage']);
            }
        }

        return $display_price;
    }

    public function getTaxAttribute()
    {
        return round($this->getDisplayPriceAttribute() - $this->attributes['price'], 2);
    }

    public function setImagesAttribute($value)
    {
        $images = [];

        if (!empty($value) && is_array($value)) {
            foreach ($value as $array_key => $array_item) {
                if (!is_null($array_item)) {
                    $images[$array_key] = $array_item;
                }
            }
        }

        $this->attributes['images'] = json_encode($images);
    }

    public function getImagesAttribute($value)
    {
        $transformed_value = [];

        if (!empty($value)) {
            $value = json_decode($value, true);
            if (!empty($value) && is_array($value)) {
                foreach ($value as $array_key => $array_item) {
                    if (!is_null($array_item)) {
                        $transformed_value[$array_key] = route('shop.product_images.product_images', ['base_dir' => $array_item]);
                    }
                }
            }
        }

        return $transformed_value;
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

    public function getConfigurationAttribute($value)
    {
        $configuration = json_decode($value, true);

        if (!empty($configuration) && is_array($configuration)) {
            foreach ($configuration as $configuration_key => &$configuration_item) {
                if ('builder_images' == $configuration_key && !empty($configuration_item)) {
                    foreach ($configuration_item as &$builder_image) {
                        $builder_image = route('shop.product_images.product_images', ['base_dir' => $builder_image]);
                    }
                    unset($builder_image);
                }
            }
            unset($configuration_item);
        }

        return $configuration;
    }

    public function setMetadataAttribute($value)
    {
        $metadata = [];

        if (!empty($value) && is_array($value)) {
            foreach ($value as $array_key => $array_item) {
                if (!is_null($array_item)) {
                    $metadata[$array_key] = $array_item;
                }
            }
        }

        $this->attributes['metadata'] = json_encode($metadata);
    }

    /**
     * Get the products for the product.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'parent_id');
    }

    /**
     * Get the product that owns the product.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    /**
     * Get the attributes for the product.
     */
    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
        ;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
