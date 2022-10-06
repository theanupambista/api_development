<?php


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

Route::get('/test', function () {
    return 'hello world';
});
Route::resource('products', \App\Http\Controllers\Api\ProductController::class);
Route::post('register', [App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('forgot-password', [App\Http\Controllers\Api\PasswordResetController::class, 'sendPasswordResetEmail']);
Route::post('reset-password/{token}', [App\Http\Controllers\Api\PasswordResetController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::post('change-password', [\App\Http\Controllers\Api\AuthController::class, 'changePassword']);
    Route::get('user-data', [\App\Http\Controllers\Api\AuthController::class, 'userData']);
});
