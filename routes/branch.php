<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BranchPanel\LoginController;
use App\Http\Controllers\BranchPanel\DashboardController;
use App\Http\Controllers\BranchPanel\ProductController;
use App\Http\Middleware\SetAdminLocale;

Route::group(['prefix' => 'branch-panel', 'middleware' => [SetAdminLocale::class]], function () {

    Route::get('login', [LoginController::class, 'showLoginForm'])->name('branch.login');
    Route::post('login', [LoginController::class, 'login']);
    Route::post('logout', [LoginController::class, 'logout'])->name('branch.logout');

    Route::group(['middleware' => ['auth:branch']], function () {
        Route::get('/', [DashboardController::class, 'index'])->name('branch.dashboard');
        
        // Products, Meals, Boxes
        Route::get('/products', [ProductController::class, 'products'])->name('branch.products.index');
        Route::get('/meals', [ProductController::class, 'meals'])->name('branch.meals.index');
        Route::get('/boxes', [ProductController::class, 'boxes'])->name('branch.boxes.index');

        // Status update for products
        Route::post('/products/{productId}/toggle-status', [ProductController::class, 'toggleProductStatus'])->name('branch.products.toggle-status');
        Route::post('/products/{productId}/update-settings', [ProductController::class, 'updateProductSettings'])->name('branch.products.update-settings');
    });

});
