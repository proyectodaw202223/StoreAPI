<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;

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

Route::group([
    "prefix" => "customer",
    "controller" => CustomerController::class],
    function() {
        Route::get(
            '/{id}',
            'getById'
        )->name('getCustomerById');
        
        Route::get(
            '/credentials/{email}/{password}',
            'getByEmailAndPassword'
        )->name('getCustomerByEmailAndPassword');
        
        Route::get(
            '/{id}/orders',
            'getCustomerAndOrdersByCustomerId'
        )->name('getCustomerAndOrdersByCustomerId');
        
        Route::get(
            '/{id}/paid-orders',
            'getCustomerAndPaidOrdersByCustomerId'
        )->name('getCustomerAndPaidOrdersByCustomerId');

        Route::get(
            '/{id}/created-order',
            'getCustomerAndCreatedOrderByCustomerId'
        )->name('getCustomerAndCreatedOrderByCustomerId');
        
        Route::post(
            '',
            'create'
        )->name('createCustomer');
        
        Route::put(
            '/{customer}',
            'update'
        )->name('updateCustomer');
        
        Route::delete(
            '/{customer}',
            'delete'
        )->name('deleteCustomer');
});

Route::group([
    "prefix" => "order",
    "controller" => OrderController::class],
    function() {
        Route::get(
            '/{id}',
            'getById'
        )->name('getOrderById');

        Route::get(
            '',
            'getAll'
        )->name('getAllOrder');

        Route::get(
            '/status/{status}',
            'getByStatus'
        )->name('getOrdersByStatus');

        Route::post(
            '',
            'create'
        )->name('createOrder');

        Route::put(
            '/{order}',
            'update'
        )->name('updateOrder');

        Route::delete(
            '/{order}',
            'delete'
        )->name('deleteOrder');
});
