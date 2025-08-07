<?php

use Vektor\OneCRM\Http\Controllers\DashboardController;
use Vektor\OneCRM\Http\Controllers\DashboardOrderController;

Route::middleware([
    'web', 'auth', 'onecrm_module_enabled',
    // 'verified'
])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.onecrm.index');
    Route::get('dashboard/orders', [DashboardOrderController::class, 'index'])->name('dashboard.onecrm.orders.index');
    Route::get('dashboard/orders/{id}', [DashboardOrderController::class, 'show'])->name('dashboard.onecrm.orders.show');
});

Route::group(['prefix' => 'api', 'middleware' => ['web', 'api_csrf']], function () {
    Route::group(['prefix' => 'onecrm'], function () {
        Route::get('account', [Vektor\OneCRM\Http\Controllers\Api\DashboardController::class, 'show'])->name('api.onecrm.personal_details.show');
        Route::put('account', [Vektor\OneCRM\Http\Controllers\Api\DashboardController::class, 'update'])->name('api.onecrm.personal_details.update');

        Route::post('orders', [Vektor\OneCRM\Http\Controllers\Api\DashboardOrderController::class, 'index'])->name('api.onecrm.orders.index');
        Route::post('orders/{id}', [Vektor\OneCRM\Http\Controllers\Api\DashboardOrderController::class, 'show'])->name('api.onecrm.orders.show');
    });
});
