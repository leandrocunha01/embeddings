<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;


Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/classify', [DashboardController::class, 'classify'])->name('classify');
