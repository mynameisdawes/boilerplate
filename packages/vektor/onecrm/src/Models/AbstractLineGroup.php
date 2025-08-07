<?php

namespace Vektor\OneCRM\Models;

use Illuminate\Database\Eloquent\Model;
use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;

abstract class AbstractLineGroup extends Model
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
        'cost_usd',
        'subtotal_usd',
        'total_usd',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
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
     * Get the model's cost_usd.
     */
    public function getCostUsdAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : (!empty($this->attributes['cost']) ? $this->attributes['cost'] : null);
    }

    /**
     * Get the model's subtotal_usd.
     */
    public function getSubtotalUsdAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : (!empty($this->attributes['subtotal']) ? $this->attributes['subtotal'] : null);
    }

    /**
     * Get the model's total_usd.
     */
    public function getTotalUsdAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : (!empty($this->attributes['total']) ? $this->attributes['total'] : null);
    }

    abstract public function persist();
}
