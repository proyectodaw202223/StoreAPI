<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get(
    'customer/{id}',
    'App\Http\Controllers\CustomerController@getById'
);

Route::get(
    'customer/credentials/{email}/{password}',
    'App\Http\Controllers\CustomerController@getByEmailAndPassword'
);

Route::get(
    'customer/{email}',
    'App\Http\Controllers\CustomerController@existsCustomerByEmail'
);

Route::get(
    'customer/{id}/orders',
    'App\Http\Controllers\CustomerController@getCustomerOrdersByCustomerId'
);

Route::get(
    'customer/{id}/active-orders',
    'App\Http\Controllers\CustomerController@getCustomerAndActiveOrderByCustomerId'
);

Route::post(
    'customer',
    'App\Http\Controllers\CustomerController@create'
);

Route::put(
    'customer/{customer}',
    'App\Http\Controllers\CustomerController@update'
);

Route::delete(
    'customer/{customer}',
    'App\Http\Controllers\CustomerController@delete'
);
