<?php

namespace Vektor\OneCRM\Models;

use Vektor\Api\Api;

class SalesOrderLineGroup extends AbstractLineGroup
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'parent_id',
        'cost',
        'cost_usd',
        'subtotal',
        'subtotal_usd',
        'total',
        'total_usd',
        'new_record',
    ];

    public function persist()
    {
        $data = $this->toArray();
        unset($data['new_record']);

        $_response = $this->crm_model->create('sales_order_line_groups', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->new_record = true;
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }
}
