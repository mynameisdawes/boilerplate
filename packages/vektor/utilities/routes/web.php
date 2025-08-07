<?php

use Vektor\Utilities\Http\Controllers\Api\CountriesController;

Route::group(['prefix' => 'api', 'middleware' => ['web', 'api_csrf']], function () {
    Route::get('countries', [CountriesController::class, 'index'])->name('api.countries.index');
});
