<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use DateTime;
use Exception;

use App\Exceptions\NotFoundException;
use App\Exceptions\ResourceAlreadyExistsException;
use App\Exceptions\RestrictedDeletionException;
use App\Exceptions\InvalidUpdateException;
use App\Exceptions\UpdateConflictException;
use App\Models\ProductItemImage;
use App\Enums\ProductItemSize;

class ProductItem extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function createItem(array $itemData): ProductItem {
        $itemData = self::unsetProductFromItemData($itemData);
        $itemData = self::unsetImagesFromItemData($itemData);
        self::validateItemDataOnCreate($itemData);

        if (!isset($itemData['stock']))
            $itemData['stock'] = 0;

        $item = ProductItem::create($itemData);
        
        return $item;
    }

    private static function unsetProductFromItemData(array $itemData): array {
        if (isset($itemData['product']))
            unset($itemData['product']);

        return $itemData;
    }

    private static function unsetImagesFromItemData(array $itemData): array {
        if (isset($itemData['images']))
            unset($itemData['images']);

        return $itemData;
    }

    public static function validateItemDataOnCreate(array $itemData): void {
        self::validateRequiredDataIsSetOnCreate($itemData);
        self::validateIfItemAlreadyExistsOnCreate($itemData);
    }

    private static function validateRequiredDataIsSetOnCreate(array $itemData): void {
        if (!isset($itemData['productId']) ||
            !isset($itemData['color']) ||
            !isset($itemData['size'])) {
            throw new InvalidUpdateException();
        }
    }

    private static function validateIfItemAlreadyExistsOnCreate(array $itemData): void {
        $item = self::findByProductIdColorAndSize(
            $itemData['productId'],
            $itemData['color'],
            ProductItemSize::from($itemData['size'])
        );

        if ($item->id) {
            throw new ResourceAlreadyExistsException();
        }
    }

    public static function findByProductIdColorAndSize(
        int $productId, string $color, ProductItemSize $size): ProductItem {

        $item = DB::table('product_items')
            ->where('productId', '=', $productId)
            ->where('color', '=', $color)
            ->where('size', '=', $size)
            ->first();

        return ProductItem::hydrate([$item])[0];
    }

    public static function updateItem(array $itemData, ProductItem $item): ProductItem {
        $itemData = self::unsetProductFromItemData($itemData);
        $itemData = self::unsetImagesFromItemData($itemData);
        self::validateItemDataOnUpdate($itemData, $item);
        $item->update($itemData);

        return $item;
    }

    public static function validateItemDataOnUpdate(array $itemData, ProductItem $item): void {
        self::validateRequiredDataIsSetOnUpdate($itemData);
        self::validateUpdateConflict($itemData, $item);
        self::validateIfItemAlreadyExistsOnUpdate($itemData);
    }

    private static function validateRequiredDataIsSetOnUpdate(array $itemData): void {
        if (!isset($itemData['id']) ||
            !isset($itemData['productId']) ||
            !isset($itemData['color']) ||
            !isset($itemData['size']) ||
            !isset($itemData['updated_at'])) {
            throw new InvalidUpdateException();
        }
    }

    private static function validateUpdateConflict(array $itemData, ProductItem $item): void {
        $currentUpdatedAt = new DateTime($item['updated_at']);
        $requestUpdatedAt = new DateTime($itemData['updated_at']);

        if ($currentUpdatedAt > $requestUpdatedAt) {
            throw new UpdateConflictException();
        }
    }

    public static function findById(int $id): ProductItem {
        $item = DB::table('product_items')->find($id);

        return ProductItem::hydrate([$item])[0];
    }

    private static function validateIfItemAlreadyExistsOnUpdate(array $itemData): void {
        $item = self::findByProductIdColorAndSize(
            $itemData['productId'],
            $itemData['color'],
            ProductItemSize::from($itemData['size'])
        );

        if ($item->id && $item->id != $itemData['id']) {
            throw new ResourceAlreadyExistsException();
        }
    }

    public static function deleteItem(ProductItem $item): void {
        try {
            $item->delete();
        } catch (Exception $e) {
            throw new RestrictedDeletionException();
        }
    }

    public static function findByIdOrFail(int $id): ProductItem {
        $item = DB::table('product_items')->find($id);

        if (!$item)
            throw new NotFoundException();

        return ProductItem::hydrate([$item])[0];
    }

    public static function findAllItems(): array {
        $items = DB::table('product_items')->get();

        return ProductItem::hydrate($items->toArray())->all();
    }

    public static function findItemsByProductId(int $productId): array {
        $items = DB::table('product_items')
            ->where('productId', '=', $productId)
            ->get();

        return ProductItem::hydrate($items->toArray())->all();
    }

    public static function appendProductToItemsArray(array $items): array {
        foreach ($items as $item) {
            $item->appendProduct();
        }

        return $items;
    }

    public function appendProduct(): void {
        $product = Product::findById($this->productId);
        $this->product = $product;
    }

    public static function appendImagesToItemsArray(array $items): array {
        foreach ($items as $item) {
            $item->appendImages();
        }

        return $items;
    }

    public function appendImages(): void {
        $images = ProductItemImage::findImagesByItemId($this->id);
        $this->images = $images;
    }

    public static function appendSaleToItemsArray(array $items, string $saleDateTime): array {
        foreach ($items as $item) {
            $item->appendSale($saleDateTime);
        }

        return $items;
    }

    public function appendSale(string $saleDateTime): void {
        $seasonalSaleLine = SeasonalSaleLine::findByItemIdAndDateTime($this->id, $saleDateTime);

        if (isset($seasonalSaleLine->id)) {
            $seasonalSale = SeasonalSale::findById($seasonalSaleLine->seasonalSaleId);
            $seasonalSale->lines = [$seasonalSaleLine];
            $this->sale = $seasonalSale;
        } else {
            $this->sale = null;
        }
    }
}
