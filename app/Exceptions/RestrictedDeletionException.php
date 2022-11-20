<?php

namespace App\Exceptions;

use Illuminate\Http\Response;

class RestrictedDeletionException extends CustomException {

    function __construct() {
        $this->message = "The deletion of the requested resource ".
            "is restricted due to a dependency of another resource.";
        $this->code = Response::HTTP_BAD_REQUEST;
    }
}
