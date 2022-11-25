<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use App\Models\Customer;

class ResourceAlreadyExistsException extends CustomException {

    function __construct() {
        $this->message = "The resource already exists.";
        $this->code = Response::HTTP_BAD_REQUEST;
    }
}
