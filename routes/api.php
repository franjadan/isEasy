<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{StoreController};

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

Route::middleware('api')->group(function () {
    Route::prefix('/tiendas')->group(function () {
        Route::get('/', [StoreController::class, 'index']);
        Route::get('/{store}', [StoreController::class, 'show']);
        Route::post('/', [StoreController::class, 'store']);
        Route::put('/{store}', [StoreController::class, 'update']);
        Route::delete('/{store}', [StoreController::class, 'destroy']);

        /*
            Las rutas se podrían resumir en Route::resource('stores', StoreController::class);
        */

        Route::post('/{store}/vender/{product}', [StoreController::class, 'sell']);

    });
});