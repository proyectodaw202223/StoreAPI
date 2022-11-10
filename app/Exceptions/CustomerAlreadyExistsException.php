<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use App\Models\Customer;

class CustomerAlreadyExistsException extends CustomException {

    function __construct(Customer $existingCustomer) {
        $this->message = "The customer with email ".$existingCustomer->email." already exists.";
        $this->code = Response::HTTP_BAD_REQUEST;
    }
}
