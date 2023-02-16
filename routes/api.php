<?php

use App\Http\Controllers\Api\AuthController;
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

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function() {
    Route::get('show', [AuthController::class, 'show']);
    Route::put('update', [AuthController::class, 'update']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::delete('destroy', [AuthController::class, 'destroy']);
});
