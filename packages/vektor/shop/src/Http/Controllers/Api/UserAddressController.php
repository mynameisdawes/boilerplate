<?php

namespace Vektor\Shop\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Utilities\Countries;
use Illuminate\Support\Facades\Auth;
use Vektor\Shop\Models\UserAddress;

class UserAddressController extends ApiController
{
    public function index(Request $request)
    {
        $addresses = [];
        $user = Auth::user();

        if ($user) {
            $user->load(['addresses' => function ($query) {
                $query->orderByDesc('is_default_shipping')->orderByDesc('is_default_billing');
            }]);

            if ($user->addresses->count() > 0) {
                foreach ($user->addresses as $address) {
                    $addresses[] = [
                        'id' => $address->id,
                        'name' => $address->name,
                        'address_line_1' => $address->address_line_1,
                        'address_line_2' => $address->address_line_2,
                        'city' => $address->city,
                        'county' => $address->county,
                        'postcode' => $address->postcode,
                        'country' => $address->country,
                        'country_name' => Countries::convert($address->country, 'iso2', 'name'),
                        'is_default_billing' => $address->is_default_billing,
                        'is_default_shipping' => $address->is_default_shipping,
                    ];
                }
            }
        }

        return $this->response([
            'success' => true,
            'data' => [
                'addresses' => $addresses
            ]
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $existing_address = UserAddress::where('user_id', $user->id)->where('address_line_1', $request->input('address_line_1'))->where('address_line_2', $request->input('address_line_2'))->where('city', $request->input('city'))->where('county', $request->input('county'))->where('postcode', $request->input('postcode'))->where('country', $request->input('country'))->first();

            if ($existing_address) {
                return $this->response([
                    'error' => true,
                    'error_message' => 'An address with these details already exists'
                ]);
            }

            $user_address = new UserAddress();
            $user_address->fill([
                'user_id' => $user->id,
                'name' => $request->input('name'),
                'address_line_1' => $request->input('address_line_1'),
                'address_line_2' => $request->input('address_line_2'),
                'city' => $request->input('city'),
                'county' => $request->input('county'),
                'postcode' => $request->input('postcode'),
                'country' => $request->input('country'),
                'is_default_billing' => $request->boolean('is_default_billing', false),
                'is_default_shipping' => $request->boolean('is_default_shipping', false),
            ]);
            $user_address->save();

            if ($user_address->is_default_billing) {
                UserAddress::where('user_id', $user->id)->where('id', '<>', $user_address->id)->update(['is_default_billing' => false]);
            }
            if ($user_address->is_default_shipping) {
                UserAddress::where('user_id', $user->id)->where('id', '<>', $user_address->id)->update(['is_default_shipping' => false]);
            }

            return $this->response([
                'success' => true,
            ]);
        }

        return $this->response([
            'error' => true,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if ($user && $request->input('id')) {
            $existing_address = UserAddress::where('id', '<>', $request->input('id'))->where('user_id', $user->id)->where('address_line_1', $request->input('address_line_1'))->where('address_line_2', $request->input('address_line_2'))->where('city', $request->input('city'))->where('county', $request->input('county'))->where('postcode', $request->input('postcode'))->where('country', $request->input('country'))->first();

            if ($existing_address) {
                return $this->response([
                    'error' => true,
                    'error_message' => 'An address with these details already exists'
                ]);
            }

            $user_address = UserAddress::where('id', $request->input('id'))->where('user_id', $user->id)->first();

            if ($user_address) {
                $user_address->fill([
                    'name' => $request->input('name'),
                    'address_line_1' => $request->input('address_line_1'),
                    'address_line_2' => $request->input('address_line_2'),
                    'city' => $request->input('city'),
                    'county' => $request->input('county'),
                    'postcode' => $request->input('postcode'),
                    'country' => $request->input('country'),
                    'is_default_billing' => $request->boolean('is_default_billing', false),
                    'is_default_shipping' => $request->boolean('is_default_shipping', false),
                ]);
                $user_address->save();

                if ($user_address->is_default_billing) {
                    UserAddress::where('user_id', $user->id)->where('id', '<>', $user_address->id)->update(['is_default_billing' => false]);
                }
                if ($user_address->is_default_shipping) {
                    UserAddress::where('user_id', $user->id)->where('id', '<>', $user_address->id)->update(['is_default_shipping' => false]);
                }

                return $this->response([
                    'success' => true,
                ]);
            }
        }

        return $this->response([
            'error' => true,
        ]);
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        if ($user && $request->input('id')) {
            $user_address = UserAddress::where('id', $request->input('id'))->where('user_id', $user->id)->first();

            if ($user_address) {
                $user_address->delete();

                return $this->response([
                    'success' => true,
                ]);
            }
        }

        return $this->response([
            'error' => true,
        ]);
    }
}
