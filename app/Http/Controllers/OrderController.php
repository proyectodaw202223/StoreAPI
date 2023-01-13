<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

use Exception;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Exceptions\CustomException;
use App\Exceptions\UnexpectedErrorException;
use App\Models\OrderLine;

class OrderController extends Controller
{
    public function getById(int $id): JsonResponse {
        try {
            return $this->getOrderById($id);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getOrderById(int $id): JsonResponse {
        $order = Order::findByIdOrFail($id);
        $order->appendCustomer();
        $order->appendOrderLines();

        return parent::createJsonResponse($order, Response::HTTP_OK);
    }

    public function getAll(): JsonResponse {
        try {
            return $this->getAllOrders();
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getAllOrders(): JsonResponse {
        $orders = Order::findAllOrders();
        $orders = Order::appendCustomerToOrdersArray($orders);
        $orders = Order::appendOrderLinesToOrdersArray($orders);

        return parent::createJsonResponse($orders, Response::HTTP_OK);
    }

    public function getByStatus(OrderStatus $status): JsonResponse {
        try {
            return $this->getOrdersByStatus($status);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getOrdersByStatus(OrderStatus $status): JsonResponse {
        $orders = Order::findOrdersByStatus($status);
        $orders = Order::appendCustomerToOrdersArray($orders);
        $orders = Order::appendOrderLinesToOrdersArray($orders);

        return parent::createJsonResponse($orders, Response::HTTP_OK);
    }

    public function create(Request $request): JsonResponse {
        try {
            return $this->createOrder($request);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function createOrder(Request $request): JsonResponse {
        $requestData = $request->all();
        $order = Order::createOrder($requestData);
        $order->lines = OrderLine::appendItemToOrderLinesArray($order->lines, $order->paymentDateTime);
        $order->appendCustomer();
        
        return parent::createJsonResponse($order, Response::HTTP_OK);
    }

    public function update(Request $request, Order $order): JsonResponse {
        try {
            return $this->updateOrder($request, $order);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function updateOrder(Request $request, Order $order): JsonResponse {
        $requestData = $request->all();
        $order = Order::updateOrder($requestData, $order);
        $order->lines = OrderLine::appendItemToOrderLinesArray($order->lines, $order->paymentDateTime);
        $order->appendCustomer();

        return parent::createJsonResponse($order, Response::HTTP_OK);
    }

    public function delete(Order $order): JsonResponse {
        try {
            return $this->deleteOrder($order);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function deleteOrder(Order $order): JsonResponse {
        Order::deleteOrder($order);

        return parent::createJsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
