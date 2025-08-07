<?php

use Vektor\Shop\Calculation\DefaultCalculator;

return [
    'enabled' => env('SHOP_ENABLED', false),
    'h1' => env('SHOP_H1', 'Shop'),
    'intro' => env('SHOP_INTRO'),
    'as_base' => env('SHOP_AS_BASE', false),
    'only' => env('SHOP_ONLY', false),
    'single_product_slug' => env('SHOP_SINGLE_PRODUCT_SLUG'),
    'requires_auth' => env('SHOP_REQUIRES_AUTH', false),
    'minimum_country_qty' => env('SHOP_MINIMUM_COUNTRY_QTY'),
    'filters' => [
        'enabled' => env('SHOP_FILTERS_ENABLED', false),
    ],
    'pagination' => [
        'enabled' => env('SHOP_PAGINATION_ENABLED', false),
        'per_pages' => env('SHOP_PAGINATION_PER_PAGES', '3,6'),
    ],
    'hide_pricing' => env('SHOP_HIDE_PRICING', false),
    'use_user_addresses' => env('SHOP_USE_USER_ADDRESSES', false),
    'billing_required' => env('SHOP_BILLING_REQUIRED', true),
    'shipping_required' => env('SHOP_SHIPPING_REQUIRED', true),
    'customer_unique' => env('SHOP_CUSTOMER_UNIQUE', false),
    'email_domain_check' => [
        'enabled' => env('SHOP_EMAIL_DOMAIN_CHECK_ENABLED', false),
        'list' => env('SHOP_EMAIL_DOMAIN_CHECK_LIST') ? str_replace(',', '|', str_replace(' ', '', env('SHOP_EMAIL_DOMAIN_CHECK_LIST'))) : null,
    ],
    'agree_terms' => env('SHOP_AGREE_TERMS', false),
    'agree_marketing' => env('SHOP_AGREE_MARKETING', false),
    'new_order_notification' => env('SHOP_NEW_ORDER_NOTIFICATION', true),
    'redirect_url' => env('SHOP_REDIRECT_URL', 'success'),

    /*
    |--------------------------------------------------------------------------
    | Gross price as base price
    |--------------------------------------------------------------------------
    |
    | This default value is used to select the method to calculate prices and taxes
    | If true the item price is managed as a gross price, so taxes will be calculated by separation/exclusion
    |
    */

    'calculator' => DefaultCalculator::class,

    /*
    |--------------------------------------------------------------------------
    | Default tax rate
    |--------------------------------------------------------------------------
    |
    | This default tax rate will be used when you make a class implement the
    | Taxable interface and use the HasTax trait.
    |
    */

    'tax' => 0,

    /*
    |--------------------------------------------------------------------------
    | Shoppingcart database settings
    |--------------------------------------------------------------------------
    |
    | Here you can set the connection that the shoppingcart should use when
    | storing and restoring a cart.
    |
    */

    'database' => [
        'connection' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Destroy the cart on user logout
    |--------------------------------------------------------------------------
    |
    | When this option is set to 'true' the cart will automatically
    | destroy all cart instances when the user logs out.
    |
    */

    'destroy_on_logout' => false,

    /*
    |--------------------------------------------------------------------------
    | Default number format
    |--------------------------------------------------------------------------
    |
    | This defaults will be used for the formatted numbers if you don't
    | set them in the method call.
    |
    */

    'format' => [
        'decimals' => 2,
        'decimal_point' => '.',
        'thousand_separator' => '',
    ],
];
