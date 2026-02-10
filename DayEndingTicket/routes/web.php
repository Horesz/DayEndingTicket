<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NapzarasController;
use App\Http\Controllers\BeosztasController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// ============================================
// PUBLIKUS OLDALAK
// ============================================

// Főoldal
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// ============================================
// BEJELENTKEZETT FELHASZNÁLÓK
// ============================================

Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Napzárások - minden bejelentkezett user
    Route::resource('napzarasok', NapzarasController::class);
    
    // Napzárás jóváhagyás - csak admin és rendszergazda
    Route::middleware('role:admin,rendszergazda')->group(function () {
        Route::post('/napzarasok/{napzaras}/approve', [NapzarasController::class, 'approve'])
            ->name('napzarasok.approve');
        Route::post('/napzarasok/{napzaras}/reject', [NapzarasController::class, 'reject'])
            ->name('napzarasok.reject');
    });

    // Beosztás
    Route::get('/beosztas', [BeosztasController::class, 'index'])->name('beosztas.index');
    
    Route::middleware('role:admin,rendszergazda')->group(function () {
        Route::post('/beosztas', [BeosztasController::class, 'store'])->name('beosztas.store');
        Route::put('/beosztas/{beosztas}', [BeosztasController::class, 'update'])->name('beosztas.update');
        Route::delete('/beosztas/{beosztas}', [BeosztasController::class, 'destroy'])->name('beosztas.destroy');
    });
    Route::resource('napzarasok', NapzarasController::class)->except(['destroy']);
Route::delete('napzarasok/{napzaras}', [NapzarasController::class, 'destroy'])->name('napzarasok.destroy');

    // Riportok - admin és rendszergazda
    Route::middleware('role:admin,rendszergazda')->group(function () {
        Route::get('/riportok', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/riportok/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
        Route::get('/riportok/export-json', [ReportController::class, 'exportJson'])->name('reports.export.json');
    });
    // Admin namespace alatt
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
        ->except(['show']); // show opcionális
        Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
// vagy resource esetén automatikusan létrejön
});
});

// ============================================
// AUTH ROUTE-OK (login, register, stb.)
// ============================================

require __DIR__.'/auth.php';