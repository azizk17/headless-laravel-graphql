<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\User;

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


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/test', function (Request $request) {
    return User::all();
});


Auth::routes();

Route::get('/users', function (Request $request) {
    return User::all();
});

// Route::get('/te', 'Auth\AuthController@login');
Route::get('/signin', function (Request $request) {
    return $request->pathInfo;
});
Route::get('/signup', function (Request $request) {
    return $request->pathInfo;
});
Route::get('/signout', function (Request $request) {
    return $request->pathInfo;
});
Route::get('/password_rest', function (Request $request) {
    return $request->pathInfo;
});
Route::get('/signin', function (Request $request) {
    return $request->pathInfo;
});
