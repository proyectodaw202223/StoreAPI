<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * The Product associated with an Item,
     * this variable is meant to be used to parse 
     * Item data alongside its Product to json 
     * when an Item resource is requested to the API.
     * 
     * @var Product
     */
    private Product $product;

    /**
     * An array of ProductItemImages associated with an Item,
     * this variable is meant to be used to parse 
     * Item data alongside its ProductItemImages to json 
     * when an Item resource is requested to the API.
     * 
     * @var Array
     */
    private Array $productItemImages;

    /**
     * The SeasonalSaleLine associated with an Item,
     * this variable is meant to be used to parse 
     * Item data alongside its SeasonalSaleLine to json 
     * when an Item resource is requested to the API.
     * 
     * @var SeasonalSaleLine
     */
    private SeasonalSaleLine $seasonalSaleLine;

    public function getProduct() {
        return $this->product;
    }

    public function setProduct(Product $product) {
        $this->product = $product;
    }

    public function getProductItemImages() {
        return $this->productItemImages;
    }

    public function setProductItemImages(Array $productItemImages) {
        $this->productItemImages = $productItemImages;
    }

    public function getSeasonalSaleLine() {
        return $this->seasonalSaleLine;
    }

    public function setSeasonalSaleLine(SeasonalSaleLine $seasonalSaleLine) {
        $this->seasonalSaleLine = $seasonalSaleLine;
    }
}
