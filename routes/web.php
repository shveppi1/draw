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

Route::get('/', function () {
    return view('welcome');
});


//Route::any('/drawPool/webhook/', 'MainCtrl@update');

Route::group(['middleware' => 'throttle:400'], function () {
    Route::any('/drawPool/webhook/', 'MainCtrl@update');
});


/*
Route::get('/getRespons', 'MainCtrl@respons');
Route::get('/getState', 'MainCtrl@state');
Route::get('/drawlist', 'MainCtrl@drawlist');
Route::get('/draw/{id}/delete', 'MainCtrl@deleteDraw')->name('deleteDraw');
*/
Route::get('/test', 'MainCtrl@test');





