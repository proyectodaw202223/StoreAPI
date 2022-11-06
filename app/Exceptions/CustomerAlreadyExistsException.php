<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use App\Models\Customer;

class CustomerAlreadyExistsException extends CustomException {

    /**
     * @param Customer $customer The customer that was found in the database.
     */
    function __construct($customer) {
        $this->message = "The customer with email ".$customer->email." already exists.";
        $this->code = Response::HTTP_BAD_REQUEST;
    }
}
