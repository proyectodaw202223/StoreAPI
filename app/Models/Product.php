<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Exception;

use App\Exceptions\NotFoundException;
use App\Exceptions\RestrictedDeletionException;
use App\Exceptions\UnexpectedErrorException;

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
        $productData = self::removeExtraData($productData);
        $product = Product::create($productData);

        if (!$product)
            throw new UnexpectedErrorException();

        return $product;
    }

    private static function removeExtraData(array $productData): array {
        if (isset($productData['items'])) {
            unset($productData['items']);
        }

        return $productData;
    }

    public static function updateProduct(array $productData, Product $product): Product {
        $productData = self::removeExtraData($productData);
        $product->update($productData);

        if (!$product)
            throw new UnexpectedErrorException();

        return $product;
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

        $this->items = $items;
    }
}
