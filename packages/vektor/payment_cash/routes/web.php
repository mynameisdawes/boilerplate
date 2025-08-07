<?php

use Vektor\Cash\Http\Controllers\Api\CashPaymentController;

Route::group(['prefix' => 'api', 'middleware' => ['web', 'api_csrf', 'shop_module_enabled', 'cash_module_enabled']], function () {
    Route::post('cash/pay', [CashPaymentController::class, 'handle'])->name('api.payment_cash.pay');
});
