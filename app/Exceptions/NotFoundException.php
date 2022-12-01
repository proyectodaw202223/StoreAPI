<?php

namespace App\Exceptions;

use Illuminate\Http\Response;

class NotFoundException extends CustomException {

    function __construct() {
        $this->message = "The requested resource could not be found.";
        $this->code = Response::HTTP_NOT_FOUND;
    }
}
