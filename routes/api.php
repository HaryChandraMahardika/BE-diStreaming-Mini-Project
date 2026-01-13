<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\WatchlistController;
use App\Http\Controllers\Api\AuthController;




//Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/{id}', [MovieController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

//Protected Routes
Route::middleware('auth:sanctum')->group(function(){
    //Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

// Movies
Route::apiResource('movies', MovieController::class)
    ->only(['store', 'update', 'destroy']);

// Movie Category
Route::apiResource('categories', CategoryController::class)
   ->only(['store', 'update', 'destroy']);

// Watchlist
Route::apiResource('watchlist', WatchlistController::class);

});



