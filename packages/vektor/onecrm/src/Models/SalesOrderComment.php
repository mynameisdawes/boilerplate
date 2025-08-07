<?php

namespace Vektor\OneCRM\Models;

use Vektor\Api\Api;

class SalesOrderComment extends AbstractComment
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'sales_orders_id',
        'line_group_id',
        'name',
        'body',
        'position',
        'new_record',
    ];

    public function persist()
    {
        $data = $this->toArray();
        unset($data['new_record']);

        $_response = $this->crm_model->create('sales_order_comments', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->new_record = true;
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }
}
