<?php

use App\Http\Controllers\AparController;
use App\Http\Controllers\HydrantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/scan', fn() => view('scan'))->name('scan');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [AparController::class, 'dashboard'])->name('dashboard');

    // HYDRANT management
    Route::get('/hydrant', [HydrantController::class, 'index'])->name('hydrant.index');
    Route::get('/hydrant/create', [HydrantController::class, 'create'])->name('hydrant.create');
    Route::post('/hydrant', [HydrantController::class, 'store'])->name('hydrant.store');
    Route::get('/hydrant/{code}/edit', [HydrantController::class, 'edit'])->name('hydrant.edit');
    Route::put('/hydrant/{code}', [HydrantController::class, 'update'])->name('hydrant.update');
    Route::get('/hydrant/print-all', [HydrantController::class, 'printAll'])->name('hydrant.print-all');
    Route::get('/hydrant/{code}/print', [HydrantController::class, 'print'])->name('hydrant.print');
    Route::get('/hydrant/export/data', [HydrantController::class, 'exportData'])->name('hydrant.export');
    Route::delete('/hydrant/{code}', [HydrantController::class, 'destroy'])->name('hydrant.destroy');

    // HYDRANT inspeksi
    Route::get('/hydrant-inspeksi', [HydrantController::class, 'inspectionIndex'])->name('hydrant.inspeksi.index');
    Route::post('/hydrant/{code}/inspeksi', [HydrantController::class, 'storeInspection'])->name('hydrant.inspeksi.store');
    Route::post('/hydrant-inspeksi', [HydrantController::class, 'storeInspectionAdmin'])->name('hydrant.inspeksi.store.admin');
    Route::post('/hydrant-inspeksi/export', [HydrantController::class, 'exportInspection'])->name('hydrant.inspeksi.export');
    Route::post('/hydrant-inspeksi/import', [HydrantController::class, 'importInspeksi'])->name('hydrant.inspeksi.import');
    Route::delete('/hydrant-inspeksi/{id}', [HydrantController::class, 'destroyInspection'])->name('hydrant.inspeksi.destroy');

    // HYDRANT maintenance
    Route::post('/hydrant/{code}/maintenance', [HydrantController::class, 'storeMaintenance'])->name('hydrant.maintenance.store');
    Route::delete('/hydrant-maintenance/{id}', [HydrantController::class, 'destroyMaintenance'])->name('hydrant.maintenance.destroy');

    // APAR management (these must be before the public show route for {code} pattern)
    Route::get('/apar', [AparController::class, 'index'])->name('apar.index');
    Route::get('/apar/create', [AparController::class, 'create'])->name('apar.create');
    Route::post('/apar', [AparController::class, 'store'])->name('apar.store');
    Route::get('/apar/{code}/edit', [AparController::class, 'edit'])->name('apar.edit');
    Route::put('/apar/{code}', [AparController::class, 'update'])->name('apar.update');
    Route::get('/apar/print-all', [AparController::class, 'printAll'])->name('apar.print-all');
    Route::get('/apar/{code}/print', [AparController::class, 'print'])->name('apar.print');
    Route::get('/apar/import/template', [AparController::class, 'downloadTemplate'])->name('apar.template');
    Route::post('/apar/import', [AparController::class, 'import'])->name('apar.import');
    Route::get('/apar/export/data', [AparController::class, 'exportData'])->name('apar.export');
    Route::delete('/apar/{code}', [AparController::class, 'destroy'])->name('apar.destroy');

    // Maintenance history
    Route::get('/maintenance', [AparController::class, 'maintenanceIndex'])->name('apar.maintenance.index');
    Route::post('/maintenance', [AparController::class, 'storeMaintenanceAdmin'])->name('apar.maintenance.store.admin');
    Route::post('/apar/{code}/maintenance', [AparController::class, 'storeMaintenance'])->name('apar.maintenance.store');
    Route::delete('/apar/maintenance/{id}', [AparController::class, 'destroyMaintenance'])->name('apar.maintenance.destroy');

    // Pemeriksaan berkala (periodic inspection)
    Route::get('/inspeksi', [AparController::class, 'inspectionIndex'])->name('apar.inspection.index');
    Route::post('/inspeksi', [AparController::class, 'storeInspectionAdmin'])->name('apar.inspection.store.admin');
    Route::post('/inspeksi/export', [AparController::class, 'exportInspection'])->name('apar.inspection.export');
    Route::post('/apar/{code}/inspection', [AparController::class, 'storeInspection'])->name('apar.inspection.store');
    Route::delete('/apar/inspection/{id}', [AparController::class, 'destroyInspection'])->name('apar.inspection.destroy');
    Route::post('/apar/{code}/toggle-maintenance', [AparController::class, 'toggleMaintenance'])->name('apar.toggle-maintenance');

    // Superadmin only
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});

// Public APAR detail — placed after auth routes so /apar/create is caught first
Route::get('/apar/{code}', [AparController::class, 'show'])->name('apar.show');
Route::get('/hydrant/{code}', [HydrantController::class, 'show'])->name('hydrant.show');
