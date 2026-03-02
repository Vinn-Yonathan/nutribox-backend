<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminOnly;
use Illuminate\Support\Facades\Route;
use PHPUnit\Metadata\Group;

Route::post('/users', [UserController::class, 'register']);
Route::post('/users/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/current', [UserController::class, 'getCurrent']);
    Route::patch('/users/current', [UserController::class, 'updateCurrent']);
    Route::delete('/users/current', [UserController::class, 'logout']);

    Route::get('/carts', [CartController::class, 'get']);
    Route::delete('/carts', [CartController::class, 'delete']);
    Route::post('/carts/items', [CartController::class, 'addItem']);
    Route::put('/carts/items/{menu_id}', [CartController::class, 'updateItem'])->where('menu_id', '[0-9]+');
    Route::delete('/carts/items/{menu_id}', [CartController::class, 'deleteItem'])->where('menu_id', '[0-9]+');
});

Route::middleware(['auth:sanctum', AdminOnly::class])->group(function () {
    Route::get('/users', [UserController::class, 'get']);
    Route::get('/users/{id}', [UserController::class, 'getById'])->where('id', '[0-9]+');
    Route::patch('/users/{id}', [UserController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/users/{id}', [UserController::class, 'delete'])->where('id', '[0-9]+');

    Route::post('/menus', [MenuController::class, 'add']);
    Route::patch('/menus/{id}', [MenuController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/menus/{id}', [MenuController::class, 'delete'])->where('id', '[0-9]+');
});

Route::get('/menus/{id}', [MenuController::class, 'getById'])->where('id', '[0-9]+');
Route::get('/menus/', [MenuController::class, 'get']);