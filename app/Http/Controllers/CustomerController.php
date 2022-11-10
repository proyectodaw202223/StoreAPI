<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

use App\Models\Customer;

class CustomerController extends Controller {

    public function getById(int $id): JsonResponse {
        $customer = Customer::findByIdOrFail($id);

        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

    public function getByEmailAndPassword(string $email, string $password): JsonResponse {
        $customer = Customer::findByEmailAndPasswordOrFail($email, $password);
        
        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

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

    public function create(Request $request): JsonResponse {
        $customer = Customer::createCustomer($request);

        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

    public function update(Request $request, Customer $customer): JsonResponse {
        $customer = Customer::updateCustomer($request, $customer);

        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

    public function delete(Customer $customer): JsonResponse {
        $customer->delete();

        return parent::createJsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
