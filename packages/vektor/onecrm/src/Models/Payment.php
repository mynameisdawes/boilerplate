<?php

namespace Vektor\OneCRM\Models;

use Illuminate\Database\Eloquent\Model;
use Vektor\Api\Api;
use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;

class Payment extends Model
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
        'related_invoice_id',
        'account_id',
        'amount',
        'amount_usdollar',
        'total_amount',
        'total_amount_usdollar',
        'payment_date',
        'customer_reference',
        'payment_type',
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
        'amount_usdollar',
        'total_amount_usdollar',
        'payment_date',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'new_record' => false,
        'payment_type' => 'Stripe',
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
     * Get the model's amount_usdollar.
     */
    public function getAmountUsdollarAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : (!empty($this->attributes['amount']) ? $this->attributes['amount'] : null);
    }

    /**
     * Get the model's total_amount_usdollar.
     */
    public function getTotalAmountUsdollarAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : (!empty($this->attributes['total_amount']) ? $this->attributes['total_amount'] : null);
    }

    /**
     * Get the model's payment_date.
     */
    public function getPaymentDateAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : date('Y-m-d');
    }

    public function show($id, $data = [])
    {
        $_response = $this->crm_model->show('payments', $id, $data);
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

        $_response = $this->crm_model->create('payments', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->new_record = true;
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }
}
