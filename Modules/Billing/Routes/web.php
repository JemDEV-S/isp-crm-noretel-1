<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\InvoiceController;
use Modules\Billing\Http\Controllers\PaymentController;

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

Route::prefix('billing')->middleware('auth')->name('billing.')->group(function() {
    // Dashboard
    Route::get('dashboard', 'BillingController@dashboard')->name('dashboard');
    
    // Invoices
    Route::prefix('invoices')->name('invoices.')->group(function() {
        // Rutas de creación
        Route::middleware('permission:invoices,create')->group(function() {
            Route::get('create', 'InvoiceController@create')->name('create');
            Route::post('', 'InvoiceController@store')->name('store');
        });

        // Rutas de visualización
        Route::middleware('permission:invoices,view')->group(function() {
            Route::get('', 'InvoiceController@index')->name('index');
            Route::get('overdue', 'InvoiceController@overdue')->name('overdue');
            Route::get('pending', 'InvoiceController@pending')->name('pending');
            Route::get('{id}', 'InvoiceController@show')->name('show')->where('id', '[0-9]+');
        });

        // Rutas de edición
        Route::middleware('permission:invoices,edit')->group(function() {
            Route::get('{id}/edit', 'InvoiceController@edit')->name('edit');
            Route::put('{id}', 'InvoiceController@update')->name('update');
            Route::post('{id}/mark-as-paid', 'InvoiceController@markAsPaid')->name('mark-as-paid');
            Route::post('{id}/mark-as-cancelled', 'InvoiceController@markAsCancelled')->name('mark-as-cancelled');
        });

        // Rutas de eliminación
        Route::middleware('permission:invoices,delete')->group(function() {
            Route::delete('{id}', 'InvoiceController@destroy')->name('destroy');
        });
    });
    
    // Payments
    Route::prefix('payments')->name('payments.')->group(function() {
        // Rutas de creación
        Route::middleware('permission:payments,create')->group(function() {
            Route::get('create', 'PaymentController@create')->name('create');
            Route::post('', 'PaymentController@store')->name('store');
        });

        // Rutas de visualización
        Route::middleware('permission:payments,view')->group(function() {
            Route::get('', 'PaymentController@index')->name('index');
            Route::get('{id}', 'PaymentController@show')->name('show')->where('id', '[0-9]+');
        });

        // Rutas de eliminación
        Route::middleware('permission:payments,delete')->group(function() {
            Route::delete('{id}', 'PaymentController@destroy')->name('destroy');
        });
    });
    
    // Informes
    Route::prefix('reports')->name('reports.')->middleware('permission:invoices,view')->group(function() {
        Route::get('income', 'ReportController@income')->name('income');
        Route::get('debtors', 'ReportController@debtors')->name('debtors');
        Route::get('payments', 'ReportController@payments')->name('payments');
    });
});