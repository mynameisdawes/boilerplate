<?php

namespace Vektor\OneCRM\Models;

use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;

class WrapperQuote
{
    public $crm;

    public $crm_model;

    public $_quote;

    public $_tax_code;

    public $directory;

    public function __construct()
    {
        $this->crm = new OneCRM();
        $this->crm_model = new OneCRMModel();

        $this->_quote = new Quote();
        $this->_tax_code = new TaxCode();

        return $this;
    }

    public function fill($data = [])
    {
        return $this->_quote->fill($data);
    }

    public function toArray()
    {
        return $this->_quote->toArray();
    }

    public function persist()
    {
        $response = null;
        $lines = $this->_quote->lines;
        $customisations = collect($this->_quote->customisations);

        if (!empty($lines)) {
            unset($this->_quote->lines);

            $quote_response = $this->_quote->persist();

            if ($quote_response) {
                $response = $quote_response;

                $tmp_so = new Quote();
                $tmp_response = $tmp_so->show($response['id']);
                if ($tmp_response) {
                    $response['prefix'] = $tmp_response['prefix'];
                    $response['quote_number'] = $tmp_response['quote_number'];

                    $response['lines'] = [];

                    $_quote_line_group = new QuoteLineGroup();
                    $_quote_line_group->fill([
                        'parent_id' => $quote_response['id'],
                        'cost' => $quote_response['pretax'],
                        'subtotal' => $quote_response['subtotal'],
                        'total' => $quote_response['amount'],
                    ]);

                    $quote_line_group_response = $_quote_line_group->persist();
                    if ($quote_line_group_response) {
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
                                $customisation->add_sku($line['mfr_part_no']);
                            } else {
                                array_push($standard_lines['standard'], $line);
                            }
                        }

                        if ($customisations->count() > 0) {
                            $this->directory = $this->make_order_dir("{$tmp_response['prefix']}{$tmp_response['quote_number']}");
                            $customisations_data = $customisations->map(function ($customisation, $idx) use ($quote_response) {
                                $customisation->make_design_dir($idx, $this->directory);
                                $customisation->handle_images();
                                $customisation->update_comment($quote_response['id']);

                                return [$customisation->get_id() => [
                                    'designs' => $customisation->get_designs(),
                                    'skus' => $customisation->get_skus(),
                                    'note' => $customisation->get_note(),
                                ]];
                            })->collapse();
                            $quote_customisations = new QuoteCustomisations();
                            $quote_customisations->fill([
                                'parent_id' => $quote_response['id'],
                                'customisations' => $customisations_data,
                            ]);
                            $quote_customisations_response = $quote_customisations->persist();
                        }

                        $groups = array_merge($customisable_lines, $standard_lines);

                        $line_position = 0;
                        foreach ($groups as $id => $group_lines) {
                            if (count($group_lines) > 0) {
                                $suffix = '';
                                if ('standard' != $id) {
                                    $suffix = '_'.sprintf('%02d', array_search($id, array_keys($groups)) + 1);
                                }

                                foreach ($group_lines as $line) {
                                    $_quote_line = new QuoteLine();

                                    $quote_line_data = [
                                        'quote_id' => $quote_response['id'],
                                        'line_group_id' => $quote_line_group_response['id'],
                                        'name' => $line['name'].$suffix,
                                        'quantity' => $line['quantity'],
                                        'unit_price' => $line['unit_price'],
                                        'std_unit_price' => $line['std_unit_price'],
                                        'ext_price' => $line['ext_price'],
                                        'net_price' => $line['net_price'],
                                        'tax_class_id' => isset($line['tax_class_id']) && !empty($line['tax_class_id']) ? $line['tax_class_id'] : $this->_tax_code->get(),
                                        'position' => strval($line_position),
                                    ];

                                    if (isset($line['cost_price'])) {
                                        $quote_line_data['cost_price'] = $line['cost_price'];
                                    }

                                    if (isset($line['list_price'])) {
                                        $quote_line_data['list_price'] = $line['list_price'];
                                    }

                                    if (isset($line['related_type'])) {
                                        $quote_line_data['related_type'] = $line['related_type'];
                                    }

                                    if (isset($line['related_id'])) {
                                        $quote_line_data['related_id'] = $line['related_id'];
                                    }

                                    if (isset($line['mfr_part_no'])) {
                                        $quote_line_data['mfr_part_no'] = $line['mfr_part_no'];
                                    }

                                    $_quote_line->fill($quote_line_data);

                                    $quote_line_response = $_quote_line->persist();

                                    if ($quote_line_response) {
                                        ++$line_position;

                                        if (isset($line['adjustments']) && !empty($line['adjustments'])) {
                                            foreach ($line['adjustments'] as $line_adjustment) {
                                                $_quote_adjustment = new QuoteAdjustment();

                                                $_quote_adjustment->fill([
                                                    'quote_id' => $quote_response['id'],
                                                    'line_group_id' => $quote_line_group_response['id'],
                                                    'line_id' => $quote_line_response['id'],
                                                    'name' => $line_adjustment['name'],
                                                    'type' => 'ProductAttributes',
                                                    'related_id' => $line_adjustment['id'],
                                                    'related_type' => 'ProductAttributes',
                                                    'position' => strval($line_position),
                                                ]);

                                                $quote_adjustment_response = $_quote_adjustment->persist();

                                                if ($quote_adjustment_response) {
                                                    ++$line_position;

                                                    if (!isset($quote_line_response['adjustments'])) {
                                                        $quote_line_response['adjustments'] = [];
                                                    }

                                                    $quote_line_response['adjustments'][] = $quote_adjustment_response;
                                                }
                                            }
                                        }

                                        if ('shipping' == $line['id'] && isset($line['comment']) && !empty($line['comment'])) {
                                            $_quote_comment = new QuoteComment();

                                            $_quote_comment->fill([
                                                'quote_id' => $quote_response['id'],
                                                'line_group_id' => $quote_line_group_response['id'],
                                                'name' => $line['name'],
                                                'body' => $line['comment'],
                                                'position' => strval($line_position),
                                            ]);

                                            $quote_comment_response = $_quote_comment->persist();

                                            if ($quote_comment_response) {
                                                ++$line_position;

                                                $quote_line_response['comment'] = $quote_comment_response;
                                            }
                                        }

                                        $response['lines'][] = $quote_line_response;
                                    }
                                }

                                if ('standard' != $id) {
                                    $customisation = $customisations->get($id);
                                    $_quote_comment = new QuoteComment();

                                    $_quote_comment->fill([
                                        'quote_id' => $quote_response['id'],
                                        'line_group_id' => $quote_line_group_response['id'],
                                        'name' => "Customisation {$id} details",
                                        'body' => $customisation->get_comment(),
                                        'position' => strval($line_position),
                                    ]);

                                    $quote_comment_response = $_quote_comment->persist();

                                    $response['customisations'][] = $quote_comment_response;

                                    if ($quote_comment_response) {
                                        ++$line_position;
                                    }
                                }
                            }
                        }
                    }

                    // $_quote_line_group = new QuoteLineGroup;

                    // $_quote_line_group->fill([
                    //     'parent_id' => $quote_response['id'],
                    //     'cost' => $quote_response['pretax'],
                    //     'subtotal' => $quote_response['subtotal'],
                    //     'total' => $quote_response['amount'],
                    // ]);

                    // $quote_line_group_response = $_quote_line_group->persist();

                    // if ($quote_line_group_response) {
                    //     $line_position = 0;

                    //     foreach ($lines as $line) {
                    //         $quote_line_response = $this->createLine(
                    //             $quote_response['id'],
                    //             $quote_line_group_response['id'],
                    //             $line
                    //         );

                    //         if ($quote_line_response) {
                    //             $line_position++;

                    //             if (isset($line['adjustments']) && !empty($line['adjustments'])) {
                    //                 foreach ($line['adjustments'] as $line_adjustment) {
                    //                     $quote_adjustment_response = $this->lineAdjustment(
                    //                         $so_id,
                    //                         $group_id,
                    //                         $line_id,
                    //                         $line_position,
                    //                         $line["adjustments"]
                    //                     );

                    //                     if ($quote_adjustment_response) {
                    //                         $line_position++;

                    //                         if (!isset($quote_line_response['adjustments'])) {
                    //                             $quote_line_response['adjustments'] = [];
                    //                         }

                    //                         $quote_line_response['adjustments'][] = $quote_adjustment_response;
                    //                     }
                    //                 }
                    //             }

                    //             if ($line['id'] == 'shipping' && isset($line['comment']) && !empty($line['comment'])) {
                    //                 $_quote_comment = new QuoteComment;

                    //                 $_quote_comment->fill([
                    //                     'quote_id' => $quote_response['id'],
                    //                     'line_group_id' => $quote_line_group_response['id'],
                    //                     'name' => $line['name'],
                    //                     'body' => $line['comment'],
                    //                     'position' => strval($line_position),
                    //                 ]);

                    //                 $quote_comment_response = $_quote_comment->persist();

                    //                 if ($quote_comment_response) {
                    //                     $line_position++;

                    //                     $quote_line_response['comment'] = $quote_comment_response;
                    //                 }
                    //             }

                    //             $response['lines'][] = $quote_line_response;
                    //         }
                    //     }
                    // }
                }
            }

            $this->_quote->lines = $lines;
        }

        return $response;
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    public function get_customisations()
    {
        return $this->_quote->customisations;
    }

    private function make_order_dir($directory, $increment = 0)
    {
        // date_default_timezone_set('Europe/London');
        // $time = date('ymdHis', time());
        do {
            $relative_path = "/builder/orders/{$directory}".($increment > 0 ? '-'.sprintf('%02d', $increment) : '');
            $proposed_name = public_path().$relative_path;
            ++$increment;
        } while (is_dir($proposed_name));
        mkdir($proposed_name);

        return $relative_path;
    }
}
