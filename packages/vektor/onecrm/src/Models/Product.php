<?php

namespace Vektor\OneCRM\Models;

use Illuminate\Database\Eloquent\Model;
use Vektor\Api\Api;
use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;

class Product extends Model
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
        'accreditations',
        'age_groups',
        'all_stock',
        'brand',
        'cost',
        'cost_usdollar',
        'created_by',
        'credit_value',
        'currency_id',
        'custom_logo',
        'custom_logo_cords',
        'customisable',
        'date_available',
        'date_entered',
        'date_modified',
        'deleted',
        'description',
        'description_long',
        'description_portal',
        'eshop',
        'exchange_rate',
        'gender',
        'image_filename',
        'image_thumb',
        'image_url',
        'img_url',
        'img_url_personalised',
        'is_available',
        'launch_date',
        'list_price',
        'list_usdollar',
        'manufacturer_id',
        'manufacturers_part_no',
        'model_id',
        'modified_user_id',
        'multi_select',
        'name',
        'parent_product_id',
        'ppf_perc',
        'price',
        'pricing_formula',
        'print_side_1_copy_to',
        'print_side_1_dimensions',
        'print_side_1_enabled',
        'print_side_1_image',
        'print_side_1_name',
        'print_side_2_copy_to',
        'print_side_2_dimensions',
        'print_side_2_enabled',
        'print_side_2_image',
        'print_side_2_name',
        'product_category_id',
        'product_class',
        'product_type_id',
        'product_types',
        'production_process',
        'purchase_from',
        'purchase_name',
        'purchase_price',
        'purchase_usdollar',
        'qty_per_order',
        'qty_per_order_grouping',
        'shipping',
        'size_guide',
        'size_guide_note',
        'sort_order',
        'supplier_id',
        'support_cost',
        'support_cost_usdollar',
        'support_list_price',
        'support_list_usdollar',
        'support_ppf_perc',
        'support_price_formula',
        'support_selling_price',
        'support_selling_usdollar',
        'tax_code_id',
        'thumbnail_url',
        'track_inventory',
        'unpurchasable_skus',
        'url',
        'vendor_part_no',
        'weight_1',
        'weight_2',
        'weight_kg',
        'woo_id',
        'woo_locked',
        'new_record',
    ];

    protected $casts = [
        'new_record' => 'boolean',
        'customisable' => 'boolean',
        'multi_select' => 'boolean',
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
        'currency_id' => '-99',
        'exchange_rate' => '1',
        'cost' => '0',
        'cost_usdollar' => '0',
        'list_price' => '0',
        'list_usdollar' => '0',
        'purchase_price' => '0',
        'purchase_usdollar' => '0',
        'support_cost' => '0',
        'support_cost_usdollar' => '0',
        'support_list_price' => '0',
        'support_list_usdollar' => '0',
        'support_selling_price' => '0',
        'support_selling_usdollar' => '0',
        'pricing_formula' => 'Fixed Price',
        'support_price_formula' => 'Fixed Price',
        'is_available' => 'yes',
        'ppf_perc' => '0',
        'support_ppf_perc' => '0',
        'track_inventory' => 'untracked',
        'all_stock' => '0',
        'modified_user_id' => '1',
        'created_by' => '1',
        'eshop' => '1',
        'deleted' => '0',
        'woo_locked' => '0',
        'customisable' => false,
        'multi_select' => false,
        'product_class' => 'simple',
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
        return !empty($value) ? $value : date('Y-m-d H:i:s');
    }

    /**
     * Get the model's date_modified.
     */
    public function getDateModifiedAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : date('Y-m-d H:i:s');
    }

    public function index($data = [])
    {
        $_response = $this->crm_model->index('products', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            return $response['data']['records'];
        }

        return [];
    }

    public function show($id, $data = [])
    {
        $_response = $this->crm_model->show('products', $id, $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }

    public function index_related_productattributes($id, $data = [
        'fields' => [
            'img_url',
            'sizes',
            'value',
            'hex_code',
            'required',
            'price',
        ],
    ])
    {
        $_response = $this->crm_model->index_related('products', $id, 'productattributes', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            return $response['data']['records'];
        }

        return [];
    }

    public function updateCrm($id, $data = [])
    {
        $data = empty($data) ? $this->toArray() : $data;
        $_response = $this->crm_model->update('products', $id, $data);
        $response = Api::transformResponse($_response);

        return $response['success'];
    }

    public function persist()
    {
        $data = $this->toArray();
        unset($data['new_record']);

        $_response = $this->crm_model->create('products', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->new_record = true;
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }

    public function addStock($id, $qty)
    {
        $vektor_id = '5fe4bc0f-bab2-09d4-1604-49a8045f09ca';
        $_response = $this->crm_model->create_related('products', $id, 'warehouses', [
            $vektor_id => [
                'in_stock' => $qty,
            ],
        ]);

        return Api::transformResponse($_response);
    }
}
