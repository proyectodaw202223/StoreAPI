<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Exception;

use App\Enums\OrderStatus;
use App\Exceptions\NotFoundException;
use App\Exceptions\OrderAmountMismatchException;
use App\Exceptions\RestrictedDeletionException;
use App\Exceptions\UnexpectedErrorException;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function createOrder(array $orderData): Order {
        DB::beginTransaction();
        self::validateOrderData($orderData);

        if (key_exists('lines', $orderData)) {
            $orderLinesData = $orderData['lines'];
            unset($orderData['lines']);
        }

        if (key_exists('customer', $orderData)) {
            unset($orderData['customer']);
        }
        
        $order = Order::create($orderData);

        if (!$order)
            throw new UnexpectedErrorException();

        $order->lines = OrderLine::createOrderLinesFromArray($orderLinesData, $order->id);
        DB::commit();

        return $order;
    }

    public static function validateOrderData(array $orderData): void {
        self::validateOrderAmountMatchesLineAmounts($orderData);
    }

    private static function validateOrderAmountMatchesLineAmounts(array $orderData): void {
        $linesAmount = 0;

        foreach ($orderData['lines'] as $line) {
            $linesAmount += $line['amount'];
        }

        if ($linesAmount != $orderData['amount'])
            throw new OrderAmountMismatchException($orderData['amount'], $linesAmount);
    }

    public static function updateOrder(array $orderData, Order $order): Order {
        DB::beginTransaction();
        self::validateOrderData($orderData);

        if (key_exists('lines', $orderData)) {
            $orderLinesData = $orderData['lines'];
            unset($orderData['lines']);
        }

        if (key_exists('customer', $orderData)) {
            unset($orderData['customer']);
        }

        $order->update($orderData);

        if (!$order)
            throw new UnexpectedErrorException();

        $order->lines = OrderLine::updateOrderLinesFromArray($orderLinesData, $order->id);
        DB::commit();

        return $order;
    }

    public static function deleteOrder(Order $order): void {
        try {
            $order->delete();
        } catch (Exception $e) {
            throw new RestrictedDeletionException();
        }
    }

    public static function findByIdOrFail(int $id): Order {
        $order = DB::table('orders')->find($id);

        if (!$order)
            throw new NotFoundException();

        return Order::hydrate([$order])[0];
    }

    public static function findById(int $id): Order {
        $order = DB::table('orders')->find($id);

        return Order::hydrate([$order])[0];
    }

    public static function findAllOrders(): array {
        $orders = DB::table('orders')->get();

        return Order::hydrate($orders->toArray())->all();
    }

    public static function findOrdersByStatus(OrderStatus $status): array {
        $orders = DB::table('orders')
            ->where('status', '=', $status->value)
            ->get();

        return Order::hydrate($orders->toArray())->all();
    }

    public static function findOrdersByCustomerId(int $customerId): array {
        $orders = DB::table('orders')
            ->where('customerId', '=', $customerId)
            ->get();

        return Order::hydrate($orders->toArray())->all();
    }

    public static function findPaidOrdersByCustomerId(int $customerId): array {
        $orders = DB::table('orders')
            ->where('customerId', '=', $customerId)
            ->where('status', '=', OrderStatus::PAID->value)
            ->get();

        return Order::hydrate($orders->toArray())->all();
    }

    public static function findCreatedOrderByCustomerId(int $customerId): Order {
        $order = DB::table('orders')
            ->where('customerId', '=', $customerId)
            ->where('status', '=', OrderStatus::CREATED->value)
            ->first();

        return Order::hydrate([$order])[0];
    }

    public static function appendOrderLinesToOrdersArray(array $orders): array {
        foreach ($orders as $order) {
            $order->appendOrderLines();
        }

        return $orders;
    }

    public function appendOrderLines(): void {
        $orderLines = OrderLine::findOrderLinesByOrderId($this->id);
        $orderLines = OrderLine::appendItemToOrderLinesArray($orderLines);
        $this->lines = $orderLines;
    }

    public static function appendCustomerToOrdersArray(array $orders): array {
        foreach ($orders as $order) {
            $order->appendCustomer();
        }

        return $orders;
    }

    public function appendCustomer(): void {
        $this->customer = Customer::findById($this->customerId);
    }
}
