<?php

use Vektor\Account\Http\Controllers\Api\AccountPaymentController;

Route::group(['prefix' => 'api', 'middleware' => ['web', 'api_csrf', 'shop_module_enabled', 'account_module_enabled']], function () {
    Route::post('account/pay', [AccountPaymentController::class, 'handle'])->name('api.payment_account.pay');
});
