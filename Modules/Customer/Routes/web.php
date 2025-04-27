<?php

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

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;
use Modules\Customer\Http\Controllers\DocumentController;
use Modules\Customer\Http\Controllers\InteractionController;
use Modules\Customer\Http\Controllers\LeadController;

Route::prefix('customer')->middleware(['auth'])->name('customer.')->group(function() {
    // Dashboard
    Route::get('/', 'CustomerDashboardController@index')->name('dashboard');
    
    // Customers - Con permisos granulares
    Route::prefix('customers')->name('customers.')->group(function() {
        // Rutas de creación
        Route::middleware('permission:customers,create')->group(function() {
            Route::get('/create', [CustomerController::class, 'create'])->name('create');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
        });
        // Rutas de visualización
        Route::middleware('permission:customers,view')->group(function() {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/{id}', [CustomerController::class, 'show'])->name('show')->where('id', '[0-9]+');
        });
        
        
        
        // Rutas de edición
        Route::middleware('permission:customers,edit')->group(function() {
            Route::get('/{id}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{id}', [CustomerController::class, 'update'])->name('update');
            Route::post('/{id}/activate', [CustomerController::class, 'activate'])->name('activate');
            Route::post('/{id}/deactivate', [CustomerController::class, 'deactivate'])->name('deactivate');
        });
        
        // Rutas de eliminación
        Route::middleware('permission:customers,delete')->group(function() {
            Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('destroy');
        });
    });
    
    // Documents - Con permisos granulares
    Route::prefix('documents')->name('documents.')->group(function() {
           // Rutas de creación
           Route::middleware('permission:customers,create')->group(function() {
            Route::get('/create', [DocumentController::class, 'create'])->name('create');
            Route::post('/', [DocumentController::class, 'store'])->name('store');
            Route::post('/{id}/versions', [DocumentController::class, 'uploadVersion'])->name('upload-version');
        });
        // Rutas de visualización
        Route::middleware('permission:customers,view')->group(function() {
            Route::get('/', [DocumentController::class, 'index'])->name('index');
            Route::get('/{id}', [DocumentController::class, 'show'])->name('show');
            Route::get('/{id}/download', [DocumentController::class, 'download'])->name('download');
            Route::get('/{id}/version/{versionId}/download', [DocumentController::class, 'downloadVersion'])->name('download-version');
        });
        
     
        
        // Rutas de edición
        Route::middleware('permission:customers,edit')->group(function() {
            Route::get('/{id}/edit', [DocumentController::class, 'edit'])->name('edit');
            Route::put('/{id}', [DocumentController::class, 'update'])->name('update');
            Route::post('/{id}/status', [DocumentController::class, 'changeStatus'])->name('change-status');
        });
        
        // Rutas de eliminación
        Route::middleware('permission:customers,delete')->group(function() {
            Route::delete('/{id}', [DocumentController::class, 'destroy'])->name('destroy');
        });
    });
    
    // Interactions - Con permisos granulares
    Route::prefix('interactions')->name('interactions.')->group(function() {
        // Rutas de creación
        Route::middleware('permission:customers,create')->group(function() {
            Route::get('/create', [InteractionController::class, 'create'])->name('create');
            Route::post('/', [InteractionController::class, 'store'])->name('store');
        });
        // Rutas de visualización
        Route::middleware('permission:customers,view')->group(function() {
            Route::get('/', [InteractionController::class, 'index'])->name('index');
            Route::get('/{id}', [InteractionController::class, 'show'])->name('show');
        });
        
        
        
        // Rutas de edición
        Route::middleware('permission:customers,edit')->group(function() {
            Route::get('/{id}/edit', [InteractionController::class, 'edit'])->name('edit');
            Route::put('/{id}', [InteractionController::class, 'update'])->name('update');
            Route::post('/{id}/follow-up', [InteractionController::class, 'markForFollowUp'])->name('mark-follow-up');
            Route::post('/{id}/unfollow-up', [InteractionController::class, 'unmarkForFollowUp'])->name('unmark-follow-up');
        });
        
        // Rutas de eliminación
        Route::middleware('permission:customers,delete')->group(function() {
            Route::delete('/{id}', [InteractionController::class, 'destroy'])->name('destroy');
        });
    });
    
    // Leads - Con permisos granulares
    Route::prefix('leads')->name('leads.')->group(function() {
         // Rutas de creación
         Route::middleware('permission:customers,create')->group(function() {
            Route::get('/create', [LeadController::class, 'create'])->name('create');
            Route::post('/', [LeadController::class, 'store'])->name('store');
        });
        // Rutas de visualización
        Route::middleware('permission:customers,view')->group(function() {
            Route::get('/', [LeadController::class, 'index'])->name('index');
            Route::get('/{id}', [LeadController::class, 'show'])->name('show');
        });
        
       
        
        // Rutas de edición
        Route::middleware('permission:customers,edit')->group(function() {
            Route::get('/{id}/edit', [LeadController::class, 'edit'])->name('edit');
            Route::put('/{id}', [LeadController::class, 'update'])->name('update');
            Route::get('/{id}/convert', [LeadController::class, 'showConvertForm'])->name('convert-form');
            Route::post('/{id}/convert', [LeadController::class, 'convert'])->name('convert');
            Route::post('/{id}/status', [LeadController::class, 'changeStatus'])->name('change-status');
        });
        
        // Rutas de eliminación
        Route::middleware('permission:customers,delete')->group(function() {
            Route::delete('/{id}', [LeadController::class, 'destroy'])->name('destroy');
        });
    });
});
