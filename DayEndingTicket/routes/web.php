<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NapzarasController;
use App\Http\Controllers\BeosztasController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Főoldal
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Bejelentkezett felhasználók
Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Napzárások
    Route::resource('napzarasok', NapzarasController::class);
    
    // Napzárás jóváhagyás - csak admin és rendszergazda
    Route::post('/napzarasok/{napzaras}/approve', [NapzarasController::class, 'approve'])
        ->name('napzarasok.approve')
        ->middleware('role:admin,rendszergazda');
    
    Route::post('/napzarasok/{napzaras}/reject', [NapzarasController::class, 'reject'])
        ->name('napzarasok.reject')
        ->middleware('role:admin,rendszergazda');

    // Beosztás
    Route::get('/beosztas', [BeosztasController::class, 'index'])->name('beosztas.index');
    Route::post('/beosztas', [BeosztasController::class, 'store'])
        ->name('beosztas.store')
        ->middleware('role:admin,rendszergazda');
    Route::put('/beosztas/{beosztas}', [BeosztasController::class, 'update'])
        ->name('beosztas.update')
        ->middleware('role:admin,rendszergazda');
    Route::delete('/beosztas/{beosztas}', [BeosztasController::class, 'destroy'])
        ->name('beosztas.destroy')
        ->middleware('role:admin,rendszergazda');

    // Riportok - csak admin és rendszergazda
    Route::get('/riportok', [ReportController::class, 'index'])
        ->name('reports.index')
        ->middleware('role:admin,rendszergazda');
    Route::get('/riportok/export-csv', [ReportController::class, 'exportCsv'])
        ->name('reports.export.csv')
        ->middleware('role:admin,rendszergazda');
    Route::get('/riportok/export-json', [ReportController::class, 'exportJson'])
        ->name('reports.export.json')
        ->middleware('role:admin,rendszergazda');
         // ADMIN - User Management (csak admin és rendszergazda)
    Route::middleware('role:admin,rendszergazda')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    });
    Route::get('/beosztas', [BeosztasController::class, 'index'])->name('beosztas.index');
Route::post('/beosztas', [BeosztasController::class, 'store'])
    ->name('beosztas.store')
    ->middleware('role:admin,rendszergazda');
Route::put('/beosztas/{beosztas}', [BeosztasController::class, 'update'])
    ->name('beosztas.update')
    ->middleware('role:admin,rendszergazda');
Route::middleware(['auth'])->group(function () {
    Route::get('/beosztas/create', [BeosztasController::class, 'create'])->name('beosztas.create');
    Route::post('/beosztas', [BeosztasController::class, 'store'])->name('beosztas.store');
});Route::get('/beosztas/{beosztas}/edit', [BeosztasController::class, 'edit']) ->name('beosztas.edit') ->middleware('role:admin,rendszergazda');


Route::delete('/beosztas/{beosztas}', [BeosztasController::class, 'destroy'])
    ->name('beosztas.destroy')
    ->middleware('role:admin,rendszergazda');
    Route::resource('beosztas', BeosztasController::class);
Route::get('beosztas/export/google', [BeosztasController::class, 'exportGoogleCalendar'])
    ->name('beosztas.export.google');
Route::get('beosztas/print', [BeosztasController::class, 'print']) ->name('beosztas.print');

// ÚJ: Kommentek és export
Route::post('/beosztas/{beosztas}/komment', [BeosztasController::class, 'addKomment'])
    ->name('beosztas.komment');
Route::get('/beosztas/export/google-calendar', [BeosztasController::class, 'exportGoogleCalendar'])
    ->name('beosztas.export.google');
Route::get('/beosztas/print', [BeosztasController::class, 'print'])
    ->name('beosztas.print');
});

// Auth route-ok
require __DIR__.'/auth.php';