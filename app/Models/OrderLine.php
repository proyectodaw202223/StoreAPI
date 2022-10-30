<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLine extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The Item associated with an OrderLine,
     * this variable is meant to be used to parse 
     * OrderLine data alongside its Item to json 
     * when an OrderLine resource is requested to the API.
     * 
     * @var Item
     */
    private Item $item;

    public function getItem() {
        return $this->item;
    }

    public function setItem(Iem $item) {
        $this->item = $item;
    }
}
