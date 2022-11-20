<?php

namespace App\Exceptions;

use Illuminate\Http\Response;

class OrderAmountMismatchException extends CustomException {

    function __construct($orderAmount, $orderLinesAmount) {
        $this->message = sprintf(
            "The amount of the order %01.2f must be equal to the sum of the line amounts %01.2f.",
            $orderAmount,
            $orderLinesAmount
        );
        
        $this->code = Response::HTTP_NOT_FOUND;
    }
}
