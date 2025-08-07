<?php

namespace Vektor\OneCRM\Models;

use Illuminate\Database\Eloquent\Model;
use Vektor\Api\Api;
use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;

class ProductAttribute extends Model
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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'product_id',
        'name',
        'date_entered',
        'date_modified',
        'value',
        'price',
        'price_usdollar',
        'deleted',
        'created_by',
        'currency_id',
        'exchange_rate',
        'woo_id',
        'woo_locked',
        'img_url',
        'variations',
        'hex_code',
        'sizes',
        'required',
        'sort_order',
    ];

    protected $casts = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'date_entered',
        'date_modified',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'price' => null,
        'price_usd' => null,
        'currency_id' => '-99',
        'exchange_rate' => '1',
        'created_by' => '1',
        'deleted' => '0',
        'woo_locked' => '0',
        'customisable' => false,
        'multi_select' => false,
        'product_class' => 'simple',
        'required' => 1,
        'sort_order' => 0,
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
     * Get the model's date_entered.
     */
    public function getDateEnteredAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : date('Y-m-d');
    }

    /**
     * Get the model's date_modified.
     */
    public function getDateModifiedAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : date('Y-m-d');
    }

    public function index($data = [])
    {
        $_response = $this->crm_model->index('product_attributes', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            return $response['data']['records'];
        }

        return [];
    }

    public function show($id, $data = [])
    {
        $_response = $this->crm_model->show('product_attributes', $id, $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }

    public function updateCrm($id, $data = [])
    {
        $data = $data ?? $this->toArray();
        $_response = $this->crm_model->update('product_attributes', $id, $data);
        $response = Api::transformResponse($_response);

        return $response['success'];
    }

    public function persist()
    {
        $data = $this->toArray();
        unset($data['new_record']);

        $_response = $this->crm_model->create('product_attributes', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->new_record = true;
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }
}
