<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('v1/register', [AuthController::class, 'store']);
Route::post('v1/verify-Email', [AuthController::class, 'verifyEmail']);
Route::post('v1/login', [AuthController::class, 'login']);
Route::post('v1/reset-password', [ForgotPasswordController::class, 'sendResetEmail']);
Route::post('v1/reset', [ForgotPasswordController::class, 'reset']);
