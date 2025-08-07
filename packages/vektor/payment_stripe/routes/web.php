<?php

use Vektor\Stripe\Http\Controllers\Api\CustomerController;
use Vektor\Stripe\Http\Controllers\Api\PaymentIntentController;
use Vektor\Stripe\Http\Controllers\Api\SetupIntentController;

Route::group(['prefix' => 'api', 'middleware' => ['web', 'api_csrf', 'shop_module_enabled', 'stripe_module_enabled']], function () {
    Route::post('stripe/setupintent', [SetupIntentController::class, 'setupIntent'])->name('api.payment_stripe.setup_intent');
    Route::post('stripe/paymentintent', [PaymentIntentController::class, 'paymentIntent'])->name('api.payment_stripe.payment_intent');

    Route::post('stripe/customercards', [CustomerController::class, 'getCustomerCards'])->name('api.payment_stripe.get_customers_cards');
    Route::delete('stripe/customercards', [CustomerController::class, 'deleteCustomerCards'])->name('api.payment_stripe.delete_customers_cards');

    Route::post('stripe/customer/create', [CustomerController::class, 'customerCreate'])->name('api.payment_stripe.customer_create');
});
