<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
});

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => view('welcome'))->name('home');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::delete('/account', [AccountController::class, 'destroy'])->name('account.destroy');
});
