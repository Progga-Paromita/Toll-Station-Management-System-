<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TollController;

// Public Routes
Route::get('/', [TollController::class, 'home'])->name('home');

// Auth Routes (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.forgot');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.change');

    // Operator / Member Routes
    Route::get('/operator/dashboard', [TollController::class, 'operatorDashboard'])->name('operator.dashboard');
    Route::post('/operator/transaction', [TollController::class, 'storeTransaction'])->name('operator.transaction.store');

    // Admin Routes
    Route::get('/admin/dashboard', [TollController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::post('/admin/unlock/{user_id}', [TollController::class, 'unlockUser'])->name('admin.unlock');
    
    // PL/SQL Playground Routes
    Route::get('/admin/playground', [TollController::class, 'showPlayground'])->name('admin.playground');
    Route::post('/admin/playground/procedure', [TollController::class, 'runProcedure'])->name('admin.playground.procedure');
    Route::post('/admin/playground/function', [TollController::class, 'runFunction'])->name('admin.playground.function');
    Route::post('/admin/playground/loop', [TollController::class, 'runLoop'])->name('admin.playground.loop');
    Route::post('/admin/playground/record', [TollController::class, 'runRecord'])->name('admin.playground.record');
});
