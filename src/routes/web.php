<?php

Route::group(['namespace' => 'Abs\InvoicePkg', 'middleware' => ['web', 'auth'], 'prefix' => 'invoice-pkg'], function () {
	//INVOICE
	Route::get('/invoices/get-list', 'InvoiceController@getInvoiceList')->name('getInvoiceList');
	Route::get('/invoice/get-form-data', 'InvoiceController@getInvoiceFormData')->name('getInvoiceFormData');
	Route::post('/invoice/save', 'InvoiceController@saveInvoice')->name('saveInvoice');
	Route::get('/invoice/delete', 'InvoiceController@deleteInvoice')->name('deleteInvoice');
});