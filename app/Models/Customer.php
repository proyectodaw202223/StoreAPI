<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * An array of orders associated with a Customer,
     * this variable is meant to be used to parse 
     * Customer data alongside its Orders to json 
     * when a Customer resource is requested to the API.
     * 
     * @var Array
     */
    private Array $orders;

    public function getOrders() {
        return $this->orders;
    }

    public function setOrders(Array $orders) {
        $this->orders = $orders;
    }
}
