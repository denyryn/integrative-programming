<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [App\Http\Controllers\AuthController::class, 'register']);

    Route::post('login', [App\Http\Controllers\AuthController::class, 'login']);
    Route::post('logout', [App\Http\Controllers\AuthController::class, 'logout']);

    Route::get('me', function () {
        return response()->json(auth()->user());
    })->middleware('auth:sanctum');
});