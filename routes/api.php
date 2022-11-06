<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CustomerController;

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
    [CustomerController::class, 'getById']
)->name('getCustomerById');

Route::get(
    'customer/credentials/{email}/{password}',
    [CustomerController::class, 'getByEmailAndPassword']
)->name('getCustomerByEmailAndPassword');

Route::get(
    'customer/{id}/orders',
    [CustomerController::class, 'getCustomerAndOrdersByCustomerId']
)->name('getCustomerAndOrdersByCustomerId');

Route::get(
    'customer/{id}/paid-orders',
    [CustomerController::class, 'getCustomerAndPaidOrdersByCustomerId']
)->name('getCustomerAndPaidOrdersByCustomerId');

Route::post(
    'customer',
    [CustomerController::class, 'create']
)->name('createCustomer');

Route::put(
    'customer/{customer}',
    [CustomerController::class, 'update']
)->name('updateCustomer');

Route::delete(
    'customer/{customer}',
    [CustomerController::class, 'delete']
)->name('deleteCustomer');
