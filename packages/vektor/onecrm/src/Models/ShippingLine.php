<?php

namespace Vektor\OneCRM\Models;

use Vektor\Api\Api;

class ShippingLine extends AbstractLine
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'shipping_id',
        'line_group_id',
        'name',
        'quantity',
        'ext_quantity',
        'cost_price',
        'cost_price_usd',
        'list_price',
        'list_price_usd',
        'unit_price',
        'unit_price_usd',
        'std_unit_price',
        'std_unit_price_usd',
        'ext_price',
        'ext_price_usd',
        'tax_class_id',
        'related_id',
        'related_type',
        'position',
        'mfr_part_no',
        'new_record',
    ];

    public function persist()
    {
        $data = $this->toArray();
        unset($data['new_record']);

        $_response = $this->crm_model->create('shipping_lines', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->new_record = true;
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }
}
