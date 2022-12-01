<?php

namespace App\Exceptions;

use Illuminate\Http\Response;

class OrderLinePriceMismatchException extends CustomException {

    function __construct($linePrice, $expectedPrice) {
        $this->message = sprintf(
            "The price of the order line %01.2f must be equal to the price of the item %01.2f.",
            $linePrice,
            $expectedPrice
        );
        
        $this->code = Response::HTTP_NOT_FOUND;
    }
}
