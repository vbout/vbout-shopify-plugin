<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('install', 'AppController@install');
Route::get('app', 'AppController@start');
Route::get('callback', 'AppController@callback');
Route::get('charge', 'AppController@charge');

// Set up steps
// Route::get('settings/{shopUrl}', 'SettingsController@setUp');
// Route::post('settings/{shopUrl}', 'SettingsController@setUpUpdate');

Route::get('settings/{shopUrl}', 'SettingsController@edit');
Route::post('settings/{shopUrl}', 'SettingsController@update');
Route::post('settings/customers/sync', 'SettingsController@syncCustomers');

Route::post('settings/assign/{shopUrl}', 'SettingsController@configurationSettingsUpdate')->name('setSettings');
Route::get('get', 'SettingsController@getFieldsName');


Route::get('success/{shopUrl}', 'AppController@success');

Route::group(['prefix' => 'webhook', 'middleware' => 'webhook'], function () {
    Route::post('sync', 'WebhookController@sync');
    Route::post('uninstall', 'AppController@uninstall');
});

Route::get('test/{apiKey}', 'SettingsController@test');