<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The Customer associated with an Order,
     * this variable is meant to be used to parse 
     * Order data alongside its Customer to json 
     * when an Order resource is requested to the API.
     * 
     * @var Customer
     */
    private Customer $customer;
    
    /**
     * An array of OrderLines associated with an Order,
     * this variable is meant to be used to parse 
     * Order data alongside its OrderLines to json 
     * when an Order resource is requested to the API.
     * 
     * @var Array
     */
    private Array $orderLines;

    public function getCustomer() {
        return $this->customer;
    }

    public function setCustomer(Customer $customer) {
        $this->customer = $customer;
    }

    public function getOrderLines() {
        return $this->orderLines;
    }

    public function setOrderLines(Array $orderLines) {
        $this->orderLines = $orderLines;
    }
}
