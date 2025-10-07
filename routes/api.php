<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\UserController;
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

// Public routes (no authentication needed)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes (user must be logged in with a valid token from Laravel Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // User Profile & Earnings
    Route::get('/user/profile', [UserController::class, 'getProfile']);
    Route::get('/user/earnings', [UserController::class, 'getEarnings']);
    Route::get('/user/withdrawal-history', [UserController::class, 'getWithdrawalHistory']);
    Route::post('/user/withdrawal', [UserController::class, 'requestWithdrawal']);

    // Videos
    Route::get('/videos', [VideoController::class, 'index']); // Get all videos
    Route::get('/videos/{video}', [VideoController::class, 'show']); // Get a specific video
    Route::post('/videos/{video}/watch', [VideoController::class, 'recordWatch']); // Record video watch

    // Ads
    Route::post('/ads/watch', [VideoController::class, 'recordAdWatch']); // Record ad watch
});
