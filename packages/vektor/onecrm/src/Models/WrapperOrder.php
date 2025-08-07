<?php

namespace Vektor\OneCRM\Models;

use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;

class WrapperOrder
{
    public $crm;

    public $crm_model;

    public $_sales_order;

    public $_tax_code;

    public $directory;

    public function __construct()
    {
        $this->crm = new OneCRM();
        $this->crm_model = new OneCRMModel();

        $this->_sales_order = new SalesOrder();
        $this->_tax_code = new TaxCode();

        return $this;
    }

    public function fill($data = [])
    {
        return $this->_sales_order->fill($data);
    }

    public function toArray()
    {
        return $this->_sales_order->toArray();
    }

    public function deleteOrderDirectory($dir)
    {
        if (!file_exists($dir)) {
            return false;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ('.' == $item || '..' == $item) {
                continue;
            }

            $itemPath = $dir.DIRECTORY_SEPARATOR.$item;
            if (is_dir($itemPath)) {
                $this->deleteOrderDirectory($itemPath);
            } else {
                unlink($itemPath);
            }
        }

        return rmdir($dir);
    }

    public function persistFromModel($model, $from_model, $from_id)
    {
        $related_quote_id = isset($this->_sales_order->related_quote_id) ? $this->_sales_order->related_quote_id : null;
        $lines = $this->_sales_order->lines;

        if ($related_quote_id && !empty($lines)) {
            unset($this->_sales_order->related_quote_id);
            $sales_order_response = $this->persist();
        }

        if ($related_quote_id && empty($lines)) {
            $sales_order_response = $this->_sales_order->persist();
        }

        $tmp_so = new SalesOrder();
        $tmp_response = $tmp_so->tally($sales_order_response['id']);

        $record = $tmp_response['record'];

        $comments = $this->_sales_order->index_related($sales_order_response['id'], 'comments_link', [
            'fields' => [
                'body',
            ],
        ]);

        $adjustments = $this->_sales_order->index_related($sales_order_response['id'], 'adjustments_link');

        foreach ($comments as $comment) {
            $comment['body'] = preg_replace('/'.$from_id.'/', $sales_order_response['id'], $comment['body']);
            $this->crm_model->update('sales_order_comments', $comment['id'], [
                'body' => $comment['body'],
            ]);
        }

        $model_record = $model['record'];
        $customisations = $model_record['customisations_data'] ?? null;

        $old_dir = $model_record['prefix'].(strpos($from_model, 'quote') ? $model_record['quote_number'] : '');
        $new_dir = $record['prefix'].$record['so_number'];

        $customisations = preg_replace('/'.$old_dir.'/', $new_dir, $customisations);

        $sales_order_data = [
            'customisations_data' => $customisations,
        ];

        if ($related_quote_id && !empty($lines)) {
            $sales_order_data['related_quote_id'] = $related_quote_id;
        }

        $this->_sales_order->updateCrm($sales_order_response['id'], $sales_order_data);

        if ($related_quote_id && !empty($lines)) {
            $tmp_quote = new Quote();
            $tmp_quote->updateCrm($sales_order_data['related_quote_id'], [
                'quote_stage' => 'Closed Accepted',
            ]);
        }

        $sales_order_response['prefix'] = $record['prefix'];
        $sales_order_response['so_number'] = $record['so_number'];

        if (file_exists(public_path("builder/orders/{$new_dir}"))) {
            $this->deleteOrderDirectory(public_path("builder/orders/{$new_dir}"));
        }

        if (file_exists(public_path("builder/orders/{$old_dir}"))) {
            rename(public_path("builder/orders/{$old_dir}"), public_path("builder/orders/{$new_dir}"));
        }

        return $sales_order_response;
    }

