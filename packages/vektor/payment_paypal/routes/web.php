<?php

use Vektor\Paypal\Http\Controllers\Api\PaypalPaymentController;

Route::group(['prefix' => 'api', 'middleware' => ['web', 'api_csrf', 'shop_module_enabled', 'paypal_module_enabled']], function () {
    Route::post('paypal/create', [PaypalPaymentController::class, 'create'])->name('api.payment_paypal.create');
    Route::post('paypal/execute', [PaypalPaymentController::class, 'execute'])->name('api.payment_paypal.execute');
});
