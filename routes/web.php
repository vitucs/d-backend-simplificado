<?php

use Illuminate\Support\Facades\Route;

use App\Controller\UserController;
use App\Controller\TransactionController;


Route::group(['prefix' => 'users'], function () {
    Route::post('/', [UserController::class, 'create']);
    Route::post('/{id}/balance', [UserController::class, 'addBalance']);
});

Route::post('/transfer', [TransactionController::class, 'create']);