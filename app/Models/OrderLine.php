<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Exceptions\UnexpectedErrorException;
use App\Exceptions\OrderLinePriceMismatchException;
use App\Exceptions\OrderLineAmountMismatchException;
use App\Exceptions\OrderLineAlreadyExistsException;

class OrderLine extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function createOrderLinesFromArray(array $orderLinesData, int $orderId): array {
        $createdOrderLines = [];
        $orderItems = [];
        
        DB::beginTransaction();

        foreach ($orderLinesData as $lineData) {
            if (array_search($lineData['itemId'], $orderItems) !== false)
                throw OrderLineAlreadyExistsException::makeNewFromArray($lineData);

            $lineData['orderId'] = $orderId;
            $orderLine = self::createOrderLine($lineData);
            array_push($createdOrderLines, $orderLine);
            array_push($orderItems, $orderLine->itemId);
        }

        DB::commit();

        return $createdOrderLines;
    }

    public static function createOrderLine(array $orderLineData): OrderLine {
        self::validateOrderLineData($orderLineData);
        $orderLine = OrderLine::create($orderLineData);

        if (!$orderLine)
            throw new UnexpectedErrorException();
        
        return $orderLine;
    }

    public static function validateOrderLineData(array $orderLineData): void {
        self::validateLinePriceMatchesItemPrice($orderLineData);
        self::validateAmountMatchesPriceSum($orderLineData);
    }

    private static function validateLinePriceMatchesItemPrice(array $orderLineData): void {
        $item = ProductItem::findById($orderLineData['itemId']);
        $product = Product::findById($item->productId);
        $order = Order::findById($orderLineData['orderId']);
        $seasonalSaleLine = SeasonalSaleLine::findByItemIdAndDateTime($item->id, $order['paymentDateTime']);

        if ($seasonalSaleLine)
            $expectedPriceWithDiscount = $product->price - $product->price * ($seasonalSaleLine->discountPercentage / 100);
        else
            $expectedPriceWithDiscount = $product->price;
        
        if ($orderLineData['priceWithDiscount'] != $expectedPriceWithDiscount)
            throw new OrderLinePriceMismatchException($orderLineData['priceWithDiscount'], $expectedPriceWithDiscount);
    }

    private static function validateAmountMatchesPriceSum(array $orderLineData): void {
        $expectedAmount = $orderLineData['priceWithDiscount'] * $orderLineData['quantity'];

        if ($orderLineData['amount'] != $expectedAmount)
            throw new OrderLineAmountMismatchException($orderLineData['amount'], $expectedAmount);
    }

    public static function updateOrderLinesFromArray(array $orderLinesToUpdate, int $orderId): array {
        $updatedOrderLines = [];
        $orderItems = [];

        foreach ($orderLinesToUpdate as $lineToUpdate) {
            if (array_search($lineToUpdate['itemId'], $orderItems) !== false)
                throw OrderLineAlreadyExistsException::makeNewFromArray($lineToUpdate);

            if (isset($lineToUpdate['id']))
                $orderLine = self::updateOrderLine($lineToUpdate);
            else
                $orderLine = self::createOrderLine($lineToUpdate);

            array_push($updatedOrderLines, $orderLine);
            array_push($orderItems, $orderLine->itemId);
        }

        self::deleteOrderLinesWhereNotIn($updatedOrderLines, $orderId);
        
        return $updatedOrderLines;
    }

    public static function updateOrderLine(array $orderLineData): OrderLine {
        self::validateOrderLineData($orderLineData);
        
        $orderLine = self::findById($orderLineData['id']);
        $orderLine->update($orderLineData);

        if (!$orderLine)
            throw new UnexpectedErrorException();

        return $orderLine;
    }

    private static function deleteOrderLinesWhereNotIn(array $lines, int $orderId): void {
        $lineIds = [];

        foreach ($lines as $line)
            array_push($lineIds, $line->id);

        DB::table('order_lines')
            ->where('orderId', '=', $orderId)
            ->whereNotIn('id', $lineIds)
            ->delete();
    }

    public static function findById(int $id): OrderLine {
        $orderLine = DB::table('order_lines')->find($id);

        return OrderLine::hydrate([$orderLine])[0];
    }

    public static function findOrderLinesByOrderId(int $orderId): array {
        $orderLines = DB::table('order_lines')
            ->where('orderId', '=', $orderId)
            ->get();

        return OrderLine::hydrate($orderLines->toArray())->all();
    }

    public static function appendItemToOrderLinesArray(array $orderLines, string $saleDateTime): array {
        foreach ($orderLines as $orderLine) {
            $orderLine->appendItem($saleDateTime);
        }

        return $orderLines;
    }

    public function appendItem(string $saleDateTime): void {
        $productItem = ProductItem::findById($this->itemId);
        $productItem->appendProduct();
        $productItem->appendSale($saleDateTime);
        
        $this->productItem = $productItem;
    }
}
