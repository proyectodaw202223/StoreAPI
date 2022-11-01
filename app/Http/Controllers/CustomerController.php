<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;

class CustomerController extends Controller
{
    /**
     * Returns a http response with the customer 
     * with the given id or a not found status code (404).
     * 
     * @param int $id The given id to search for the Customer.
     * @return Illuminate\Http\Response A http response with the customer
     * with the given id or a not found (404) status code if it doesn't exist.
     */
    public function getById($id) {
        $customer = DB::table('customers')->find($id);

        if ($customer) {
            $response = parent::createResponse($customer, parent::HTTP_OK_CODE);
        } else {
            $response = parent::createResponse(null, parent::HTTP_NOT_FOUND_CODE);
        }

        return $response;
    }

    /**
     * Returns a http response with the customer with the given email 
     * and password or a not found status code (404) if it doesn't exist.
     * 
     * @param string $email The given email to search for the Customer.
     * @param string $password The given password to search for the Customer.
     * @return Illuminate\Http\Response A http response with the Customer with 
     * the given email and password or a not found (404) status code if it doesn't exist.
     */
    public function getByEmailAndPassword($email, $password) {
        $customer = DB::table('customers')
            ->where('email', $email)
            ->where('password', $password)
            ->get()[0];

        if ($customer) {
            $response = parent::createResponse($customer, parent::HTTP_OK_CODE);
        } else {
            $response = parent::createResponse(null, parent::HTTP_NOT_FOUND_CODE);
        }
        
        return $response;
    }

    public function existsCustomerByEmail($email) {
        // TODO: Return true/false
    }

    /**
     * Returns a http response with the customer with the given id and its orders
     * or a not found status code (404) if the customer doesn't exist.
     * 
     * @param int $id The given id to search for the customer.
     * @return Illuminate\Http\Response A http response with the Customer with 
     * the given id and its orders or a not found status code (404) if it doesn't exist.
     */
    public function getCustomerOrdersByCustomerId($id) {
        $customer = DB::table('customers')->find($id);

        if ($customer) {
            $orders = self::getOrdersByCustomerId($customer->id);

            $customer->orders = $orders;

            $response = parent::createResponse($customer, parent::HTTP_OK_CODE);
        } else {
            $response = parent::createResponse(null, parent::HTTP_NOT_FOUND_CODE);
        }

        return $response;
    }

    private function getOrdersByCustomerId($customerId) {
        return DB::table('orders')
                ->where('customerId', '=', $customerId)
                ->get();
    }

    public function getCustomerAndActiveOrderByCustomerId($id) {
        // TODO: Customer + Order + O. Lines + Item + Product
    }

    /**
     * Creates a customer with the data in the 
     * given request and returns it in a http response.
     * 
     * @param Illuminate\Http\Request $request The request holding the customer data.
     * @return Illuminate\Http\Response A http response with the newly created Customer
     * or an internal server error status code (500) if the customer couldn't be created.
     */
    public function create(Request $request) {
        $customer = Customer::create($request->all());

        if ($customer) {
            $response = parent::createResponse($customer, parent::HTTP_OK_CODE);
        } else  {
            $response = parent::createResponse(null, parent::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }

    /**
     * Updates a customer with the data in the 
     * given request and returns it in a http response.
     * 
     * @param Illuminate\Http\Request $request The request holding the new customer data.
     * @param App\Models\Customer $customer The customer to be updated.
     * @return Illuminate\Http\Response A http response with the newly created Customer
     * or an internal server error status code (500) if the customer couldn't be created.
     */
    public function update(Request $request, Customer $customer) {
        $customer->update($request->all());

        if ($customer) {
            $response = parent::createResponse($customer, parent::HTTP_OK_CODE);
        } else  {
            $response = parent::createResponse(null, parent::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }

    /**
     * Deletes the given customer and returns a response
     * with a http no content status code (204).
     * 
     * @param Illuminate\Http\Request $request The request holding the new customer data.
     * @param App\Models\Customer $customer The customer to be updated.
     * @return Illuminate\Http\Response A http response with the newly created Customer
     * or an internal server error status code (500) if the customer couldn't be created.
     */
    public function delete(Customer $customer) {
        $customer->delete();

        return parent::createResponse(null, parent::HTTP_NO_CONTENT_CODE);
    }
}
