<?php

use App\Http\Controllers\ChargeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Ruta raíz - redirige a facturas (o login si no está autenticado)
Route::get('/', fn() => redirect()->route('charges.index'));

//Route::get('/dashboard', function () {
//    return view('dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('facturas')->name('charges.')->group(function () {
        Route::get('/', [ChargeController::class, 'index'])->name('index');
        Route::post('/upload', [ChargeController::class, 'upload'])->name('upload');
        Route::get('/generate', [ChargeController::class, 'generate'])->name('generate');
        Route::get('/list', [ChargeController::class, 'list'])->name('list');
        Route::get('/export', [ChargeController::class, 'export'])->name('export');
    });
});

require __DIR__.'/auth.php';
