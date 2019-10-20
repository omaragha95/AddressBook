<?php
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });



// Route::group([

//     'middleware' => 'api',
//     'prefix' => 'auth'

// ], function ($router) {

//     Route::post('login', 'AuthController@login');
//     Route::post('logout', 'AuthController@logout');
//     Route::post('refresh', 'AuthController@refresh');
//     Route::post('me', 'AuthController@me');
//     Route::post('register', 'AuthController@create');

// });

Route::get('sections', 'SectionController@index');

// admin operation
Route::get('log', 'UserController@getLog');
Route::get('users', 'UserController@index');
Route::delete('user/{id}', 'UserController@delete');
Route::get('orders', 'ContactController@getAllOrders');
Route::get('contact/{id}', 'ContactController@changeStatus');


// user operations
Route::post('users', 'UserController@create');
Route::post('login', 'UserController@login');
Route::post('logout', 'UserController@logout');
Route::put('user/{id}', 'UserController@update');




// Contact operation
Route::get('contacts', 'ContactController@index');
Route::post('join', 'ContactController@join');
Route::post('contacts', 'ContactController@create');
Route::put('contact/{id}', 'ContactController@update');
Route::delete('contact/{id}', 'ContactController@delete');







Route::get('test', function () {
    return view('welcome');
});