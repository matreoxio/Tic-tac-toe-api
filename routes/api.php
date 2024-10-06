<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;

Route::middleware('api')->group(function () {
    Route::post('/games', [GameController::class, 'create']); // Create a game
    Route::post('/games/{gameId}/join', [GameController::class, 'join']); // Join a game
    Route::post('/games/{gameId}/move', [GameController::class, 'makeMove']); // Make a move
    Route::get('/games/{gameId}', [GameController::class, 'show']); // Get game status
});
