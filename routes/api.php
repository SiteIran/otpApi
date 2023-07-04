<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\Score\ScoreController;

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

//Route::post('/register', [AuthController::class, 'register']);
Route::post('/authmobile', [AuthController::class, 'authMobile']);
Route::post('/verifymobile', [AuthController::class, 'verifyMobile']);

Route::post('/authemail', [AuthController::class, 'authEmail']);
Route::post('/verifyemail', [AuthController::class, 'verifyEmail']);

Route::get('/user-info', [AuthController::class, 'getUserInfo']);

// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
/*    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);*/
    //Show all settings records
    Route::get('/settings', [SettingController::class, 'index']);

    //Logout all token from database
    Route::post('/logout', [AuthController::class, 'logout']);
});



Route::prefix('scores')->group(function () {
    Route::get('/', [ScoreController::class, 'index']); // لیست کردن همه امتیازها
    Route::post('/', [ScoreController::class, 'store']); // ایجاد یک امتیاز جدید
    Route::get('/{id}', [ScoreController::class, 'show']); // نمایش جزئیات یک امتیاز با شناسه مشخص
    Route::put('/{id}', [ScoreController::class, 'update']); // به‌روزرسانی یک امتیاز با شناسه مشخص
    Route::delete('/{id}', [ScoreController::class, 'destroy']); // حذف یک امتیاز با شناسه مشخص
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
