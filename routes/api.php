<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    Route::middleware(['auth:sanctum', 'throttle:5,1'])->group(function () {
        Route::resource('posts', PostController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::resource('comments', CommentController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy']);
    });
});

Route::prefix('')->group(function () {
    Route::resource('anggota', \App\Http\Controllers\AnggotaController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::resource('peminjaman', \App\Http\Controllers\PeminjamanController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::resource('pembayaran', \App\Http\Controllers\PembayaranController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
});