    public function persist()
    {
        $response = null;
        $lines = $this->_sales_order->lines;
        $customisations = collect($this->_sales_order->customisations);

        if (!empty($lines)) {
            unset($this->_sales_order->lines);

            $sales_order_response = $this->_sales_order->persist();

            if ($sales_order_response) {
                $response = $sales_order_response;

                $tmp_so = new SalesOrder();
                $tmp_response = $tmp_so->show($response['id']);
                if ($tmp_response) {
                    $response['prefix'] = $tmp_response['prefix'];
                    $response['so_number'] = $tmp_response['so_number'];

                    $response['lines'] = [];

                    $_sales_order_line_group = new SalesOrderLineGroup();
                    $_sales_order_line_group->fill([
                        'parent_id' => $sales_order_response['id'],
                        'cost' => $sales_order_response['pretax'],
                        'subtotal' => $sales_order_response['subtotal'],
                        'total' => $sales_order_response['amount'],
                    ]);

                    $sales_order_line_group_response = $_sales_order_line_group->persist();
                    if ($sales_order_line_group_response) {
                        $customisable_lines = [];
                        $standard_lines = [
                            'standard' => [],
                        ];

                        foreach ($lines as $line) {
                            if (isset($line['customisation_id']) && $customisation = $customisations->get($line['customisation_id'])) {
                                $id = $line['customisation_id'];
                                if (!isset($customisable_lines[$id])) {
                                    $customisable_lines[$id] = [];
                                }
                                array_push($customisable_lines[$id], $line);
                                $customisation->add_sku($line['name']);
                            } else {
                                array_push($standard_lines['standard'], $line);
                            }
                        }

                        if ($customisations->count() > 0) {
                            $this->directory = $this->make_order_dir("{$tmp_response['prefix']}{$tmp_response['so_number']}");
                            $customisations_data = $customisations->map(function ($customisation, $idx) use ($sales_order_response) {
                                $customisation->make_design_dir($idx, $this->directory);
                                $customisation->handle_images();
                                $customisation->update_comment($sales_order_response['id']);

                                return [$customisation->get_id() => [
                                    'designs' => $customisation->get_designs(),
                                    'skus' => $customisation->get_skus(),
                                    'note' => $customisation->get_note(),
                                ]];
                            })->collapse();
                            $sales_order_customisations = new SalesOrderCustomisations();
                            $sales_order_customisations->fill([
                                'parent_id' => $sales_order_response['id'],
                                'customisations' => $customisations_data,
                            ]);
                            $sales_order_customisations_response = $sales_order_customisations->persist();
                        }

                        $groups = array_merge($customisable_lines, $standard_lines);

                        $line_position = 0;
                        foreach ($groups as $id => $group_lines) {
                            if (count($group_lines) > 0) {
                                foreach ($group_lines as $line) {
                                    $_sales_order_line = new SalesOrderLine();

                                    $sales_order_line_data = [
                                        'sales_orders_id' => $sales_order_response['id'],
                                        'line_group_id' => $sales_order_line_group_response['id'],
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
                                        $sales_order_line_data['cost_price'] = $line['cost_price'];
                                    }

                                    if (isset($line['list_price'])) {
                                        $sales_order_line_data['list_price'] = $line['list_price'];
                                    }

                                    if (isset($line['related_type'])) {
                                        $sales_order_line_data['related_type'] = $line['related_type'];
                                    }

                                    if (isset($line['related_id'])) {
                                        $sales_order_line_data['related_id'] = $line['related_id'];
                                    }

                                    if (isset($line['mfr_part_no'])) {
                                        $sales_order_line_data['mfr_part_no'] = $line['mfr_part_no'];
                                    }

                                    $_sales_order_line->fill($sales_order_line_data);

                                    $sales_order_line_response = $_sales_order_line->persist();

                                    if ($sales_order_line_response) {
                                        ++$line_position;

                                        if (isset($line['adjustments']) && !empty($line['adjustments'])) {
                                            foreach ($line['adjustments'] as $line_adjustment) {
                                                $_sales_order_adjustment = new SalesOrderAdjustment();

                                                $_sales_order_adjustment->fill([
                                                    'sales_orders_id' => $sales_order_response['id'],
                                                    'line_group_id' => $sales_order_line_group_response['id'],
                                                    'line_id' => $sales_order_line_response['id'],
                                                    'name' => $line_adjustment['name'],
                                                    'type' => 'ProductAttributes',
                                                    'related_id' => $line_adjustment['id'],
                                                    'related_type' => 'ProductAttributes',
                                                    'position' => strval($line_position),
                                                ]);

                                                $sales_order_adjustment_response = $_sales_order_adjustment->persist();

                                                if ($sales_order_adjustment_response) {
                                                    ++$line_position;

                                                    if (!isset($sales_order_line_response['adjustments'])) {
                                                        $sales_order_line_response['adjustments'] = [];
                                                    }

                                                    $sales_order_line_response['adjustments'][] = $sales_order_adjustment_response;
                                                }
                                            }
                                        }

                                        if (isset($line['comment']) && !empty($line['comment'])) {
                                            $_sales_order_comment = new SalesOrderComment();

                                            $_sales_order_comment->fill([
                                                'sales_orders_id' => $sales_order_response['id'],
                                                'line_group_id' => $sales_order_line_group_response['id'],
                                                'name' => $line['name'],
                                                'body' => $line['comment'],
                                                'position' => strval($line_position),
                                            ]);

                                            $sales_order_comment_response = $_sales_order_comment->persist();

                                            if ($sales_order_comment_response) {
                                                ++$line_position;

                                                $sales_order_line_response['comment'] = $sales_order_comment_response;
                                            }
                                        }

                                        $response['lines'][] = $sales_order_line_response;
                                    }
                                }

                                if ('standard' !== $id) {
                                    $customisation = $customisations->get($id);
                                    $_sales_order_comment = new SalesOrderComment();

                                    $_sales_order_comment->fill([
                                        'sales_orders_id' => $sales_order_response['id'],
                                        'line_group_id' => $sales_order_line_group_response['id'],
                                        'name' => "Customisation {$id} details",
                                        'body' => $customisation->get_comment(),
                                        'position' => strval($line_position),
                                    ]);

                                    $sales_order_comment_response = $_sales_order_comment->persist();

                                    $response['customisations'][] = $sales_order_comment_response;

                                    if ($sales_order_comment_response) {
                                        ++$line_position;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $this->_sales_order->lines = $lines;
        }

        return $response;
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    public function get_customisations()
    {
        return $this->_sales_order->customisations;
    }

    private function make_order_dir($directory, $increment = 0)
    {
        do {
            $relative_path = "/builder/orders/{$directory}".($increment > 0 ? '-'.sprintf('%02d', $increment) : '');
            $proposed_name = public_path().$relative_path;
            ++$increment;
        } while (is_dir($proposed_name));
        mkdir($proposed_name);

        return $relative_path;
    }
}
