<?php

use Vektor\Blog\Http\Controllers\PostCategoryController;
use Vektor\Blog\Http\Controllers\PostController;
use Vektor\Blog\Http\Controllers\PostTagController;

Route::prefix('api')->middleware(['web', 'api_csrf'])->group(function () {
    Route::post('posts', [Vektor\Blog\Http\Controllers\Api\PostController::class, 'index'])->name('api.posts.index');
});

Route::prefix('blog')->middleware(['web'])->group(function () {
    // Route::get('tags', [PostTagController::class, 'index'])->name('post_tags.index');
    Route::get('tags/{slug}', [PostTagController::class, 'show'])->name('post_tags.show');
    // Route::get('categories', [PostCategoryController::class, 'index'])->name('post_categories.index');
    Route::get('categories/{category_path}', [PostCategoryController::class, 'show'])->where('category_path', '.*')->name('post_categories.show');
    Route::get('{slug}', [PostController::class, 'show'])->name('posts.show');
    Route::get('{category_path}/{slug}', [PostController::class, 'show'])->where('category_path', '.*')->name('posts.show');
});
