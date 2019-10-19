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


// admin operation
Route::get('users', 'UserController@index');
Route::delete('delete/user/{id}', 'UserController@delete');



// user operations
Route::post('register', 'UserController@register');
Route::post('login', 'UserController@login');
Route::post('logout', 'UserController@logout');
Route::put('update/{id}', 'UserController@update');




// Contact operation
Route::get('contacts', 'ContactController@index');
Route::get('section/{id}/contacts', 'ContactController@indexBySection');
Route::get('my-contacts', 'ContactController@myContacts');
Route::post('add-contact', 'ContactController@create');
Route::put('update/contact/{id}', 'ContactController@update');
Route::delete('delete/contact/{id}', 'ContactController@delete');







