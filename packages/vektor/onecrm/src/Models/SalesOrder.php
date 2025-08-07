<?php

namespace Vektor\OneCRM\Models;

use Illuminate\Database\Eloquent\Model;
use Vektor\Api\Api;
use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;
use Vektor\Utilities\Formatter;

class SalesOrder extends Model
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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'due_date',
        'delivery_date',
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

        'so_stage',
        'so_number',
        'prefix',
        'date_entered',
        'date_modified',
        'shipping_provider_id',

        'lines',
        'customisations',
        'new_record',

        'related_quote_id',
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
        'due_date',
        'delivery_date',
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
    ];

    /**
     * The attributes that are not to be used to update.
     *
     * @var array
     */
    protected $excluded_update_attributes = [
    ];
    private $crm;
    private $crm_model;

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
     * Get the model's due_date.
     */
    public function getDueDateAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : date('Y-m-d');
    }

    /**
     * Get the model's delivery_date.
     */
    public function getDeliveryDateAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : date('Y-m-d');
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
        $_response = $this->crm_model->index('sales_orders', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            return $response['data']['records'];
        }

        return [];
    }

    public function index_related($id, $related, $data = [])
    {
        $_response = $this->crm_model->index_related('sales_orders', $id, $related, $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            return $response['data']['records'];
        }

        return [];
    }

    public function show($id, $data = [])
    {
        $_response = $this->crm_model->show('sales_orders', $id, $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }

    public function tally($id, $data = [])
    {
        $_response = $this->crm_model->tally('sales_orders', $id, $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->id = $response['data']['record']['id'];

            return $response['data'];
        }

        return null;
    }

    public function updateCrm($id, $data = [])
    {
        $_response = $this->crm_model->update('sales_orders', $id, $data);
        $response = Api::transformResponse($_response);

        return $response['success'];
    }

    public function persist()
    {
        $data = $this->toArray();
        unset($data['new_record']);

        $_response = $this->crm_model->create('sales_orders', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->new_record = true;
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }
}
