<?php

namespace Vektor\OneCRM\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\OneCRM\Models\SalesOrder;
use Vektor\Utilities\Countries;
use Vektor\Utilities\Formatter;

class DashboardOrderController extends ApiController
{
    public function index(Request $request)
    {
        $orders = [];

        $user = \Auth::user();

        if ($user) {
            $onecrm_account_id_provided = config('onecrm.account_id') ? true : false;
            $onecrm_account_id = $onecrm_account_id_provided ? config('onecrm.account_id') : data_get($user, 'configuration.onecrm_account_id');
            $onecrm_contact_id_provided = config('onecrm.contact_id') ? true : false;
            $onecrm_contact_id = $onecrm_contact_id_provided ? config('onecrm.contact_id') : data_get($user, 'configuration.onecrm_contact_id');

            if (!empty($onecrm_account_id) && !empty($onecrm_contact_id)) {
                $_order = new SalesOrder();

                $order_response = $_order->index([
                    'filters' => [
                        'billing_account_id' => $onecrm_account_id,
                        'billing_contact_id' => $onecrm_contact_id,
                    ],
                    'per_page' => 50,
                ]);

                if (!empty($order_response)) {
                    $orders = array_map(function ($order) {
                        return array_merge($order, [
                            'number' => $order['prefix'].$order['so_number'],
                            'formatted' => [
                                'amount' => Formatter::currency($order['amount']),
                            ],
                        ]);
                    }, $order_response);
                }
            } else {
                return $this->response([
                    'error' => true,
                    'error_message' => 'No valid account or contact id found',
                    'http_code' => 404,
                ]);
            }
        }

        return $this->response([
            'success' => true,
            'data' => [
                'orders' => $orders,
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $_order = new SalesOrder();
        $order = $_order->show($id);

        $formatted_data = [
            'id' => $order['id'],
            'full_name' => null,
            'number' => $order['prefix'].$order['so_number'],
            'name' => $order['name'],
            'status' => $order['so_stage'],
            'shipping_address_line_1' => null,
            'shipping_address_line_2' => null,
            'shipping_city' => $order['shipping_address_city'],
            'shipping_county' => $order['shipping_address_state'],
            'shipping_postcode' => $order['shipping_address_postalcode'],
            'shipping_country' => null,
            'shipping_country_name' => null,
            'shipping_address_street' => null,
            'shipping_address' => null,
            'billing_address_line_1' => null,
            'billing_address_line_2' => null,
            'billing_city' => $order['billing_address_city'],
            'billing_county' => $order['billing_address_state'],
            'billing_postcode' => $order['billing_address_postalcode'],
            'billing_country' => null,
            'billing_country_name' => null,
            'billing_address_street' => null,
            'billing_address' => null,
            'subtotal' => floatval($order['subtotal']),
            'total_tax' => floatval($order['amount']) - floatval($order['subtotal']),
            'amount' => floatval($order['amount']),
            'formatted' => [
                'subtotal' => Formatter::currency($order['subtotal']),
                'total_tax' => Formatter::currency(floatval($order['amount']) - floatval($order['subtotal'])),
                'amount' => Formatter::currency($order['amount']),
            ],
            'lines' => [],
        ];

        $formatted_data['full_name'] = $formatted_data['number'].': '.$formatted_data['name'];

        if (!empty($order['shipping_address_countrycode'])) {
            $shipping_address_country = Countries::convert($order['shipping_address_countrycode'], 'iso2', 'name');
            if (!empty($shipping_address_country)) {
                $formatted_data['shipping_country'] = $order['shipping_address_countrycode'];
                $formatted_data['shipping_country_name'] = $shipping_address_country;
            }
        }

        $shipping_address_street = array_map(function ($address_line) {
            return trim($address_line);
        }, explode("\n", $order['shipping_address_street']));

        if (isset($shipping_address_street[0])) {
            $formatted_data['shipping_address_line_1'] = $shipping_address_street[0];
        }
        if (isset($shipping_address_street[1])) {
            $formatted_data['shipping_address_line_2'] = $shipping_address_street[1];
        }

        $formatted_data['shipping_address_street'] = implode('<br />', array_map(function ($address_line) {
            return trim($address_line);
        }, array_values(array_filter([
            $formatted_data['shipping_address_line_1'],
            $formatted_data['shipping_address_line_2'],
        ]))));

        $formatted_data['shipping_address'] = implode('<br />', array_map(function ($address_line) {
            return trim($address_line);
        }, array_values(array_filter([
            $formatted_data['shipping_address_line_1'],
            $formatted_data['shipping_address_line_2'],
            $formatted_data['shipping_city'],
            $formatted_data['shipping_county'],
            $formatted_data['shipping_postcode'],
            $formatted_data['shipping_country_name'],
        ]))));

        if (!empty($order['billing_address_countrycode'])) {
            $billing_address_country = Countries::convert($order['billing_address_countrycode'], 'iso2', 'name');
            if (!empty($billing_address_country)) {
                $formatted_data['billing_country'] = $order['billing_address_countrycode'];
                $formatted_data['billing_country_name'] = $billing_address_country;
            }
        }

        $billing_address_street = array_map(function ($address_line) {
            return trim($address_line);
        }, explode("\n", $order['billing_address_street']));

        if (isset($billing_address_street[0])) {
            $formatted_data['billing_address_line_1'] = $billing_address_street[0];
        }
        if (isset($billing_address_street[1])) {
            $formatted_data['billing_address_line_2'] = $billing_address_street[1];
        }

        $formatted_data['billing_address_street'] = implode('<br />', array_map(function ($address_line) {
            return trim($address_line);
        }, array_values(array_filter([
            $formatted_data['billing_address_line_1'],
            $formatted_data['billing_address_line_2'],
        ]))));

        $formatted_data['billing_address'] = implode('<br />', array_map(function ($address_line) {
            return trim($address_line);
        }, array_values(array_filter([
            $formatted_data['billing_address_line_1'],
            $formatted_data['billing_address_line_2'],
            $formatted_data['billing_city'],
            $formatted_data['billing_county'],
            $formatted_data['billing_postcode'],
            $formatted_data['billing_country_name'],
        ]))));

        if (!empty($order['line_items'])) {
            $formatted_data['lines'] = array_map(function ($line_item) {
                return [
                    'name' => $line_item['name'],
                    'quantity' => intval($line_item['quantity']),
                    'unit_price' => floatval($line_item['unit_price']),
                    'ext_price' => floatval($line_item['ext_price']),
                    'formatted' => [
                        'unit_price' => Formatter::currency($line_item['unit_price']),
                        'ext_price' => Formatter::currency($line_item['ext_price']),
                    ],
                ];
            }, $order['line_items']);
        }

        return $this->response([
            'success' => true,
            'data' => [
                '_order' => $order,
                'order' => $formatted_data,
            ],
        ]);
    }
}
