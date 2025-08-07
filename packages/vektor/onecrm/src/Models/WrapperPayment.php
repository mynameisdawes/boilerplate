<?php

namespace Vektor\OneCRM\Models;

use Vektor\Api\Api;
use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;

class WrapperPayment
{
    public $crm;

    public $crm_model;

    public $_invoice;

    public $_payment;

    public function __construct()
    {
        $this->crm = new OneCRM();
        $this->crm_model = new OneCRMModel();

        $this->_invoice = new Invoice();
        $this->_payment = new Payment();

        return $this;
    }

    public function fill($data = [])
    {
        return $this->_payment->fill($data);
    }

    public function toArray()
    {
        return $this->_payment->toArray();
    }

    public function persist()
    {
        $response = null;

        $payment_response = $this->_payment->persist();

        if ($payment_response) {
            $response = $payment_response;

            $invoice_payment_data = [
                $payment_response['id'] => [
                    'amount' => $payment_response['amount'],
                    'amount_usdollar' => $payment_response['amount_usdollar'],
                ],
            ];

            $_invoice_payment_response = $this->crm_model->create_related('invoices', $payment_response['related_invoice_id'], 'payments', $invoice_payment_data);
            $invoice_payment_response = Api::transformResponse($_invoice_payment_response);

            if ($invoice_payment_response['success']) {
                $invoice_response = $this->_invoice->show($payment_response['related_invoice_id']);

                if ($invoice_response) {
                    $paid_invoice_data = [
                        'amount_due' => floatval($invoice_response['amount_due']) - floatval($payment_response['amount']),
                        'amount_due_usdollar' => floatval($invoice_response['amount_due_usdollar']) - floatval($payment_response['amount_usdollar']),
                    ];

                    if (isset($invoice_response['from_so_id']) && !empty($invoice_response['from_so_id'])) {
                        $paid_invoice_data['from_so_id'] = $invoice_response['from_so_id'];
                    }

                    $_paid_invoice_response = $this->crm_model->update('invoices', $payment_response['related_invoice_id'], $paid_invoice_data);
                    $paid_invoice_response = Api::transformResponse($_paid_invoice_response);

                    if ($paid_invoice_response['success']) {
                        // Payment amount successfully subtracted from invoice amount due
                    }
                }
            }
        }

        return $response;
    }
}
