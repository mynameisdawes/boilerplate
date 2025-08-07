<?php

use Vektor\Events\Http\Controllers\Api\EventController;

Route::prefix('api')->middleware(['web', 'api_csrf'])->group(function () {
    Route::post('events/past', [EventController::class, 'past'])->name('api.events.past');
    Route::post('events/upcoming', [EventController::class, 'upcoming'])->name('api.events.upcoming');
});
