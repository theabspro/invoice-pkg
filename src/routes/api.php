<?php
Route::group(['namespace' => 'Abs\InvoicePkg\Api', 'middleware' => ['api']], function () {
	Route::group(['prefix' => 'invoice-pkg/api'], function () {
		Route::group(['middleware' => ['auth:api']], function () {
			// Route::get('taxes/get', 'TaxController@getTaxes');
		});
	});
});