<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * An array of Items associated with a Product,
     * this variable is meant to be used to parse 
     * Product data alongside its Items to json 
     * when a Product resource is requested to the API.
     * 
     * @var Array
     */
    private Array $items;

    public function getItems() {
        return $this->items;
    }

    public function setItems(Array $items) {
        $this->items = $items;
    }
}
