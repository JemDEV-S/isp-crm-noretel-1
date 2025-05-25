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

Route::prefix('billing')->middleware(['web', 'auth'])->group(function() {
    // Dashboard
    Route::get('/', 'BillingController@dashboard')->name('billing.dashboard');

    // Invoices
    Route::get('invoices', 'InvoiceController@index')->name('billing.invoices.index')->middleware('permission:invoices,view');
    Route::get('invoices/create', 'InvoiceController@create')->name('billing.invoices.create')->middleware('permission:invoices,create');
    Route::post('invoices', 'InvoiceController@store')->name('billing.invoices.store')->middleware('permission:invoices,create');
    Route::get('invoices/{id}', 'InvoiceController@show')->name('billing.invoices.show')->middleware('permission:invoices,view');
    Route::get('invoices/{id}/edit', 'InvoiceController@edit')->name('billing.invoices.edit')->middleware('permission:invoices,edit');
    Route::put('invoices/{id}', 'InvoiceController@update')->name('billing.invoices.update')->middleware('permission:invoices,edit');
    Route::delete('invoices/{id}', 'InvoiceController@destroy')->name('billing.invoices.destroy')->middleware('permission:invoices,delete');

    // Invoice additional actions
    Route::post('invoices/{id}/void', 'InvoiceController@void')->name('billing.invoices.void')->middleware('permission:invoices,edit');
    Route::post('invoices/{id}/mark-as-sent', 'InvoiceController@markAsSent')->name('billing.invoices.mark-as-sent')->middleware('permission:invoices,edit');
    Route::get('invoices/{id}/print', 'InvoiceController@print')->name('billing.invoices.print')->middleware('permission:invoices,view');
    Route::post('invoices/{id}/email', 'InvoiceController@email')->name('billing.invoices.email')->middleware('permission:invoices,edit');
    Route::post('generate-invoice', 'InvoiceController@generateForContract')->name('billing.invoices.generate')->middleware('permission:invoices,create');

    // Payments
    Route::get('payments', 'PaymentController@index')->name('billing.payments.index')->middleware('permission:payments,view');
    Route::get('payments/create', 'PaymentController@create')->name('billing.payments.create')->middleware('permission:payments,create');
    Route::post('payments', 'PaymentController@store')->name('billing.payments.store')->middleware('permission:payments,create');
    Route::get('payments/{id}', 'PaymentController@show')->name('billing.payments.show')->middleware('permission:payments,view');
    Route::get('payments/{id}/edit', 'PaymentController@edit')->name('billing.payments.edit')->middleware('permission:payments,edit');
    Route::put('payments/{id}', 'PaymentController@update')->name('billing.payments.update')->middleware('permission:payments,edit');
    Route::delete('payments/{id}', 'PaymentController@destroy')->name('billing.payments.destroy')->middleware('permission:payments,delete');

    // Payment additional actions
    Route::post('payments/{id}/void', 'PaymentController@void')->name('billing.payments.void')->middleware('permission:payments,edit');
    Route::get('payments/{id}/print', 'PaymentController@printReceipt')->name('billing.payments.print')->middleware('permission:payments,view');
    Route::get('payments/report', 'PaymentController@report')->name('billing.payments.report')->middleware('permission:financial_reports,view');

    // Credit Notes
    Route::get('credit-notes', 'CreditNoteController@index')->name('billing.credit-notes.index')->middleware('permission:credit_notes,view');
    Route::get('credit-notes/create', 'CreditNoteController@create')->name('billing.credit-notes.create')->middleware('permission:credit_notes,create');
    Route::post('credit-notes', 'CreditNoteController@store')->name('billing.credit-notes.store')->middleware('permission:credit_notes,create');
    Route::get('credit-notes/{id}', 'CreditNoteController@show')->name('billing.credit-notes.show')->middleware('permission:credit_notes,view');
    Route::get('credit-notes/{id}/edit', 'CreditNoteController@edit')->name('billing.credit-notes.edit')->middleware('permission:credit_notes,edit');
    Route::put('credit-notes/{id}', 'CreditNoteController@update')->name('billing.credit-notes.update')->middleware('permission:credit_notes,edit');
    Route::delete('credit-notes/{id}', 'CreditNoteController@destroy')->name('billing.credit-notes.destroy')->middleware('permission:credit_notes,delete');

    // Credit Note additional actions
    Route::post('credit-notes/{id}/apply', 'CreditNoteController@apply')->name('billing.credit-notes.apply')->middleware('permission:credit_notes,edit');
    Route::post('credit-notes/{id}/void', 'CreditNoteController@void')->name('billing.credit-notes.void')->middleware('permission:credit_notes,edit');
    Route::get('credit-notes/{id}/print', 'CreditNoteController@print')->name('billing.credit-notes.print')->middleware('permission:credit_notes,view');

    // Customer Billing
    Route::get('customer/{customerId}', 'BillingController@customerBillingSummary')->name('billing.customer')->middleware('permission:invoices,view');

    // Reports
    Route::get('reports', 'BillingController@reports')->name('billing.reports')->middleware('permission:financial_reports,view');
});
