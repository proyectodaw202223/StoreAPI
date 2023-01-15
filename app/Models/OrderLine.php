<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use DateTime;
use Exception;

use App\Exceptions\UnexpectedErrorException;
use App\Exceptions\OrderLinePriceMismatchException;
use App\Exceptions\OrderLineAmountMismatchException;
use App\Exceptions\InvalidUpdateException;
use App\Exceptions\RestrictedDeletionException;
use App\Exceptions\UpdateConflictException;
use App\Exceptions\ResourceAlreadyExistsException;

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
                throw new ResourceAlreadyExistsException();

            $lineData['orderId'] = $orderId;
            $orderLine = self::createOrderLine($lineData);
            array_push($createdOrderLines, $orderLine);
            array_push($orderItems, $orderLine->itemId);
        }

        DB::commit();

        return $createdOrderLines;
    }

    public static function createOrderLine(array $orderLineData): OrderLine {
        $orderLineData = self::unsetItemFromOrderLineData($orderLineData);
        self::validateOrderLineDataOnCreate($orderLineData);
        $orderLine = OrderLine::create($orderLineData);
        
        return $orderLine;
    }

    private static function unsetItemFromOrderLineData(array $orderLineData): array {
        if (isset($orderLineData['productItem']))
            unset($orderLineData['productItem']);

        return $orderLineData;
    }

    public static function validateOrderLineDataOnCreate(array $orderLineData): void {
        self::validateRequiredDataIsSetOnCreate($orderLineData);
        self::validateLinePriceMatchesItemPrice($orderLineData);
        self::validateAmountMatchesPriceSum($orderLineData);
    }

    private static function validateRequiredDataIsSetOnCreate(array $orderLineData): void {
        if (!isset($orderLineData['orderId']) ||
            !isset($orderLineData['itemId']) ||
            !isset($orderLineData['quantity']) ||
            !isset($orderLineData['priceWithDiscount']) ||
            !isset($orderLineData['amount'])) {
            throw new InvalidUpdateException();
        }
    }

    private static function validateLinePriceMatchesItemPrice(array $orderLineData): void {
        $item = ProductItem::findById($orderLineData['itemId']);
        $product = Product::findById($item->productId);
        $order = Order::findById($orderLineData['orderId']);
        $discountDateTime = ($order->paymentDateTime == "") ? date('Y-m-d H:i:s') : $order->paymentDateTime;
        $seasonalSaleLine = SeasonalSaleLine::findByItemIdAndDateTime($item->id, $discountDateTime);

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
                throw new ResourceAlreadyExistsException();

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
        $orderLine = self::findById($orderLineData['id']);

        $orderLineData = self::unsetItemFromOrderLineData($orderLineData);
        self::validateOrderLineDataOnUpdate($orderLineData, $orderLine);
        $orderLine->update($orderLineData);

        return $orderLine;
    }

    public static function validateOrderLineDataOnUpdate(
        array $orderLineData, OrderLine $orderLine): void {
        self::validateRequiredDataIsSetOnUpdate($orderLineData);
        self::validateUpdateConflict($orderLineData, $orderLine);
        self::validateLinePriceMatchesItemPrice($orderLineData);
        self::validateAmountMatchesPriceSum($orderLineData);
    }

    private static function validateRequiredDataIsSetOnUpdate(array $orderLineData): void {
        if (!isset($orderLineData['id']) ||
            !isset($orderLineData['orderId']) ||
            !isset($orderLineData['itemId']) ||
            !isset($orderLineData['quantity']) ||
            !isset($orderLineData['priceWithDiscount']) ||
            !isset($orderLineData['amount']) ||
            !isset($orderLineData['updated_at'])) {
            throw new InvalidUpdateException();
        }
    }

    private static function validateUpdateConflict(
        array $orderLineData, OrderLine $orderLine): void {
        $currentUpdatedAt = new DateTime($orderLine['updated_at']);
        $requestUpdatedAt = new DateTime($orderLineData['updated_at']);

        if ($currentUpdatedAt > $requestUpdatedAt) {
            throw new UpdateConflictException();
        }
    }

    public static function deleteOrderLinesWhereNotIn(array $lines, int $orderId): void {
        $lineIds = [];

        foreach ($lines as $line)
            array_push($lineIds, $line->id);

        try {
            DB::table('order_lines')
                ->where('orderId', '=', $orderId)
                ->whereNotIn('id', $lineIds)
                ->delete();
        } catch (Exception $e) {
            throw new RestrictedDeletionException();
        }
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

    public static function existsOrderLineByItemId(int $itemId): bool {
        $orderLine = DB::table('order_lines')
            ->where('itemId', '=', $itemId)
            ->first();

        return ($orderLine) ? true : false;
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
        $productItem->appendImages();
        
        $this->productItem = $productItem;
    }
}
