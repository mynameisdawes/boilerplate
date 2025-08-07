<?php

use Vektor\Api\Api;

Route::group(['prefix' => 'api', 'middleware' => ['web']], function () {
    Route::post('token', [Api::class, 'generateToken'])->name('api.token');
});
