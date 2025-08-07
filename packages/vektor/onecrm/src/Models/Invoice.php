<?php

namespace Vektor\OneCRM\Models;

use Illuminate\Database\Eloquent\Model;
use Vektor\Api\Api;
use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;
use Vektor\Utilities\Formatter;

class Invoice extends Model
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
        'from_so_id',
        'name',
        'due_date',
        'delivery_date',
        'invoice_date',
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
        'lines',
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
        'due_date',
        'delivery_date',
        'invoice_date',
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
     * Get the model's invoice_date.
     */
    public function getInvoiceDateAttribute(?string $value): ?string
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

    public function show($id, $data = [])
    {
        $_response = $this->crm_model->show('invoices', $id, $data);
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

        $_response = $this->crm_model->create('invoices', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->new_record = true;
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }
}
