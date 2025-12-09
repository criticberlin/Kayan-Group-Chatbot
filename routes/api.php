<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Import API Routes
Route::middleware('auth:sanctum')->prefix('imports')->group(function () {
    Route::get('/', [App\Http\Controllers\API\ImportApiController::class, 'index']);
    Route::post('/', [App\Http\Controllers\API\ImportApiController::class, 'store']);
    Route::get('{import}', [App\Http\Controllers\API\ImportApiController::class, 'show']);
    Route::get('{import}/progress', [App\Http\Controllers\API\ImportApiController::class, 'progress']);
    Route::post('{import}/reprocess', [App\Http\Controllers\API\ImportApiController::class, 'reprocess']);
    Route::delete('{import}', [App\Http\Controllers\API\ImportApiController::class, 'destroy']);
    Route::get('{import}/failed-rows', [App\Http\Controllers\API\ImportApiController::class, 'failedRows']);
});

// Chat API Routes
Route::prefix('chat')->group(function () {
    Route::post('send', [App\Http\Controllers\Api\ChatController::class, 'sendMessage']);
    Route::get('history', [App\Http\Controllers\Api\ChatController::class, 'getHistory']);
});

// n8n Webhook Routes (no authentication - secured by HMAC signature)
Route::prefix('webhooks/n8n')->group(function () {
    Route::post('callback', [App\Http\Controllers\Webhooks\N8nController::class, 'callback']);
    Route::get('health', [App\Http\Controllers\Webhooks\N8nController::class, 'health']);
    Route::post('{action}', [App\Http\Controllers\Webhooks\N8nController::class, 'custom']);
});
