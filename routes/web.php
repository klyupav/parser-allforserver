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


// Auth::routes();
Route::get('/login', 'Auth\LoginController@showLoginForm' );
Route::post('/login', 'Auth\LoginController@login');
Route::post('/logout', 'Auth\LoginController@logout');

Route::get('/', 'ParseitController@index');
Route::get('/parseit/go', 'ParseitController@start');
Route::get('/parseit/stop', 'ParseitController@stop');
Route::get('/parseit/run', 'ParseitController@run');
Route::get('/parseit/export', 'ParseitController@export');
Route::get('/parseit/parsing-category-from-citilink', 'ParseitController@parsingCategoryFromCitilink');
Route::post('/parseit/get-parsing-info', 'ParseitController@ajaxGetParsingInfo');

Route::resource('/data-set', 'DataSetController');

Route::post('/donor/{donor_name}/sources/store', 'SourceController@store');
Route::post('/donor/{donor_name}/sources/{id}/update', 'SourceController@update');
Route::post('/donor/{donor_name}/sources/{id}/destroy', 'SourceController@destroy');
Route::post('/sources/massive-change-nacenka', 'SourceController@massiveChangeNacenka');
Route::post('/sources/massive-change-review', 'SourceController@massiveChangeReview');

Route::resource('/donor/{donor_name}/sources', 'SourceController');

Route::resource('/donor', 'DonorController');

Route::post('/logs/search', 'LoggerController@ajaxFilterLogs');
Route::get('/logs/{filename]', 'LoggerController@view');
Route::resource('/logs', 'LoggerController');
Route::resource('/proxy', 'ProxyController');


Route::get('/test', function(){
    foreach (\App\Models\Donor::findByName('wat') as $donor)
    {
        print_r($donor->id);
    }
});
