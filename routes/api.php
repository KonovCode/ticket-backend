<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [\App\Auth\AuthService::class, 'registration']);
Route::post('/login', [\App\Auth\AuthService::class, 'login']);
Route::post('/logout', [\App\Auth\AuthService::class, 'logout']);
