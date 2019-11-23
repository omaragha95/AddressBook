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

use Illuminate\Http\Request;


// Route::get('/', function () {
//     return view('index');
//     // return view('image');
// });

// Route::post('/upload', function(Request $request) {
//     if ($request->hasfile('image')) {
//         foreach($request->file('image') as $image) {
//             $date = now()->toArray()['timestamp'];
//             $fileName = $image->getClientOriginalName() . '_' . $date;
//             $image->move(public_path('uploads'),$fileName);
//         }
//         return redirect()->back();
//     }    
// });


// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
