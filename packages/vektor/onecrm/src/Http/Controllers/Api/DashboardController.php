<?php

namespace Vektor\OneCRM\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\OneCRM\Models\Account;
use Vektor\OneCRM\Models\Contact;
use Vektor\Utilities\Countries;
use Vektor\Utilities\Formatter;

class DashboardController extends ApiController
{
    public function show(Request $request)
    {
        $user = \Auth::user();

        if ($user) {
            $onecrm_account_id_provided = config('onecrm.account_id') ? true : false;
            $onecrm_account_id = $onecrm_account_id_provided ? config('onecrm.account_id') : data_get($user, 'configuration.onecrm_account_id');
            $onecrm_contact_id_provided = config('onecrm.contact_id') ? true : false;
            $onecrm_contact_id = $onecrm_contact_id_provided ? config('onecrm.contact_id') : data_get($user, 'configuration.onecrm_contact_id');
            $stripe_customer_id = data_get($user, 'configuration.stripe_customer_id');

            if (!empty($onecrm_account_id) && !empty($onecrm_contact_id)) {
                $_account = new Account();
                $account = $_account->show($onecrm_account_id);

                $_contact = new Contact();
                $contact = $_contact->show($onecrm_contact_id);

                $formatted_data = [
                    'first_name' => $contact['first_name'],
                    'last_name' => $contact['last_name'],
                    'email' => $contact['email1'],
                    'phone' => $contact['phone_work'],
                    'same_as_shipping' => false,
                    'shipping_address_line_1' => null,
                    'shipping_address_line_2' => null,
                    'shipping_city' => $contact['primary_address_city'],
                    'shipping_county' => $contact['primary_address_state'],
                    'shipping_postcode' => $contact['primary_address_postalcode'],
                    'shipping_country' => null,
                    'shipping_country_name' => null,
                    'shipping_address_street' => null,
                    'shipping_address' => null,
                    'billing_address_line_1' => null,
                    'billing_address_line_2' => null,
                    'billing_city' => $onecrm_account_id_provided ? $contact['alt_address_city'] : $account['billing_address_city'],
                    'billing_county' => $onecrm_account_id_provided ? $contact['alt_address_state'] : $account['billing_address_state'],
                    'billing_postcode' => $onecrm_account_id_provided ? $contact['alt_address_postalcode'] : $account['billing_address_postalcode'],
                    'billing_country' => null,
                    'billing_country_name' => null,
                    'billing_address_street' => null,
                    'billing_address' => null,
                    'full_name' => null,
                    'tel' => null,
                    'stripe_customer_id' => $stripe_customer_id,
                ];

                if (!empty($contact['primary_address_countrycode'])) {
                    $primary_address_country = Countries::convert($contact['primary_address_countrycode'], 'iso2', 'name');
                    if (!empty($primary_address_country)) {
                        $formatted_data['shipping_country'] = $contact['primary_address_countrycode'];
                        $formatted_data['shipping_country_name'] = $primary_address_country;
                    }
                }

                $shipping_address_street = array_map(function ($address_line) {
                    return trim($address_line);
                }, explode("\n", $contact['primary_address_street']));

                if (isset($shipping_address_street[0])) {
                    $formatted_data['shipping_address_line_1'] = $shipping_address_street[0];
                }
                if (isset($shipping_address_street[1])) {
                    $formatted_data['shipping_address_line_2'] = $shipping_address_street[1];
                }

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

                if (!empty($onecrm_account_id_provided ? $contact['alt_address_countrycode'] : $account['billing_address_countrycode'])) {
                    $alt_address_country = Countries::convert($onecrm_account_id_provided ? $contact['alt_address_countrycode'] : $account['billing_address_countrycode'], 'iso2', 'name');
                    if (!empty($alt_address_country)) {
                        $formatted_data['billing_country'] = $onecrm_account_id_provided ? $contact['alt_address_countrycode'] : $account['billing_address_countrycode'];
                        $formatted_data['billing_country_name'] = $alt_address_country;
                    }
                }

                $billing_address_street = array_map(function ($address_line) {
                    return trim($address_line);
                }, explode("\n", $onecrm_account_id_provided ? $contact['alt_address_street'] : $account['billing_address_street']));

                if (isset($billing_address_street[0])) {
                    $formatted_data['billing_address_line_1'] = $billing_address_street[0];
                }
                if (isset($billing_address_street[1])) {
                    $formatted_data['billing_address_line_2'] = $billing_address_street[1];
                }

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

                if ($formatted_data['shipping_address'] == $formatted_data['billing_address']) {
                    $formatted_data['same_as_shipping'] = true;
                }

                $formatted_data['full_name'] = implode(' ', array_filter([
                    $formatted_data['first_name'],
                    $formatted_data['last_name'],
                ]));

                $formatted_data['tel'] = str_replace(' ', '', $formatted_data['phone']);

                return $this->response([
                    'success' => true,
                    'data' => $formatted_data,
                ]);
            }
        }

        return $this->response([
            'error' => true,
            'http_code' => 404,
        ]);
    }

