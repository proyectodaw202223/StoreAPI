<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductItemImage extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function findImagesByItemId(int $itemId): array {
        $images = DB::table('product_item_images')
            ->where('itemId', '=', $itemId)
            ->get();

        return ProductItemImage::hydrate($images->toArray())->all();
    }
}
