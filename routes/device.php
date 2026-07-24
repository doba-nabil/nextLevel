<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;

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
Route::group(['prefix' => 'device'], function () {
    Route::post('login', [DeviceController::class, 'login']);
    Route::get('orders', [DeviceController::class, 'orders']);
    Route::get('order/{id}', [DeviceController::class, 'order']);
    Route::get('products', [DeviceController::class, 'products']);
    Route::post('product/{id}', [DeviceController::class, 'product']);
    Route::post('delete_account', [DeviceController::class, 'delete_account']);
});
