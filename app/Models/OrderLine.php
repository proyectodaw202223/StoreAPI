<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderLine extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Finds and returns all the order lines of the order with the given id.
     * 
     * @param int $orderId The id of the order used to search for the order lines.
     * @return array An array containing all the order lines of the given order.
     */
    public static function findOrderLinesByOrderId(int $orderId): array {
        $orderLines = DB::table('order_lines')
            ->where('orderId', '=', $orderId)
            ->get();

        return OrderLine::hydrate($orderLines->toArray())->all();
    }

    /**
     * Finds the item of all the order lines in the given Collection and
     * appends the item in an attribute called item.
     * 
     * @param array $orderLines An array of order lines to which the items will be appended.
     * @return array The given array of order lines with their items appended.
     */
    public static function appendItemToOrderLinesArray(array $orderLines): array {
        foreach ($orderLines as $orderLine) {
            $orderLine->appendItem();
        }

        return $orderLines;
    }

    /**
     * Finds and appends the ProductItem related to the current
     * OrderLine as a new attribute named 'productItem'.
     */
    public function appendItem(): void {
        $productItem = ProductItem::findById($this->itemId);
        $productItem->appendProduct();
        $this->productItem = $productItem;
    }
}
