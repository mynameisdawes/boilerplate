<?php

namespace Vektor\OneCRM\Models;

use Illuminate\Database\Eloquent\Model;
use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;

abstract class AbstractLine extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    public $crm;

    public $crm_model;

    protected $casts = [
        'new_record' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'ext_quantity',
        'cost_price_usd',
        'list_price_usd',
        'unit_price_usd',
        'std_unit_price_usd',
        'ext_price_usd',
        'net_price_usd',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'related_id' => null,
        'related_type' => null,
        'new_record' => false,
    ];

    /**
     * The attributes that are not to be used to update.
     *
     * @var array
     */
    protected $excluded_update_attributes = [
    ];

    public function __construct()
    {
        $this->crm = new OneCRM();
        $this->crm_model = new OneCRMModel();

        return $this;
    }

    /**
     * Get the model's ext_quantity.
     */
    public function getExtQuantityAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : (!empty($this->attributes['quantity']) ? $this->attributes['quantity'] : null);
    }

    /**
     * Get the model's cost_price_usd.
     */
    public function getCostPriceUsdAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : (!empty($this->attributes['cost_price']) ? $this->attributes['cost_price'] : null);
    }

    /**
     * Get the model's list_price_usd.
     */
    public function getListPriceUsdAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : (!empty($this->attributes['list_price']) ? $this->attributes['list_price'] : null);
    }

    /**
     * Get the model's unit_price_usd.
     */
    public function getUnitPriceUsdAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : (!empty($this->attributes['unit_price']) ? $this->attributes['unit_price'] : null);
    }

    /**
     * Get the model's std_unit_price_usd.
     */
    public function getStdUnitPriceUsdAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : (!empty($this->attributes['std_unit_price']) ? $this->attributes['std_unit_price'] : null);
    }

    /**
     * Get the model's ext_price_usd.
     */
    public function getExtPriceUsdAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : (!empty($this->attributes['ext_price']) ? $this->attributes['ext_price'] : null);
    }

    /**
     * Get the model's net_price_usd.
     */
    public function getNetPriceUsdAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : (!empty($this->attributes['net_price']) ? $this->attributes['net_price'] : null);
    }

    abstract public function persist();
}
