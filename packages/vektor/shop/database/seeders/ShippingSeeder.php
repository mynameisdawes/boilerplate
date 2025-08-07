<?php

namespace Vektor\Shop\Database\Seeders;

use Illuminate\Database\Seeder;
use Vektor\Shop\Models\ShippingMethod;
use Vektor\Shop\Models\ShippingRate;
use Vektor\Shop\Models\ShippingZone;

class ShippingSeeder extends Seeder
{
    /**
     * Seed the application"s database.
     */
    public function run(): void
    {
        $_zones_royal_mail = [
            [
                'name' => 'UK',
                'countries' => [
                    'GB',
                ],
                'rates' => [
                    ['price' => 4.80, 'weight_from' => 0, 'weight_to' => 0.25],
                    ['price' => 5.19, 'weight_from' => 0.251, 'weight_to' => 0.5],
                    ['price' => 5.19, 'weight_from' => 0.501, 'weight_to' => 0.75],
                    ['price' => 5.19, 'weight_from' => 0.751, 'weight_to' => 1],
                    ['price' => 5.19, 'weight_from' => 1.001, 'weight_to' => 1.25],
                    ['price' => 5.19, 'weight_from' => 1.251, 'weight_to' => 1.5],
                    ['price' => 5.19, 'weight_from' => 1.501, 'weight_to' => 2],
                    ['price' => 8.19, 'weight_from' => 2.001, 'weight_to' => 3],
                    ['price' => 8.19, 'weight_from' => 3.001, 'weight_to' => 4],
                    ['price' => 8.19, 'weight_from' => 4.001, 'weight_to' => 5],
                    ['price' => 8.19, 'weight_from' => 5.001, 'weight_to' => 6],
                    ['price' => 8.19, 'weight_from' => 6.001, 'weight_to' => 7],
                    ['price' => 8.19, 'weight_from' => 7.001, 'weight_to' => 8],
                    ['price' => 8.19, 'weight_from' => 8.001, 'weight_to' => 9],
                    ['price' => 8.19, 'weight_from' => 9.001, 'weight_to' => 10],
                ],
            ],
            [
                'name' => 'EU1',
                'countries' => [
                    'DK', 'FR', 'DE', 'MC', 'IE',
                ],
                'rates' => [
                    ['price' => 11.46, 'weight_from' => 0, 'weight_to' => 0.25],
                    ['price' => 13.02, 'weight_from' => 0.251, 'weight_to' => 0.5],
                    ['price' => 14.28, 'weight_from' => 0.501, 'weight_to' => 0.75],
                    ['price' => 15.42, 'weight_from' => 0.751, 'weight_to' => 1],
                    ['price' => 16.08, 'weight_from' => 1.001, 'weight_to' => 1.25],
                    ['price' => 16.20, 'weight_from' => 1.251, 'weight_to' => 1.5],
                    ['price' => 16.38, 'weight_from' => 1.501, 'weight_to' => 2],
                    ['price' => 19.98, 'weight_from' => 2.001, 'weight_to' => 3],
                    ['price' => 20.58, 'weight_from' => 3.001, 'weight_to' => 4],
                    ['price' => 21.24, 'weight_from' => 4.001, 'weight_to' => 5],
                    ['price' => 27.78, 'weight_from' => 5.001, 'weight_to' => 6],
                    ['price' => 27.78, 'weight_from' => 6.001, 'weight_to' => 7],
                    ['price' => 33.78, 'weight_from' => 7.001, 'weight_to' => 8],
                    ['price' => 33.78, 'weight_from' => 8.001, 'weight_to' => 9],
                    ['price' => 33.78, 'weight_from' => 9.001, 'weight_to' => 10],
                ],
            ],
            [
                'name' => 'EU2',
                'countries' => [
                    'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'EE', 'FI', 'GR', 'HU', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
                ],
                'rates' => [
                    ['price' => 11.64, 'weight_from' => 0, 'weight_to' => 0.25],
                    ['price' => 13.50, 'weight_from' => 0.251, 'weight_to' => 0.5],
                    ['price' => 14.70, 'weight_from' => 0.501, 'weight_to' => 0.75],
                    ['price' => 15.78, 'weight_from' => 0.751, 'weight_to' => 1],
                    ['price' => 16.14, 'weight_from' => 1.001, 'weight_to' => 1.25],
                    ['price' => 16.44, 'weight_from' => 1.251, 'weight_to' => 1.5],
                    ['price' => 16.98, 'weight_from' => 1.501, 'weight_to' => 2],
                    ['price' => 21.96, 'weight_from' => 2.001, 'weight_to' => 3],
                    ['price' => 23.94, 'weight_from' => 3.001, 'weight_to' => 4],
                    ['price' => 25.14, 'weight_from' => 4.001, 'weight_to' => 5],
                    ['price' => 28.08, 'weight_from' => 5.001, 'weight_to' => 6],
                    ['price' => 28.08, 'weight_from' => 6.001, 'weight_to' => 7],
                    ['price' => 34.92, 'weight_from' => 7.001, 'weight_to' => 8],
                    ['price' => 34.92, 'weight_from' => 8.001, 'weight_to' => 9],
                    ['price' => 34.92, 'weight_from' => 9.001, 'weight_to' => 10],
                ],
            ],
            [
                'name' => 'EU3',
                'countries' => [
                    'AL', 'AD', 'AM', 'AZ', 'BY', 'BA', 'GE', 'GI', 'IS', 'KZ', 'XK', 'KG', 'LI', 'MD', 'ME', 'MK', 'NO', 'RU', 'SM', 'RS', 'CH', 'TJ', 'TR', 'TM', 'UA', 'UZ', 'VA',
                ],
                'rates' => [
                    ['price' => 13.12, 'weight_from' => 0, 'weight_to' => 0.25],
                    ['price' => 15.00, 'weight_from' => 0.251, 'weight_to' => 0.5],
                    ['price' => 16.38, 'weight_from' => 0.501, 'weight_to' => 0.75],
                    ['price' => 17.76, 'weight_from' => 0.751, 'weight_to' => 1],
                    ['price' => 18.54, 'weight_from' => 1.001, 'weight_to' => 1.25],
                    ['price' => 19.26, 'weight_from' => 1.251, 'weight_to' => 1.5],
                    ['price' => 19.62, 'weight_from' => 1.501, 'weight_to' => 2],
                    ['price' => 30.90, 'weight_from' => 2.001, 'weight_to' => 3],
                    ['price' => 35.46, 'weight_from' => 3.001, 'weight_to' => 4],
                    ['price' => 40.02, 'weight_from' => 4.001, 'weight_to' => 5],
                    ['price' => 63.68, 'weight_from' => 5.001, 'weight_to' => 6],
                    ['price' => 63.68, 'weight_from' => 6.001, 'weight_to' => 7],
                    ['price' => 80.64, 'weight_from' => 7.001, 'weight_to' => 8],
                    ['price' => 80.64, 'weight_from' => 8.001, 'weight_to' => 9],
                    ['price' => 80.64, 'weight_from' => 9.001, 'weight_to' => 10],
                ],
            ],
            [
                'name' => 'W1',
                'countries' => [
                    'AF', 'DZ', 'AO', 'AI', 'AG', 'AR', 'AW', 'BS', 'BH', 'BD', 'BB', 'BZ', 'BJ', 'BM', 'BT', 'BO', 'BW', 'BR', 'VG', 'BN', 'BF', 'BI', 'KH', 'CM', 'CA', 'CV', 'KY', 'CF', 'TD', 'CL', 'CN', 'CO', 'KM', 'CR', 'CU', 'CW', 'CI', 'KP', 'CD', 'DJ', 'DM', 'DO', 'EC', 'EG', 'SV', 'GQ', 'ER', 'ET', 'FO', 'FK', 'GA', 'GH', 'GD', 'GP', 'GT', 'GN', 'GW', 'GY', 'HT', 'HN', 'HK', 'IN', 'ID', 'IR', 'IQ', 'IL', 'JM', 'JP', 'JO', 'KE', 'KW', 'LB', 'LS', 'LR', 'LY', 'MG', 'MW', 'MY', 'MV', 'ML', 'MQ', 'MR', 'MU', 'YT', 'MX', 'MN', 'MS', 'MA', 'MZ', 'MM', 'NA', 'NP', 'NI', 'NE', 'NG', 'OM', 'PK', 'PS', 'PA', 'PY', 'PE', 'PH', 'PR', 'QA', 'CG', 'KR', 'RE', 'RW', 'KN', 'LC', 'MF', 'VC', 'SA', 'SN', 'SC', 'SL', 'SO', 'ZA', 'SS', 'LK', 'SD', 'SR', 'SZ', 'SY', 'ST', 'TW', 'TZ', 'TH', 'GM', 'TL', 'TG', 'TT', 'TN', 'TC', 'UG', 'AE', 'VI', 'UY', 'VU', 'VE', 'VN', 'EH', 'YE', 'ZM', 'ZW',
                ],
                'rates' => [
                    ['price' => 15.30, 'weight_from' => 0, 'weight_to' => 0.25],
                    ['price' => 19.98, 'weight_from' => 0.251, 'weight_to' => 0.5],
                    ['price' => 22.86, 'weight_from' => 0.501, 'weight_to' => 0.75],
                    ['price' => 26.04, 'weight_from' => 0.751, 'weight_to' => 1],
                    ['price' => 28.44, 'weight_from' => 1.001, 'weight_to' => 1.25],
                    ['price' => 20.12, 'weight_from' => 1.251, 'weight_to' => 1.5],
                    ['price' => 30.66, 'weight_from' => 1.501, 'weight_to' => 2],
                    ['price' => 35.10, 'weight_from' => 2.001, 'weight_to' => 3],
                    ['price' => 40.38, 'weight_from' => 3.001, 'weight_to' => 4],
                    ['price' => 46.95, 'weight_from' => 4.001, 'weight_to' => 5],
                    ['price' => 64.02, 'weight_from' => 5.001, 'weight_to' => 6],
                    ['price' => 64.02, 'weight_from' => 6.001, 'weight_to' => 7],
                    ['price' => 74.16, 'weight_from' => 7.001, 'weight_to' => 8],
                    ['price' => 74.16, 'weight_from' => 8.001, 'weight_to' => 9],
                    ['price' => 74.16, 'weight_from' => 9.001, 'weight_to' => 10],
                ],
            ],
            [
                'name' => 'W2',
                'countries' => [
                    'AU', 'PW', 'IO', 'CX', 'CX', 'CC', 'CK', 'FJ', 'PF', 'KI', 'MO', 'NR', 'NC', 'AQ', 'NZ', 'NU', 'NF', 'PG', 'LA', 'PN', 'SG', 'SB', 'TK', 'TO', 'TV', 'AS', 'WS',
                ],
                'rates' => [
                    ['price' => 16.38, 'weight_from' => 0, 'weight_to' => 0.25],
                    ['price' => 21.78, 'weight_from' => 0.251, 'weight_to' => 0.5],
                    ['price' => 25.14, 'weight_from' => 0.501, 'weight_to' => 0.75],
                    ['price' => 28.92, 'weight_from' => 0.751, 'weight_to' => 1],
                    ['price' => 31.86, 'weight_from' => 1.001, 'weight_to' => 1.25],
                    ['price' => 34.86, 'weight_from' => 1.251, 'weight_to' => 1.5],
                    ['price' => 36.12, 'weight_from' => 1.501, 'weight_to' => 2],
                    ['price' => 40.92, 'weight_from' => 2.001, 'weight_to' => 3],
                    ['price' => 45.96, 'weight_from' => 3.001, 'weight_to' => 4],
                    ['price' => 51.24, 'weight_from' => 4.001, 'weight_to' => 5],
                    ['price' => 64.44, 'weight_from' => 5.001, 'weight_to' => 6],
                    ['price' => 64.44, 'weight_from' => 6.001, 'weight_to' => 7],
                    ['price' => 72.00, 'weight_from' => 7.001, 'weight_to' => 8],
                    ['price' => 72.00, 'weight_from' => 8.001, 'weight_to' => 9],
                    ['price' => 72.00, 'weight_from' => 9.001, 'weight_to' => 10],
                ],
            ],
            [
                'name' => 'W3',
                'countries' => [
                    'US',
                ],
                'rates' => [
                    ['price' => 17.28, 'weight_from' => 0, 'weight_to' => 0.25],
                    ['price' => 23.76, 'weight_from' => 0.251, 'weight_to' => 0.5],
                    ['price' => 26.16, 'weight_from' => 0.501, 'weight_to' => 0.75],
                    ['price' => 30.50, 'weight_from' => 0.751, 'weight_to' => 1],
                    ['price' => 34.68, 'weight_from' => 1.001, 'weight_to' => 1.25],
                    ['price' => 37.98, 'weight_from' => 1.251, 'weight_to' => 1.5],
                    ['price' => 38.58, 'weight_from' => 1.501, 'weight_to' => 2],
                    ['price' => 40.08, 'weight_from' => 2.001, 'weight_to' => 3],
                    ['price' => 47.04, 'weight_from' => 3.001, 'weight_to' => 4],
                    ['price' => 54.00, 'weight_from' => 4.001, 'weight_to' => 5],
                    ['price' => 73.20, 'weight_from' => 5.001, 'weight_to' => 6],
                    ['price' => 73.20, 'weight_from' => 6.001, 'weight_to' => 7],
                    ['price' => 91.20, 'weight_from' => 7.001, 'weight_to' => 8],
                    ['price' => 91.20, 'weight_from' => 8.001, 'weight_to' => 9],
                    ['price' => 91.20, 'weight_from' => 9.001, 'weight_to' => 10],
                ],
            ],
        ];

        $zones_royal_mail = [];
        $rates_royal_mail = [];

        foreach ($_zones_royal_mail as $zone_royal_mail) {
            if (isset($zone_royal_mail['countries']) && !empty($zone_royal_mail['countries'])) {
                $zones_royal_mail[] = [
                    'code' => $zone_royal_mail['name'],
                    'is_active' => true,
                    'configuration' => [
                        'shipping_countries' => $zone_royal_mail['countries'],
                    ],
                ];
            }
            if (isset($zone_royal_mail['rates']) && !empty($zone_royal_mail['rates'])) {
                foreach ($zone_royal_mail['rates'] as $rate) {
                    $rates_royal_mail[] = [
                        'price' => floatval($rate['price']) / 1.2,
                        'is_active' => true,
                        'configuration' => [
                            'count_from' => 0,
                            'count_to' => 999999999,
                            'price_from' => 0,
                            'price_to' => 999999999,
                            'weight_from' => $rate['weight_from'],
                            'weight_to' => $rate['weight_to'],
                            'shipping_zones' => [
                                $zone_royal_mail['name'],
                            ],
                        ],
                    ];
                }
            }
        }

        $shipping = [
            [
                'name' => 'Collection',
                'code' => 'collection',
                'description' => 'Collect from <a href="https://vektor.co.uk/contact/" target="_blank" class="text-primary">vektor</a>',
                'configuration' => [
                    'onecrm_shipping_provider_id' => 'f165f9e8-c364-7f2b-1282-646783251679',
                ],
                'is_active' => true,
                'zones' => [],
                'rates' => [
                    [
                        'price' => 0,
                        'is_active' => true,
                        'configuration' => [
                            'count_from' => 0,
                            'count_to' => 999999999,
                            'price_from' => 0,
                            'price_to' => 999999999,
                            'shipping_countries' => [
                                'GB',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Royal Mail',
                'code' => 'royal_mail',
                'description' => '3 to 5 working days',
                'configuration' => [
                    'onecrm_shipping_provider_id' => 'c86a2c23-27fd-14b3-848a-49f773bc527c',
                ],
                'is_active' => true,
                'zones' => $zones_royal_mail,
                'rates' => $rates_royal_mail,
            ],
            [
                'name' => 'DPD',
                'code' => 'dpd',
                'description' => '1 to 2 working days',
                'configuration' => [
                    'onecrm_shipping_provider_id' => 'f3219a4a-670d-74ea-c06a-646783ae5bf7',
                ],
                'is_active' => true,
                'zones' => [],
                'rates' => [
                    [
                        'price' => 10,
                        'is_active' => true,
                        'configuration' => [
                            'count_from' => 0,
                            'count_to' => 999999999,
                            'price_from' => 0,
                            'price_to' => 999999999,
                            'shipping_countries' => [
                                'GB',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($shipping as $shipping_item) {
            if (!empty($shipping_item['rates'])) {
                $_shipping_method = array_filter($shipping_item, function ($shipping_item_key) {
                    return !in_array($shipping_item_key, ['zones', 'rates']);
                }, ARRAY_FILTER_USE_KEY);

                $shipping_method = ShippingMethod::create($_shipping_method);

                foreach ($shipping_item['rates'] as $_shipping_rate_key => $_shipping_rate) {
                    $_shipping_rate['shipping_method_id'] = $shipping_method->id;
                    $_shipping_rate['code'] = $_shipping_rate_key + 1;
                    $shipping_rate = ShippingRate::create($_shipping_rate);
                }

                if (!empty($shipping_item['zones'])) {
                    foreach ($shipping_item['zones'] as $_shipping_zone_key => $_shipping_zone) {
                        $_shipping_zone['shipping_method_id'] = $shipping_method->id;
                        $shipping_zone = ShippingZone::create($_shipping_zone);
                    }
                }
            }
        }
    }
}
