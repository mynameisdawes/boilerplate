<?php

use Illuminate\Support\Facades\Route;
use Vektor\PasswordlessAuth\Http\Controllers\PasswordlessController;

Route::middleware(['web', 'guest', 'passwordless_auth_module_enabled'])->group(function () {
    Route::get('register', [PasswordlessController::class, 'showRegistrationForm'])->name('passwordless.register');
    Route::get('login', [PasswordlessController::class, 'showLoginForm'])->name('passwordless.login');
    Route::get('login/{token}', [PasswordlessController::class, 'authenticateWithToken'])->name('passwordless.authenticate');
});

Route::group(['prefix' => 'api', 'middleware' => ['web', 'api_csrf', 'passwordless_auth_module_enabled']], function () {
    Route::post('register/passwordless', [Vektor\PasswordlessAuth\Http\Controllers\Api\PasswordlessController::class, 'register'])->name('api.passwordless.register');
    Route::post('login/passwordless', [Vektor\PasswordlessAuth\Http\Controllers\Api\PasswordlessController::class, 'login'])->name('api.passwordless.login');
});
