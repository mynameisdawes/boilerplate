<?php

use Vektor\Pages\Http\Controllers\PageController;

Route::group(['middleware' => ['web']], function () {
    Route::get('{slug}', [PageController::class, 'show'])->name('pages.show');
});
