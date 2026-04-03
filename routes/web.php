<?php

use App\Http\Controllers\CoupleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\OfficiantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VowsController;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/couple/create', [CoupleController::class, 'create'])->name('couple.create');
    Route::get('/couple/join', [CoupleController::class, 'join'])->name('couple.join');
    Route::post('/couple/join', [CoupleController::class, 'attach'])->name('couple.attach');
    Route::get('/couple', [CoupleController::class, 'show'])->name('couple.show');
    Route::post('/couple', [CoupleController::class, 'store'])->name('couple.store');

    Route::get('/officiant/join', [OfficiantController::class, 'create'])->name('officiant.create');
    Route::post('/officiant/join', [OfficiantController::class, 'store'])->name('officiant.store');
    Route::get('/officiant', [OfficiantController::class, 'index'])->name('officiant.index');
    Route::post('/officiant/answer', [OfficiantController::class, 'answer'])->name('officiant.answer');
    Route::get('/officiant/preview', [OfficiantController::class, 'preview'])->name('officiant.preview');

    Route::get('/voeux/export', [ExportController::class, 'vows'])->name('voeux.export');
    Route::post('/voeux/tone', [VowsController::class, 'setTone'])->name('voeux.tone');
    Route::post('/voeux/regenerate', [VowsController::class, 'regenerate'])->name('voeux.regenerate');
    Route::get('/voeux', [VowsController::class, 'index'])->name('voeux.index');
    Route::post('/voeux/answer', [VowsController::class, 'answer'])->name('voeux.answer');
    Route::get('/voeux/preview', [VowsController::class, 'preview'])->name('voeux.preview');
    Route::get('/voeux/edit', [VowsController::class, 'edit'])->name('voeux.edit');
    Route::put('/voeux', [VowsController::class, 'update'])->name('voeux.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
