<?php

namespace Vektor\OneCRM\Models;

use Vektor\Api\Api;
use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;

class WrapperInvoice
{
    public $crm;

    public $crm_model;

    public $_invoice;

    public $_tax_code;

    public function __construct()
    {
        $this->crm = new OneCRM();
        $this->crm_model = new OneCRMModel();

        $this->_invoice = new Invoice();
        $this->_tax_code = new TaxCode();

        return $this;
    }

    public function fill($data = [])
    {
        return $this->_invoice->fill($data);
    }

    public function toArray()
    {
        return $this->_invoice->toArray();
    }

    public function persist()
    {
        $response = null;
        $lines = $this->_invoice->lines;

        if (!empty($lines)) {
            unset($this->_invoice->lines);
            $from_so_id = null;

            if (isset($this->_invoice->from_so_id)) {
                $from_so_id = $this->_invoice->from_so_id;
                unset($this->_invoice->from_so_id);
            }

            $invoice_response = $this->_invoice->persist();

            if ($invoice_response) {
                $response = $invoice_response;
                $response['lines'] = [];

                $_invoice_line_group = new InvoiceLineGroup();

                $_invoice_line_group->fill([
                    'parent_id' => $invoice_response['id'],
                    'cost' => $invoice_response['pretax'],
                    'subtotal' => $invoice_response['subtotal'],
                    'total' => $invoice_response['amount'],
                ]);

                $invoice_line_group_response = $_invoice_line_group->persist();

                if ($invoice_line_group_response) {
                    $line_position = 0;

                    foreach ($lines as $line) {
                        $_invoice_line = new InvoiceLine();

                        $invoice_line_data = [
                            'invoice_id' => $invoice_response['id'],
                            'line_group_id' => $invoice_line_group_response['id'],
                            'name' => $line['name'],
                            'quantity' => $line['quantity'],
                            'unit_price' => $line['unit_price'],
                            'std_unit_price' => $line['std_unit_price'],
                            'ext_price' => $line['ext_price'],
                            'net_price' => $line['net_price'],
                            'tax_class_id' => isset($line['tax_class_id']) && !empty($line['tax_class_id']) ? $line['tax_class_id'] : $this->_tax_code->get(),
                            'position' => strval($line_position),
                        ];

                        if (isset($line['cost_price'])) {
                            $invoice_line_data['cost_price'] = $line['cost_price'];
                        }

                        if (isset($line['list_price'])) {
                            $invoice_line_data['list_price'] = $line['list_price'];
                        }

                        if (isset($line['related_type'])) {
                            $invoice_line_data['related_type'] = $line['related_type'];
                        }

                        if (isset($line['related_id'])) {
                            $invoice_line_data['related_id'] = $line['related_id'];
                        }

                        if (isset($line['mfr_part_no'])) {
                            $invoice_line_data['mfr_part_no'] = $line['mfr_part_no'];
                        }

                        $_invoice_line->fill($invoice_line_data);

                        $invoice_line_response = $_invoice_line->persist();

                        if ($invoice_line_response) {
                            ++$line_position;

                            if (isset($line['adjustments']) && !empty($line['adjustments'])) {
                                foreach ($line['adjustments'] as $line_adjustment) {
                                    $_invoice_adjustment = new InvoiceAdjustment();

                                    $_invoice_adjustment->fill([
                                        'invoice_id' => $invoice_response['id'],
                                        'line_group_id' => $invoice_line_group_response['id'],
                                        'line_id' => $invoice_line_response['id'],
                                        'name' => $line_adjustment['name'],
                                        'type' => 'ProductAttributes',
                                        'related_id' => $line_adjustment['id'],
                                        'related_type' => 'ProductAttributes',
                                        'position' => strval($line_position),
                                    ]);

                                    $invoice_adjustment_response = $_invoice_adjustment->persist();

                                    if ($invoice_adjustment_response) {
                                        ++$line_position;

                                        if (!isset($invoice_line_response['adjustments'])) {
                                            $invoice_line_response['adjustments'] = [];
                                        }

                                        $invoice_line_response['adjustments'][] = $invoice_adjustment_response;
                                    }
                                }
                            }

                            if ('shipping' == $line['id'] && isset($line['comment']) && !empty($line['comment'])) {
                                $_invoice_comment = new InvoiceComment();

                                $_invoice_comment->fill([
                                    'invoice_id' => $invoice_response['id'],
                                    'line_group_id' => $invoice_line_group_response['id'],
                                    'name' => $line['name'],
                                    'body' => $line['comment'],
                                    'position' => strval($line_position),
                                ]);

                                $invoice_comment_response = $_invoice_comment->persist();

                                if ($invoice_comment_response) {
                                    ++$line_position;

                                    $invoice_line_response['comment'] = $invoice_comment_response;
                                }
                            }

                            $response['lines'][] = $invoice_line_response;
                        }
                    }
                }

                if ($from_so_id) {
                    $_invoice_update_response = $this->crm_model->update('invoices', $invoice_response['id'], ['from_so_id' => $from_so_id]);

                    $invoice_update_response = Api::transformResponse($_invoice_update_response);

                    if ($invoice_update_response['success']) {
                        // Related sales order ID retrospectively added after lines have been provided
                    }
                }
            }

            $this->_invoice->lines = $lines;
        } elseif (!empty($this->_invoice->from_so_id)) {
            $invoice_response = $this->_invoice->persist();

            if ($invoice_response) {
                $response = $invoice_response;
            }
        }

        return $response;
    }
}
