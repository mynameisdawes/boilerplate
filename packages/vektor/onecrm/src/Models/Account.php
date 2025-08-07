<?php

namespace Vektor\OneCRM\Models;

use Illuminate\Database\Eloquent\Model;
use Vektor\Api\Api;
use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;
use Vektor\Utilities\Formatter;

class Account extends Model
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
        'phone_office',
        'email1',
        'shipping_address_street',
        'shipping_address_city',
        'shipping_address_state',
        'shipping_address_statecode',
        'shipping_address_postalcode',
        'shipping_address_countrycode',
        'shipping_address_country',
        'billing_address_street',
        'billing_address_city',
        'billing_address_state',
        'billing_address_statecode',
        'billing_address_postalcode',
        'billing_address_countrycode',
        'billing_address_country',
        'account_type',
        'account_status',
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
        'account_type' => 'Customer',
        'account_status' => 'Active',
        'new_record' => false,
    ];

    /**
     * The attributes that are not to be used to update.
     *
     * @var array
     */
    protected $excluded_update_attributes = [
        'name',
        'phone_office',
        'email1',
        'account_type',
        'account_status',
    ];

    public function __construct()
    {
        $this->crm = new OneCRM();
        $this->crm_model = new OneCRMModel();

        return $this;
    }

    /**
     * Set the model's email1.
     */
    public function setEmail1Attribute(?string $value): void
    {
        $this->attributes['email1'] = !empty($value) ? Formatter::email($value) : $value;
    }

    /**
     * Set the model's shipping_address_street.
     */
    public function setShippingAddressStreetAttribute(?array $value): void
    {
        $this->attributes['shipping_address_street'] = !empty($value) ? Formatter::arrayToString($value, "\n") : null;
    }

    /**
     * Set the model's shipping_address_city.
     */
    public function setShippingAddressCityAttribute(?string $value): void
    {
        $this->attributes['shipping_address_city'] = !empty($value) ? Formatter::name($value) : $value;
    }

    /**
     * Set the model's shipping_address_postalcode.
     */
    public function setShippingAddressPostalcodeAttribute(?string $value): void
    {
        $this->attributes['shipping_address_postalcode'] = !empty($value) ? Formatter::postcode($value) : $value;
    }

    /**
     * Set the model's billing_address_street.
     */
    public function setBillingAddressStreetAttribute(?array $value): void
    {
        $this->attributes['billing_address_street'] = !empty($value) ? Formatter::arrayToString($value, "\n") : $value;
    }

    /**
     * Set the model's billing_address_city.
     */
    public function setBillingAddressCityAttribute(?string $value): void
    {
        $this->attributes['billing_address_city'] = !empty($value) ? Formatter::name($value) : $value;
    }

    /**
     * Set the model's billing_address_postalcode.
     */
    public function setBillingAddressPostalcodeAttribute(?string $value): void
    {
        $this->attributes['billing_address_postalcode'] = !empty($value) ? Formatter::postcode($value) : $value;
    }

    public function show($id, $data = [])
    {
        $_response = $this->crm_model->show('accounts', $id, $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }

    public function persist()
    {
        $data = $this->toArray();
        unset($data['new_record']);

        $_existing_record = null;

        if (isset($data['id'])) {
            $_existing_response = $this->crm_model->show('accounts', $data['id']);
            $existing_response = Api::transformResponse($_existing_response);

            if ($existing_response['success']) {
                if (
                    isset($existing_response['data']['record'])
                    && !empty($existing_response['data']['record'])
                ) {
                    $_existing_record = $existing_response['data']['record'];
                }
            }
        } else {
            $existing_data = [
                'filters' => [
                    'any_email' => $data['email1'],
                ],
            ];

            $_existing_response = $this->crm_model->index('accounts', $existing_data);
            $existing_response = Api::transformResponse($_existing_response);

            if ($existing_response['success']) {
                if (
                    isset($existing_response['data']['records'])
                    && !empty($existing_response['data']['records'])
                ) {
                    $_existing_record = current($existing_response['data']['records']);
                }
            }
        }

        if ($_existing_record) {
            if (!empty($this->excluded_update_attributes)) {
                foreach ($this->excluded_update_attributes as $excluded_update_attribute) {
                    unset($data[$excluded_update_attribute]);
                }
            }

            $this->crm_model->update('accounts', $_existing_record['id'], $data);
            $existing_record = $this->crm_model->show('accounts', $_existing_record['id']);

            $this->id = $existing_record['data']['record']['id'];

            return $existing_record['data']['record'];
        }

        $_response = $this->crm_model->create('accounts', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->new_record = true;
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }
}
