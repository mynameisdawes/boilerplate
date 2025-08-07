<?php

namespace Vektor\OneCRM\Models;

use Illuminate\Database\Eloquent\Model;
use Vektor\Api\Api;
use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;
use Vektor\Utilities\Formatter;

class Quote extends Model
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
        'assigned_user_id',
        'name',
        'description',
        'can_edit',
        'enforce_minimum_quantities',
        'low_res_artwork_provided',
        'shipping_account_id',
        'shipping_contact_id',
        'shipping_address_street',
        'shipping_address_city',
        'shipping_address_state',
        'shipping_address_statecode',
        'shipping_address_postalcode',
        'shipping_address_country',
        'shipping_address_countrycode',
        'billing_account_id',
        'billing_contact_id',
        'billing_address_street',
        'billing_address_city',
        'billing_address_state',
        'billing_address_statecode',
        'billing_address_postalcode',
        'billing_address_country',
        'billing_address_countrycode',
        'amount',
        'amount_usdollar',
        'subtotal',
        'subtotal_usd',
        'pretax',
        'pretax_usd',
        'terms',

        'quote_stage',
        'quote_number',
        'prefix',
        'date_entered',
        'date_modified',
        'shipping_provider_id',
        'valid_until',

        'lines',
        'customisations',
        'new_record',
    ];

    protected $casts = [
        'lines' => 'array',
        'new_record' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'amount_usdollar',
        'subtotal_usd',
        'pretax_usd',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'new_record' => false,
        'terms' => 'Due on Receipt',
        'quote_stage' => 'Awaiting Approval',
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
     * Set the model's shipping_address_street.
     */
    public function setShippingAddressStreetAttribute(?array $value): void
    {
        $this->attributes['shipping_address_street'] = !empty($value) ? Formatter::arrayToString($value, "\n") : $value;
    }

    /**
     * Set the model's shipping_address_city.
     */
    public function setShippingAddressCityAttribute(?string $value): void
    {
        $this->attributes['shipping_address_city'] = !empty($value) ? Formatter::name($value) : $value;
    }

    /**
     * Set the model's shipping_address_state.
     */
    public function setShippingAddressStateAttribute(?string $value): void
    {
        $this->attributes['shipping_address_state'] = !empty($value) ? Formatter::name($value) : $value;
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
     * Set the model's billing_address_state.
     */
    public function setBillingAddressStateAttribute(?string $value): void
    {
        $this->attributes['billing_address_state'] = !empty($value) ? Formatter::name($value) : $value;
    }

    /**
     * Get the model's amount_usdollar.
     */
    public function getAmountUsdollarAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : (!empty($this->attributes['amount']) ? $this->attributes['amount'] : null);
    }

    /**
     * Get the model's subtotal_usd.
     */
    public function getSubtotalUsdAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : (!empty($this->attributes['subtotal']) ? $this->attributes['subtotal'] : null);
    }

    /**
     * Get the model's pretax_usd.
     */
    public function getPretaxUsdAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : (!empty($this->attributes['pretax']) ? $this->attributes['pretax'] : null);
    }

    public function index($data = [])
    {
        $data['fields'] = $this->fillable;
        $_response = $this->crm_model->index('quotes', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            return $response['data']['records'];
        }

        return [];
    }

    public function index_related($id, $related, $data = [])
    {
        $_response = $this->crm_model->index_related('quotes', $id, $related, $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            return $response['data']['records'];
        }

        return [];
    }

    public function tally($id, $data = [])
    {
        $_response = $this->crm_model->tally('quotes', $id, $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->id = $response['data']['record']['id'];

            return $response['data'];
        }

        return null;
    }

    public function show($id, $data = [])
    {
        $_response = $this->crm_model->show('quotes', $id, $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }

    public function updateCrm($id, $data = [])
    {
        $_response = $this->crm_model->update('quotes', $id, $data);
        $response = Api::transformResponse($_response);

        return $response['success'];
    }

    public function persist()
    {
        $data = $this->toArray();
        unset($data['new_record']);

        $_response = $this->crm_model->create('quotes', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->new_record = true;
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }
}
