<?php

use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [\App\Auth\AuthService::class, 'registration']);
Route::post('/login', [\App\Auth\AuthService::class, 'login']);
Route::post('/logout', [\App\Auth\AuthService::class, 'logout']);

//Route::get('/tickets', [TicketController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/show-ticket/{ticket_id}', [TicketController::class, 'show']);
    Route::post('/purchase-ticket/{ticket_id}', [TicketOrderController::class, 'purchase']);
    Route::post('/confirm-payment/ticket-order/{order_id}', [TicketOrderController::class, 'paymentCallback']);
    Route::middleware('abilities:ticket-crud')->group(function () {
        Route::post('/create-ticket', [TicketController::class, 'store']);
        Route::post('/update-ticket/{ticket_id}', [TicketController::class, 'update']);
        Route::post('/delete-ticket/{ticket_id}', [TicketController::class, 'delete']);
    });
});


