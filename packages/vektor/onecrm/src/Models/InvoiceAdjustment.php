<?php

namespace Vektor\OneCRM\Models;

use Vektor\Api\Api;

class InvoiceAdjustment extends AbstractAdjustment
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'invoice_id',
        'line_group_id',
        'line_id',
        'name',
        'type',
        'related_id',
        'related_type',
        'position',
        'new_record',
    ];

    public function persist()
    {
        $data = $this->toArray();
        unset($data['new_record']);

        $_response = $this->crm_model->create('invoice_adjustments', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->new_record = true;
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }
}
