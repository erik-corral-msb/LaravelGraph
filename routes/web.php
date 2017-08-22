<?php
use Illuminate\Http\Request;
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

Route::get('/', function (Request $request) {
    return view('welcome');
});

Route::get('getToken', ['as' => 'welcome' , 'uses' => 'GraphController@createSubscriptionCurl']);


Route::any('success', function () {
    return response('Hello World', 200)
                  ->header('Content-Type', 'text/plain');
});




