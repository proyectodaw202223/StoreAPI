<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

use Exception;

use App\Models\Customer;
use App\Exceptions\CustomException;
use App\Exceptions\UnexpectedErrorException;

class CustomerController extends Controller {

    public function getById(int $id): JsonResponse {
        try {
            return $this->getCustomerById($id);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getCustomerById(int $id): JsonResponse {
        $customer = Customer::findByIdOrFail($id);

        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

    public function getByEmailAndPassword(Request $request): JsonResponse {
        try {
            return $this->getCustomerByEmailAndPassword($request);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getCustomerByEmailAndPassword(Request $request): JsonResponse {
        $requestData = $request->all();
        $customer = Customer::findByEmailAndPasswordOrFail($requestData['email'], $requestData['password']);
        
        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

    public function getCustomerAndOrdersByCustomerId(int $id): JsonResponse {
        try {
            return $this->getCustomerAndOrders($id);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getCustomerAndOrders(int $customerId): JsonResponse {
        $customer = Customer::findByIdOrFail($customerId);
        $customer->appendOrders();
        
        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

    public function getCustomerAndPaidOrdersByCustomerId(int $id): JsonResponse {
        try {
            return $this->getCustomerAndPaidOrders($id);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getCustomerAndPaidOrders(int $customerId): JsonResponse {
        $customer = Customer::findByIdOrFail($customerId);
        $customer->appendPaidOrders();
        
        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

    public function getCustomerAndCreatedOrderByCustomerId(int $id): JsonResponse {
        try {
            return $this->getCustomerAndCreatedOrder($id);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getCustomerAndCreatedOrder(int $customerId): JsonResponse {
        $customer = Customer::findByIdOrFail($customerId);
        $customer->appendCreatedOrder();

        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

    public function create(Request $request): JsonResponse {
        try {
            return $this->createCustomer($request);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function createCustomer(Request $request): JsonResponse {
        $customerData = $request->all();
        $customer = Customer::createCustomer($customerData);

        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

    public function update(Request $request, Customer $customer): JsonResponse {
        try {
            return $this->updateCustomer($request, $customer);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function updateCustomer(Request $request, Customer $customer): JsonResponse {
        $customerData = $request->all();
        $customer = Customer::updateCustomer($customerData, $customer);

        return parent::createJsonResponse($customer, Response::HTTP_OK);
    }

    public function delete(Customer $customer): JsonResponse {
        try {
            return $this->deleteCustomer($customer);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function deleteCustomer(Customer $customer): JsonResponse {
        Customer::deleteCustomer($customer);

        return parent::createJsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
