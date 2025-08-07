<?php

use App\Http\Controllers\Api;
use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Mail\Markdown;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware([
    'shop_requires_auth',
    // 'shop_requires_verified'
])->group(function () {
    Route::get('/', [App\Http\Controllers\PageController::class, 'base'])->name('base');
});

Route::get('favicon', [App\Http\Controllers\AssetController::class, 'favicon'])->name('favicon');
Route::get('favicon_type', [App\Http\Controllers\AssetController::class, 'favicon_type'])->name('favicon_type');
Route::get('logo', [App\Http\Controllers\AssetController::class, 'logo'])->name('logo');
Route::get('email_logo', [App\Http\Controllers\AssetController::class, 'email_logo'])->name('email_logo');

// Route::get('terms', [App\Http\Controllers\PageController::class, 'terms'])->name('terms');
// Route::get('policy', [App\Http\Controllers\PageController::class, 'policy'])->name('policy');

Route::middleware(['shop_only_disabled'])->group(function () {
    Route::get('about', [App\Http\Controllers\PageController::class, 'about'])->name('about');
    Route::get('tabs', [App\Http\Controllers\PageController::class, 'tabs'])->name('tabs');
    Route::get('article', [App\Http\Controllers\PageController::class, 'article'])->name('article');
    Route::get('map', [App\Http\Controllers\PageController::class, 'map'])->name('map');
    Route::get('contact', [App\Http\Controllers\PageController::class, 'contact'])->name('contact');
    Route::get('cards', [App\Http\Controllers\PageController::class, 'cards'])->name('cards');
});

Route::get('register', [Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::get('login', [Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::get('password/reset', [Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::get('password/email', [Auth\ForgotPasswordController::class, 'showCheckEmailForm'])->name('password.email');
Route::get('password/reset/{token}', [Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::middleware(['auth'])->group(function () {
    Route::get('logout', [Auth\LoginController::class, 'logout'])->name('logout');
});

// Route::middleware(['auth'])->group(function () {
//     Route::get('verify-email', Auth\EmailVerificationPromptController::class)->name('verification.notice');
//     Route::get('verify-email/{id}/{hash}', Auth\VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
//     Route::post('email/verification-notification', [Auth\EmailVerificationNotificationController::class, 'store'])->middleware(['throttle:6,1'])->name('verification.send');
// });

Route::prefix('api')->group(function () {
    Route::post('files', [Api\FileUploadController::class, 'nova_upload'])->name('api.files.nova_upload');
    Route::post('upload', [Api\FileUploadController::class, 'standard_upload'])->name('api.files.upload');
    Route::delete('upload', [Api\FileUploadController::class, 'delete_standard_upload'])->name('api.files.delete');

    Route::post('markers', [Api\MarkerController::class, 'handle'])->name('api.markers.index');
    Route::post('locations/geocode/place', [Api\LocationController::class, 'geocode_place'])->name('api.locations.geocode.place');
    Route::post('locations/autocomplete/places', [Api\LocationController::class, 'autocomplete_places'])->name('api.locations.autocomplete.places');
});

Route::prefix('api')->middleware(['api_csrf'])->group(function () {
    Route::get('/', function () {})->name('api.base');

    Route::post('register', [Auth\RegisterController::class, 'register'])->name('api.register');
    Route::post('exists', [Auth\LoginController::class, 'exists'])->name('api.exists');
    Route::post('matches', [Auth\LoginController::class, 'matches'])->name('api.matches');
    Route::post('verify', [Auth\LoginController::class, 'verify'])->name('api.verify');
    Route::post('login', [Auth\LoginController::class, 'login'])->name('api.login');
    Route::get('login', [Auth\LoginController::class, 'isLoggedIn']);
    Route::post('password/email', [Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('api.password.email');
    Route::post('password/reset', [Auth\ResetPasswordController::class, 'reset'])->name('api.password.update');

    Route::post('contact', [Api\ContactController::class, 'handle'])->name('api.contact.submit');
});

// Route::get('test/mail', function () {
//     $markdown = new Markdown(view(), config('mail.markdown'));
//     return $markdown->render('emails.contact.created', [ 'data'=> [] ]);
// });
