<?php

Route::group(['namespace' => 'Abs\InvoicePkg', 'middleware' => ['web', 'auth'], 'prefix' => 'invoice-pkg'], function () {
	//INVOICE
	Route::get('/invoices/get-list', 'InvoiceController@getInvoiceList')->name('getInvoiceList');
	Route::get('/invoice/get-view-data', 'InvoiceController@getInvoiceViewData')->name('getInvoiceViewData');
	Route::get('/invoice/get-session-data', 'InvoiceController@getInvoiceSessionData')->name('getInvoiceSessionData');
	Route::get('/invoice/delete', 'InvoiceController@deleteInvoiceData')->name('deleteInvoiceData');
	Route::get('/invoice/get', 'InvoiceController@getInvoices')->name('getInvoices');

});