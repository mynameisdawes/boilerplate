<?php

use Vektor\PurchaseOrder\Http\Controllers\Api\PurchaseOrderPaymentController;

Route::group(['prefix' => 'api', 'middleware' => ['web', 'api_csrf', 'shop_module_enabled', 'purchase_order_module_enabled']], function () {
    Route::post('purchase_order/pay', [PurchaseOrderPaymentController::class, 'handle'])->name('api.payment_purchase_order.pay');
});
