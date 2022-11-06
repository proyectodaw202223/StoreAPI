<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Enums\OrderStatus;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Finds and returns all the orders of the customer with the given id.
     * 
     * @param int $customerId The id of the customer used to search for the orders.
     * @return array An array containing the orders of the customer with the given id.
     */
    public static function findOrdersByCustomerId(int $customerId): array {
        $orders = DB::table('orders')
            ->where('customerId', '=', $customerId)
            ->get();

        return Order::hydrate($orders->toArray())->all();
    }

    /**
     * Finds and returns all active orders of the customer with the given id.
     * 
     * @param int $customerId The id of the customer used to search for the orders.
     * @return array An array containing all the paid orders of the given customer.
     */
    public static function findPaidOrdersByCustomerId(int $customerId): array {
        $orders = DB::table('orders')
            ->where('customerId', '=', $customerId)
            ->where('status', '=', OrderStatus::Pagado->name)
            ->get();

        return Order::hydrate($orders->toArray())->all();
    }

    /**
     * Finds the order lines of the orders in the given Collection and 
     * appends an array containing the lines in an attribute named lines.
     * 
     * @param array $orders An array of orders to which the lines will be appended.
     * @return array The given array of orders with their lines appended.
     */
    public static function appendOrderLinesToOrdersArray(array $orders): array {
        foreach ($orders as $order) {
            $order->appendOrderLines();
        }

        return $orders;
    }

    /**
     * Finds and appends the OrderLines relates to the current 
     * Order as a new attribute named 'lines'.
     */
    public function appendOrderLines(): void {
        $orderLines = OrderLine::findOrderLinesByOrderId($this->id);
        $orderLines = OrderLine::appendItemToOrderLinesArray($orderLines);
        $this->lines = $orderLines;
    }
}
