<?php

namespace App\Exceptions;

use Illuminate\Http\Response;

class InvalidUpdateException extends CustomException {

    function __construct() {
        $this->message = "An invalid update attempt was made.";
        $this->code = Response::HTTP_BAD_REQUEST;
    }
}
