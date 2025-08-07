<?php

use Vektor\Shop\Http\Controllers\Api\CustomerController;
use Vektor\Shop\Http\Controllers\Api\DiscountController;
use Vektor\Shop\Http\Controllers\Api\DiscountPromoController;
use Vektor\Shop\Http\Controllers\Api\ProductAttributeController;
use Vektor\Shop\Http\Controllers\Api\ShippingMethodController;
use Vektor\Shop\Http\Controllers\AssetController;
use Vektor\Shop\Http\Controllers\BuilderController;
use Vektor\Shop\Http\Controllers\CartController;
use Vektor\Shop\Http\Controllers\CheckoutController;
use Vektor\Shop\Http\Controllers\ProductController;
use Vektor\Shop\Http\Controllers\QuoteController;
use Vektor\Shop\Http\Controllers\SuccessController;

Route::group(['middleware' => ['web']], function () {
    Route::get('product_images/{base_dir}/{filename?}', [AssetController::class, 'product_images'])->name('shop.product_images.product_images');

    Route::get('preview/{model_id}/{design}/{area}/original', [BuilderController::class, 'original'])->name('crm.preview.original');
    Route::get('preview/{model_id}/{design}/{area}/resized', [BuilderController::class, 'resized'])->name('crm.preview.resized');
    Route::get('preview/{model_id}/{design?}/{variant?}/{area?}', [BuilderController::class, 'preview'])->name('crm.preview');

    Route::get('quote/{quote_id}', [QuoteController::class, 'preview'])->name('checkout_quote.preview');
});

Route::group(['middleware' => ['web', 'shop_module_enabled', 'shop_requires_auth']], function () {
    Route::get('shop', [ProductController::class, 'index'])->name('shop.product.index');
    Route::get('shop/{product_type}', [ProductController::class, 'product_type_index'])->name('shop.product.product_type_index');
    Route::get('product/{slug}/{customisation}', [ProductController::class, 'show'])->name('shop.product.edit');
    Route::get('product/{slug}', [ProductController::class, 'show'])->name('shop.product.show');

    Route::get('cart', [CartController::class, 'index'])->name('shop.cart.index');
    Route::get('checkout', [CheckoutController::class, 'index'])->name('shop.checkout.index');
    Route::post('success', [SuccessController::class, 'index'])->name('shop.success.index');
    Route::get('success', [SuccessController::class, 'redirect'])->name('shop.checkout.redirect');

    Route::get('quote/{quote_id}/checkout', [QuoteController::class, 'checkout'])->name('checkout_quote.checkout');

    Route::group(['prefix' => 'webhook'], function () {
        Route::post('quote/email', [QuoteController::class, 'email'])->name('checkout_quote.email_webhook');
    });
});

Route::group(['prefix' => 'api', 'middleware' => ['web', 'shop_module_enabled', 'shop_requires_auth']], function () {
    Route::post('upload/builder', [Vektor\Shop\Http\Controllers\Api\BuilderController::class, 'handleFileUpload']);
    Route::delete('upload/builder', [Vektor\Shop\Http\Controllers\Api\BuilderController::class, 'handleFileDelete']);
});

Route::group(['prefix' => 'api', 'middleware' => ['web', 'api_csrf', 'shop_module_enabled', 'shop_requires_auth']], function () {
    Route::post('products/attributes', [ProductAttributeController::class, 'index'])->name('api.product_attributes.index');
    Route::post('products', [Vektor\Shop\Http\Controllers\Api\ProductController::class, 'index'])->name('api.products.index');
    Route::get('products', [Vektor\Shop\Http\Controllers\Api\ProductController::class, 'index'])->name('api.products.index');
    Route::get('carts', [Vektor\Shop\Http\Controllers\Api\CartController::class, 'fetchSavedCarts'])->name('api.cart.fetch_saved_carts');
    Route::get('cart/store/{instance?}', [Vektor\Shop\Http\Controllers\Api\CartController::class, 'restoreFromDb'])->name('api.cart.restore_from_db');
    Route::post('cart/store/{instance?}', [Vektor\Shop\Http\Controllers\Api\CartController::class, 'storeToDb'])->name('api.cart.store_to_db');
    Route::get('cart/{instance?}', [Vektor\Shop\Http\Controllers\Api\CartController::class, 'index'])->name('api.cart.index');
    Route::post('cart/{instance?}', [Vektor\Shop\Http\Controllers\Api\CartController::class, 'store'])->name('api.cart.store');
    Route::put('cart/{row_id}/{instance?}', [Vektor\Shop\Http\Controllers\Api\CartController::class, 'update'])->name('api.cart.update');
    Route::delete('cart/{row_id}/{instance?}', [Vektor\Shop\Http\Controllers\Api\CartController::class, 'remove'])->name('api.cart.remove');
    Route::delete('cart/{instance?}', [Vektor\Shop\Http\Controllers\Api\CartController::class, 'destroy'])->name('api.cart.destroy');
    Route::post('discount/{instance?}', [DiscountController::class, 'apply'])->name('api.discount.apply');
    Route::delete('discount/{instance?}', [DiscountController::class, 'cancel'])->name('api.discount.cancel');
    Route::post('customer/email', [CustomerController::class, 'show'])->name('api.customer.show_by_email');
    Route::post('shipping_methods', [ShippingMethodController::class, 'index'])->name('api.shipping_methods.index');
    Route::post('checkout/can/{instance?}', [Vektor\Shop\Http\Controllers\Api\CheckoutController::class, 'can'])->name('api.checkout.can');
    Route::post('quote/create', [Vektor\Shop\Http\Controllers\Api\QuoteController::class, 'handle'])->name('api.checkout_quote.create');
    Route::post('quote/{quote_id}', [Vektor\Shop\Http\Controllers\Api\QuoteController::class, 'show'])->name('api.checkout_quote.show');
    Route::get('user/addresses', [Vektor\Shop\Http\Controllers\Api\UserAddressController::class, 'index'])->name('api.user.addresses.index');
    Route::post('user/addresses/create', [Vektor\Shop\Http\Controllers\Api\UserAddressController::class, 'create'])->name('api.user.addresses.create');
    Route::post('user/addresses/update', [Vektor\Shop\Http\Controllers\Api\UserAddressController::class, 'update'])->name('api.user.addresses.update');
    Route::post('user/addresses/delete', [Vektor\Shop\Http\Controllers\Api\UserAddressController::class, 'destroy'])->name('api.user.addresses.delete');
});

Route::group(['prefix' => 'api', 'middleware' => ['web', 'api_csrf']], function () {
    Route::post('discount_promo/register', [DiscountPromoController::class, 'register'])->name('api.discount_promo.register');
});
