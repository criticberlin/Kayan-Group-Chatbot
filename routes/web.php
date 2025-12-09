<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});

// Import Management Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Chatbot Page
    Route::get('chatbot', [App\Http\Controllers\Admin\ChatbotPageController::class, 'index'])->name('chatbot');

    // Simple Products List (bypassing Voyager BREAD issues)
    Route::resource('products-list', App\Http\Controllers\Admin\ProductListController::class)
        ->only(['index', 'show', 'edit', 'update']);

    Route::resource('imports', App\Http\Controllers\Admin\ImportController::class);
    Route::post('imports/{import}/reprocess', [App\Http\Controllers\Admin\ImportController::class, 'reprocess'])->name('imports.reprocess');
    Route::post('imports/{import}/rollback', [App\Http\Controllers\Admin\ImportController::class, 'rollback'])->name('imports.rollback');
    Route::get('imports/{import}/download', [App\Http\Controllers\Admin\ImportController::class, 'download'])->name('imports.download');
    Route::get('imports/{import}/export-errors', [App\Http\Controllers\Admin\ImportController::class, 'exportErrors'])->name('imports.export-errors');
});
