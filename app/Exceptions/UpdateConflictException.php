<?php

namespace App\Exceptions;

use Illuminate\Http\Response;

class UpdateConflictException extends CustomException {

    function __construct() {
        $this->message = "There is a newer version of the resource in the server.";
        $this->code = Response::HTTP_BAD_REQUEST;
    }
}
