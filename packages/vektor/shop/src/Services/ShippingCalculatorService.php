<?php

namespace Vektor\Shop\Services;

use Vektor\Shop\Models\ShippingMethod;
use Vektor\Shop\Utilities as ShopUtilities;
use Vektor\Utilities\Formatter;

class ShippingCalculatorService
{
    public function handle($shipping_country, $product_count = 0, $product_subtotal = 0, $product_weight = 0)
    {
        $shipping_methods = [];

        $_shipping_methods = ShippingMethod::with(['zones', 'rates'])->get();

        if (!empty($_shipping_methods) > 0) {
            foreach ($_shipping_methods as $_shipping_method) {
                $rate = null;
                $is_disabled = false;
                if (true === $_shipping_method->is_active && $_shipping_method->rates->count() > 0) {
                    $rates_all_disabled = $_shipping_method->rates->every(function ($rate) {
                        return false === $rate->is_active;
                    });
                    if (true == $rates_all_disabled) {
                        $_shipping_method->is_active = false;
                    }
                    if (true === $_shipping_method->is_active) {
                        $rates = self::calculateRates($shipping_country, $_shipping_method, $product_subtotal, $product_count, $product_weight);

                        if ($rates->count() > 0) {
                            $rate = $rates->sortBy('price', SORT_NATURAL)->first();
                        } else {
                            $rate = $_shipping_method->rates->sortBy('price', SORT_NATURAL)->first();
                            $is_disabled = true;
                        }
                    }
                }
                $is_hidden = !$_shipping_method->is_active;

                $shipping_methods[] = [
                    'type' => $_shipping_method->type,
                    'name' => $_shipping_method->name,
                    'code' => $_shipping_method->code,
                    'description' => $_shipping_method->description,
                    'is_hidden' => $is_hidden,
                    'is_disabled' => $is_disabled,
                    'price' => $rate ? $rate->price : null,
                    'display_price' => $rate ? ShopUtilities::addPercentage($rate->price, 20) : null,
                    'configuration' => $_shipping_method->configuration,
                    'formatted' => [
                        'type' => ucwords($_shipping_method->type),
                        'price' => $rate ? Formatter::currency($rate->price) : null,
                        'display_price' => $rate ? Formatter::currency(ShopUtilities::addPercentage($rate->price, 20)) : null,
                    ],
                ];
            }
        }

        return $shipping_methods;
    }

    public function updateMethodPrice($method_code, $shipping_country, $product_count = 0, $product_subtotal = 0, $product_weight = 0)
    {
        $shipping_method = ShippingMethod::with(['zones', 'rates'])->firstWhere('code', $method_code);
        if ($shipping_method->exists()) {
            $rate = null;
            $rates = self::calculateRates($shipping_country, $shipping_method, $product_subtotal, $product_count, $product_weight);
            if ($rates->count() > 0) {
                $rate = $rates->sortBy('price', SORT_NATURAL)->first();
            } else {
                $rate = $shipping_method->rates->sortBy('price', SORT_NATURAL)->first();
                $is_disabled = true;
            }

            return $rate ? $rate->price : null;
        }
    }

    private static function calculateRates($shipping_country, $_shipping_method, $product_subtotal, $product_count, $product_weight)
    {
        return $_shipping_method->rates->filter(function ($rate) use ($shipping_country, $_shipping_method, $product_subtotal, $product_count, $product_weight) {
            $price_from = data_get($rate->configuration, 'price_from', 0);
            $price_to = data_get($rate->configuration, 'price_to', 999999999);
            if ($product_subtotal < $price_from || $product_subtotal > $price_to) {
                return false;
            }

            $count_from = data_get($rate->configuration, 'count_from', 0);
            $count_to = data_get($rate->configuration, 'count_to', 999999999);
            if ($product_count < $count_from || $product_count > $count_to) {
                return false;
            }

            $weight_from = data_get($rate->configuration, 'weight_from', 0);
            $weight_to = data_get($rate->configuration, 'weight_to', 999999999);
            if ($product_weight < $weight_from || $product_weight > $weight_to) {
                return false;
            }

            $shipping_zones = data_get($rate->configuration, 'shipping_zones', []);
            $_shipping_method_zones = collect([]);
            $_shipping_countries = [];
            if (!empty($shipping_zones)) {
                $_shipping_method_zones = $_shipping_method->zones->filter(function ($zone) use ($shipping_zones) {
                    return in_array($zone->code, $shipping_zones);
                });
            }
            if ($_shipping_method_zones->count() > 0) {
                foreach ($_shipping_method_zones as $_shipping_method_zone) {
                    $_shipping_countries = array_merge($_shipping_countries, data_get($_shipping_method_zone->configuration, 'shipping_countries', []));
                }
            }

            $shipping_countries = data_get($rate->configuration, 'shipping_countries', []);
            if (!empty($_shipping_countries)) {
                $shipping_countries = array_merge($shipping_countries, $_shipping_countries);
            }

            if (!empty($shipping_countries)) {
                $shipping_countries = array_values(array_unique($shipping_countries));
                if (!in_array($shipping_country, $shipping_countries)) {
                    return false;
                }
            }

            return true;
        });
    }
}
