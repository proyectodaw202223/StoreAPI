<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\ProductItemImage;

class ProductItem extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function findById(int $id): ProductItem {
        $item = DB::table('product_items')->find($id);

        return ProductItem::hydrate([$item])[0];
    }

    public static function findItemsByProductId(int $productId): array {
        $items = DB::table('product_items')
            ->where('productId', '=', $productId)
            ->get();

        return ProductItem::hydrate($items->toArray())->all();
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
