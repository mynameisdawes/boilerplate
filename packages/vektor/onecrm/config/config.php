<?php

return [
    'enabled' => env('ONECRM_ENABLED', false),
    'account_id' => env('ONECRM_ACCOUNT_ID'),
    'contact_id' => env('ONECRM_CONTACT_ID'),
    'product_category_id' => env('ONECRM_PRODUCT_CATEGORY_ID'),
    'shipping_related_id' => env('ONECRM_SHIPPING_RELATED_ID'),
    'shipping_mfr_part_no' => env('ONECRM_SHIPPING_MFR_PART_NO'),
    'shipping_custom_provider_id' => env('ONECRM_SHIPPING_CUSTOM_PROVIDER_ID'),
    'api_prefix' => env('ONECRM_PREFIX'),
    'client_id' => env('ONECRM_ID'),
    'client_secret' => env('ONECRM_SECRET'),
    'username' => env('ONECRM_USERNAME'),
    'password' => env('ONECRM_PASSWORD'),
    'on_order' => [
        'create' => [
            'accounts' => env('ONECRM_ONORDER_CREATE_ACCOUNTS', false),
            'contacts' => env('ONECRM_ONORDER_CREATE_CONTACTS', false),
            'sales_orders' => env('ONECRM_ONORDER_CREATE_SALES_ORDERS', false),
            'shipping' => env('ONECRM_ONORDER_CREATE_SHIPPING', false),
            'invoices' => env('ONECRM_ONORDER_CREATE_INVOICES', false),
            'payments' => env('ONECRM_ONORDER_CREATE_PAYMENTS', false),
            'tasks' => env('ONECRM_ONORDER_CREATE_TASKS', false),
        ],
    ],
];
