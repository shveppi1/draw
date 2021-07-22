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


/*
Route::get('/', function () {
    return view('welcome');
});
*/

Route::get('/', 'MainController@index');
Route::get('/thanks', 'MainController@thanks');
Route::get('/test', 'MainController@test');



Route::get('/email', function() {

    \Illuminate\Support\Facades\Mail::to('info@shveppi.ru')->send(new \App\Mail\WelcomeMail());

    return new \App\Mail\WelcomeMail();

});



Route::post('/ajax/formyoo', 'MainController@createKey');
Route::post('/yoo/pay/info', 'MainController@payTrans');
