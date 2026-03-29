<?php

use App\Http\Controllers\CoupleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/couple/create', [CoupleController::class, 'create'])->name('couple.create');
    Route::post('/couple', [CoupleController::class, 'store'])->name('couple.store');
    Route::get('/couple/join', [CoupleController::class, 'join'])->name('couple.join');
    Route::post('/couple/join', [CoupleController::class, 'attach'])->name('couple.attach');
    Route::get('/couple', [CoupleController::class, 'show'])->name('couple.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
