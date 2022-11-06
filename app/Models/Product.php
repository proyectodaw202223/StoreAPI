<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Finds and returns the Product with the given id.
     * 
     * @param int $id The given id to search for the Product.
     * @return Product The Product with the given id.
     */
    public static function findById(int $id): Product {
        $product = DB::table('products')->find($id);

        return Product::hydrate([$product])[0];
    }
}
