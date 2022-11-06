<?php

namespace App\Exceptions;

use Illuminate\Http\Response;

class UnexpectedErrorException extends CustomException {

    function __construct() {
        $this->message = "An unexpected error occurred in the server.";
        $this->code = Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