    public function update(Request $request)
    {
        $user = \Auth::user();

        if ($user) {
            $_user_payload = [];

            if (
                $request->input('new_password')
                && $request->input('new_password_confirmation')
                && $request->input('new_password') == $request->input('new_password_confirmation')
                && $request->input('password')
            ) {
                if (Hash::check($request->input('password'), $user->password)) {
                    $_user_payload['password'] = Hash::make($request->input('new_password'));
                } else {
                    return $this->response([
                        'error' => true,
                        'error_message' => "Your existing password doesn't match our records. Please try again",
                        'http_code' => 403,
                    ]);
                }
            }

            $onecrm_account_id_provided = config('onecrm.account_id') ? true : false;
            $onecrm_account_id = $onecrm_account_id_provided ? config('onecrm.account_id') : data_get($user, 'configuration.onecrm_account_id');
            $onecrm_contact_id_provided = config('onecrm.contact_id') ? true : false;
            $onecrm_contact_id = $onecrm_contact_id_provided ? config('onecrm.contact_id') : data_get($user, 'configuration.onecrm_contact_id');
            $stripe_customer_id = data_get($user, 'configuration.stripe_customer_id');

            if (!empty($onecrm_account_id) && !empty($onecrm_contact_id)) {
                $formatted_data = [
                    'first_name' => Formatter::name($request->input('first_name')),
                    'last_name' => Formatter::name($request->input('last_name')),
                    'email' => Formatter::email($request->input('email')),
                    'phone' => Formatter::phone($request->input('phone')),
                    'same_as_shipping' => $request->input('same_as_shipping'),
                    'shipping_address_line_1' => Formatter::name($request->input('shipping_address_line_1')),
                    'shipping_address_line_2' => Formatter::name($request->input('shipping_address_line_2')),
                    'shipping_city' => Formatter::name($request->input('shipping_city')),
                    'shipping_county' => Formatter::name($request->input('shipping_county')),
                    'shipping_postcode' => Formatter::postcode($request->input('shipping_postcode')),
                    'shipping_country' => $request->input('shipping_country'),
                    'shipping_country_name' => Countries::convert($request->input('shipping_country'), 'iso2', 'name'),
                    'shipping_address_street' => null,
                    'shipping_address' => null,
                    'billing_address_line_1' => Formatter::name($request->input('billing_address_line_1')),
                    'billing_address_line_2' => Formatter::name($request->input('billing_address_line_2')),
                    'billing_city' => Formatter::name($request->input('billing_city')),
                    'billing_county' => Formatter::name($request->input('billing_county')),
                    'billing_postcode' => Formatter::postcode($request->input('billing_postcode')),
                    'billing_country' => $request->input('billing_country'),
                    'billing_country_name' => Countries::convert($request->input('billing_country'), 'iso2', 'name'),
                    'billing_address_street' => null,
                    'billing_address' => null,
                    'full_name' => null,
                    'tel' => null,
                    'stripe_customer_id' => $stripe_customer_id,
                ];

                if ('true' === $request->input('same_as_shipping') || true === $request->input('same_as_shipping')) {
                    $formatted_data['billing_address_line_1'] = $formatted_data['shipping_address_line_1'];
                    $formatted_data['billing_address_line_2'] = $formatted_data['shipping_address_line_2'];
                    $formatted_data['billing_city'] = $formatted_data['shipping_city'];
                    $formatted_data['billing_county'] = $formatted_data['shipping_county'];
                    $formatted_data['billing_postcode'] = $formatted_data['shipping_postcode'];
                    $formatted_data['billing_country'] = $formatted_data['shipping_country'];
                    $formatted_data['billing_country_name'] = $formatted_data['shipping_country_name'];
                }

                $formatted_data['shipping_address_street'] = [
                    $formatted_data['shipping_address_line_1'],
                    $formatted_data['shipping_address_line_2'],
                ];

                $formatted_data['shipping_address'] = implode('<br />', array_values(array_filter([
                    $formatted_data['shipping_address_line_1'],
                    $formatted_data['shipping_address_line_2'],
                    $formatted_data['shipping_city'],
                    $formatted_data['shipping_county'],
                    $formatted_data['shipping_postcode'],
                    $formatted_data['shipping_country_name'],
                ])));

                $formatted_data['billing_address_street'] = [
                    $formatted_data['billing_address_line_1'],
                    $formatted_data['billing_address_line_2'],
                ];

                $formatted_data['billing_address'] = implode('<br />', array_values(array_filter([
                    $formatted_data['billing_address_line_1'],
                    $formatted_data['billing_address_line_2'],
                    $formatted_data['billing_city'],
                    $formatted_data['billing_county'],
                    $formatted_data['billing_postcode'],
                    $formatted_data['billing_country_name'],
                ])));

                $formatted_data['full_name'] = implode(' ', array_filter([
                    $formatted_data['first_name'],
                    $formatted_data['last_name'],
                ]));

                $formatted_data['tel'] = str_replace(' ', '', $formatted_data['phone']);

                if (false == $onecrm_account_id_provided) {
                    $_account = new Account();

                    $account_data = [
                        'name' => $formatted_data['full_name'],
                        'phone_office' => $formatted_data['phone'],
                        'email1' => $formatted_data['email'],
                        'shipping_address_street' => $formatted_data['shipping_address_street'],
                        'shipping_address_city' => $formatted_data['shipping_city'],
                        'shipping_address_state' => $formatted_data['shipping_county'],
                        'shipping_address_postalcode' => $formatted_data['shipping_postcode'],
                        'shipping_address_countrycode' => $formatted_data['shipping_country'],
                        'shipping_address_country' => $formatted_data['shipping_country_name'],
                        'billing_address_street' => $formatted_data['billing_address_street'],
                        'billing_address_city' => $formatted_data['billing_city'],
                        'billing_address_state' => $formatted_data['billing_county'],
                        'billing_address_postalcode' => $formatted_data['billing_postcode'],
                        'billing_address_countrycode' => $formatted_data['billing_country'],
                        'billing_address_country' => $formatted_data['billing_country_name'],
                    ];

                    $_account->fill($account_data);
                    $account_response = $_account->persist();
                }

                $_contact = new Contact();

                $contact_data = [
                    'primary_account_id' => $onecrm_account_id,
                    'first_name' => $formatted_data['first_name'],
                    'last_name' => $formatted_data['last_name'],
                    'phone_work' => $formatted_data['phone'],
                    'email1' => $formatted_data['email'],
                    'primary_address_street' => $formatted_data['shipping_address_street'],
                    'primary_address_city' => $formatted_data['shipping_city'],
                    'primary_address_state' => $formatted_data['shipping_county'],
                    'primary_address_postalcode' => $formatted_data['shipping_postcode'],
                    'primary_address_countrycode' => $formatted_data['shipping_country'],
                    'primary_address_country' => $formatted_data['shipping_country_name'],
                    'alt_address_street' => $formatted_data['billing_address_street'],
                    'alt_address_city' => $formatted_data['billing_city'],
                    'alt_address_state' => $formatted_data['billing_county'],
                    'alt_address_postalcode' => $formatted_data['billing_postcode'],
                    'alt_address_countrycode' => $formatted_data['billing_country'],
                    'alt_address_country' => $formatted_data['billing_country_name'],
                ];

                $_contact->fill($contact_data);
                $contact_response = $_contact->persist();

                $_user_payload = array_merge($_user_payload, [
                    'first_name' => $formatted_data['first_name'],
                    'last_name' => $formatted_data['last_name'],
                    'email' => $formatted_data['email'],
                ]);

                $user->update($_user_payload);

                if (!empty($onecrm_account_id) && true == $contact_response) {
                    return $this->response([
                        'success' => true,
                        'data' => $formatted_data,
                    ]);
                }
            }
        }

        return $this->response([
            'error' => true,
            'http_code' => 404,
        ]);
    }
}
