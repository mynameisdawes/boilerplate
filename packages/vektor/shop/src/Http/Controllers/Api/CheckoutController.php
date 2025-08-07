<?php

namespace Vektor\Shop\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Shop\Utilities as ShopUtilities;

class CheckoutController extends ApiController
{
    public function can(Request $request, $instance = null)
    {
        $options = [];

        if ($instance) {
            \Cart::instance($instance);
            $options['instance'] = $instance;
        }

        $min_qty_per_order = 1;
        $user = \Auth::user();
        if (
            $user
            && isset($user->configuration, $user->configuration['shipping_country'])

            && !empty($user->configuration['shipping_country'])
        ) {
            $minimum_country_qtys = json_decode(config('shop.minimum_country_qty'), true);
            if ($minimum_country_qtys && isset($minimum_country_qtys[$user->configuration['shipping_country']])) {
                $min_qty_per_order = intval($minimum_country_qtys[$user->configuration['shipping_country']]);
                if ($min_qty_per_order < 1) {
                    $min_qty_per_order = 1;
                }
            }
        }

        $cart = ShopUtilities::cart($options);

        if ($cart->product_count < $min_qty_per_order) {
            $error_message = "You must add at least {$min_qty_per_order} items to your order to be able to proceed";

            return $this->response([
                'error' => true,
                'error_message' => $error_message,
                'http_code' => 403,
            ]);
        }

        return $this->response([
            'success' => true,
        ]);
    }
}
