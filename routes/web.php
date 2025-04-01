<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\web\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and will be assigned
| to the "web" middleware group which includes session state, CSRF
| protection, and authentication.
|
*/

// Authentication Redirects
Route::get('/', function () {
    return redirect()->route('login');
});

// Authenticated Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Blog Posts - Main application view
    Route::get('/dashboard', [PostController::class, 'index'])->name('dashboard');
    
    // User Profile Management
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

// Authentication Routes (from auth.php)
require __DIR__.'/auth.php';