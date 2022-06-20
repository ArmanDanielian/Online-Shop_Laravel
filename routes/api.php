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

Route::group(['middleware' => 'api'], function () {

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    Route::group(['middleware' => ['jwt.auth']], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);


        Route::group(['prefix' => 'admin', 'middleware' => 'userType:admin'], function () {
            Route::apiResource('users', UserController::class);
            Route::apiResource('categories', CategoryController::class);
            Route::apiResource('shops', ShopController::class)->except(['store']);
            Route::apiResource('products', ProductController::class)->except(['store']);
            Route::apiResource('product-images', ProductImageController::class)
                ->parameters([
                    'product-images' => 'image'
                ])
                ->except(['store', 'reorder']);
            Route::patch('rates/{rate}/report', [RateController::class, 'report']);
            Route::apiResource('rates', RateController::class)->except(['store', 'update']);
            Route::get('orders', [OrderController::class, 'index']);
        });

        Route::group(['prefix' => 'seller', 'middleware' => 'userType:seller'], function () {
            Route::apiResource('users', UserController::class)->only(['update', 'destroy']);
            Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
            Route::apiResource('shops', ShopController::class);
            Route::apiResource('shops.products', ProductController::class)->scoped();
            Route::post('products/{product}/product-images/reorder', [ProductImageController::class, 'reorder']);
            Route::apiResource('products.product-images', ProductImageController::class)->parameters([
                'product-images' => 'image'
            ])->scoped();
            Route::patch('rates/{rate}/report', [RateController::class, 'report']);
            Route::apiResource('rates', RateController::class)->only(['index', 'show']);
        });

        Route::group(['middleware' => 'userType:buyer'], function ($type) {
            Route::apiResource('users', UserController::class)->only(['update', 'destroy']);
            Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
            Route::apiResource('shops', ShopController::class)->only(['index', 'show']);
            Route::apiResource('products', ProductController::class)->only(['index', 'show']);
            Route::apiResource('product-images', ProductImageController::class)
                ->parameters([
                    'product-images' => 'image'
                ])
                ->only(['index', 'show']);
            Route::apiResource('orders.rates', RateController::class)->except(['destroy', 'report']);
            Route::post('cart-items/checkout', [CartItemController::class, 'checkout']);
            Route::apiResource('cart-items', CartItemController::class);
            Route::get('orders', [OrderController::class, 'index']);
        });
    });
});
