<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api'], function ($router) {

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    Route::group(['middleware' => ['jwt.auth']], function ($router) {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    Route::group(['prefix' => 'admin', 'middleware' => 'userType:admin'], function ($type) {
        Route::Resource('users', UserController::class);
        Route::Resource('categories', CategoryController::class);
        Route::Resource('shops', ShopController::class)->except(['store']);
        Route::Resource('products', ProductController::class)->except(['store']);
        Route::Resource('product-images', ProductImageController::class)->except(['store', 'reorder']);
        Route::post('rates/{rate}/report', [RateController::class, 'report']);
        Route::Resource('rates', RateController::class);
    });

    Route::group(['prefix' => 'seller', 'middleware' => 'userType:seller'], function ($type) {
        Route::Resource('users', UserController::class)->only(['update', 'destroy']);
        Route::Resource('shops', ShopController::class);
        Route::get('products', [ProductController::class, 'index']);  //index-ը անտեր ա մնում
        Route::Resource('shops.products', ProductController::class);
        Route::Resource('categories', CategoryController::class)->only(['index', 'show']);
        Route::post('products.product-images', [ProductImageController::class, 'reorder']);
        Route::Resource('products.product-images', ProductImageController::class);
        Route::post('rates/{rate}/report', [RateController::class, 'report']);
        Route::Resource('rates', RateController::class)->only(['index', 'show', 'report']);
    });

    Route::group(['middleware' => 'userType:buyer'], function ($type) {
        Route::Resource('users', UserController::class)->only(['update', 'destroy']);
        Route::Resource('shops', ShopController::class)->only(['index', 'show']);
        Route::Resource('products', ProductController::class)->only(['index', 'show']);
        Route::Resource('orders.rates', RateController::class)->except(['destroy', 'report']);
        Route::Resource('rates', RateController::class)->except(['destroy', 'report']);
        Route::Resource('categories', CategoryController::class)->only(['index', 'show']);
        Route::post('cart-items/checkout', [CartItemController::class, 'checkout']);
        Route::Resource('cart-items', CartItemController::class);
        Route::Resource('product-images', ProductImageController::class)->only(['index']);
    });
});
