<?php

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here is where you can register tenant-specific routes for your module.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Ingenius\Coins\Http\Controllers\CoinsController;

Route::middleware([
    'api',
    'tenant.user',
])->prefix('api')->group(function () {
    Route::prefix('coins')->group(function () {
        Route::get('/', [CoinsController::class, 'index'])->middleware('tenant.has.feature:list-coins');
        Route::post('/', [CoinsController::class, 'store'])->middleware(['auth:sanctum', 'tenant.has.feature:create-coin']);
        Route::get('/{coin}', [CoinsController::class, 'show'])->middleware('tenant.has.feature:view-coin');
        Route::put('/{coin}', [CoinsController::class, 'update'])->middleware(['auth:sanctum', 'tenant.has.feature:update-coin']);
        Route::delete('/{coin}', [CoinsController::class, 'destroy'])->middleware(['auth:sanctum', 'tenant.has.feature:delete-coin']);
        Route::patch('/{coin}/set-main', [CoinsController::class, 'setMain'])->middleware(['auth:sanctum', 'tenant.has.feature:set-main-coin']);
    });
});

// Route::get('tenant-example', function () {
//     return 'Hello from tenant-specific route! Current tenant: ' . tenant('id');
// });