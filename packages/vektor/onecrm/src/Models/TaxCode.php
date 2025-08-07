<?php

namespace Vektor\OneCRM\Models;

use Illuminate\Database\Eloquent\Model;
use Vektor\Api\Api;
use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;

class TaxCode extends Model
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

    public $_tax_codes = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
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

    public function get($name = null)
    {
        if (empty($this->_tax_codes)) {
            $this->index();
        }
        if (!empty($this->_tax_codes)) {
            if ($name) {
                return $this->_tax_codes[$name];
            }

            return $this->_tax_codes['VAT 20%'];
        }

        return null;
    }

    public function index()
    {
        $_response = $this->crm_model->index('tax_codes');
        $response = Api::transformResponse($_response);

        if ($response['success'] && !empty($response['data']['records'])) {
            foreach ($response['data']['records'] as $tax_code) {
                $this->_tax_codes[$tax_code['id']] = $tax_code['name'];
                $this->_tax_codes[$tax_code['name']] = $tax_code['id'];
            }
        }

        return $this->_tax_codes;
    }
}
