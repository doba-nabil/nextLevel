<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArmadaWebhookController;
use App\Http\Controllers\Api\TableTruncateController;
use App\Http\Controllers\Api\ImageCleanupController;
use App\Http\Controllers\Api\ProductImageUploadController;
use App\Http\Controllers\Api\ProductBranchController;
use App\Http\Controllers\Api\CacheController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

Route::prefix('webhooks')->group(function () {
    Route::post('armada/delivery', [ArmadaWebhookController::class, 'handleDeliveryUpdate']);
});

// Table truncate endpoint
Route::get('truncate', [TableTruncateController::class, 'truncate']);

// Image cleanup endpoint
Route::get('cleanup-images', [ImageCleanupController::class, 'cleanup']);

Route::post('products/link-images', [ProductImageUploadController::class, 'linkImagesFromFolder']);

Route::post('products/link-all-branches', [ProductBranchController::class, 'linkAllProductsToAllBranches']);
Route::get('cache/clear', [CacheController::class, 'clear']);
Route::get('cache/clear-categories', [CacheController::class, 'clearCategoriesCache']);
