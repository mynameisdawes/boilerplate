<?php

namespace Vektor\OneCRM\Models;

use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;

class WrapperShipping
{
    public $crm;

    public $crm_model;

    public $_shipping;

    public $_tax_code;

    public function __construct()
    {
        $this->crm = new OneCRM();
        $this->crm_model = new OneCRMModel();

        $this->_shipping = new Shipping();
        $this->_tax_code = new TaxCode();

        return $this;
    }

    public function fill($data = [])
    {
        return $this->_shipping->fill($data);
    }

    public function toArray()
    {
        return $this->_shipping->toArray();
    }

    public function persist()
    {
        $response = null;
        $lines = $this->_shipping->lines;

        if (!empty($lines)) {
            unset($this->_shipping->lines);

            $shipping_response = $this->_shipping->persist();

            if ($shipping_response) {
                $response = $shipping_response;
                $response['lines'] = [];

                $_shipping_line_group = new ShippingLineGroup();

                $_shipping_line_group->fill([
                    'parent_id' => $shipping_response['id'],
                    'subtotal' => $shipping_response['shipping_cost'],
                    'total' => $shipping_response['shipping_cost'],
                ]);

                $shipping_line_group_response = $_shipping_line_group->persist();

                if ($shipping_line_group_response) {
                    $line_position = 0;

                    foreach ($lines as $line) {
                        $_shipping_line = new ShippingLine();

                        $shipping_line_data = [
                            'shipping_id' => $shipping_response['id'],
                            'line_group_id' => $shipping_line_group_response['id'],
                            'name' => $line['name'],
                            'quantity' => $line['quantity'],
                            'unit_price' => $line['unit_price'],
                            'std_unit_price' => $line['std_unit_price'],
                            'ext_price' => $line['ext_price'],
                            'tax_class_id' => isset($line['tax_class_id']) && !empty($line['tax_class_id']) ? $line['tax_class_id'] : $this->_tax_code->get(),
                            'position' => strval($line_position),
                        ];

                        if (isset($line['cost_price'])) {
                            $shipping_line_data['cost_price'] = $line['cost_price'];
                        }

                        if (isset($line['list_price'])) {
                            $shipping_line_data['list_price'] = $line['list_price'];
                        }

                        if (isset($line['related_type'])) {
                            $shipping_line_data['related_type'] = $line['related_type'];
                        }

                        if (isset($line['related_id'])) {
                            $shipping_line_data['related_id'] = $line['related_id'];
                        }

                        if (isset($line['mfr_part_no'])) {
                            $shipping_line_data['mfr_part_no'] = $line['mfr_part_no'];
                        }

                        $_shipping_line->fill($shipping_line_data);

                        $shipping_line_response = $_shipping_line->persist();

                        if ($shipping_line_response) {
                            ++$line_position;

                            if (isset($line['adjustments']) && !empty($line['adjustments'])) {
                                foreach ($line['adjustments'] as $line_adjustment) {
                                    $_shipping_adjustment = new ShippingAdjustment();

                                    $_shipping_adjustment->fill([
                                        'shipping_id' => $shipping_response['id'],
                                        'line_group_id' => $shipping_line_group_response['id'],
                                        'line_id' => $shipping_line_response['id'],
                                        'name' => $line_adjustment['name'],
                                        'type' => 'ProductAttributes',
                                        'related_id' => $line_adjustment['id'],
                                        'related_type' => 'ProductAttributes',
                                        'position' => strval($line_position),
                                    ]);

                                    $shipping_adjustment_response = $_shipping_adjustment->persist();

                                    if ($shipping_adjustment_response) {
                                        ++$line_position;

                                        if (!isset($shipping_line_response['adjustments'])) {
                                            $shipping_line_response['adjustments'] = [];
                                        }

                                        $shipping_line_response['adjustments'][] = $shipping_adjustment_response;
                                    }
                                }
                            }

                            if ('shipping' == $line['id'] && isset($line['comment']) && !empty($line['comment'])) {
                                $_shipping_comment = new ShippingComment();

                                $_shipping_comment->fill([
                                    'shipping_id' => $shipping_response['id'],
                                    'line_group_id' => $shipping_line_group_response['id'],
                                    'name' => $line['name'],
                                    'body' => $line['comment'],
                                    'position' => strval($line_position),
                                ]);

                                $shipping_comment_response = $_shipping_comment->persist();

                                if ($shipping_comment_response) {
                                    ++$line_position;

                                    $shipping_line_response['comment'] = $shipping_comment_response;
                                }
                            }

                            $response['lines'][] = $shipping_line_response;
                        }
                    }
                }
            }

            $this->_shipping->lines = $lines;
        }

        return $response;
    }
}
