<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\departamentoController;
use App\Http\Controllers\documentController;
use App\Http\Controllers\expedienteController;
use App\Http\Controllers\periodoEscolarController;
use App\Http\Controllers\personalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\tipoDocumentoController;
use App\Mail\registroUsuarioAdminMailable;
use App\Models\Personal;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/*Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/admin', function () {
    return Inertia::render('Dashboard_admin');
})->middleware(['auth','verified'])->name('Admin_dashboard');

//Route::get('/Dasboard2', DashboardController::class);
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/personal', [personalController::class, 'index'])->middleware(['auth', 'verified'])->name('personal');

Route::get('/documentos', [documentController::class, 'documentsIndex'])->middleware(['auth', 'verified'])->name('documentos');

Route::get('/tipoDocumento', [tipoDocumentoController::class, 'index'])->middleware(['auth', 'verified'])->name('tipoDoc');

Route::get('/periodoEscolar', [periodoEscolarController::class, 'index'])->middleware(['auth', 'verified'])->name('periodoEscolar');

Route::get('/departamento', [departamentoController::class, 'index'])->middleware(['auth', 'verified'])->name('departamento');

Route::get('/nuevoDocumento', [documentController::class, 'index'])->middleware(['auth', 'verified'])->name('nuevoDocumento');

Route::get('/miExpediente', [expedienteController::class, 'index'])->middleware(['auth', 'verified'])->name('miExpediente');

Route::get('/Expedientes', [expedienteController::class, 'index'])->middleware(['auth', 'verified'])->name('expedientes');

require __DIR__.'/auth.php';
