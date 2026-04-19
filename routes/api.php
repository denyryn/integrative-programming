<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::resource('posts', App\Http\Controllers\PostController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::resource('comments', App\Http\Controllers\CommentController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
});