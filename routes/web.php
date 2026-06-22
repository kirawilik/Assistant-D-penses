<?php

use App\Http\Controllers\DepenseController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecuController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware('auth')->group(function () {
    Route::get('/recus', [RecuController::class, 'index'])
        ->name('recus.index');

    Route::get('/recus/create', [RecuController::class, 'create'])
        ->name('recus.create');

    Route::post('/recus', [RecuController::class, 'store'])
        ->name('recus.store');

    Route::get('/recus/{recu}', [RecuController::class, 'show'])
        ->name('recus.show');

    Route::delete('/recus/{recu}', [RecuController::class, 'destroy'])
        ->name('recus.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/depenses', [DepenseController::class, 'index'])
        ->name('depenses.index');
});

require __DIR__.'/auth.php';
