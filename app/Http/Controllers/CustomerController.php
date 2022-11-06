<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

use App\Models\Customer;

use App\Exceptions\UnexpectedErrorException;

class CustomerController extends Controller {

    /**
     * Returns a http response with the customer 
     * with the given id or a not found status code (404).
     * 
     * @param int $id The given id to search for the Customer.
     * @return JsonResponse A http response with the customer with the given id.
     */
    public function getById(int $id): JsonResponse {
        $customer = Customer::findByIdOrFail($id);

        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

    /**
     * Returns a http response with the customer with the given email and password.
     * 
     * @param string $email The given email to search for the Customer.
     * @param string $password The given password to search for the Customer.
     * @return JsonResponse A http response with the Customer with the given email and password.
     */
    public function getByEmailAndPassword(string $email, string $password): JsonResponse {
        $customer = Customer::findByEmailAndPasswordOrFail($email, $password);
        
        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

    /**
     * Returns a http response with the customer with the given id and its orders.
     * 
     * @param int $id The given id to search for the customer.
     * @return JsonResponse A http response with the Customer with 
     * the given id and its orders.
     */
    public function getCustomerAndOrdersByCustomerId(int $id): JsonResponse {
        $customer = Customer::findByIdOrFail($id);
        $customer->appendOrders();
        
        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

    public function getCustomerAndPaidOrdersByCustomerId(int $id): JsonResponse {
        $customer = Customer::findByIdOrFail($id);
        $customer->appendPaidOrders();
        
        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

    /**
     * Creates a customer with the data in the 
     * given request and returns it in a http response.
     * 
     * @param Request $request The request holding the customer data.
     * @return JsonResponse A http response with the newly created Customer.
     */
    public function create(Request $request): JsonResponse {
        Customer::validateCreateRequest($request);

        return $this->createCustomer($request);
    }

    /**
     * Creates a customer with the data in the given request.
     * 
     * @param Request $request The request holding the customer data.
     * @return JsonResponse A http response with the newly created Customer.
     */
    private function createCustomer(Request $request): JsonResponse {
        $customer = Customer::create($request->all());

        if (!$customer)
            throw new UnexpectedErrorException();

        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

    /**
     * Updates a customer with the data in the 
     * given request and returns it in a http response.
     * 
     * @param Request $request The request holding the customer new data.
     * @param Customer $customer The customer to be updated.
     * @return JsonResponse A http response with the updated Customer.
     */
    public function update(Request $request, Customer $customer): JsonResponse {
        Customer::validateUpdateRequest($request, $customer);

        return $this->updateCustomer($request, $customer);
    }

    /**
     * Updates the given customer with the data in the given request.
     * 
     * @param Request $request The request holding the customer new data.
     * @param Customer $customer The customer that will be updates.
     * @return JsonResponse A http response with the updated Customer.
     */
    private function updateCustomer(Request $request, Customer $customer): JsonResponse {
        $customer->update($request->all());
        
        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

    /**
     * Deletes the given customer and returns a response
     * with a http no content status code (204).
     * 
     * @param Customer $customer The customer to be deleted.
     * @return JsonResponse A http response with no content.
     */
    public function delete(Customer $customer): JsonResponse {
        $customer->delete();

        return parent::createJsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
