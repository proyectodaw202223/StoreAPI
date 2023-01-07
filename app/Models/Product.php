<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use DateTime;
use Exception;

use App\Exceptions\NotFoundException;
use App\Exceptions\RestrictedDeletionException;
use App\Exceptions\UnexpectedErrorException;
use App\Exceptions\InvalidUpdateException;
use App\Exceptions\ResourceAlreadyExistsException;
use App\Exceptions\UpdateConflictException;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function createProduct(array $productData): Product {
        $productData = self::unsetItemsFromProductData($productData);
        self::validateProductDataOnCreate($productData);

        $product = Product::create($productData);

        if (!$product)
            throw new UnexpectedErrorException();

        return $product;
    }

    private static function unsetItemsFromProductData(array $productData): array {
        if (isset($productData['productItems']))
            unset($productData['productItems']);

        return $productData;
    }

    public static function validateProductDataOnCreate(array $productData): void {
        self::validateRequiredDataIsSetOnCreate($productData);
        self::validateIfProductAlreadyExistsOnCreate($productData);
    }

    private static function validateRequiredDataIsSetOnCreate(array $productData): void {
        if (!isset($productData['name']) ||
            !isset($productData['price']) ||
            !isset($productData['category']) ||
            !isset($productData['description'])) {
            throw new InvalidUpdateException();
        }
    }

    private static function validateIfProductAlreadyExistsOnCreate(array $productData): void {
        $product = self::findByName($productData['name']);

        if ($product->id) {
            throw new ResourceAlreadyExistsException();
        }
    }

    public static function findByName(string $name): Product {
        $product = DB::table('products')
            ->where('name', '=', $name)
            ->first();

        return Product::hydrate([$product])[0];
    }

    public static function updateProduct(array $productData, Product $product): Product {
        self::validateProductDataOnUpdate($productData, $product);

        $product->update($productData);

        if (!$product)
            throw new UnexpectedErrorException();

        return $product;
    }

    public static function validateProductDataOnUpdate(array $productData, Product $product): void {
        self::validateRequiredDataIsSetOnUpdate($productData);
        self::validateUpdateConflict($productData, $product);
        self::validateIfProductAlreadyExistsOnUpdate($productData);
    }

    private static function validateRequiredDataIsSetOnUpdate(array $productData): void {
        if (!isset($productData['id']) ||
            !isset($productData['name']) ||
            !isset($productData['price']) ||
            !isset($productData['category']) ||
            !isset($productData['description']) ||
            !isset($productData['updated_at'])) {
            throw new InvalidUpdateException();
        }
    }

    private static function validateUpdateConflict(array $productData, Product $product): void {
        $currentUpdatedAt = new DateTime($product['updated_at']);
        $requestUpdatedAt = new DateTime($productData['updated_at']);

        if ($currentUpdatedAt > $requestUpdatedAt) {
            throw new UpdateConflictException();
        }
    }

    private static function validateIfProductAlreadyExistsOnUpdate(array $productData): void {
        $product = self::findByName($productData['name']);

        if ($product->id && $product->id != $productData['id']) {
            throw new ResourceAlreadyExistsException();
        }
    }

    public static function deleteProduct(Product $product): void {
        try {
            $product->delete();
        } catch (Exception $e) {
            throw new RestrictedDeletionException();
        }
    }

    public static function findByIdOrFail(int $id): Product {
        $product = DB::table('products')->find($id);

        if (!$product)
            throw new NotFoundException();

        return Product::hydrate([$product])[0];
    }

    public static function findById(int $id): Product {
        $product = DB::table('products')->find($id);

        return Product::hydrate([$product])[0];
    }

    public static function findAllProducts(): array {
        $products = DB::table('products')->get();

        return Product::hydrate($products->toArray())->all();
    }

    public static function findNewProductsLimit(int $limit): array {
        $products = DB::table('products')
            ->latest()
            ->limit($limit)
            ->get();

        return Product::hydrate($products->toArray())->all();
    }

    public static function findNewProducts(): array {
        $products = DB::table('products')
            ->latest()
            ->get();

        return Product::hydrate($products->toArray())->all();
    }

    public static function findProductsForSaleLimit(int $limit): array {
        $currentDateTime = date('Y-m-d H:i:s');
        $products = DB::select(
            DB::raw(
                "SELECT * FROM products WHERE ".
                    "id IN (".
                        "SELECT productId FROM product_items WHERE ".
                            "id IN (".
                                "SELECT itemId FROM seasonal_sale_lines WHERE ".
                                    "seasonalSaleId IN (".
                                        "SELECT id FROM seasonal_sales WHERE ".
                                            "isCanceled = ? AND ".
                                            "validFromDateTime <= ? AND ".
                                            "validToDateTime >= ? ".
                                        ")".
                                ")".
                        ")".
                    "LIMIT ?"
            ),
            [0, $currentDateTime, $currentDateTime, $limit]
        );

        return Product::hydrate($products)->all();
    }

    public static function findProductsForSale(): array {
        $currentDateTime = date('Y-m-d H:i:s');
        $products = DB::select(
            DB::raw(
                "SELECT * FROM products WHERE ".
                    "id IN (".
                        "SELECT productId FROM product_items WHERE ".
                            "id IN (".
                                "SELECT itemId FROM seasonal_sale_lines WHERE ".
                                    "seasonalSaleId IN (".
                                        "SELECT id FROM seasonal_sales WHERE ".
                                            "isCanceled = ? AND ".
                                            "validFromDateTime <= ? AND ".
                                            "validToDateTime >= ? ".
                                        ")".
                                ")".
                        ")"
            ),
            [0, $currentDateTime, $currentDateTime]
        );

        return Product::hydrate($products)->all();
    }

    public static function appendItemsToProductsArray(array $products): array {
        foreach ($products as $product) {
            $product->appendItems();
        }

        return $products;
    }

    public function appendItems(): void {
        $items = ProductItem::findItemsByProductId($this->id);
        $items = ProductItem::appendImagesToItemsArray($items);
        $items = ProductItem::appendSaleToItemsArray($items, date('Y-m-d H:i:s'));

        $this->productItems = $items;
    }
}
