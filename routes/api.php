<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

// authentication controller
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);


// for authenticated users
Route::group(['middleware' => 'api', 'prefix' => 'auth'], function () {
    // user profile
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::delete('profile/delete', [AuthController::class, 'deleteProfile']);
    // cart
    Route::post('cart/add', [CartController::class, 'createCart']);
    Route::get('carts', [CartController::class, 'getCarts']);
    Route::put('cart/{id}', [CartController::class, 'updateCart']);
    Route::delete('cart/{id}', [CartController::class, 'destroyCart']);
    // order
    Route::post('create_order', [OrderController::class, 'createOrder']);
    Route::get('get_orders', [OrderController::class, 'getOrders']);
    Route::get('get_order/{id}', [OrderController::class, 'getSingleOrder']);
    Route::post('cancel_order/{id}', [OrderController::class, 'cancelOrder']);

    // rating
    Route::post('rate_product', [RatingController::class, 'addRating']);
    Route::delete('delete_rating', [RatingController::class, 'destroyRating']);
});

// category controller
Route::controller(CategoryController::class)->group(function () {
    Route::get('categories', 'allCatory');
    Route::post('category', 'saveCategory');
    Route::delete('category/{id}', 'destroy');
    // Route::get('product/category/{id}', 'getCategoryProduct');
});

// product controller
Route::controller(ProductController::class)->group(function () {
    Route::get('products', 'getProducts');
    Route::post('product_create', 'createProduct');
    Route::get('product/{id}', 'getProduct');
    Route::delete('product/{id}', 'destroy');
    Route::get('product/category/{id}', 'getProductByCategory');
});
