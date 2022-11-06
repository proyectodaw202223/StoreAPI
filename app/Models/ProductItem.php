<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductItem extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Finds and returns the ProductItem with the given id.
     * 
     * @param int $id The given id to search for the ProductItem.
     * @return ProductItem The productItem with the given id.
     */
    public static function findById(int $id): ProductItem {
        $item = DB::table('product_items')->find($id);

        return ProductItem::hydrate([$item])[0];
    }

    /**
     * Finds and appends the Product related to the current 
     * ProductItem as a new attribute named 'product'.
     */
    public function appendProduct(): void {
        $product = Product::findById($this->productId);
        $this->product = $product;
    }
}
