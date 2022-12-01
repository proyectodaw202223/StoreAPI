<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use DateTime;
use Exception;

use App\Enums\OrderStatus;
use App\Exceptions\NotFoundException;
use App\Exceptions\OrderAmountMismatchException;
use App\Exceptions\RestrictedDeletionException;
use App\Exceptions\UnexpectedErrorException;
use App\Exceptions\InvalidUpdateException;
use App\Exceptions\UpdateConflictException;

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
        $orderData = self::unsetCustomerFromOrderData($orderData);

        if (isset($orderData['lines'])) {
            $orderLinesData = $orderData['lines'];
            unset($orderData['lines']);
        }

        self::validateOrderDataOnCreate($orderData);
        $order = Order::create($orderData);

        if (isset($orderLinesData))
            $order->lines = OrderLine::createOrderLinesFromArray($orderLinesData, $order->id);
            
        DB::commit();

        return $order;
    }

    private static function unsetCustomerFromOrderData(array $orderData): array {
        if (isset($orderData['customer']))
            unset($orderData['customer']);

        return $orderData;
    }

    public static function validateOrderDataOnCreate(array $orderData): void {
        self::validateRequiredDataIsSetOnCreate($orderData);
        self::validateOrderAmountMatchesLineAmounts($orderData);
    }

    private static function validateRequiredDataIsSetOnCreate(array $orderData): void {
        if (!isset($orderData['customerId']) ||
            !isset($orderData['amount']) ||
            !isset($orderData['paymentDateTime']) ||
            !isset($orderData['status'])) {
            throw new InvalidUpdateException();
        }
    }

    private static function validateOrderAmountMatchesLineAmounts(array $orderData): void {
        $linesAmount = 0;

        foreach ($orderData['lines'] as $line) {
            $linesAmount += $line['amount'];
        }

        if ($linesAmount != $orderData['amount']) {
            throw new OrderAmountMismatchException($orderData['amount'], $linesAmount);
        }
    }

    public static function updateOrder(array $orderData, Order $order): Order {
        DB::beginTransaction();
        $orderData = self::unsetCustomerFromOrderData($orderData);
        
        if (isset($orderData['lines'])) {
            $orderLinesData = $orderData['lines'];
            unset($orderData['lines']);
        }

        self::validateOrderDataOnUpdate($orderData, $order);
        $order->update($orderData);

        if (isset($orderLinesData)) {
            $order->lines = OrderLine::updateOrderLinesFromArray($orderLinesData, $order->id);
        } else {
            OrderLine::deleteOrderLinesWhereNotIn([], $order->id);
            $order->lines = [];
        }

        DB::commit();

        return $order;
    }

    public static function validateOrderDataOnUpdate(array $orderData, Order $order): void {
        self::validateRequiredDataIsSetOnUpdate($orderData);
        self::validateUpdateConflict($orderData, $order);
        self::validateOrderAmountMatchesLineAmounts($orderData);
    }

    private static function validateRequiredDataIsSetOnUpdate(array $orderData): void {
        if (!isset($orderData['customerId']) ||
            !isset($orderData['amount']) ||
            !isset($orderData['paymentDateTime']) ||
            !isset($orderData['status']) ||
            !isset($orderData['updated_at'])) {
            throw new InvalidUpdateException();
        }
    }

    private static function validateUpdateConflict(array $orderData, Order $order): void {
        $currentUpdatedAt = new DateTime($order['updated_at']);
        $requestUpdatedAt = new DateTime($orderData['updated_at']);

        if ($currentUpdatedAt > $requestUpdatedAt) {
            throw new UpdateConflictException();
        }
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
        $orderLines = OrderLine::appendItemToOrderLinesArray($orderLines, $this->paymentDateTime);
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
