<?php

use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [\App\Auth\AuthService::class, 'registration']);
Route::post('/login', [\App\Auth\AuthService::class, 'login']);
Route::post('/logout', [\App\Auth\AuthService::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/show-ticket/{ticket_id}', [TicketController::class, 'show']);
    Route::middleware('abilities:ticket-crud')->group(function () {
        Route::post('/create-ticket', [TicketController::class, 'store']);
        Route::post('/update-ticket/{ticket_id}', [TicketController::class, 'update']);
        Route::post('/delete-ticket/{ticket_id}', [TicketController::class, 'delete']);
    });
});


