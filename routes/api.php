<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductItemController;
use App\Http\Controllers\UserController;

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
        )->name('getAllOrders');

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

Route::group([
    "prefix" => "product",
    "controller" => ProductController::class],
    function() {
        Route::get(
            '/{id}',
            'getById'
        )->name('getProductById');

        Route::get(
            '',
            'getAll'
        )->name('getAllProducts');

        Route::get(
            'new/{limit}',
            'getNew'
        )->name('getNewProducts');

        Route::get(
            'sale/{limit}',
            'getForSale'
        )->name('geProductsForSale');

        Route::post(
            '',
            'create'
        )->name('createProduct');

        Route::put(
            '/{product}',
            'update'
        )->name('updateProduct');

        Route::delete(
            '/{product}',
            'delete'
        )->name('deleteProduct');
});

Route::group([
    "prefix" => "item",
    "controller" => ProductItemController::class],
    function() {
        Route::get(
            '/{id}',
            'getById'
        )->name('getItemById');

        Route::post(
            '',
            'create'
        )->name('createItem');

        Route::put(
            '/{item}',
            'update'
        )->name('updateItem');

        Route::delete(
            '/{item}',
            'delete'
        )->name('deleteItem');
});

Route::group([
    "prefix" => "user",
    "controller" => UserController::class],
    function() {
        Route::get(
            '/{id}',
            'getById'
        )->name('getUserById');

        Route::get(
            '/{email}/{password}',
            'getByEmailAndPassword'
        )->name('getUserByEmailAndPassword');

        Route::post(
            '',
            'create'
        )->name('createUser');

        Route::put(
            '/{user}',
            'update'
        )->name('updateUser');

        Route::delete(
            '/{user}',
            'delete'
        )->name('deleteUser');
});
