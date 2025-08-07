<?php

namespace Vektor\OneCRM\Models;

use Illuminate\Database\Eloquent\Model;
use Vektor\Api\Api;
use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;

class ProductCategory extends Model
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
        'name',
        'description',
        'date_entered',
        'date_modified',
        'deleted',
        'created_by',
        'eshop',
        'parent_id',
        'new_record',
    ];

    protected $casts = [
        'new_record' => 'boolean',
    ];

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
        'new_record' => false,
        'deleted' => '0',
        'created_by' => '1',
        'eshop' => '1',
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
        $_response = $this->crm_model->index('product_categories', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            return $response['data']['records'];
        }

        return [];
    }

    public function show($id, $data = [])
    {
        $_response = $this->crm_model->show('product_categories', $id, $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }

    public function index_related_products($id, $data = [
        'fields' => ['product_category_id', 'image_url'],
    ])
    {
        $_response = $this->crm_model->index_related('product_categories', $id, 'products', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            return $response['data']['records'];
        }

        return [];
    }
}
